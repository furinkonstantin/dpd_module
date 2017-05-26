<?

IncludeModuleLangFile(__FILE__);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/rocketstudio.dpdext/delivery/dpd/dpd_service.class.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/rocketstudio.dpdext/install/index.php');
class CRocketstudioDPDExtOrder
{

    private static $module_id = 'rocketstudio.dpdext';
		
		static $statusDPD = "STATUS_DPD";
		static $terminalCode = "TERMINAL_CODE";
		//static $infoTerminal = "INFO_TERMINAL";
		static $orderDPDId = "ORDER_DPD_ID";
		
		static $cargo_properties = array(
			"serviceCode",
			"serviceVariant",
			"cargoNumPack",
			"cargoWeight", 
			"cargoCategory",
			"cargoValue",
			"cargoRegistered",
			"datePickup",
			"pickupTimePeriod"
		);
		static $sale_properties = array(
			'name',
			'terminalCode',
			'countryName',
			'region',
			'city',
			'street',
			'streetAbbr',
			'house',
			'houseKorpus',
			'str',
			'vlad',
			'office',
			'flat',
			'contactFio',
			'contactPhone'
		);
		
		static function SetDateForDPD($date) {
			return date("Y-m-d",strtotime($date));
		}
		
		static function UpdateStatusByOrderId($ORDER_ID, $statusText) {
			$db_vals = CSaleOrderPropsValue::GetList(
				array("SORT" => "ASC"),
				array(
						"ORDER_ID" => $ORDER_ID,
						"CODE" => self::$statusDPD
				)
			);
      $arVals = $db_vals->Fetch();
			if ($arVals["VALUE"] != GetMessage($statusText)) {
				CSaleOrderPropsValue::Update($arVals["ID"], array("VALUE"=>GetMessage($statusText)));
			}
		}
		
		static function CancelOrder($arData){
				$cancelArr = array(
						"cancel" => array(
								"orderNum" => $arData['orderNum'],
								"orderNumberInternal" => $arData['orderNumberInternal']
						)
				);
				$dpdService = new DPD_service;
				$res = $dpdService->cancelOrder($cancelArr);
				return $res;
		}
				
		static function setFormatDateForDPD($date) {
			$res = explode(" ", $date);
			$res = $res[0];
			return $res;
		}
		
		static function getCodeDelivery($deliveryId) {
			$delivery = explode(":",$deliveryId);
			$res = explode("_", $delivery[0]);
			return $res[0];
		}
		
		static function GetCurrentOrderForSite($orderId) {
			$res = CSaleOrder::GetById($orderId);
			return $res;
		}
		
		static function GetCurrentOrderPropsForSite($orderId) {
			$db_props = CSaleOrderPropsValue::GetList(
        array("SORT" => "ASC"),
        array(
					"ORDER_ID" => $orderId
				)
			);
			$res = array();
			while($arProp = $db_props->GetNext()) {
				$res[$arProp["ORDER_PROPS_ID"]] = $arProp["VALUE"];
			}
			return $res;
		}
		
		// static function setTemplateForInfoTerminal($value) {
			// $templateForInfoTerminal = COption::GetOptionString(self::$module_id, "TEMPLATE_FOR_INFO_TERMINAL");
			// $res = $templateForInfoTerminal;
			// foreach($value as $keyTerminal=>$terminalValue) {
				// $res = str_replace("#".$keyTerminal."#", $terminalValue, $res);
			// }
			// return $res;
		// }
		
		static function getDeliveryWeightByHandler($sid) {
			$arResult = CSaleDeliveryHandler::GetList(
					array(
							'SORT' => 'ASC',
							'NAME' => 'ASC'
					),
					array(
							'ACTIVE' => 'Y',
							'SID'=>$sid
					)
			)->Fetch();
			$res = $arResult["CONFIG"]["CONFIG"]["WEIGHT"]["VALUE"];
			return $res;
		}
		
		static function GetLocationForOrder($orderId) {
			$allProps = self::GetCurrentOrderPropsForSite($orderId);
			foreach($allProps as $propId=>$arPropValue) {
				$arOrderProps = CSaleOrderProps::GetByID($propId);
				if ($arOrderProps["IS_LOCATION"] == "Y") {
					$res = $arPropValue;
					break;
				}
			}
			return $res;
		}
		
