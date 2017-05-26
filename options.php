<script>
function openDialog(filter, bLoadJS, Params, nameBtnClick, functionFileDialogSubmit, pathOpenDialog) {
	if (!Params)
	Params = {};
	if (typeof(pathOpenDialog) == "undefined") {
		pathOpenDialog = '/assets';
	}
	console.log(pathOpenDialog);
		var UserConfig;
		UserConfig =
	{
		site : '<?=SITE_ID?>',
		path : pathOpenDialog,
		view : "list",
		sort : "type",
		sort_order : "asc"
	};
	if (!window.BXFileDialog)
	{
		if (bLoadJS !== false)
			BX.loadScript('/bitrix/js/main/file_dialog.js');
		return setTimeout(function(){window[nameBtnClick](false, Params)}, 50);
	}

	var oConfig =
	{
		submitFuncName : functionFileDialogSubmit,
		select : 'F',
		operation: 'O',
		showUploadTab : true,
		showAddToMenuTab : false,
		site : '<?=SITE_ID?>',
		path : pathOpenDialog,
		lang : '<?=LANGUAGE_ID?>',
		fileFilter : filter,
		allowAllFiles : true,
		saveConfig : true,
		sessid: BX.bitrix_sessid(),
		checkChildren: true,
		genThumb: true,
		zIndex: 2500				
	};

	if(window.oBXFileDialog && window.oBXFileDialog.UserConfig)
	{
		UserConfig = oBXFileDialog.UserConfig;
		oConfig.path = UserConfig.path;
		oConfig.site = UserConfig.site;
	}

	if (Params.path)
		oConfig.path = Params.path;
	if (Params.site)
		oConfig.site = Params.site;

	oBXFileDialog = new BXFileDialog();
	oBXFileDialog.Open(oConfig, UserConfig);
}

