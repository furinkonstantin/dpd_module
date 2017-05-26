<?
IncludeModuleLangFile(__FILE__);

class CRocketstudioDPDExt
{	
	private static $module_id = 'rocketstudio.dpdext';
	
	function Declension($int, $expressions) {
		if (count($expressions) < 3) $expressions[2] = $expressions[1];
		settype($int, "integer"); 
		$count = $int % 100; 
		if ($count >= 5 && $count <= 20) { 
				$result = $expressions['2']; 
		} else { 
				$count = $count % 10; 
				if ($count == 1) { 
						$result = $expressions['0']; 
				} elseif ($count >= 2 && $count <= 4) { 
						$result = $expressions['1']; 
				} else { 
						$result = $expressions['2']; 
				}
		} 
		return $result; 
	}
	
	function SetPropertyOrder($code, $value, $order, $personTypeId)
	{
		if(!strlen($code))
		{
			return false;
		}
		$db_props = CSaleOrderProps::GetList(
			array("SORT" => "ASC"),
			array(
				"PERSON_TYPE_ID" => $personTypeId,
				'CODE' => $code
			)
		);
		if($arProps = $db_props->Fetch())
		{			
			$db_vals = CSaleOrderPropsValue::GetList(
				array("SORT" => "ASC"),
				array(
					"ORDER_ID" => $order,
					"ORDER_PROPS_ID" => $arProps["ID"]
				)
			);
			if($arVals = $db_vals->Fetch())
			{				
				if($arVals["ID"])
				{
					return CSaleOrderPropsValue::Update(
						$arVals["ID"],
						array(
							'VALUE' => $value,
						)
					);
				};
			}
			else
			{
				return CSaleOrderPropsValue::Add(
					array(
						'NAME' => $arProps['NAME'],
						'CODE' => $arProps['CODE'],
						'ORDER_PROPS_ID' => $arProps['ID'],
						'ORDER_ID' => $order,
						'VALUE' => $value,
					)
				);
			};
		}	
	}
	
	function SetStatusOrderDelivered($order_id)
	{
		$STATUS_ORDER_DELIVERED = COption::GetOptionString(self::$module_id, "STATUS_ORDER_DELIVERED");
		if($STATUS_ORDER_DELIVERED)
		{
			CSaleOrder::StatusOrder($order_id, $STATUS_ORDER_DELIVERED);
		}
		return $order_id;
	}
	
	function ChangeStatusWhenDPDCreated($order_id)
	{
		$STATUS_ORDER_DELIVERY = COption::GetOptionString(self::$module_id, "STATUS_ORDER_DELIVERY");
		if($STATUS_ORDER_DELIVERY)
		{
			CSaleOrder::StatusOrder($order_id, $STATUS_ORDER_DELIVERY);
		}
		return $order_id;
	}
	
	function TransitionTimeToTime($date)
	{		
		$arTemp = explode('T', $date);
		
		$arDay = explode('-', $arTemp[0]);
		
		$arTime = explode(':', $arTemp[1]);
		
		$ar = array(
			'Y' => $arDay[0],
			'M' => $arDay[1],
			'D' => $arDay[2],
			'H' => $arTime[0],
			'i' => $arTime[1],
			's' => $arTime[2],
		);
		
		$time = intval(mktime($ar['H'], $ar['i'], $ar['s'], $ar['M'], $ar['D'], $ar['Y']));
				
		return $time;
	}
	