		static function SetOrderDPDForSaleProperty($orderId, $orderNum, $personTypeId) {
			$salePropertyOrderDPDId = self::getSalePropertyByCodeAndPersonTypeId(self::$orderDPDId, $personTypeId);
			$props[$salePropertyOrderDPDId["ID"]] = $orderNum;
			self::UpdateInfoDPD($orderId, $props);
		}
		
		static function UpdateStatusOrderDPD($orderId) {
			$arOrderData = self::CompleteDataOrder($orderId);
			$arData = array(
				"order"=>array(
					"orderNumberInternal"=>$orderId,
					"datePickup"=>self::SetDateForDPD($arOrderData['ORDER_PROPS_FOR_DETAIL']['datePickup'])
				) 
			);
			$dpdService = new DPD_service;
			$getOrder = $dpdService->getOrderStatus($arData);
			self::SetOrderDPDForSaleProperty($orderId, $getOrder->return->orderNum, $arOrderData["PERSON_TYPE_ID"]); 
			$res = $getOrder;
			self::UpdateStatusByOrderId($orderId, $getOrder->return->status);
			return $res;
		}
		
		static function GetOrderWeight($orderId) {
			$dbBasketItems = CSaleBasket::GetList(array("ID" => "ASC"), array("ORDER_ID" => $orderId));
			$res = 0;
			while($arItem = $dbBasketItems->GetNext()) {
				$res += $arItem["WEIGHT"] * $arItem["QUANTITY"];
			}
			return $res;
		}
		
