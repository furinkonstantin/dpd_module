<?

require_once('delivery_DPD.php');

class CDeliveryDPDPCL extends CDeliveryDPD
{
	
	public static function GetDeliveryCode()
	{
		return 'PCL';
	}
	
	function Init()
	{
		$result = array(
			"SID" => "dpd_pcl", 
			"NAME" => "DPD CLASSIC Parcel",
			"DESCRIPTION" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DESCRIPTION'),
			"DESCRIPTION_INNER" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DESCRIPTION_INNER'),
			
			"BASE_CURRENCY" => COption::GetOptionString("sale", "default_currency", "RUB"),			
			"HANDLER" => __FILE__,
			
			"DBGETSETTINGS" => array("CDeliveryDPDPCL", "GetSettings"),
			"DBSETSETTINGS" => array("CDeliveryDPDPCL", "SetSettings"),
			"GETCONFIG" => array("CDeliveryDPD", "GetConfig"),
			
			"COMPABILITY" => array("CDeliveryDPDPCL", "Compability"),      
			"CALCULATOR" => array("CDeliveryDPDPCL", "Calculate"),      
//
//			"PROFILES" => array(
//				"PCL_DD" => array(
//					"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DD_TITLE'),
//					"DESCRIPTION" => "DPD CLASSIC Parcel (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_DD_TITLE').")",
//					"RESTRICTIONS_WEIGHT" => array(0),
//					"RESTRICTIONS_SUM" => array(0),
//				),
//				"PCL_DT" => array(
//					"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DT_TITLE'),
//					"DESCRIPTION" => "DPD CLASSIC Parcel (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_DT_TITLE').")",
//					"RESTRICTIONS_WEIGHT" => array(0),
//					"RESTRICTIONS_SUM" => array(0),
//				),
//				"PCL_TD" => array(
//					"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_TD_TITLE'),
//					"DESCRIPTION" => "DPD CLASSIC Parcel (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_TD_TITLE').")",
//					"RESTRICTIONS_WEIGHT" => array(0),
//					"RESTRICTIONS_SUM" => array(0),
//				),
//				"PCL_TT" => array(
//					"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_TT_TITLE'),
//					"DESCRIPTION" => "DPD CLASSIC Parcel (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_TT_TITLE').")",
//					"RESTRICTIONS_WEIGHT" => array(0),
//					"RESTRICTIONS_SUM" => array(0),
//				),
//			)
		);

		if(self::GetPostType() != 'FROM_TERMINAL'){
			$result['PROFILES']["PCL_DD"] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DD_TITLE'),
				"DESCRIPTION" => "DPD CLASSIC Parcel (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_DD_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
			$result['PROFILES']["PCL_DT"] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_DT_TITLE'),
				"DESCRIPTION" => "DPD CLASSIC Parcel (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_DT_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
		}
		if(self::GetPostType() != 'FROM_DOOR'){
			$result['PROFILES']["PCL_TD"] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_TD_TITLE'),
				"DESCRIPTION" => "DPD CLASSIC Parcel (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_TD_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
			$result['PROFILES']['PCL_TT'] = array(
				"TITLE" => GetMessage('ROCKETSTUDIO_DPD_PROFILE_TT_TITLE'),
				"DESCRIPTION" => "DPD CLASSIC Parcel (".GetMessage('ROCKETSTUDIO_DPD_PROFILE_TT_TITLE').")",
				"RESTRICTIONS_WEIGHT" => array(0),
				"RESTRICTIONS_SUM" => array(0),
			);
		}

		return $result;
	}	
  
}

AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('CDeliveryDPDPCL', 'Init')); 
?>