	function NameStatusDPD($status)
	{		
		$ar = array(
			"Unknow" => GetMessage('ROCKETSTUDIO_DPDEXT_DPD_STATUS_0'),
			"NewOrderByClient" => GetMessage('ROCKETSTUDIO_DPDEXT_DPD_STATUS_1'),
			"NotDone" => GetMessage('ROCKETSTUDIO_DPDEXT_DPD_STATUS_2'),
			"OnTerminalPickup" => GetMessage('ROCKETSTUDIO_DPDEXT_DPD_STATUS_3'),
			"OnRoad" => GetMessage('ROCKETSTUDIO_DPDEXT_DPD_STATUS_4'),
			"OnTerminal" => GetMessage('ROCKETSTUDIO_DPDEXT_DPD_STATUS_5'),
			"OnTerminalDelivery" => GetMessage('ROCKETSTUDIO_DPDEXT_DPD_STATUS_6'),
			"Delivering" => GetMessage('ROCKETSTUDIO_DPDEXT_DPD_STATUS_7'),
			"Delivered" => GetMessage('ROCKETSTUDIO_DPDEXT_DPD_STATUS_8'),
			"Lost" => GetMessage('ROCKETSTUDIO_DPDEXT_DPD_STATUS_9'),
			"Problem" => GetMessage('ROCKETSTUDIO_DPDEXT_DPD_STATUS_10'),
			"ReturnedFromDelivery" => GetMessage('ROCKETSTUDIO_DPDEXT_DPD_STATUS_11'),
			"NewOrderByDPD" => GetMessage('ROCKETSTUDIO_DPDEXT_DPD_STATUS_12'),
		);		
		return $ar[$status];
	}
	
	function GetTerminal($city, $region)
	{
		$arrTerminals = include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".self::$module_id."/delivery/dpd/terminals.php");		
		
		$res = array();
				
		foreach($arrTerminals as $terminal)
		{
			if($terminal["cityName"] == $city and $terminal["regionCode"] == $region)
			{
				$res[] = $terminal;
			}
		}
		
		return $res;		
	}
	
	function GetTerminals()
	{		
		$res = include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".self::$module_id."/delivery/dpd/terminals.php");	
		
		$rgOrder = array_map(
			function($rgItem)
			{
				return $rgItem['terminalName'];
			},
			$res
		);
		
		array_multisort($res, SORT_ASC, $rgOrder);
		
		return $res;
	}
	
	function GetTerminalName($terminalCode)
	{
		$terminalName = '';
				
		$terminals = self::GetTerminals();
		
		foreach($terminals as $terminal)
		{
			if($terminal['terminalCode'] == $terminalCode)
				$terminalName = $terminal['terminalName'];			
		}
		
		return $terminalName;
	}
	
	function GetTerminalAddress($terminalCode)
	{
		$terminalAddress = '';
				
		$terminals = self::GetTerminals();
		
		foreach($terminals as $terminal)
		{
			if($terminal['terminalCode'] == $terminalCode)
				$terminalAddress = $terminal['terminalAddress'];			
		}
		
		return $terminalAddress;
	}
	
	function OrderMaxId()
	{
		$ID = CRocketstudioDPDExtMySQLOrder::MaxId();
		return $ID;
	}
	
	function AddOrderDB($orderArr)
	{					
		$ar = array(
			"ID" => $orderArr['ID'],
			"ORDER_ID" => $orderArr['orderID'],
			"ORDER_NUM" => $orderArr['orderNum'],
			"ORDER_STATUS" => $orderArr['orderStatus'],
			"ORDER_ERROR" => $orderArr['orderError'],
			"ORDER_DATE" => $orderArr['datePickup'],
			"ORDER_DATE_BITRIX" => $orderArr['datePickupBitrix'],
			"SERVICE_CODE" => $orderArr['serviceCode'], 
			"SERVICE_VARIANT" => $orderArr['serviceVariant'],
			"CARGO_CATEGORY" => $orderArr['cargoCategory'],			
			"CARGO_WEIGHT" => $orderArr['cargoWeight'],
			"CARGO_NUM_PACK" => $orderArr['cargoNumPack'],
			"CARGO_VALUE" => $orderArr['cargoValue'],
			"SUM_NPP" => $orderArr['sum_npp'],
			"PICKUP_TIME_PERIOD" => $orderArr['pickupTimePeriod'],
						
			"TERMINAL_CODE" => $orderArr['receiverAddress']['terminalCode'],
			"RECEIVER_NAME" => $orderArr['receiverAddress']['name'],
			"RECEIVER_FIO" => $orderArr['receiverAddress']['fio'],
			"RECEIVER_PHONE" => $orderArr['receiverAddress']['phone'],
			"RECEIVER_COUNTRY" => $orderArr['receiverAddress']['country'],
			"RECEIVER_REGION" => $orderArr['receiverAddress']['region'],
			"RECEIVER_CITY" => $orderArr['receiverAddress']['city'],			
			"RECEIVER_STREET" => $orderArr['receiverAddress']['street'],
			"RECEIVER_STREETABBR" => $orderArr['receiverAddress']['streetAbbr'],
			"RECEIVER_HOUSE" => $orderArr['receiverAddress']['house'],
			"RECEIVER_KORPUS" => $orderArr['receiverAddress']['korpus'],
			"RECEIVER_STR" => $orderArr['receiverAddress']['str'],
			"RECEIVER_VLAD" => $orderArr['receiverAddress']['vlad'],
			"RECEIVER_OFFICE" => $orderArr['receiverAddress']['office'],
			"RECEIVER_FLAT" => $orderArr['receiverAddress']['flat'],			
		);
		
		$ID = CRocketstudioDPDExtMySQLOrder::Add($ar);
		
		return $ID;
	}
	