		static function CompleteDataOrder($orderId) {
			$arOrder = CSaleOrder::GetById($orderId);
			$personTypeId = $arOrder["PERSON_TYPE_ID"];
			$res = array();
			$allProps = self::GetCurrentOrderPropsForSite($orderId);
			
			//Получаем номер заказа DPD из детального заказа
			$salePropertyOrderDPDId = self::getSalePropertyByCodeAndPersonTypeId(self::$orderDPDId, $personTypeId);
			$res[self::$orderDPDId] = $allProps[$salePropertyOrderDPDId["ID"]];
			
			$res["DATE_PICKUP"] = self::setFormatDateForDPD($arOrder["DATE_INSERT"]);			
			$res["PICKUP_TIME_PERIOD"] = COption::GetOptionString(self::$module_id, "PICKUP_TIME_PERIOD");
			$res["PERSON_TYPE_ID"] = $arOrder["PERSON_TYPE_ID"];
			
			//Данные получателя
			if (!empty($_REQUEST[self::$terminalCode])) {
				$terminalCode = $_REQUEST[self::$terminalCode];
			}
			if (!empty($_REQUEST["terminalCode"])) {
				$terminalCode = $_REQUEST["terminalCode"];
			}
			
			//Код TERMINAL_CODE
			$salePropertyTerminalCode = self::getSalePropertyByCodeAndPersonTypeId(self::$terminalCode,$personTypeId);
			if (empty($terminalCode)) {
				$salePropertyTerminalCode = self::getSalePropertyByCodeAndPersonTypeId(self::$terminalCode,$personTypeId);
				$terminalCode = $allProps[$salePropertyTerminalCode["ID"]];
			}

			//Код INFO_TERMINAL
			// $salePropertyInfoTerminal = self::getSalePropertyByCodeAndPersonTypeId(self::$infoTerminal,$personTypeId);
			
			$terminals = CDeliveryDPD::GetTerminals();
			$res["TERMINALS"] = $terminals;
			$receiverData = array();
			$dateProperties = array();
			foreach($terminals as $arTerminal) {
				if ($arTerminal["terminalCode"] == $terminalCode) {
					$res["TERMINAL_NAME"] = $arTerminal["terminalName"];
					// $res[self::$infoTerminal] = self::setTemplateForInfoTerminal($arTerminal);
					// $dateProperties[$salePropertyInfoTerminal["ID"]] = $res[self::$infoTerminal];
				}
			}
			$cargoProperties = self::$cargo_properties;
			foreach(self::$sale_properties as $saleProperty) {
				if ($saleProperty == "terminalCode") {
					$receiverData[$saleProperty] = $terminalCode;
					$res[self::$terminalCode] = $terminalCode;
					$dateProperties[$salePropertyTerminalCode["ID"]] = $terminalCode;
				}
				$prop = COption::GetOptionString(self::$module_id, $saleProperty."_".$personTypeId);
				if (!empty($prop)) {
					$receiverData[$saleProperty] = $allProps[$prop];
					$dateProperties[$prop] = $allProps[$prop];
				}
				if (!empty($_POST["ORDER_MAKE"])) {
					$receiverData[$saleProperty] = $_POST[$saleProperty];
					$dateProperties[$prop] = $_POST[$saleProperty];
				}
			}
			
			$delivery = explode(":",$arOrder["DELIVERY_ID"]);
			$serviceCode = strtoupper(substr($delivery[0], -3));
			$serviceVariant = strtoupper(substr($delivery[1], -2));
			
			switch($serviceVariant) {
				case "TD": $serviceVariant = GetMessage("ROCKETSTUDIO_DPDEXT_DPD_TD");break;
				case "DD": $serviceVariant = GetMessage("ROCKETSTUDIO_DPDEXT_DPD_DD");break;
				case "DT": $serviceVariant = GetMessage("ROCKETSTUDIO_DPDEXT_DPD_DT");break;
				case "TT": $serviceVariant = GetMessage("ROCKETSTUDIO_DPDEXT_DPD_TT");break;
			}

			$cargoNumPack = COption::GetOptionString(self::$module_id, "CARGO_NUMPACK");
				
			$cargoWeight = self::getDeliveryWeightByHandler($delivery[0]);
			$cargoCategory = COption::GetOptionString(self::$module_id, "CARGO_CATEGORY");
			$orderWeight = self::GetOrderWeight($orderId);
			if (!empty($orderWeight)) {
				$cargoWeight = CSaleMeasure::Convert($orderWeight, "G", "KG");
			}
			if (empty($cargoWeight)) {
				$cargoWeight = 1;
			}

			foreach($cargoProperties as $cargoProperty) {
				$prop = COption::GetOptionString(self::$module_id, $cargoProperty."_".$personTypeId);
				if (!empty($prop)) {
					if ($cargoProperty == "datePickup") {
						$cargoData[$cargoProperty] = self::SetDateForDPD($allProps[$prop]);
						$dateProperties[$prop] = self::SetDateForDPD($allProps[$prop]);
					} else {
						$cargoData[$cargoProperty] = $allProps[$prop];
					}
					if (empty($allProps[$prop])) {
						switch($cargoProperty) {
							case 'datePickup': 
								$dateProperties[$prop] = self::SetDateForDPD($res["DATE_PICKUP"]);
								$cargoData[$cargoProperty] = self::SetDateForDPD($res["DATE_PICKUP"]);
							break;
							case 'cargoWeight': 
								$dateProperties[$prop] = $cargoWeight;
								$cargoData[$cargoProperty] = $cargoWeight;
							break;
							case 'serviceCode': 
								$dateProperties[$prop] = $serviceCode;
								$cargoData[$cargoProperty] = $serviceCode;
							break;
							case 'serviceVariant': 
								$dateProperties[$prop] = $serviceVariant;
								$cargoData[$cargoProperty] = $serviceVariant;
							break;
							case 'cargoNumPack': 
								$dateProperties[$prop] = $cargoNumPack;
								$cargoData[$cargoProperty] = $cargoNumPack;
							break;
							case 'cargoCategory': 
								$dateProperties[$prop] = $cargoCategory;
								$cargoData[$cargoProperty] = $cargoCategory;
							break;
							case 'cargoValue': 
								$dateProperties[$prop] = $arOrder["PRICE"];
								$cargoData[$cargoProperty] = $arOrder["PRICE"];
							break;
							case 'cargoRegistered': 
								$dateProperties[$prop] = $cargoRegistered;
								$cargoData[$cargoProperty] = $cargoRegistered;
							break;
							case 'pickupTimePeriod': 
								$dateProperties[$prop] = $res["PICKUP_TIME_PERIOD"];
								$cargoData[$cargoProperty] = $res["PICKUP_TIME_PERIOD"];
							break;
						}
					}
				}

				if (!empty($_POST["ORDER_MAKE"])) {
					if ($cargoProperty == "datePickup") {
						$cargoData[$cargoProperty] = self::SetDateForDPD($_POST[$cargoProperty]);
						$dateProperties[$prop] = self::SetDateForDPD($_POST[$cargoProperty]);
					} else {
						$cargoData[$cargoProperty] = $_POST[$cargoProperty];
						$dateProperties[$prop] = $_POST[$cargoProperty];
					}
				}
			}
			if (!empty($_POST["ORDER_MAKE"])) {
				$res["DATE_PICKUP"] = self::SetDateForDPD($_POST["datePickup"]);
				$res["PICKUP_TIME_PERIOD"] = $_POST["pickupTimePeriod"];
			}
			$locationId = self::GetLocationForOrder($orderId);
			$arLoc = CSaleLocation::GetByID($locationId);
			$receiverData["countryName"] = $arLoc["COUNTRY_NAME"];
			$receiverData["region"] = $arLoc["REGION_NAME"];
			$receiverData["city"] = $arLoc["CITY_NAME"];
			$res["RECEIVER_DATA"] = $receiverData;
			$res["CARGO_DATA"] = $cargoData;
			$res["CARGO_DATA"]["datePickup"] = $res["DATE_PICKUP"];
			$res["ORDER_PROPS"] = $dateProperties;
			$res["ORDER_PROPS_FOR_DETAIL"] = array_merge($receiverData, $cargoData);
			return $res;
		}

