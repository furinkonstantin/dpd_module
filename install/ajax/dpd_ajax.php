<?require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$APPLICATION->IncludeComponent($_REQUEST['componentName'], $_REQUEST['componentTemplate'], $_REQUEST['arParams']);