	function RemoveOrderDB($orderID)
	{
		CRocketstudioDPDExtMySQLOrder::Delete($orderID);
	}
	
	function CancelOrderDB($orderID)
	{
		$arFields = array(
			"ORDER_ID" => $orderID,
			"ORDER_STATUS" => "Canceled",
		);
		CRocketstudioDPDExtMySQLOrder::Update($orderID, $arFields);
		return true;
	}
    function CancelOrderByDPDid($orderID)
    {
        $arFields = array(
            "ORDER_NUM" => $orderID,
            "ORDER_STATUS" => "Canceled",
        );
        CRocketstudioDPDExtMySQLOrder::UpdateByDPDid($orderID, $arFields);
        return true;
    }
	
	function AddTerminalFromComponent($orderID)
	{
		$arrDelevery = explode(":", $_REQUEST['DELIVERY_ID']);
		
		$DELIVERY = $arrDelevery[1];
        
		$profile = array(
			'BZP_DT', 'BZP_TT',
			'CSM_DT', 'CSM_TT',
			'CUR_DT', 'CUR_TT',
			'DPT_DT', 'DPT_TT',
			'ECN_DT', 'ECN_TT',
			'NDY_DT', 'NDY_TT',
			'PCL_DT', 'PCL_TT',
			'TEN_DT', 'TEN_TT',
		);
		
        if(in_array($DELIVERY, $profile)){

            $nameInput = $DELIVERY.':'.$_REQUEST['DPD_CITY_ID'];
            
            $nameInputCode = $DELIVERY.':'.$_REQUEST['DPD_CITY_ID'].'_'.$_REQUEST[$nameInput];
						
			$ar = array(
                "ORDER_ID" => $orderID,				
				
                "TERMINAL_CODE" => $_REQUEST[$nameInput],        
                "TERMINAL_NAME" => $_REQUEST['TERMINAL_NAME_'.$nameInputCode],
				"TERMINAL_ADDRESS"  => $_REQUEST['TERMINAL_ADDRESS_'.$nameInputCode],
                
                "COUNTRY_CODE" => $_REQUEST['COUNTRY_CODE_'.$nameInputCode],
				"COUNTRY_NAME" => $_REQUEST['COUNTRY_NAME_'.$nameInputCode],
                "REGION_CODE" => $_REQUEST['REGION_CODE_'.$nameInputCode],
				"REGION_NAME" => $_REQUEST['REGION_NAME_'.$nameInputCode],
				"CITY_ID" => $_REQUEST['CITY_ID_'.$nameInputCode],
				"CITY_NAME" => $_REQUEST['CITY_NAME_'.$nameInputCode],
            );
			
			if(!CRocketstudioDPDExtMySQLTerminals::GetIdByOrder($orderID))
			{
            	CRocketstudioDPDExtMySQLTerminals::Add($ar);
			}
                    
        }
	}
		
