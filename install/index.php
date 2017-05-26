<?
IncludeModuleLangFile(__FILE__);
Class rocketstudio_dpdext extends CModule
{
	var $MODULE_ID = 'rocketstudio.dpdext';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';
	var $addSaleProperties = array(
		"STATUS_DPD",
		//"INFO_TERMINAL",
		"TERMINAL_CODE",
		"STREET_ABBR",
		"STREET",
		"FIO",
		"COMPANY",
		"CONTACT_PERSON",
		"PHONE",
		"HOUSE",
		"KORPUS",
		"STR",
		"VLAD",
		"FLAT",
		"OFFICE",
		"SERVICE_CODE",
		"SERVICE_VARIANT",
		"CARGO_NUM_PACK",
		"CARGO_WEIGHT", 
		"CARGO_CATEGORY",
		"CARGO_VALUE",
		"CARGO_REGISTERED",
		"DATE_PICKUP",
		"PICKUP_TIME_PERIOD",
		"ORDER_DPD_ID",
		"LOCATION"
	);
	var $prefixDPD = array(
		"dpd_csm",
		"dpd_bzp",
		"dpd_cur",
		"dpd_dpt",
		"dpd_ecn",
		"dpd_ndy",
		"dpd_ten",
		"dpd_pcl"
	);

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("rocketstudio.DPDEXT_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("rocketstudio.DPDEXT_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("rocketstudio.DPDEXT_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("rocketstudio.DPDEXT_PARTNER_URI");
	}

	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;

		RegisterModuleDependences('main', 'OnPageStart', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'PageStart');
		RegisterModuleDependences('main', 'OnAdminTabControlBegin', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'MyOnAdminTabControlBegin');
		RegisterModuleDependences('main', 'OnAdminContextMenuShow', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'MyOnAdminContextMenuShow');
		RegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepComplete', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'SaleComponentOrderOneStepComplete');
		RegisterModuleDependences('sale', 'OnOrderAdd', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'OrderAdd');
		RegisterModuleDependences('sale', 'OnOrderUpdate', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'OrderUpdate');
		RegisterModuleDependences('sale', 'OnOrderDelete', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'OrderDelete');
		RegisterModuleDependences('sale', 'OnSaleCalculateOrderDelivery', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'CalculateOrderDelivery');
		RegisterModuleDependences('sale', 'OnSaleCalculateOrder', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'SaleCalculateOrder');
		if(!$DB->Query("SELECT 'x' FROM b_dpdext_states", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/db/".$DBType."/install.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}
		return true;
	}

	function UnInstallDB($arParams = array())
	{

		global $DB, $DBType, $APPLICATION;

		UnRegisterModuleDependences('main', 'OnPageStart', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'PageStart');
		UnRegisterModuleDependences('main', 'OnAdminTabControlBegin', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'MyOnAdminTabControlBegin');
		UnRegisterModuleDependences('main', 'OnAdminContextMenuShow', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'MyOnAdminContextMenuShow');
		UnRegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepComplete', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'SaleComponentOrderOneStepComplete');
		UnRegisterModuleDependences('sale', 'OnOrderAdd', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'OrderAdd');
		UnRegisterModuleDependences('sale', 'OnOrderUpdate', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'OrderUpdate');
		UnRegisterModuleDependences('sale', 'OnOrderDelete', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'OrderDelete');
		UnRegisterModuleDependences('sale', 'OnSaleCalculateOrderDelivery', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'CalculateOrderDelivery');
		UnRegisterModuleDependences('sale', 'OnSaleCalculateOrder', $this->MODULE_ID, 'CRocketstudioDPDExtEvents', 'SaleCalculateOrder');
		$this->errors = false;

		$this->errors = false;
		if($DB->Query("SELECT 'x' FROM b_dpdext_states", true))
		{
				$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/db/".$DBType."/uninstall.sql");
		}
		if($this->errors !== false)
		{
				$APPLICATION->ThrowException(implode("", $this->errors));
				return false;
		}
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles($arParams = array())
	{
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/js'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.$this->MODULE_ID.'/'.$item, $ReWrite = True, $Recursive = True);
				}
				closedir($dir);
			}
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/panel'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/panel/'.$this->MODULE_ID.'/'.$item, $ReWrite = True, $Recursive = True);
				}
				closedir($dir);
			}
		}
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin', true);
    CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/ajax', $_SERVER['DOCUMENT_ROOT'].'/ajax', true);
    CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/delivery/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/sale_delivery/', true, true);
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.$item, $ReWrite = True, $Recursive = True);
				}
				closedir($dir);
			}
		}
		
		CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/install/assets/',
				$_SERVER["DOCUMENT_ROOT"]."/assets/", true, true
    );
		
		return true;
	}

	function UnInstallFiles()
	{
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/panel'))
		{
			DeleteDirFilesEx("/bitrix/panel/".$this->MODULE_ID."/");
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/js'))
		{
			DeleteDirFilesEx("/bitrix/js/".$this->MODULE_ID."/");
		}
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin');
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/assets/css', $_SERVER['DOCUMENT_ROOT'].'/assets/css');
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/assets/js', $_SERVER['DOCUMENT_ROOT'].'/assets/js');
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/assets/docs', $_SERVER['DOCUMENT_ROOT'].'/assets/docs');

        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/ajax', $_SERVER['DOCUMENT_ROOT'].'/ajax');
				DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/delivery/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/sale_delivery/");
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || !is_dir($p0 = $p.'/'.$item))
						continue;

					$dir0 = opendir($p0);
					while (false !== $item0 = readdir($dir0))
					{
						if ($item0 == '..' || $item0 == '.')
							continue;
						DeleteDirFilesEx('/bitrix/components/'.$item.'/'.$item0);
					}
					closedir($dir0);
				}
				closedir($dir);
			}
		}
		return true;
	}

	function InstallModule()
	{
		if(!CModule::IncludeModule('iblock') or !CModule::IncludeModule('catalog') or !CModule::IncludeModule("sale"))
		{
			die(GetMessage("ERROR_MODULE"));
		};

		$arPtypes = $this->getSalePersonTypesIds();
		foreach($arPtypes as $Ptype)
		{
			foreach($this->addSaleProperties as $addSaleProperty) {
				$DPD_GROUP_NAME = $addSaleProperty;
				$DPD_GROUP_ID = "";
				$STATUS_CODE = $addSaleProperty;
				$statusPropID = '';

				$db_propsGroup = CSaleOrderPropsGroup::GetList(
					array("SORT" => "ASC"),
					array("PERSON_TYPE_ID" => $Ptype, "NAME"=>GetMessage("NAME_GROUP_DPD")),
					false,
					false,
					array()
				);
				$propsGroup = $db_propsGroup->Fetch();
				if (empty($propsGroup)) {
					$DPD_GROUP_ID = CSaleOrderPropsGroup::Add(array(
						"PERSON_TYPE_ID"=>$Ptype,
						"NAME"=>GetMessage("NAME_GROUP_DPD")
					));
				} else {
					$DPD_GROUP_ID = $propsGroup["ID"];
				}

				$arFields = array(
					 "PERSON_TYPE_ID" => $Ptype,
					 "NAME" => GetMessage("rocketstudio.DPDEXT_".$addSaleProperty),
					 "TYPE" => "TEXTAREA",
					 "REQUIED" => "N",
					 "DEFAULT_VALUE" => "",
					 "SORT" => 100,
					 "CODE" => $STATUS_CODE,
					 "USER_PROPS" => "Y",
					 "IS_LOCATION" => "N",
					 "IS_LOCATION4TAX" => "N",
					 "PROPS_GROUP_ID" => $DPD_GROUP_ID,
					 "SIZE1" => 0,
					 "SIZE2" => 0,
					 "DESCRIPTION" => "",
					 "IS_EMAIL" => "N",
					 "IS_PROFILE_NAME" => "N",
					 "IS_PAYER" => "N",
					 "UTIL" => "Y"
				);
				if (in_array($addSaleProperty, array(
					"STREET_ABBR", "STREET"))) {
					$arFields["UTIL"] = "N";
					$arFields["TYPE"] = "STRING";
					$arFields["REQUIED"] = "Y";
				}
				
				if ($addSaleProperty == "CARGO_REGISTERED") {
					$arFields["TYPE"] = "Y/N";
				}
				
				$excludeSaleProperties = array(
					"STATUS_DPD",
					//"INFO_TERMINAL",
					"TERMINAL_CODE",
					"STREET_ABBR",
					"STREET"
				);
				
				if (!in_array($addSaleProperty, $excludeSaleProperties)) {
					$arFields["UTIL"] = "N";
					$arFields["TYPE"] = "STRING";
				}
				
				if (in_array($addSaleProperty, array("SERVICE_CODE",
					"SERVICE_VARIANT",
					"CARGO_NUM_PACK",
					"CARGO_WEIGHT",
					"CARGO_CATEGORY",
					"CARGO_VALUE",
					"CARGO_REGISTERED",
					"DATE_PICKUP",
					"ORDER_DPD_ID"
				))) {
							$arFields["UTIL"] = "Y";
				}
				if ($addSaleProperty == "LOCATION") {
					$arFields["IS_LOCATION4TAX"] = "Y";
					$arFields["TYPE"] = "LOCATION";
					$arFields["IS_LOCATION"] = "Y";
					$arFields["REQUIED"] = "Y";
				}	
				$db_props = CSaleOrderProps::GetList(
					array("SORT" => "ASC"),
					array(
						"PERSON_TYPE_ID" => $Ptype,
						"PROPS_GROUP_ID" => $DPD_GROUP_ID,
						"CODE" => $STATUS_CODE,
					),
					false,
					false,
					array()
				);

				$arrProps = array();
				if ($props = $db_props->Fetch())
				{
					$statusPropID = $props["ID"];
					$arrProps[] = $props;
				}

				if(!$statusPropID)
				{
					if (in_array($STATUS_CODE, $excludeSaleProperties)) {
						$statusPropID = CSaleOrderProps::Add($arFields);
					} else {
						if (!self::GetSalePropertyByCodeAndPersonTypeId($STATUS_CODE, $Ptype)) {
							$statusPropID = CSaleOrderProps::Add($arFields);
						}
					}
				}
			}
		}
	}
	
	static function GetSalePropertyByCodeAndPersonTypeId($code, $personTypeId) {
		$res = CSaleOrderProps::GetList(
			array("SORT" => "ASC"),
			array(
				"PERSON_TYPE_ID"=>$personTypeId,
				"CODE"=>$code
			),
			false,
			false,
			array()
		)->Fetch();
		return $res;
	}

    function AddIbProp($ib_id, $name, $code, $type='S')
    {
        $ibp = new CIBlockProperty;

        $arFields = Array(
            "NAME" => $name,
            "ACTIVE" => "Y",
            "CODE" => $code,
            "PROPERTY_TYPE" => $type, // Список
            "IBLOCK_ID" => $ib_id
        );
        $propId = $ibp->Add($arFields);
        return $propId;
    }
		
	function deleteOptions() {
		global $DB;
		$query = "DELETE FROM `b_option` WHERE `MODULE_ID`='{$this->MODULE_ID}'";
		$DB->Query($query, false);
	}
	
	function deleteSaleProperties() {
		$arPtypes = $this->getSalePersonTypesIds();
		foreach($arPtypes as $Ptype)
		{
			$group = CSaleOrderPropsGroup::GetList(array(), array("NAME"=>GetMessage("NAME_GROUP_DPD"), "PERSON_TYPE_ID"=>$Ptype))->Fetch();
			foreach($this->addSaleProperties as $addSaleProperty) {
				$db_props = CSaleOrderProps::GetList(
					array("SORT" => "ASC"),
					array(
						"PERSON_TYPE_ID" => $Ptype,
						"PROPS_GROUP_ID"=>$group["ID"],
						"CODE" => $addSaleProperty,
					),
					false,
					false,
					array()
				);
				$props = $db_props->Fetch();
				$this->deletePropsForSale($props["ID"]);
				CSaleOrderProps::Delete($props["ID"]);
			}
			
			CSaleOrderPropsGroup::Delete($group["ID"]);
		}
	}
	
	function deletePropsForSale($propId) {
		global $DB;
		$DB->Query("DELETE FROM `b_sale_order_props_value` WHERE `ORDER_PROPS_ID`={$propId}", true);
	}
	
	function deleteDeliveriesForDPD() {
		global $DB;
		$query = $DB->Query("SELECT * FROM `b_sale_delivery_srv`");
		while($result = $query->GetNext()) {
			foreach($this->prefixDPD as $prefix) {
				if(preg_match("!".$prefix."!", $result["CODE"])) {
					$DB->Query("DELETE FROM `b_sale_delivery_srv` WHERE `CODE`='{$result["CODE"]}'", false);
				}
			}
		}
	}
		
	function UnInstallModule()
	{
		CModule::IncludeModule("sale");
		$this->deleteDeliveriesForDPD();
		$this->deleteSaleProperties();
		$this->deleteOptions();
		return true;
	}

	function getSalePersonTypesIds() {
		$dbSites = CSite::GetList($by = "active", $order = "desc", Array("ACTIVE" => "Y"));
		$arSites = array();
		$aSubTabs = array();
		while($site = $dbSites->Fetch())
		{
			$arSites[] = $site["ID"];
		}
		$aSubTabs = array();
		$res = array();
		$db_ptype = CSalePersonType::GetList(Array("SORT" => "ASC"), Array("LID" => $arSites));
		while($ptype = $db_ptype->Fetch())
		{
			$res[] = $ptype["ID"];
		}
		return $res;
	}
	
	function InstallAgents()
	{
		CAgent::AddAgent(
			"CRocketstudioDPDExt::AgentStatusDPD();",
			$this->MODULE_ID,
			"N",
			600,
			"",
			"Y"
		);
		return true;
	}
	
	function UnInstallAgents()
	{
		CAgent::RemoveModuleAgents($this->MODULE_ID);
		return true;
	}
	
	function DoInstall()
	{
		global $APPLICATION;
		if (COption::GetOptionString("sale", "EXPIRATION_PROCESSING_EVENTS") == "N") {
			COption::SetOptionString("sale", "EXPIRATION_PROCESSING_EVENTS", "Y");
		}
		$this->InstallAgents();
		$this->InstallFiles();
		RegisterModule($this->MODULE_ID);
		$this->InstallDB();
		$this->InstallModule();
	}

	function DoUninstall()
	{
		global $APPLICATION;
		$this->UnInstallAgents();
		$this->UnInstallModule();
		$this->UnInstallDB();
		UnRegisterModule($this->MODULE_ID);
		$this->UnInstallFiles();
	}
}
?>