function setPathForDialogFile(filename, path, site, title, menu) {
	path = jsUtils.trim(path);
	path = path.replace(/\\/ig,"/");
	path = path.replace(/\/\//ig,"/");
	if (path.substr(path.length-1) == "/")
		path = path.substr(0, path.length-1);
	var full = (path + '/' + filename).replace(/\/\//ig, '/');
	if (path == '')
		path = '/';

	var arBuckets = [];
	if(arBuckets[site])
	{
		full = arBuckets[site] + filename;
		path = arBuckets[site] + path;
	}

	if ('F' == 'D')
		name = full;
	return full;
}

function getPathForOpenDialog(value) {
	var value = value.split("/");
	var res = "";
	for(i=0;i<value.length-1;i++){
		res = res.concat("/", value[i]);
	}
	res = res.substr(1);
	return res;
}

window.BtnClickCSS = function(bLoadJS, Params)
{
	openDialog("css", bLoadJS, Params, "BtnClickCSS", "BtnClickResultCSS", getPathForOpenDialog($("#PATH_TO_CSS").val()));
};

window.BtnClickJS = function(bLoadJS, Params)
{
	openDialog("js", bLoadJS, Params, "BtnClickJS", "BtnClickResultJS", getPathForOpenDialog($("#PATH_TO_JS").val()));
};

window.BtnClickResultCSS = function(filename, path, site, title, menu)
{
	$("#PATH_TO_CSS").val(setPathForDialogFile(filename, path, site, title, menu));
};

window.BtnClickResultJS = function(filename, path, site, title, menu)
{
	$("#PATH_TO_JS").val(setPathForDialogFile(filename, path, site, title, menu));
};
</script>

<?
$module_id = "rocketstudio.dpdext";

if(!$USER->IsAdmin() || !CModule::IncludeModule('iblock') || !CModule::IncludeModule($module_id))
	return;	

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/options.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/include.php");
IncludeModuleLangFile(__FILE__);
if (!empty($_REQUEST["REFRESH_TERMINALS"])) {
	$deliveryDPD = new CDeliveryDPD;
	$deliveryDPD->RefreshTerminals();
}

$scenarios = array(
	1=>GetMessage("ROCKETSTUDIO_DPDEXT_SCENARIO_1"),
	2=>GetMessage("ROCKETSTUDIO_DPDEXT_SCENARIO_2")
);
if (!empty($_REQUEST["SCENARIO_DPD"])) {
	COption::SetOptionString($module_id, "SCENARIO_DPD", trim($_REQUEST["SCENARIO_DPD"]));
}

$DB_SCENARIO_ID = COption::GetOptionString($module_id, "SCENARIO_DPD");
if (empty($DB_SCENARIO_ID)) {
	$DB_SCENARIO_ID = 1;
}

$saleForDPDProperties = array(
	"name"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_NAME"),
		"CODES"=>array(
			"FIO",
			"COMPANY"
		)
	),
	"contactFio"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_FIO"),
		"CODES"=>array(
			"FIO",
			"CONTACT_PERSON"
		)
	),
	"contactPhone"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_PHONE"),
		"CODES"=>array(
			"PHONE"
		)
	),
	"ORDER_PROPERTY_LOCATION"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_LOCATION"),
		"CODES"=>array(
			"LOCATION"
		)
	),
	"street"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_STREET"),
		"CODES"=>array(
			"STREET"
		)
	),
	"streetAbbr"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_STREET_ABBR"),
		"CODES"=>array(
			"STREET_ABBR"
		)
	),
	"house"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_HOUSE"),
		"CODES"=>array(
			"HOUSE"
		)
	),
	"houseKorpus"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_KORPUS"),
		"CODES"=>array(
			"KORPUS"
		)
	),
	"str"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_STR"),
		"CODES"=>array(
			"STR"
		)
	),
	"vlad"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_VLAD"),
		"CODES"=>array(
			"VLAD"
		)
	),
	"flat"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_FLAT"),
		"CODES"=>array(
			"FLAT"
		)
	),
	"office"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_OFFICE"),
		"CODES"=>array(
			"OFFICE"
		)
	),
	"datePickup"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_DATE_PICKUP"),
		"CODES"=>array(
			"DATE_PICKUP"
		)
	),
	"pickupTimePeriod"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_PICKUP_TIME_PERIOD"),
		"CODES"=>array(
			"PICKUP_TIME_PERIOD"
		)
	),
	"serviceCode"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_SERVICE_CODE"),
		"CODES"=>array(
			"SERVICE_CODE"
		)
	),
	"serviceVariant"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_SERVICE_VARIANT"),
		"CODES"=>array(
			"SERVICE_VARIANT"
		)
	),
	"cargoNumPack"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_CARGO_NUM_PACK"),
		"CODES"=>array(
			"CARGO_NUM_PACK"
		)
	),
	"cargoWeight"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_CARGO_WEIGHT"),
		"CODES"=>array(
			"CARGO_WEIGHT"
		)
	),
	"cargoRegistered"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_CARGO_REGISTERED"),
		"CODES"=>array(
			"CARGO_REGISTERED"
		)
	),
	"cargoCategory"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_CARGO_CATEGORY"),
		"CODES"=>array(
			"CARGO_CATEGORY"
		)
	),
	"cargoValue"=>array(
		"NAME"=>GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_CARGO_VALUE"),
		"CODES"=>array(
			"CARGO_VALUE"
		) 
	),
);

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("ROCKETSTUDIO_DPDEXT_TAB_1"),
		"ICON" => "support_settings",
		"TITLE" => GetMessage("ROCKETSTUDIO_DPDEXT_TAB_1_TITLE"),
		"OPTIONS" => array(),	
	),
	array(
		"DIV" => "edit2",
		"TAB" => GetMessage("ROCKETSTUDIO_DPDEXT_TAB_2"),
		"ICON" => "support_settings",
		"TITLE" => GetMessage("ROCKETSTUDIO_DPDEXT_TAB_2_TITLE"),
		"OPTIONS" => array(),
	),
	array(
		"DIV" => "edit3",
		"TAB" => GetMessage("ROCKETSTUDIO_DPDEXT_TAB_3"),
		"ICON" => "support_settings",
		"TITLE" => GetMessage("ROCKETSTUDIO_DPDEXT_TAB_3_TITLE"),
		"OPTIONS" => array(),
	),
	array(
		"DIV" => "edit4",
		"TAB" => GetMessage("ROCKETSTUDIO_DPDEXT_TAB_4"),
		"ICON" => "support_settings",
		"TITLE" => GetMessage("ROCKETSTUDIO_DPDEXT_TAB_4_TITLE"),
		"OPTIONS" => array(),
	),
	array(
		"DIV" => "edit5",
		"TAB" => GetMessage("ROCKETSTUDIO_DPDEXT_TAB_5"),
		"ICON" => "support_settings",
		"TITLE" => GetMessage("ROCKETSTUDIO_DPDEXT_TAB_5_TITLE"),
		"OPTIONS" => array(),
	),
	array(
		"DIV" => "status",
		"TAB" => GetMessage("ROCKETSTUDIO_DPDEXT_TAB_STATUS"),
		"ICON" => "support_settings",
		"TITLE" => GetMessage("ROCKETSTUDIO_DPDEXT_TAB_STATUS_TITLE"),
		"OPTIONS" => array(),
	),
	array(
		"DIV" => "edit6",
		"TAB" => GetMessage("ROCKETSTUDIO_DPDEXT_TAB_REFRESH_TERMINALS"),
		"ICON" => "support_settings",
		"TITLE" => GetMessage("ROCKETSTUDIO_DPDEXT_TAB_REFRESH_TERMINALS"),
		"OPTIONS" => array(),
	),
);


