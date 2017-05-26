<?

require_once('delivery_DPD.php');

class CDeliveryDPDCUR extends CDeliveryDPD
{
	
	public static function GetDeliveryCode()
	{
		return 'CUR';
	}
	
	function Init()
	{
		$result = array(
			"SID" => "dpd_cur", 
			"NAME" => "DPD CLASSIC domestic",
			"DESCRIPTION" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DESCRIPTION'),
			"DESCRIPTION_INNER" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DESCRIPTION_INNER'),
			
			"BASE_CURRENCY" => COption::GetOptionString("sale", "default_currency", "RUB"),			
			"HANDLER" => __FILE__,
			
			"DBGETSETTINGS" => array("CDeliveryDPDCUR", "GetSettings"),
			"DBSETSETTINGS" => array("CDeliveryDPDCUR", "SetSettings"),
			"GETCONFIG" => array("CDeliveryDPD", "GetConfig"),
			
			"COMPABILITY" => array("CDeliveryDPDCUR", "Compability"),      
			"CALCULATOR" => array("CDeliveryDPDCUR", "Calculate"),      
			
			"PROFILES" => array(				


			)
		);

		if(self::GetPostType() != 'FROM_TERMINAL'){
			$result['PROFILES']['CUR_DD'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DD_TITLE'),
				"DESCRIPTION" => "DPD CLASSIC domestic (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_DD_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
			$result['PROFILES']['CUR_DT'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DT_TITLE'),
				"DESCRIPTION" => "DPD CLASSIC domestic (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_DT_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
		}
		if(self::GetPostType() != 'FROM_DOOR'){
			$result['PROFILES']['CUR_TD'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_TD_TITLE'),
				"DESCRIPTION" => "DPD CLASSIC domestic (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_TD_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
			$result['PROFILES']['CUR_TT'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_TT_TITLE'),
				"DESCRIPTION" => "DPD CLASSIC domestic (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_TT_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
		}

		return $result;
	}	
  
}

AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('CDeliveryDPDCUR', 'Init')); 
?>