	function AddTerminalFromAdmin($orderID, $terminalAddress)
	{
		
		if(empty($terminalAddress))
		{
			$terminalAddress = $_REQUEST["order_terminals_select"];
		}
		
		if(!empty($terminalAddress))
		{
			
			$arrTerminals = include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".self::$module_id."/delivery/dpd/terminals.php");
			
			$selectedTerminal = "";
							
			foreach($arrTerminals as $terminal)
			{
				if($terminal["terminalAddress"] == $terminalAddress)
				{
					$selectedTerminal = $terminal;
				}
			}
			
			if($selectedTerminal)
			{		
				$arFields = array(
					"ORDER_ID" => $orderID,
					
					"TERMINAL_CODE" => $selectedTerminal["terminalCode"],        
					"TERMINAL_NAME" => $selectedTerminal["terminalName"],
					"TERMINAL_ADDRESS"  => $selectedTerminal["terminalAddress"],
					
					"COUNTRY_CODE" => $selectedTerminal["countryCode"],
					"COUNTRY_NAME" => $selectedTerminal["countryName"],
					"REGION_CODE" => $selectedTerminal["regionCode"],
					"REGION_NAME" => $selectedTerminal["regionName"],
					"CITY_ID" => $selectedTerminal["cityId"],
					"CITY_NAME" => $selectedTerminal["cityName"],					
				);
			}
			else
			{
				$arFields = array(
					"ORDER_ID" => $orderID,
					
					"TERMINAL_CODE" => "",        
					"TERMINAL_NAME" => "",
					"TERMINAL_ADDRESS"  => "",
					
					"COUNTRY_CODE" => "",
					"COUNTRY_NAME" => "",
					"REGION_CODE" => "",
					"REGION_NAME" => "",
					"CITY_ID" => "",
					"CITY_NAME" => "",
				);
			}
			
			CRocketstudioDPDExtMySQLTerminals::Update($orderID, $arFields);
			
		}
		return $orderID;
		
	}
	
	function DeleteTerminal($orderID)
	{
		CRocketstudioDPDExtMySQLTerminals::Delete($orderID);
	}
	
	function RegionList()
	{
		$res = array();
		$db_vars = CSaleLocation::GetList(
			array("SORT" => "ASC", "REGION_NAME" => "ASC"),
			array("LID" => LANGUAGE_ID, "COUNTRY_NAME" => GetMessage("ROCKETSTUDIO_DPDEXT_DPD_COUNTRY_ROSSIA"), "CITY_ID" => NULL),
			false,
			false,
			array()
		);
		while($vars = $db_vars->Fetch()):
			
			if(!$vars["REGION_NAME"]) continue;
			
			$regionName = $vars["REGION_NAME"];
			
			$regionNameTrim = array(
				GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_TRIM_1'),
				GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_TRIM_2'),
				GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_TRIM_3'),
				GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_TRIM_4'),
			);			
			foreach($regionNameTrim as $regionNameTrimItem):
				$regionName = str_replace($regionNameTrimItem, $regionNameTrimItem.".", $regionName); 
			endforeach;
			$regionNameTrimMP = array(
				array(
					GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_MOSCOW'),
					GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_MOSCOW_G'),
				),
				array(
					GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_PITER'),
					GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_PITER_G'),
				)
			);
			foreach($regionNameTrimMP as $regionNameTrimMPItem):
				$pos = strpos($regionName, $regionNameTrimMPItem[0]);
				if($pos !== false)
					$regionName = $regionNameTrimMPItem[1]; 
			endforeach;
			
			$res[] = $regionName;
			
		endwhile;
			
		return $res;
	}
	
	function RegionBxToDpd($region_bitrix)
	{
		$regionName = $region_bitrix;
		$regionNameTrim = array(
			GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_TRIM_1'),
			GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_TRIM_2'),
			GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_TRIM_3'),
			GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_TRIM_4'),
		);			
		foreach($regionNameTrim as $regionNameTrimItem):
			$regionName = str_replace($regionNameTrimItem, $regionNameTrimItem.".", $regionName); 
		endforeach;
		$regionNameTrimMP = array(
			array(
				GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_MOSCOW'),
				GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_MOSCOW_G'),
			),
			array(
				GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_PITER'),
				GetMessage('ROCKETSTUDIO_DPDEXT_DPD_REGION_PITER_G'),
			)
		);
		foreach($regionNameTrimMP as $regionNameTrimMPItem):
			$pos = strpos($regionName, $regionNameTrimMPItem[0]);
			if($pos !== false)
				$regionName = $regionNameTrimMPItem[1]; 
		endforeach;
			
		return $regionName;
	}
	