$dbSites = CSite::GetList($by = "active", $order = "desc", Array("ACTIVE" => "Y"));
$arSites = array();
$aSubTabs = array();
while($site = $dbSites->Fetch())
{	
	$arSites[] = $site["ID"];
}
$aSubTabs = array();
$arPtype = array();
$db_ptype = CSalePersonType::GetList(Array("SORT" => "ASC"), Array("LID" => $arSites));
while($ptype = $db_ptype->Fetch())
{
	$aSubTabs[] = array(
		"DIV" => "ptype_".$ptype["ID"],
		"TAB" => "(".$ptype["ID"].") ".$ptype["NAME"],
		'TITLE' => ''
	);
	$arPtype[] = $ptype;
}
$subTabControl = new CAdminViewTabControl("subTabControl", $aSubTabs);

$arGroups = array("REFERENCE"=>array(), "REFERENCE_ID"=>array());
$rsGroups = CGroup::GetDropDownList();
while($ar = $rsGroups->Fetch())
{
	$arGroups["REFERENCE"][] = $ar["REFERENCE"];
	$arGroups["REFERENCE_ID"][] = $ar["REFERENCE_ID"];
}

$arIBTypes = array("REFERENCE"=>array(), "REFERENCE_ID"=>array());
$rsIBTypes = CIBlockType::GetList();
while($arIBType = $rsIBTypes->GetNext())
{
	$arIBTypes["REFERENCE"][] = $arIBType["~ID"];
	$arIBTypes["REFERENCE_ID"][] = $arIBType["~ID"];
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($REQUEST_METHOD=="POST" && strlen($Update.$Apply.$RestoreDefaults)>0 && check_bitrix_sessid())
{
	if(strlen($RestoreDefaults) > 0)
	{
		COption::RemoveOption($module_id);
	}
	else
	{
		$arRights = array();
		if(
			isset($_POST["type_right"]) && is_array($_POST["type_right"])
			&& isset($_POST["group_right"]) && is_array($_POST["group_right"])
		)
		{
			$keys = array_keys($_POST["type_right"]);
			foreach($keys as $i)
			{
				if(
					array_key_exists($i, $_POST["type_right"])
					&& array_key_exists($i, $_POST["group_right"])
				)
				{
					$arRights[$_POST["type_right"][$i]][] = $_POST["group_right"][$i];
				}
			}
		}

		foreach($arRights as $type_id => $groups)
		{
			CLists::SetPermission($type_id, $groups);
		}
		
						
		if($_POST["is_test"] === "Y")
		{
			COption::SetOptionInt($module_id, "IS_TEST", 1);
		}
		else
		{
			COption::SetOptionInt($module_id, "IS_TEST", 0);
		}
		
		COption::SetOptionString($module_id, "KLIENT_NUMBER", trim($_REQUEST["KLIENT_NUMBER"]));
		COption::SetOptionString($module_id, "PATH_TO_CSS", trim($_REQUEST["PATH_TO_CSS"]));
		COption::SetOptionString($module_id, "PATH_TO_JS", trim($_REQUEST["PATH_TO_JS"]));
		//COption::SetOptionString($module_id, "TEMPLATE_FOR_INFO_TERMINAL", trim($_REQUEST["TEMPLATE_FOR_INFO_TERMINAL"]));
		COption::SetOptionString($module_id, "KLIENT_KEY", trim($_REQUEST["KLIENT_KEY"]));
		COption::SetOptionString($module_id, "POST_TYPE", trim($_REQUEST["POST_TYPE"]));

		COption::SetOptionString($module_id, "PICKUP_TIME_PERIOD", trim($_REQUEST["PICKUP_TIME_PERIOD"]));
		COption::SetOptionString($module_id, "SENDER_ADDRESS_NAME", trim($_REQUEST["SENDER_ADDRESS_NAME"]));
		COption::SetOptionString($module_id, "SENDER_ADDRESS_FIO", trim($_REQUEST["SENDER_ADDRESS_FIO"]));
		COption::SetOptionString($module_id, "SENDER_ADDRESS_PHONE", trim($_REQUEST["SENDER_ADDRESS_PHONE"]));
		COption::SetOptionString($module_id, "SENDER_ADDRESS_TERMINAL", trim($_REQUEST["SENDER_ADDRESS_TERMINAL"]));
		COption::SetOptionString($module_id, "SENDER_ADDRESS_REGION", trim($_REQUEST["SENDER_ADDRESS_REGION"]));
		COption::SetOptionString($module_id, "SENDER_ADDRESS_CITY", trim($_REQUEST["SENDER_ADDRESS_CITY"]));
		COption::SetOptionString($module_id, "SENDER_ADDRESS_STREET", trim($_REQUEST["SENDER_ADDRESS_STREET"]));
		COption::SetOptionString($module_id, "SENDER_ADDRESS_STREET_ABBR", trim($_REQUEST["SENDER_ADDRESS_STREET_ABBR"]));		
		COption::SetOptionString($module_id, "SENDER_ADDRESS_HOUSE", trim($_REQUEST["SENDER_ADDRESS_HOUSE"]));
		COption::SetOptionString($module_id, "SENDER_ADDRESS_HOUSEKORPUS", trim($_REQUEST["SENDER_ADDRESS_HOUSEKORPUS"]));
		COption::SetOptionString($module_id, "SENDER_ADDRESS_STR", trim($_REQUEST["SENDER_ADDRESS_STR"]));
		COption::SetOptionString($module_id, "SENDER_ADDRESS_VLAD", trim($_REQUEST["SENDER_ADDRESS_VLAD"]));
		COption::SetOptionString($module_id, "SENDER_ADDRESS_OFFICE", trim($_REQUEST["SENDER_ADDRESS_OFFICE"]));
		COption::SetOptionString($module_id, "SENDER_ADDRESS_FLAT", trim($_REQUEST["SENDER_ADDRESS_FLAT"]));
		
		if(is_numeric(trim($_REQUEST["CARGO_NUMPACK"])))
		{		
			COption::SetOptionString($module_id, "CARGO_NUMPACK", trim($_REQUEST["CARGO_NUMPACK"]));
		};
		COption::SetOptionString($module_id, "CARGO_CATEGORY", trim($_REQUEST["CARGO_CATEGORY"]));
		
		foreach($arPtype as $typePl)
		{			
			foreach($saleForDPDProperties as $saleForDPDProperty=>$saleForDPDPropertyData) {
				COption::SetOptionString($module_id, $saleForDPDProperty."_".$typePl["ID"], trim($_REQUEST[$saleForDPDProperty."_".$typePl["ID"]]));
			}			
		}
		
		COption::SetOptionString($module_id, "STATUS_ORDER_DELIVERY", trim($_REQUEST["STATUS_ORDER_DELIVERY"]));
		COption::SetOptionString($module_id, "STATUS_ORDER_DELIVERED", trim($_REQUEST["STATUS_ORDER_DELIVERED"]));
					
	}

	if(strlen($Update) > 0 && strlen($_REQUEST["back_url_settings"]) > 0)
		LocalRedirect($_REQUEST["back_url_settings"]);
	else
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
}
	
$IS_TEST = COption::GetOptionInt($module_id, "IS_TEST");
$KLIENT_NUMBER = COption::GetOptionString($module_id, "KLIENT_NUMBER");
$PATH_TO_JS = COption::GetOptionString($module_id, "PATH_TO_JS");
if (empty($PATH_TO_JS)) {
	$PATH_TO_JS = "/assets/js/dpd_map.js";
}
$PATH_TO_CSS = COption::GetOptionString($module_id, "PATH_TO_CSS");
if (empty($PATH_TO_CSS)) {
	$PATH_TO_CSS = "/assets/css/dpd_map.css";
}
// $TEMPLATE_FOR_INFO_TERMINAL = COption::GetOptionString($module_id, "TEMPLATE_FOR_INFO_TERMINAL");
// if (empty($TEMPLATE_FOR_INFO_TERMINAL)) {
	// $TEMPLATE_FOR_INFO_TERMINAL = "
// #terminalCode#, 
// #terminalName#, 
// #terminalAddress#,
// #scheduleSelfPickup#,
// #scheduleSelfDelivery#";
// }
$KLIENT_KEY = COption::GetOptionString($module_id, "KLIENT_KEY");
$POST_TYPE = COption::GetOptionString($module_id, "POST_TYPE");

$PICKUP_TIME_PERIOD = COption::GetOptionString($module_id, "PICKUP_TIME_PERIOD");
$SENDER_ADDRESS_NAME = COption::GetOptionString($module_id, "SENDER_ADDRESS_NAME");
$SENDER_ADDRESS_FIO = COption::GetOptionString($module_id, "SENDER_ADDRESS_FIO");
$SENDER_ADDRESS_PHONE = COption::GetOptionString($module_id, "SENDER_ADDRESS_PHONE");
$SENDER_ADDRESS_TERMINAL = COption::GetOptionString($module_id, "SENDER_ADDRESS_TERMINAL");
$SENDER_ADDRESS_REGION = COption::GetOptionString($module_id, "SENDER_ADDRESS_REGION");
$SENDER_ADDRESS_CITY = COption::GetOptionString($module_id, "SENDER_ADDRESS_CITY");
$SENDER_ADDRESS_STREET = COption::GetOptionString($module_id, "SENDER_ADDRESS_STREET");
$SENDER_ADDRESS_STREET_ABBR = COption::GetOptionString($module_id, "SENDER_ADDRESS_STREET_ABBR");
$SENDER_ADDRESS_HOUSE = COption::GetOptionString($module_id, "SENDER_ADDRESS_HOUSE");
$SENDER_ADDRESS_HOUSEKORPUS = COption::GetOptionString($module_id, "SENDER_ADDRESS_HOUSEKORPUS");
$SENDER_ADDRESS_STR = COption::GetOptionString($module_id, "SENDER_ADDRESS_STR");
$SENDER_ADDRESS_VLAD = COption::GetOptionString($module_id, "SENDER_ADDRESS_VLAD");
$SENDER_ADDRESS_OFFICE = COption::GetOptionString($module_id, "SENDER_ADDRESS_OFFICE");
$SENDER_ADDRESS_FLAT = COption::GetOptionString($module_id, "SENDER_ADDRESS_FLAT");

$CARGO_NUMPACK = COption::GetOptionString($module_id, "CARGO_NUMPACK");
$CARGO_CATEGORY = COption::GetOptionString($module_id, "CARGO_CATEGORY");

$arSalePropertiesData = array();
foreach($arPtype as $typePl)
{	
	$arData = array();
	foreach($saleForDPDProperties as $saleForDPDProperty=>$saleForDPDPropertyData) {
		$value = COption::GetOptionString($module_id, $saleForDPDProperty."_".$typePl["ID"]);	
		foreach($saleForDPDPropertyData["CODES"] as $code) {
			if (empty($value)) {
				$value = CRocketstudioDPDExtAdmin::GetPropertyIDForPedding($typePl["ID"], $code);
			}
		}
		$arData = array(
			"VALUE"=>$value,
			"NAME"=>$saleForDPDPropertyData["NAME"],
			"PROPERTY"=>$saleForDPDProperty
		);
		$arSalePropertiesData[$typePl["ID"]][] = $arData;
	}
}

$STATUS_ORDER_DELIVERY = COption::GetOptionString($module_id, "STATUS_ORDER_DELIVERY");
$STATUS_ORDER_DELIVERED = COption::GetOptionString($module_id, "STATUS_ORDER_DELIVERED");
$tabControl->Begin();
?>
<form method="post" action="<? echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<? echo LANGUAGE_ID?>" id="dataload">
	<? $tabControl->BeginNextTab();?>
		<tr>
			<td width="40%"><?=GetMessage("ROCKETSTUDIO_DPDEXT_GUIDE")?>:</td>
			<td>        
				<a href="http://<?=$_SERVER["SERVER_NAME"]."/assets/docs/rs-guide-dpd-ext.pdf"?>" target="_blank"><?=GetMessage("ROCKETSTUDIO_DPDEXT_READ_GUIDE")?></a>
			</td>
		</tr>
    <tr>
		<td width="40%"><?=GetMessage("ROCKETSTUDIO_DPDEXT_IS_TEST")?>:</td>
		<td>        
        	<input type="checkbox" name="is_test" <? if($IS_TEST) echo "checked"?> value="Y">
		</td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage("ROCKETSTUDIO_DPDEXT_SCENARIO")?>:</td>
		<td>        
        <select name="SCENARIO_DPD">
					<? foreach($scenarios as $scenarioId=>$scenarioName):?>
						<? if ($scenarioId == $DB_SCENARIO_ID) {
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}?>
						<option <?=$selected?> value="<?=$scenarioId?>"><?=$scenarioName?></option>
					<? endforeach;?>
				</select>
		</td>
	</tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_KLIENT_NUMBER")?>:</td>
		<td>        
        	<input type="text" name="KLIENT_NUMBER" value="<?=$KLIENT_NUMBER?>">
		</td>
	</tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_KLIENT_KEY")?>:</td>
		<td>        
        	<input type="text" name="KLIENT_KEY" value="<?=$KLIENT_KEY?>">
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_POST_TYPE")?>:</td>
		<td>
			<select name="POST_TYPE" id="POST_TYPE">
				<option <?if($POST_TYPE==''):?>selected=""<?endif?> value=""><?=GetMessage("ROCKETSTUDIO_DPDEXT_POST_TYPE_NONE")?></option>
				<option <?if($POST_TYPE=='FROM_TERMINAL'):?>selected=""<?endif?> value="FROM_TERMINAL"><?=GetMessage("ROCKETSTUDIO_DPDEXT_POST_TYPE_TERMINAL")?></option>
				<option <?if($POST_TYPE=='FROM_DOOR'):?>selected=""<?endif?> value="FROM_DOOR"><?=GetMessage("ROCKETSTUDIO_DPDEXT_POST_TYPE_DOOR")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_PATH_TO_CSS")?>:</td>
		<td>        
        <input type="text" id="PATH_TO_CSS" name="PATH_TO_CSS" value="<?=$PATH_TO_CSS?>">
				<input type="button" onclick="BtnClickCSS()" value="<?=GetMessage("OPEN_FILE")?>">
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_PATH_TO_JS")?>:</td>
		<td>        
        <input type="text" id="PATH_TO_JS" name="PATH_TO_JS" value="<?=$PATH_TO_JS?>">
				<input type="button" onclick="BtnClickJS()" value="<?=GetMessage("OPEN_FILE")?>">
		</td>
	</tr>
	<!--<tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_TEMPLATE_FOR_INFO_TERMINAL")?>:</td>
		<td>        
        <textarea rows="10" cols="30" name="TEMPLATE_FOR_INFO_TERMINAL"><?=$TEMPLATE_FOR_INFO_TERMINAL?></textarea>
		</td>
	</tr>-->
	<? //echo $POST_TYPE;?>
    <? $tabControl->BeginNextTab();?>
    <tr>
		<td width="40%"><?=GetMessage("ROCKETSTUDIO_DPDEXT_PICKUP_TIME_PERIOD_TITLE")?>:</td>
		<td>
        	<?
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rocketstudio.mediacontent/resolutions.php");
            $pickupTimePeriod = array(                
                "9-18" => GetMessage("ROCKETSTUDIO_DPDEXT_PICKUP_TIME_PERIOD_9_18"),
				"9-13" => GetMessage("ROCKETSTUDIO_DPDEXT_PICKUP_TIME_PERIOD_9_13"),
				"13-18" => GetMessage("ROCKETSTUDIO_DPDEXT_PICKUP_TIME_PERIOD_13_18"),
            );
            $arrPickupTimePeriod = Array("reference" => array_values($pickupTimePeriod), "reference_id" => array_keys($pickupTimePeriod));
            echo SelectBoxFromArray("PICKUP_TIME_PERIOD", $arrPickupTimePeriod, $PICKUP_TIME_PERIOD, "");
            ?> 
		</td>
	</tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_NAME")?>:</td>
		<td>        
        	<input type="text" name="SENDER_ADDRESS_NAME" value='<?=$SENDER_ADDRESS_NAME?>'>
		</td>
	</tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_FIO")?>:</td>
		<td>        
        	<input type="text" name="SENDER_ADDRESS_FIO" value='<?=$SENDER_ADDRESS_FIO?>'>
		</td>
	</tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_PHONE")?>:</td>
		<td>        
        	<input type="text" name="SENDER_ADDRESS_PHONE" value='<?=$SENDER_ADDRESS_PHONE?>'>
		</td>
	</tr>
    <tr>
    	<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_REGION")?>:</td>
        <td>            
            <?
			$regions_arr = CRocketstudioDPDExt::RegionList();
            $regions_arr_b = Array("reference" => array_values($regions_arr), "reference_id" => array_values($regions_arr));
            echo SelectBoxFromArray("SENDER_ADDRESS_REGION", $regions_arr_b, $SENDER_ADDRESS_REGION, "");
            ?>
        </td>
    </tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_CITY")?>:</td>
		<td>        
        	<input type="text" name="SENDER_ADDRESS_CITY" value='<?=$SENDER_ADDRESS_CITY?>'>
		</td>
	</tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_STREET")?>:</td>
		<td>        
        	<input type="text" name="SENDER_ADDRESS_STREET" value='<?=$SENDER_ADDRESS_STREET?>'>
		</td>
	</tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_STREET_ABBR")?>:</td>
		<td>        
        	<input type="text" name="SENDER_ADDRESS_STREET_ABBR" value='<?=$SENDER_ADDRESS_STREET_ABBR?>'>
		</td>
	</tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_HOUSE")?>:</td>
		<td>        
        	<input type="text" name="SENDER_ADDRESS_HOUSE" value='<?=$SENDER_ADDRESS_HOUSE?>'>
		</td>
	</tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_HOUSEKORPUS")?>:</td>
		<td>        
        	<input type="text" name="SENDER_ADDRESS_HOUSEKORPUS" value='<?=$SENDER_ADDRESS_HOUSEKORPUS?>'>
		</td>
	</tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_STR")?>:</td>
		<td>        
        	<input type="text" name="SENDER_ADDRESS_STR" value='<?=$SENDER_ADDRESS_STR?>'>
		</td>
	</tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_VLAD")?>:</td>
		<td>        
        	<input type="text" name="SENDER_ADDRESS_VLAD" value='<?=$SENDER_ADDRESS_VLAD?>'>
		</td>
	</tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_OFFICE")?>:</td>
		<td>        
        	<input type="text" name="SENDER_ADDRESS_OFFICE" value='<?=$SENDER_ADDRESS_OFFICE?>'>
		</td>
	</tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_FLAT")?>:</td>
		<td>        
        	<input type="text" name="SENDER_ADDRESS_FLAT" value='<?=$SENDER_ADDRESS_FLAT?>'>
		</td>
	</tr>
    <tr>
		<td></td>
		<td>        
        	<?=GetMessage("ROCKETSTUDIO_DPDEXT_REQUARI")?>
		</td>
	</tr>
    <? echo COption::GetOptionString($module_id, "SENDER_ADDRESS_FLAT");?>
    <? $tabControl->BeginNextTab();?>    
    <tr>
    	<?
        $ar_terminals = CDeliveryDPD::GetTerminals();
		$ar_terminals_code = array();
		foreach($ar_terminals as $terminal):
			if($terminal["terminalCode"])
			{
				$ar_terminals_code[$terminal["terminalCode"]] = $terminal["terminalName"];
			}
		endforeach;
		?>
			<p><?=GetMessage("ROCKETSTUDIO_DPDEXT_TAB_3_NOTE")?></p>
    	<td width="40%"><?=GetMessage("ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_TERMINAL")?>:</td>
        <td>
        	<?
            $terminalsList = Array("reference" => array_values($ar_terminals_code), "reference_id" => array_keys($ar_terminals_code));
            echo SelectBoxFromArray("SENDER_ADDRESS_TERMINAL", $terminalsList, $SENDER_ADDRESS_TERMINAL, "");
			?>
            <div id="ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_TERMINAL_addr">            	        
            </div>
            <?
			foreach($ar_terminals as $terminal):
			?>	
            <div style="display: none;" class="dpd-terminals-base" data-code='<?=$terminal["terminalCode"]?>' data-address='<?=$terminal["terminalAddress"]?>'></div>
            <?
			endforeach;
			?>			
        </td>
    </tr>
    <? $tabControl->BeginNextTab();?>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_CARGO_NUMPACK")?>:</td>
		<td>        
        	<input type="text" name="CARGO_NUMPACK" value='<?=$CARGO_NUMPACK?>'>
		</td>
	</tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_CARGO_CATEGORY")?>:</td>
		<td>        
        	<input type="text" name="CARGO_CATEGORY" value='<?=$CARGO_CATEGORY?>'>
		</td>
	</tr>
    <? $tabControl->BeginNextTab();?>
    <tr>
		<td valign="top" colspan="2">
        	
			<?
			$subTabControl->Begin();
			foreach($arPtype as $typePl)
			{
				$subTabControl->BeginNextTab();				
				?>
				<table width="100%">
                	<?
					$OrderProps = CRocketstudioDPDExt::GetOrderProps($typePl["ID"]);
					$OrderProps[] = array(
						"ID" => 0,
						"NAME" => GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_PROPERTY_CUSTOM")
					);
					$OrderProps = array_reverse($OrderProps, true);
					?>   
					<? foreach($arSalePropertiesData[$typePl["ID"]] as $arSalePropertiesDataItem):?>
					<tr>
						<td width="40%"><?=$arSalePropertiesDataItem["NAME"]?>:</td>
						<td>					
							<select name="<?=$arSalePropertiesDataItem["PROPERTY"]?>_<?=$typePl["ID"]?>">
								<? foreach($OrderProps as $OrderProp):?>
									<option <? if($arSalePropertiesDataItem["VALUE"] == $OrderProp["ID"]) echo('selected="selected"');?> value="<?=$OrderProp["ID"]?>"><?=$OrderProp["NAME"]?></option>
								<? endforeach;?>
							</select>
						</td>
					</tr>
					<? endforeach;?>                             
       </table>
                <?
			}
			$subTabControl->End();
			?>
            
		</td>
	</tr>
    <? $tabControl->BeginNextTab();?>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_STATUS_ORDER_DELIVERY")?>:</td>
		<td>
     <?
			$arrStatusOrderDelivery = array();
			$db_status = CSaleStatus::GetList(array("SORT" => "ASC"), array("LID" => SITE_ID));
			while($status = $db_status->Fetch())
			{
				$arrStatusOrderDelivery[] = $status;
			}
			$arrStatusOrderDelivery[] = array(
				"ID" => 0,
				"NAME" => GetMessage("ROCKETSTUDIO_DPDEXT_STATUS_ORDER_CUSTOM")
			);
			$arrStatusOrderDelivery = array_reverse($arrStatusOrderDelivery, true);
			?>		
            <select name="STATUS_ORDER_DELIVERY">
                <? 
								foreach($arrStatusOrderDelivery as $statusOrderDelivery):?>
                <option <? if($STATUS_ORDER_DELIVERY == $statusOrderDelivery["ID"]) echo('selected="selected"');?> value="<?=$statusOrderDelivery["ID"]?>">[<?=$statusOrderDelivery["ID"]?>] <?=$statusOrderDelivery["NAME"]?></option>
                <? endforeach;?>
            </select>
		</td>
	</tr>
    <tr>
		<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_STATUS_ORDER_DELIVERED")?>:</td>
		<td>		
            <select name="STATUS_ORDER_DELIVERED">
                <? foreach($arrStatusOrderDelivery as $statusOrderDelivery):?>
                <option <? if($STATUS_ORDER_DELIVERED == $statusOrderDelivery["ID"]) echo('selected="selected"');?> value="<?=$statusOrderDelivery["ID"]?>">[<?=$statusOrderDelivery["ID"]?>] <?=$statusOrderDelivery["NAME"]?></option>
                <? endforeach;?>
            </select>
		</td>
	</tr>
	<? $tabControl->BeginNextTab();?>
		<tr>
			<td><?=GetMessage("ROCKETSTUDIO_DPDEXT_TAB_REFRESH_TERMINALS")?>:</td>
			<td>
				<input type="submit" value="<?=GetMessage("REFRESH_TERMINALS")?>" name="REFRESH_TERMINALS" id="REFRESH_TERMINALS">
			</td>
		</tr>
	<? $tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<? if(strlen($_REQUEST["back_url_settings"])>0):?>
		<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
	<? endif?>
	<input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="return confirm('<? echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<? echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
<? $tabControl->End();?>
</form>
<style>
.adm-detail-content-cell-r input[type=text] {
	width: 350px;
}
</style>
<script>
$(document).ready(function(){
	
	var terminalActive = function(){
		var code = $('#SENDER_ADDRESS_TERMINAL').val();
		var addr = '';
		$('.dpd-terminals-base').each(function(){
			if($(this).attr('data-code') == code) addr = $(this).attr('data-address');
		});
		$('#ROCKETSTUDIO_DPDEXT_SENDER_ADDRESS_TERMINAL_addr').html(addr);
	};
	
	$(document).on('change', '#SENDER_ADDRESS_TERMINAL', function(){
		terminalActive();
	});
	
	terminalActive();
	
});
</script>
<?ShowNote(GetMessage('ROCKETSTUDIO_DPDEXT_KEY_INFO'), 'adm-info-message');?>