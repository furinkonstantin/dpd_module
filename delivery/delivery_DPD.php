<?

require_once('dpd/dpd_service.class.php');

CModule::IncludeModule("sale");
//IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/options.php");
IncludeModuleLangFile(__FILE__);
class CDeliveryDPD
{
	
	private static $pathTerminalsList = "/dpd/terminals.php";

	private static $module_id = 'rocketstudio.dpdext';

	public static function GetPostType()
	{
		return COption::GetOptionString(self::$module_id, "POST_TYPE");
	}

	public static function GetDeliveryCode()
	{
		return '';
	}

	function SetSettings($arSettings)
	{		
		foreach ($arSettings as $key => $value) 
		{
			if (strlen($value) > 0)
				$arSettings[$key] = doubleval($value);
			else
				unset($arSettings[$key]);
		}
		return serialize($arSettings);
	}
	
	function GetSettings($strSettings)
	{		
		return unserialize($strSettings);
	}

    function GetConfig()
	{
        $arConfig = array(
            "CONFIG_GROUPS" => array(
                "all" => GetMessage('ROCKETSTUDIO_DPD_CONFIG_GROUPS_all'),
            ),
            "CONFIG" => array(
                "WEIGHT" => array(
                    "TYPE" => "TEXT",
                    "TITLE" => GetMessage('ROCKETSTUDIO_DPD_WEIGHT_TITLE'),
                    "GROUP" => "all",
                    "DEFAULT" =>  "1"
                ),                     
            ),
        );
     
        
        return $arConfig;
    }	
		
	public static function GetTerminals() {
		$terminals = unserialize(file_get_contents(__DIR__.self::$pathTerminalsList));
		$res = array();
		foreach($terminals["terminal"] as $i=>$terminal) {
			$terminal["terminalAddress"] = implode(", ", $terminal["address"]);
			foreach($terminal["schedule"] as $arSchedule) {
				foreach($arSchedule["timetable"] as $arTime) {
					 $terminal["schedule".$arSchedule["operation"]] .= $arTime["weekDays"]." ".$arTime["workTime"]."; ";
				}
			}
			$terminal["scheduleSelfPickup"] = substr($terminal["scheduleSelfPickup"],0,-1);
			$terminal["scheduleSelfDelivery"] = substr($terminal["scheduleSelfDelivery"],0,-1);
			$res[] = $terminal;
		}
		return $res;
	}
		
	function RefreshTerminals() {
		global $APPLICATION;
		$DPD_service = new DPD_service;		
		if (!$resTerminals = $DPD_service->getTerminalsList()) {
			echo CAdminMessage::ShowMessage(GetMessage("ERROR_CONNECT_SERVICE"));
			return false;
		}
		$serResTerminals = serialize($resTerminals);
		if (!file_put_contents(__DIR__.self::$pathTerminalsList, $serResTerminals)) {
			echo CAdminMessage::ShowMessage(GetMessage("ERROR_REFRESH_TERMINALS")); 
		} else {
			echo CAdminMessage::ShowMessage(array(
				"MESSAGE"=>GetMessage("SUCCESS_REFRESH_TERMINALS"),
				"TYPE"=>"OK"
			));
		}
	}
	
	function __GetRegion($regionName)
	{
		include('dpd/region_code.php');
		$regionCode = "";		
		$regionNameTrim = array(
			GetMessage('ROCKETSTUDIO_DPD_REGION_NAME_TRIM_1'),
			GetMessage('ROCKETSTUDIO_DPD_REGION_NAME_TRIM_2'),
			GetMessage('ROCKETSTUDIO_DPD_REGION_NAME_TRIM_3'),
			GetMessage('ROCKETSTUDIO_DPD_REGION_NAME_TRIM_4'),
			GetMessage('ROCKETSTUDIO_DPD_REGION_NAME_TRIM_5'),
			GetMessage('ROCKETSTUDIO_DPD_REGION_NAME_TRIM_6'),
			GetMessage('ROCKETSTUDIO_DPD_REGION_NAME_TRIM_7')
		);
		$regionName = str_replace($regionNameTrim, "", $regionName);
		$regionName = trim($regionName);
		$region = array();
		$region["NAME"] = $regionName;	
	
		foreach($arrRegionCode as $reg)
		{
			$regName = $reg["NAME"];
			if(preg_match("!".$regName."!", $regionName))
			{
				$region["CODE"] = $reg["CODE"];
				break;
			}
		}		
		return $region;		
	}
	