		function CreateNewOrder($ORDER_ID, $orderArr) {	
			global $APPLICATION;
			$arOrder = CSaleOrder::GetById($ORDER_ID);
			$deliveryCode = self::getCodeDelivery($arOrder["DELIVERY_ID"]);
			$module = new rocketstudio_dpdext;
			if ($deliveryCode == "dpd") {
				$arData = self::CompleteDataOrder($ORDER_ID);
				
				$personTypeId = $arData["PERSON_TYPE_ID"];//Тип плательщика
				//Данные получателя
				$receiverAddress = $arData["RECEIVER_DATA"];
				
				//Данные отправителя
				$senderAddress = array();
				$senderAddress['name'] = COption::GetOptionString(self::$module_id, "SENDER_ADDRESS_NAME");
				
				$senderAddress['terminalCode'] = COption::GetOptionString(self::$module_id, "SENDER_ADDRESS_TERMINAL");
				
				$senderAddress['contactFio'] = COption::GetOptionString(self::$module_id, "SENDER_ADDRESS_FIO");
				
				$senderAddress['countryName'] = GetMessage("ROCKETSTUDIO_DPDEXT_DPD_ORDER_ROSSIA");
				
				$senderAddress['region'] = COption::GetOptionString(self::$module_id, "SENDER_ADDRESS_REGION");
				
				$senderAddress['contactPhone'] = COption::GetOptionString(self::$module_id, "SENDER_ADDRESS_PHONE");
				
				$senderAddress['city'] = COption::GetOptionString(self::$module_id, "SENDER_ADDRESS_CITY");
				
				$senderAddress['street'] = COption::GetOptionString(self::$module_id, "SENDER_ADDRESS_STREET");
				
				$senderAddress['streetAbbr'] = COption::GetOptionString(self::$module_id, "SENDER_ADDRESS_STREET_ABBR");
				
				$senderAddress['house'] = COption::GetOptionString(self::$module_id, "SENDER_ADDRESS_HOUSE");
				
				$senderAddress['houseKorpus'] = COption::GetOptionString(self::$module_id, "SENDER_ADDRESS_HOUSEKORPUS");
				
				$senderAddress['str'] = COption::GetOptionString(self::$module_id, "SENDER_ADDRESS_STR");
				
				$senderAddress['vlad'] = COption::GetOptionString(self::$module_id, "SENDER_ADDRESS_VLAD");

				$senderAddress['office'] = COption::GetOptionString(self::$module_id, "SENDER_ADDRESS_OFFICE");
				
				$senderAddress['flat'] = COption::GetOptionString(self::$module_id, "SENDER_ADDRESS_FLAT");

				$arOrder_DPD = array(
						'header' => array(
							'datePickup' => $arData["CARGO_DATA"]["datePickup"],//'2014-03-25'
							'pickupTimePeriod' => $arData["CARGO_DATA"]["pickupTimePeriod"],//9-18, 9-13, 13-18
							'senderAddress'=>$senderAddress
						),
						'order' => array(
							'orderNumberInternal' => $ORDER_ID,
							'serviceCode' => $arData["CARGO_DATA"]["serviceCode"],//'PCL',
							'serviceVariant' => $arData["CARGO_DATA"]["serviceVariant"],//'ТТ',
							'cargoNumPack' => $arData["CARGO_DATA"]["cargoNumPack"],//'1',
							'cargoWeight' => $arData["CARGO_DATA"]["cargoWeight"],//'2',
							'cargoRegistered' => $arData["CARGO_DATA"]["cargoRegistered"],
							'cargoCategory' => $arData["CARGO_DATA"]["cargoCategory"],//'КПБ',
							'cargoValue' => $arData["CARGO_DATA"]["cargoValue"],//'1000'
							'receiverAddress' => $receiverAddress,
						),
				);
				$delivery = explode(":",$arOrder["DELIVERY_ID"]);
				if(substr($delivery[1], -2, 1) == "D")
					$selfPickup = false; //от двери
				else
					$selfPickup = true; //от терминала

				if(substr($delivery[1], -1) == "D")
					$selfDelivery = false; //до двери
				else
					$selfDelivery = true; //до терминала
					
				if ($selfPickup) {
					unset($arOrder_DPD["header"]["senderAddress"]["region"]);
					unset($arOrder_DPD["header"]["senderAddress"]["flat"]);
					unset($arOrder_DPD["header"]["senderAddress"]["vlad"]);
					unset($arOrder_DPD["header"]["senderAddress"]["str"]);
					unset($arOrder_DPD["header"]["senderAddress"]["houseKorpus"]);
					unset($arOrder_DPD["header"]["senderAddress"]["streetAbbr"]);
					unset($arOrder_DPD["header"]["senderAddress"]["street"]);
					unset($arOrder_DPD["header"]["senderAddress"]["city"]);
					unset($arOrder_DPD["header"]["senderAddress"]["house"]);
					unset($arOrder_DPD["header"]["senderAddress"]["office"]);
					unset($arOrder_DPD["header"]["senderAddress"]["countryName"]);
				}
				if ($selfDelivery) {
					unset($arOrder_DPD["order"]["receiverAddress"]["region"]);
					unset($arOrder_DPD["order"]["receiverAddress"]["flat"]);
					unset($arOrder_DPD["order"]["receiverAddress"]["vlad"]);
					unset($arOrder_DPD["order"]["receiverAddress"]["str"]);
					unset($arOrder_DPD["order"]["receiverAddress"]["street"]);
					unset($arOrder_DPD["order"]["receiverAddress"]["houseKorpus"]);
					unset($arOrder_DPD["order"]["receiverAddress"]["streetAbbr"]);
					unset($arOrder_DPD["order"]["receiverAddress"]["house"]);
					unset($arOrder_DPD["order"]["receiverAddress"]["office"]);
					unset($arOrder_DPD["order"]["receiverAddress"]["city"]);
					unset($arOrder_DPD["order"]["receiverAddress"]["countryName"]);
				}
				
				if (!$selfDelivery) {
					unset($arOrder_DPD["order"]["receiverAddress"]["terminalCode"]);
				}
				if (!$selfPickup) {
					unset($arOrder_DPD["header"]["senderAddress"]["terminalCode"]);
				}
				if (empty($arOrder_DPD["order"]["cargoRegistered"]) || $arOrder_DPD["order"]["cargoRegistered"] == "N") {
					$arOrder_DPD["order"]["cargoRegistered"] = false;
				} else {
					$arOrder_DPD["order"]["cargoRegistered"] = true;
				}
				
				if (COption::GetOptionString(self::$module_id, "SCENARIO_DPD") == 1 || preg_match("!\/bitrix\/admin\/!", $APPLICATION->GetCurPage(false))) {
					$DPD_service = new DPD_service;
					$arOrderDPD = $DPD_service->createOrder($arOrder_DPD);
					if (!empty($arOrderDPD["orderNum"])) {
						//Сохраняем номер заказа DPD в свойство заказа
						$salePropertyOrderDPDId = self::getSalePropertyByCodeAndPersonTypeId(self::$orderDPDId, $personTypeId);
						$orderArr["ORDER_PROP"][$salePropertyOrderDPDId["ID"]] = $arOrderDPD["orderNum"];
						$arData["ORDER_PROPS"][$salePropertyOrderDPDId["ID"]] = $arOrderDPD["orderNum"];
					}
					
					$salePropertyStatusDPD = self::getSalePropertyByCodeAndPersonTypeId(self::$statusDPD, $personTypeId);
					if ($salePropertyStatusDPD) {
						$orderArr["ORDER_PROP"][$salePropertyStatusDPD["ID"]] = GetMessage($arOrderDPD["status"]);
						$arData["ORDER_PROPS"][$salePropertyStatusDPD["ID"]] = GetMessage($arOrderDPD["status"]);
					}
					$salePropertyTerminalCode = self::getSalePropertyByCodeAndPersonTypeId(self::$terminalCode,$personTypeId);
					$orderArr["ORDER_PROP"][$salePropertyTerminalCode["ID"]] = $arData[self::$terminalCode];
				}
				if (!preg_match("!\/bitrix\/admin\/!", $APPLICATION->GetCurPage(false))) {
					$salePropertyCode = self::getSalePropertyByCodeAndPersonTypeId("CARGO_WEIGHT", $personTypeId);
					$orderArr["ORDER_PROP"][$salePropertyCode["ID"]] = $arData["CARGO_DATA"]["cargoWeight"];
					
					$salePropertyCode = self::getSalePropertyByCodeAndPersonTypeId("CARGO_VALUE", $personTypeId);
					$orderArr["ORDER_PROP"][$salePropertyCode["ID"]] = $arData["CARGO_DATA"]["cargoValue"];
					
					$salePropertyCode = self::getSalePropertyByCodeAndPersonTypeId("SERVICE_CODE", $personTypeId);
					$orderArr["ORDER_PROP"][$salePropertyCode["ID"]] = $arData["CARGO_DATA"]["serviceCode"];
					
					$salePropertyCode = self::getSalePropertyByCodeAndPersonTypeId("SERVICE_VARIANT", $personTypeId);
					$orderArr["ORDER_PROP"][$salePropertyCode["ID"]] = $arData["CARGO_DATA"]["serviceVariant"];
					
					$salePropertyCode = self::getSalePropertyByCodeAndPersonTypeId("CARGO_NUM_PACK", $personTypeId);
					$orderArr["ORDER_PROP"][$salePropertyCode["ID"]] = $arData["CARGO_DATA"]["cargoNumPack"];
					
					$salePropertyCode = self::getSalePropertyByCodeAndPersonTypeId("DATE_PICKUP", $personTypeId);
					$orderArr["ORDER_PROP"][$salePropertyCode["ID"]] = $arData["CARGO_DATA"]["datePickup"];
					
					$salePropertyCode = self::getSalePropertyByCodeAndPersonTypeId("CARGO_CATEGORY", $personTypeId);
					$orderArr["ORDER_PROP"][$salePropertyCode["ID"]] = $arData["CARGO_DATA"]["cargoCategory"];
					
					$salePropertyCode = self::getSalePropertyByCodeAndPersonTypeId("CARGO_REGISTERED", $personTypeId);
					$orderArr["ORDER_PROP"][$salePropertyCode["ID"]] = $arData["CARGO_DATA"]["cargoRegistered"];
					
					$salePropertyCode = self::getSalePropertyByCodeAndPersonTypeId("PICKUP_TIME_PERIOD", $personTypeId);
					$orderArr["ORDER_PROP"][$salePropertyCode["ID"]] = $arData["CARGO_DATA"]["pickupTimePeriod"];
					
					// $salePropertyCode = self::getSalePropertyByCodeAndPersonTypeId(self::$infoTerminal, $personTypeId);
					// $orderArr["ORDER_PROP"][$salePropertyCode["ID"]] = $arData[self::$infoTerminal];

					self::UpdateInfoDPD($ORDER_ID, $orderArr["ORDER_PROP"]);
				}

				if (COption::GetOptionString(self::$module_id, "SCENARIO_DPD") == 2) {
					self::UpdateInfoDPD($ORDER_ID, $arData["ORDER_PROPS"]);
				}
				return $arOrderDPD;
			}
		}
		
