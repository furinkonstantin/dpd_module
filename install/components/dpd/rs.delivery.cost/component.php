<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

if(!empty($arParams)){
		$weight = 1;
    CModule::IncludeModule('sale');

    $module_id = "rocketstudio.dpdext";
    $shopCity = COption::GetOptionString($module_id, "SENDER_ADDRESS_CITY");
    //Получения способов доставки
    $dbResult = CSaleDeliveryHandler::GetList(
        array(
            'SORT' => 'ASC',
            'NAME' => 'ASC'
        ),
        array(
            'ACTIVE' => 'Y'
        )
    );
		$arWeight = array();
    while ($ob = $dbResult->GetNext()){
				$arWeight[] = $ob["CONFIG"]["CONFIG"]["WEIGHT"]["VALUE"];
        $arResult['DH'][] = strtoupper(substr($ob['SID'], -3));
    }
    //Отправка запроса ДПД
    $DPD_service = new DPD_service();
    $arData = array(
        'pickup' => array(
            'cityName' => $shopCity,
        ),
        'delivery' => array(
            'cityName' => $arParams['CITY'],
        ),
        'selfPickup' => false,
        'selfDelivery' =>false,
        'weight' => $weight,
       'serviceCode' => implode(',', $arResult['DH']),
    );
    $arResult['COST'] = $DPD_service->getServiceCost($arData);
		if (!empty($arResult['COST'])) {
			if(!is_array($arResult["COST"][0])) {
				$arResult["COST"] = array($arResult["COST"]);
			}
		}
		if (!empty($arResult['COST'])) {
			$i = 0;
			foreach($arResult['COST'] as $arItem) {
				$arItem["WEIGHT"] = $arWeight[$i];
				if (empty($arItem["WEIGHT"])) {
					$arItem["WEIGHT"] = 1;
				}
				$arResult['COST'][$i] = $arItem;
				$i++;
			}
		}
}

$this->IncludeComponentTemplate();