	function __GetServiceLocation($LOCATION)
	{
		$arLocs = CSaleLocation::GetByID($LOCATION, "ru");
		if($arLocs)
		{
			$region = static::__GetRegion($arLocs["REGION_NAME"]);
			$regionName = $region["NAME"];
			$cityName = $arLocs["CITY_NAME"];
			$regionCode = $region["CODE"];			
			if($arLocs["CITY_NAME"] == GetMessage('ROCKETSTUDIO_DPD_CITY_NAME_MOSCOW'))//"??????"
			{
				$regionCode = "77";
				$regionName = GetMessage('ROCKETSTUDIO_DPD_CITY_NAME_MOSCOW');
			}
			if($arLocs["CITY_NAME"] == GetMessage('ROCKETSTUDIO_DPD_CITY_NAME_PITER'))//"?????-?????????"
			{
				$regionCode = "78";
				$regionName = GetMessage('ROCKETSTUDIO_DPD_CITY_NAME_PITER');
			}			
			return array("cityName" => $cityName, "regionCode" => $regionCode, "regionName" => $regionName);
		}
		else
		{			
			return array();			
		}
	}
	
	function __GetServiceCost($LOCATION_FROM, $LOCATION_TO, $WEIGHT, $VOLUME, $serviceCode, $selfPickup, $selfDelivery)
	{
		$cityArrFrom = static::__GetServiceLocation($LOCATION_FROM);		
		$cityNameFrom = $cityArrFrom['cityName'];
		$regionCodeFrom = $cityArrFrom['regionCode'];
			
		$cityArrTo = static::__GetServiceLocation($LOCATION_TO);		
		$cityNameTo = $cityArrTo['cityName'];
		$regionCodeTo = $cityArrTo['regionCode'];
		$regionNameTo = $cityArrTo['regionName'];
		if($cityNameFrom and $cityNameTo and $regionCodeFrom and $regionCodeTo)
		{
			$DPD_service = new DPD_service;
						
			$arData = array(
				'pickup' => array(
					'cityName' => $cityNameFrom,
					'regionCode' => $regionCodeFrom,
					'countryCode' => 'RU',
				),
				'delivery' => array(//????
					'cityName' => $cityNameTo,
					'regionCode' => $regionCodeTo,
					'countryCode' => 'RU',
				),
				'selfPickup' => $selfPickup,
				'selfDelivery' => $selfDelivery,
				'weight' => $WEIGHT,
				'serviceCode' => $serviceCode				
			);	
			
			$resCost = $DPD_service->getServiceCost($arData);
		}
		else
		{
			$resCost = "";
		}
		
		if($resCost)
		{
			$resCost["cityName"] = $cityNameTo;
			$resCost["regionName"] = $regionNameTo;
			$resCost["regionCode"] = $regionCodeTo;
		}
				
		return $resCost;
	}
	
	function __GetTerminalsParcelShops($region, $city)
	{
		$dpd = new DPD_service;
		$arData = array(
			"cityName"=>$city,
			"regionCode"=>$region
		);
		$res = $dpd->getParcelShops($arData);
		var_dump($res);
		return $res;
	}
	