		static function UpdateInfoDPD($orderId, $props) {
			foreach($props as $propId=>$propValue) {
				$db_vals = CSaleOrderPropsValue::GetList(
          array("SORT" => "ASC"),
					array(
						"ORDER_ID" => $orderId,
						"ORDER_PROPS_ID" => $propId
					)
				);
				if ($arVals = $db_vals->Fetch()) {
					$arFields = array(
						"VALUE"=>$propValue
					);
					CSaleOrderPropsValue::Update($arVals["ID"], $arFields);
				} else {
					$arProp = CSaleOrderProps::GetByID($propId);
					$arFields = array(
						 "ORDER_ID" => $orderId,
						 "ORDER_PROPS_ID" => $propId,
						 "NAME" => $arProp["NAME"],
						 "CODE" => $arProp["CODE"],
						 "VALUE" => $propValue
					);
					CSaleOrderPropsValue::Add($arFields);
				}
			}
		}
		
		
		static function getSalePropertyByCodeAndPersonTypeId($code, $personTypeId) {
			$res = CSaleOrderProps::GetList(
        array("SORT" => "ASC"),
        array(
							"PERSON_TYPE_ID" => $personTypeId,
							"CODE"=>$code
				),
        false,
        false,
        array()
			)->Fetch();
			return $res;
		}
		
