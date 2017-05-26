<?

require_once('delivery_DPD.php');

class CDeliveryDPDBZP extends CDeliveryDPD
{
	
	public static function GetDeliveryCode()
	{
		return 'BZP';
	}
	
	function Init()
	{
		$result = array(
			"SID" => "dpd_bzp", 
			"NAME" => "DPD 18:00",
			"DESCRIPTION" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DESCRIPTION'),
			"DESCRIPTION_INNER" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DESCRIPTION_INNER'),
			
			"BASE_CURRENCY" => COption::GetOptionString("sale", "default_currency", "RUB"),			
			"HANDLER" => __FILE__,
			
			"DBGETSETTINGS" => array("CDeliveryDPDBZP", "GetSettings"),
			"DBSETSETTINGS" => array("CDeliveryDPDBZP", "SetSettings"),
			"GETCONFIG" => array("CDeliveryDPD", "GetConfig"),
			
			"COMPABILITY" => array("CDeliveryDPDBZP", "Compability"),      
			"CALCULATOR" => array("CDeliveryDPDBZP", "Calculate"),
		);

		if(self::GetPostType() != 'FROM_TERMINAL'){
			$result['PROFILES']['BZP_DD'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DD_TITLE'),
				"DESCRIPTION" => "DPD 18:00 (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_DD_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
			$result['PROFILES']['BZP_DT'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DT_TITLE'),
				"DESCRIPTION" => "DPD 18:00 (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_DT_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
		}
		if(self::GetPostType() != 'FROM_DOOR'){
			$result['PROFILES']["BZP_TD"] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_TD_TITLE'),
				"DESCRIPTION" => "DPD 18:00 (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_TD_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
			$result['PROFILES']["BZP_TT"] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_TT_TITLE'),
				"DESCRIPTION" => "DPD 18:00 (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_TT_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
		}

		return $result;
	}	
  
}

AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('CDeliveryDPDBZP', 'Init')); 
?>