	function GetStatusOrder($orderID, $remote = false)
	{
		if($orderID)
		{
			if($remote)
			{
				$status = "";
			}
			else
			{				
				$res = CRocketstudioDPDExtMySQLOrder::GetById($orderID);
				$status = $res["ORDER_STATUS"];	
			}
			
			return $status;
		}
		else
		{
			return false;
		}
		
	}

    function GetStatusDPDOrder($orderID, $remote = false)
    {
        if($orderID)
        {
            if($remote)
            {
                $status = "";
            }
            else
            {
                $res = CRocketstudioDPDExtMySQLOrder::GetByDPDId($orderID);
                $status = $res["ORDER_STATUS"];
            }

            return $status;
        }
        else
        {
            return false;
        }

    }
	
	function GetOrderProps($PERSON_TYPE_ID)
	{
		$orderPropsArr = array();
		$db_props = CSaleOrderProps::GetList(
			array("SORT" => "ASC"),
			array(
				"PERSON_TYPE_ID" => $PERSON_TYPE_ID,
				/*"USER_PROPS" => "Y"*/
			),
			false,
			false,
			array()
		);				
		while($props = $db_props->Fetch())
		{
			$orderPropsArr[] = $props;
		}
		return $orderPropsArr;
	}
	
	function OrderPropsValue($orderID, $personType)
	{
		$orderProps = array();
		$orderPropsDPD =  array();
		$db_props = CSaleOrderPropsValue::GetOrderProps($orderID);
		while($arProps = $db_props->Fetch())
		{
			$orderProps[] = $arProps;
		}
		
		foreach($orderProps as $orderProp):
			
			if($orderProp["ORDER_PROPS_ID"] == COption::GetOptionString(self::$module_id, "ORDER_PROPERTY_NAME_".$personType)):
				$orderPropsDPD["NAME"] = $orderProp["VALUE"];
			endif;
			
			if($orderProp["ORDER_PROPS_ID"] == COption::GetOptionString(self::$module_id, "ORDER_PROPERTY_PHONE_".$personType)):
				$orderPropsDPD["PHONE"] = $orderProp["VALUE"];
			endif;
			
			if($orderProp["ORDER_PROPS_ID"] == COption::GetOptionString(self::$module_id, "ORDER_PROPERTY_STREET_".$personType)):
				$orderPropsDPD["STREET"] = $orderProp["VALUE"];
			endif;
			
			if($orderProp["ORDER_PROPS_ID"] == COption::GetOptionString(self::$module_id, "ORDER_PROPERTY_HOUSE_".$personType)):
				$orderPropsDPD["HOUSE"] = $orderProp["VALUE"];
			endif;
			
			if($orderProp["ORDER_PROPS_ID"] == COption::GetOptionString(self::$module_id, "ORDER_PROPERTY_KORPUS_".$personType)):
				$orderPropsDPD["KORPUS"] = $orderProp["VALUE"];
			endif;
			
			if($orderProp["ORDER_PROPS_ID"] == COption::GetOptionString(self::$module_id, "ORDER_PROPERTY_STR_".$personType)):
				$orderPropsDPD["STR"] = $orderProp["VALUE"];
			endif;
			
			if($orderProp["ORDER_PROPS_ID"] == COption::GetOptionString(self::$module_id, "ORDER_PROPERTY_VLAD_".$personType)):
				$orderPropsDPD["VLAD"] = $orderProp["VALUE"];
			endif;
			
			if($orderProp["ORDER_PROPS_ID"] == COption::GetOptionString(self::$module_id, "ORDER_PROPERTY_OFFICE_".$personType)):
				$orderPropsDPD["OFFICE"] = $orderProp["VALUE"];
			endif;
			
			if($orderProp["ORDER_PROPS_ID"] == COption::GetOptionString(self::$module_id, "ORDER_PROPERTY_FLAT_".$personType)):
				$orderPropsDPD["FLAT"] = $orderProp["VALUE"];
			endif;
					
		endforeach;
			
		return $orderPropsDPD;
	}
	