		function CreateLabelFile($labelFileArr)
    {
        $DPD_service = new DPD_service;
        $arData = array(
            "fileFormat" => $labelFileArr["fileFormat"],
            "pageSize" => $labelFileArr["pageSize"],
            "order" => array(
                "orderNum" => $labelFileArr["order"]["orderNum"],
                "parcelsNumber" => $labelFileArr["order"]["parcelsNumber"]
            )
        );
        $res = $DPD_service->createLabelFile($arData);

        return $res;
    }
		
		static function CreateLabelFileForOrder($orderId, $orderDPD)
		{
				global $DB, $DBType, $APPLICATION;

				$arr = array();
				$arr["url"] = "";

				$url = '/rocketstudio.dpdext/labels/label_'.$orderId.'.pdf';

				function getLabelIs($url)
				{
						$io = CBXVirtualIo::GetInstance();
						$fp = $io->RelativeToAbsolutePath($url);
						$f = $io->GetFile($fp);
						$s = $f->GetContents();
						return $s;
				}

				$labelIs = getLabelIs($url);
		
				if(!$labelIs)
				{
						$labelFileArr = array(
								"fileFormat" => "PDF",
								"pageSize" => "A5",
								"order" => array(
										"orderNum" => $orderDPD,
										"parcelsNumber" => 1
								)
						);
						$arrLabel = self::CreateLabelFile($labelFileArr);
						$arr['dpd'] = $arrLabel;

						$ok = '';

						$file = $arrLabel->return->file;

						if(!empty($file))
						{
								function faleW($file, $url)
								{
										$io = CBXVirtualIo::GetInstance();
										$fp = $io->RelativeToAbsolutePath($url);
										$f = $io->GetFile($fp);
										$s = $f->PutContents($file);
										return $s;
								}

								$ok = faleW($file, $url);

								if($ok)
								{
										$arr["url"] = $url;
								}

						};
				}
				else
				{
						$arr["url"] = $url;
				}

				// $res = json_encode($arr);
				// echo $res;
				return $arr;
		}
		
