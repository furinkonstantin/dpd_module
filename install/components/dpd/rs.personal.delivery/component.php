<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

if(empty($_REQUEST['FUNCTION'])){
    CModule::IncludeModule("sale");
		$order = CSaleOrder::GetById($arParams['ORDER_ID']);
		$deliveryCode = CRocketstudioDPDExtOrder::getCodeDelivery($order["DELIVERY_ID"]);
		$date = CRocketstudioDPDExtOrder::setFormatDateForDPD($order["DATE_INSERT"]);
		$arResult['DPD']["DATE_INSERT"] = $date;
		if (!empty($arParams["orderNumberInternal"]) && !empty($arParams['datePickup'])) {
			$arResult['DPD'] = CRocketstudioDPDExtOrder::UpdateStatusOrderDPD($arParams['orderNumberInternal']);
		}
}else{
    $arResult["DPD"] = CRocketstudioDPDExtOrder::$_REQUEST['FUNCTION']($arParams);
		CRocketstudioDPDExtOrder::UpdateStatusByOrderId($arParams['orderNumberInternal'],  $arResult["DPD"]["status"]);
}
if ($deliveryCode == "dpd" || $arParams["IS_DPD"]) {
	$this->IncludeComponentTemplate();
}