	function AddStatesDB($orderArr)
	{
		foreach($orderArr as $order)
		{
			if(is_array($order))
			{
				$ar = array(
					"dpdOrderNr" => $order['dpdOrderNr'],
					"dpdParcelNr" => $order['dpdParcelNr'],
					"pickupDate" => $order['pickupDate'],
					"planDeliveryDate" => $order['planDeliveryDate'],
					"newState" => $order['newState'],
					"transitionTime" => $order['transitionTime'],
					"terminalCode" => $order['terminalCode'],
					"terminalCity" => $order['terminalCity'],
					"consignee" => $order['consignee'],
				);
			
				CRocketstudioDPDExtMySQLStates::Add($ar);
			}
			else
			{				
				$ar = array(
					"dpdOrderNr" => $orderArr['dpdOrderNr'],
					"dpdParcelNr" => $orderArr['dpdParcelNr'],
					"pickupDate" => $orderArr['pickupDate'],
					"planDeliveryDate" => $orderArr['planDeliveryDate'],
					"newState" => $orderArr['newState'],
					"transitionTime" => $orderArr['transitionTime'],
					"terminalCode" => $orderArr['terminalCode'],
					"terminalCity" => $orderArr['terminalCity'],
					"consignee" => $orderArr['consignee'],
				);
				
				CRocketstudioDPDExtMySQLStates::Add($ar);
				break;
			}
		}
	}
	
	function AgentStatusDPD()
	{
		$states = CRocketstudioDPDExt::StatesGetToSave();
		if($states)
		{		
			$dpdExtOrder = new CRocketstudioDPDExtOrder;
			$arOrders = array();
			$STATUS_ORDER_DELIVERY = COption::GetOptionString(self::$module_id, "STATUS_ORDER_DELIVERY");		
			$rsSales = CSaleOrder::GetList(array('ID' => 'DESC'), array("STATUS_ID" => $STATUS_ORDER_DELIVERY));
			while($arSales = $rsSales->Fetch())
			{			
				$arOrders[] = $arSales;
			}		
			$arOrdersHaveDPD = array(); 
			foreach($arOrders as $arOrderKey => $arOrder)
			{
				$arOrderData = $dpdExtOrder::CompleteDataOrder($arOrder["ID"]);	
				$orderNum = $arOrderData['ORDER_DPD_ID'];
				if($orderNum and $orderNum != 'undefined')
				{
					$arOrders[$arOrderKey]['DPD_ORDER_NR'] = $orderNum;
					$arOrdersHaveDPD[] = array(
						"ID" => $arOrder["ID"],
						"PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"],
						"DPD_ORDER_NR" => $orderNum
					);
				}
			}
			
			$arrDPDOrderNR = array();
			foreach($arOrdersHaveDPD as $OrdersHaveDPD)
			{
				$arrDPDOrderNR[] = $OrdersHaveDPD["DPD_ORDER_NR"];
			}	
			
			$arrStatusDPD = array();
			if(!empty($arrDPDOrderNR))
			{
				$arrStatusDPD = CRocketstudioDPDExt::StatesNumbersList($arrDPDOrderNR);
			}
			
			$arOrdersStatus = array();
			if($arrStatusDPD)
			{
				foreach($arOrders as $arOrder)
				{
					if(!$arOrder['DPD_ORDER_NR'])
					{
						continue;
					}
					else
					{
						foreach($arrStatusDPD as $statusDPD)
						{
							if($statusDPD["dpdOrderNr"] == $arOrder['DPD_ORDER_NR'])
							{
								$arOrdersStatus[] = array(
									"ID" => $arOrder["ID"],
									"PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"],
									"dpdOrderNr" => $statusDPD["dpdOrderNr"],
									"dpdParcelNr" => $statusDPD["dpdParcelNr"],
									"pickupDate" => $statusDPD["pickupDate"],
									"planDeliveryDate" => $statusDPD["planDeliveryDate"],
									"newState" => $statusDPD["newState"],
									"newStateName" => CRocketstudioDPDExt::NameStatusDPD($statusDPD["newState"]),
									"transitionTime" => $statusDPD["transitionTime"],
									"terminalCode" => $statusDPD["terminalCode"],
									"terminalCity" => $statusDPD["terminalCity"],
									"consignee" => $statusDPD["consignee"],
								);							
							}
						}
					}
				}
			}
			