		static function GetInvoiceFileForOrder($orderNum)
		{
				global $DB, $DBType, $APPLICATION;

				$arr = array();
				$arr["url"] = "";

				$url = '/rocketstudio.dpdext/invoice/invoice_'.$orderNum.'.pdf';

				function getInvoiceIs($url)
				{
						$io = CBXVirtualIo::GetInstance();
						$fp = $io->RelativeToAbsolutePath($url);
						$f = $io->GetFile($fp);
						$s = $f->GetContents($file);
						return $s;
				}

				$invoiceIs = getInvoiceIs($url);

				if(!empty($invoiceIs))
				{
						$arrInvoice = self::GetInvoiceFile($orderNum);

						$arr['dpd'] = $arrInvoice;

						$ok = '';

						$file = $arrInvoice->return->file;

						if(!empty($file))
						{
								function faleW($file, $url)
								{
										$io = CBXVirtualIo::GetInstance();
										$fp = $io->RelativeToAbsolutePath($url);
										$f = $io->GetFile($fp);
										$s = $f->PutContents($file);
										return $s;
								}

								$ok = faleW($file, $url);
								if($ok)
								{
										$arr["url"] = $url;
								}

						};
				}
				else
				{
						$arr["url"] = $url;
				}
				// $res = json_encode($arr);
				return $arr;
		}
		
		function GetInvoiceFile($orderNum)
    {
        $DPD_service = new DPD_service;
        $invoiceFileArr = array(
            "orderNum" => $orderNum
        );
        $invoiceFile = $DPD_service->getInvoiceFile($invoiceFileArr);
        return $invoiceFile;
    }
}
?>