	function __GetTerminals($region, $city)
	{
		$resTerminals = self::getTerminals();
		$res = array();
		foreach($resTerminals as $terminal)
		{
			if ($city == $terminal["address"]["cityName"] && $region == $terminal["address"]["regionCode"]) {
				$coords = $terminal["geoCoordinates"];
				if(empty($terminal["geoCoordinates"])) {
					$jsonCoordinates = file_get_contents('http://geocode-maps.yandex.ru/1.x/?format=json&geocode='.$terminal["terminalAddress"]);
					$arCoordinates = json_decode($jsonCoordinates);
					$arCoordinates = explode(' ', $arCoordinates->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
					$coords["latitude"] = $arCoordinates[1];
					$coords["longitude"] = $arCoordinates[0];
				}
				$terminal["geoCoordinates"] = $terminal["latitude"].", ".$terminal["longitude"];
				$terminal["coords"] = $coords;
				$res[] = $terminal;
			}
		}

		return $res;
	}

	function Compability($arOrder, $arConfig)
	{
		$resFrom = static::__GetServiceLocation($arOrder["LOCATION_FROM"]);
		$resTo = static::__GetServiceLocation($arOrder["LOCATION_TO"]);
		
		$arrTerminals = static::__GetTerminals($resTo["regionCode"], $resTo["cityName"]);
		if($resFrom and $resTo) {
			if(!empty($arrTerminals)) {
				return array(static::GetDeliveryCode().'_DD', static::GetDeliveryCode().'_DT', static::GetDeliveryCode().'_TD', static::GetDeliveryCode().'_TT');
			} else {
				return array(static::GetDeliveryCode().'_DD', static::GetDeliveryCode().'_TD');
			}
		} else {
			return array();		
		}
	}

	function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
	{
		global $APPLICATION;
    $cityName = '';
		if($arOrder["WEIGHT"] <= 0 || empty($arOrder["WEIGHT"]))
		{
			$weight = $arConfig['WEIGHT']['VALUE'];
		}
		else
		{
			$weight = $arOrder["WEIGHT"];
			$weight = CSaleMeasure::Convert($weight, "G", "KG");
		}
		if ($weight == '')
		{
			$weight = $arConfig['WEIGHT']['DEFAULT'];
		}

		$volume == '';
		if(substr($profile, -2, 1) == "D")
			$selfPickup = false; //от двери
		else
			$selfPickup = true; //от терминала

		if(substr($profile, -1) == "D")
			$selfDelivery = false; //до двери
		else
			$selfDelivery = true; //до терминала
		$resCost = static::__GetServiceCost($arOrder["LOCATION_FROM"], $arOrder["LOCATION_TO"], $weight, $volume, static::GetDeliveryCode(), $selfPickup, $selfDelivery);
		$price = "";
		if($resCost)
		{
			$price = $resCost["cost"];
			$transit = $resCost["days"].' '.GetMessage("ROCKETSTUDIO_DPD_DELIVERY_DAY");

			if(self::$module_id == 'rocketstudio.dpdext')
			{
				$profileTerminals = array(static::GetDeliveryCode().'_DT', static::GetDeliveryCode().'_TT');
			}
			else
			{
				$profileTerminals = array();
			}
			if(in_array($profile, $profileTerminals)) {

				$dpdTarif = static::GetDeliveryCode();

				$arrTerminals = static::__GetTerminals($resCost["regionCode"], $resCost["cityName"]);
				// $arrTerminals = static::__GetTerminalsParcelShops($resCost["regionCode"], $resCost["cityName"]);
				$htmlTerminals = "";
				if(!empty($arrTerminals))
				{
					ob_start();
					$APPLICATION->IncludeComponent("dpd:rs.pwp.map", "main", array(
							"TERMINALS"=>$arrTerminals,
							"ORDER"=>$arOrder,
							"PROFILE"=>$profile,
							"DPD_TARIFF"=>$dpdTarif
						), false
					);
					$htmlTerminals = ob_get_contents();
					ob_end_clean();
				}

				$transit = $transit. ' '.$htmlTerminals;

			}
			return array(
				"RESULT" => "OK",
				"VALUE" => $price,
				"TRANSIT" => $transit,
			);
		}
		else
		{
			return array(
				"RESULT" => "ERROR",
				"TEXT" => GetMessage("ROCKETSTUDIO_DPD_DELIVERY_ERROR"),
			);
		}
	}
  
}
?>