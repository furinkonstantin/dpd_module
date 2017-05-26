<?

require_once('delivery_DPD.php');

class CDeliveryDPDDPT extends CDeliveryDPD
{
	
	public static function GetDeliveryCode()
	{
		return 'DPT';
	}
	
	function Init()
	{
		$result = array(
			"SID" => "dpd_dpt", 
			"NAME" => "DPD 13:00",
			"DESCRIPTION" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DESCRIPTION'),
			"DESCRIPTION_INNER" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DESCRIPTION_INNER'),
			
			"BASE_CURRENCY" => COption::GetOptionString("sale", "default_currency", "RUB"),			
			"HANDLER" => __FILE__,
			
			"DBGETSETTINGS" => array("CDeliveryDPDDPT", "GetSettings"),
			"DBSETSETTINGS" => array("CDeliveryDPDDPT", "SetSettings"),
			"GETCONFIG" => array("CDeliveryDPD", "GetConfig"),
			
			"COMPABILITY" => array("CDeliveryDPDDPT", "Compability"),      
			"CALCULATOR" => array("CDeliveryDPDDPT", "Calculate"),
		);

		if(self::GetPostType() != 'FROM_TERMINAL'){
			$result['PROFILES']['DPT_DD'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DD_TITLE'),
				"DESCRIPTION" => "DPD 13:00 (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_DD_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
			$result['PROFILES']['DPT_DT'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DT_TITLE'),
				"DESCRIPTION" => "DPD 13:00 (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_DT_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
		}
		if(self::GetPostType() != 'FROM_DOOR'){
			$result['PROFILES']['DPT_TD'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_TD_TITLE'),
				"DESCRIPTION" => "DPD 13:00 (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_TD_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
			$result['PROFILES']['DPT_TT'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_TT_TITLE'),
				"DESCRIPTION" => "DPD 13:00 (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_TT_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
		}

		return $result;
	}	
  
}

AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('CDeliveryDPDDPT', 'Init')); 
?>