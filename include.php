<?
if(!CModule::IncludeModule('iblock') or !CModule::IncludeModule('catalog') or !CModule::IncludeModule("sale"))
{
    die(GetMessage("ERROR_MODULE"));
};

require_once('delivery/delivery_DPD.php');

CModule::AddAutoloadClasses(
	"rocketstudio.dpdext",
	array(
		"CRocketstudioDPDExt" => "classes/general/dpd.php",
		"CRocketstudioDPDExtEvents" => "classes/general/events.php",
		"CRocketstudioDPDExtOrder" => "classes/general/order.php",
		"CRocketstudioDPDExtAdmin" => "classes/general/admin.php",
		"CRocketstudioDPDExtMySQLStates" => "classes/mysql/dpd_states.php"
	)
);
?>