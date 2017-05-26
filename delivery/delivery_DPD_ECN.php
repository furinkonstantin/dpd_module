<?

require_once('delivery_DPD.php');

class CDeliveryDPDECN extends CDeliveryDPD
{
	
	public static function GetDeliveryCode()
	{
		return 'ECN';
	}
	
	function Init()
	{
		$result =  array(
			"SID" => "dpd_ecn", 
			"NAME" => "DPD ECONOMY",
			"DESCRIPTION" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DESCRIPTION'),
			"DESCRIPTION_INNER" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DESCRIPTION_INNER'),
			
			"BASE_CURRENCY" => COption::GetOptionString("sale", "default_currency", "RUB"),			
			"HANDLER" => __FILE__,
			
			"DBGETSETTINGS" => array("CDeliveryDPDECN", "GetSettings"),
			"DBSETSETTINGS" => array("CDeliveryDPDECN", "SetSettings"),
			"GETCONFIG" => array("CDeliveryDPD", "GetConfig"),
			
			"COMPABILITY" => array("CDeliveryDPDECN", "Compability"),      
			"CALCULATOR" => array("CDeliveryDPDECN", "Calculate"),
		);

		if(self::GetPostType() != 'FROM_TERMINAL'){
			$result['PROFILES']['ECN_DD'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DD_TITLE'),
				"DESCRIPTION" => "DPD ECONOMY (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_DD_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
			$result['PROFILES']['ECN_DT'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DT_TITLE'),
				"DESCRIPTION" => "DPD ECONOMY (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_DT_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
		}
		if(self::GetPostType() != 'FROM_DOOR'){
			$result['PROFILES']['ECN_TD'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_TD_TITLE'),
				"DESCRIPTION" => "DPD ECONOMY (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_TD_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
			$result['PROFILES']['ECN_TT'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_TT_TITLE'),
				"DESCRIPTION" => "DPD ECONOMY (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_TT_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
		}

		return $result;
	}	
  
}

AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('CDeliveryDPDECN', 'Init')); 
?>