			$arParcelNr = array();
			if(!empty($arOrdersStatus))
			{
				foreach($arOrdersStatus as $orderStatus)
				{
					$dpdParcelNr = $orderStatus["dpdParcelNr"];
					$arParcelNr[$dpdParcelNr] = $dpdParcelNr;
				}
			}
			
			$arParcelState = array();
			if(!empty($arParcelNr) and !empty($arOrdersStatus))
			{
				$i = 0;			
				foreach($arParcelNr as $parcelNr)
				{
					$i++;
					$transitionTimeTemp = 0;
					foreach($arOrdersStatus as $orderStatus)
					{
						if($orderStatus["dpdParcelNr"] != $parcelNr)
							continue;
												
						$transitionTime = CRocketstudioDPDExt::TransitionTimeToTime($orderStatus["transitionTime"]);
						
						if($transitionTimeTemp < $transitionTime)
						{
							$arParcelState[$i] = $orderStatus;
						}
						
						$transitionTimeTemp = $transitionTime;
					}
				}
			}
			$arParcelOrdersID = array();
			if(!empty($arParcelState))
			{
				foreach($arParcelState as $parcelState)
				{
					$dpdParcelOrderID = $parcelState["ID"];
					$arParcelOrdersID[$dpdParcelOrderID] = $dpdParcelOrderID;
				}
			}
			$arOrderState = array();
			if(!empty($arParcelOrdersID) and !empty($arParcelState))
			{
				foreach($arParcelOrdersID as $parcelOrdersID)
				{
					foreach($arParcelState as $parcelState)
					{
						if($parcelOrdersID == $parcelState["ID"])
						{
							$arOrderState[$parcelOrdersID][] = $parcelState;					
						};
					}
				}
			}
			
			foreach($arOrderState as $orderStateKey => $orderState)
			{
				$stateValue = "";
				$personTypeId = "";			
				
				if($count = count($orderState))
				{
					$deliveredWant = true;
				}
				else
				{
					$deliveredWant = false;
				}
				
				foreach($orderState as $orderStateItem)
				{
					$br = "\n";
					$hr = $br."-----------------------------------".$br;
					$stateValue .= GetMessage('ROCKETSTUDIO_DPDEXT_DPD_PARCEL').": #".$orderStateItem["dpdParcelNr"]."#".$br."(".$orderStateItem["transitionTime"].")".$br."".$orderStateItem["newStateName"].$hr;
					$personTypeId = $orderStateItem["PERSON_TYPE_ID"];
					
					if($orderStateItem["newState"] != "Delivered")
					{
						$deliveredWant = false;
					}
				}
				
				CRocketstudioDPDExt::SetPropertyOrder($dpdExtOrder::$statusDPD, $stateValue, $orderStateKey, $personTypeId);
				
				if($deliveredWant)
				{
					CRocketstudioDPDExt::SetStatusOrderDelivered($orderStateKey);
				};			
			}
		}
	}
	
	function StatesNumbersList($arrDPDOrderNr)
	{
		$arr = array();
		
		foreach($arrDPDOrderNr as $DPDOrderNr)
		{		
			 $dbRes = CRocketstudioDPDExtMySQLStates::GetNumbersList($DPDOrderNr);
			 while($arRes = $dbRes->Fetch())
			 {
				 $arr[] = $arRes;
			 }
		}
		
		return $arr;
	}
	
	function StatesGetToSave()
	{
		$DPD_service = new DPD_service;
		$StatusByClient = $DPD_service->getStatesByClient();
		$docId = $StatusByClient['docId'];
		$states = false;		
		if(!empty($StatusByClient["states"]))
		{
			$states = true;		
			CRocketstudioDPDExt::AddStatesDB($StatusByClient["states"]);
		}; 		
		if($docId)
		{
			$arConfirmStates = array(
				"docId" => $docId
			);
			$confirmStates = $DPD_service->confirmStates($arConfirmStates);
		}
		return $states;
	}
}

?>