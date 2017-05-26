<?

require_once('delivery_DPD.php');

class CDeliveryDPDNDY extends CDeliveryDPD
{
	
	public static function GetDeliveryCode()
	{
		return 'NDY';
	}
	
	function Init()
	{
		$result =  array(
			"SID" => "dpd_ndy", 
			"NAME" => "DPD EXPRESS",
			"DESCRIPTION" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DESCRIPTION'),
			"DESCRIPTION_INNER" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DESCRIPTION_INNER'),
			
			"BASE_CURRENCY" => COption::GetOptionString("sale", "default_currency", "RUB"),			
			"HANDLER" => __FILE__,
			
			"DBGETSETTINGS" => array("CDeliveryDPDNDY", "GetSettings"),
			"DBSETSETTINGS" => array("CDeliveryDPDNDY", "SetSettings"),
			"GETCONFIG" => array("CDeliveryDPD", "GetConfig"),
			
			"COMPABILITY" => array("CDeliveryDPDNDY", "Compability"),      
			"CALCULATOR" => array("CDeliveryDPDNDY", "Calculate"),
		);

		if(self::GetPostType() != 'FROM_TERMINAL'){
			$result['PROFILES']['NDY_DD'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DD_TITLE'),
				"DESCRIPTION" => "DPD EXPRESS (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_DD_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
			$result['PROFILES']['NDY_DT'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DT_TITLE'),
				"DESCRIPTION" => "DPD EXPRESS (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_DT_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
		}
		if(self::GetPostType() != 'FROM_DOOR'){
			$result['PROFILES']["NDY_TD"] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_TD_TITLE'),
				"DESCRIPTION" => "DPD EXPRESS (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_TD_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
			$result['PROFILES']["NDY_TT"] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_TT_TITLE'),
				"DESCRIPTION" => "DPD EXPRESS (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_TT_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
		}

		return $result;
	}	
  
}

AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('CDeliveryDPDNDY', 'Init')); 
?>