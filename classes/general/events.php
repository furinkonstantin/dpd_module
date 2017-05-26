<?

IncludeModuleLangFile(__FILE__);

class CRocketstudioDPDExtEvents
{
	
	private static $module_id = 'rocketstudio.dpdext';
	
	function PageStart()
	{
		global $APPLICATION;
		$dir = $APPLICATION->GetCurDir();
		$dirs = explode('/', $dir);
		CJSCore::Init(array("jquery"));
		if($dirs[1] == 'bitrix')
		{
			CJSCore::Init(array('fx', 'popup', 'window', 'ajax', 'ls'));
			$GLOBALS["APPLICATION"]->AddHeadString('<script src="/bitrix/js/'.self::$module_id.'/jquery.colorbox.js"></script>');		
			$GLOBALS["APPLICATION"]->AddHeadString('<link href="/bitrix/panel/'.self::$module_id.'/colorbox2.css" rel="stylesheet">');
			$GLOBALS["APPLICATION"]->AddHeadString('<link href="/bitrix/panel/'.self::$module_id.'/rocketstudio.dpd.css" rel="stylesheet">');
		} else {
			$GLOBALS["APPLICATION"]->SetAdditionalCSS(COption::GetOptionString(self::$module_id, "PATH_TO_CSS"));
			$GLOBALS["APPLICATION"]->AddHeadScript(COption::GetOptionString(self::$module_id, "PATH_TO_JS"));
			$GLOBALS["APPLICATION"]->AddHeadString('<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>');
		}
	}
	
	function CalculateOrderDelivery($arOrder)
	{			

	}
	
	function SaleCalculateOrder($arOrder)
	{

	}
	
	function MyOnAdminTabControlBegin(&$form)
	{		
		if (COption::GetOptionString(self::$module_id, "SCENARIO_DPD") == 2) {
			if($GLOBALS["APPLICATION"]->GetCurPage() == "/bitrix/admin/sale_order_view.php")
			{
				CRocketstudioDPDExtAdmin::SetWorkWithOrdersDPD();
				?>
				<script>
					function setContentForDeiliveryDPD(text) {
						$(".sale-order-props-group").each(function() {
							if ($(this).find(".adm-bus-table-caption-title").text() == '<?=GetMessage("NAME_GROUP_DPD")?>') {
								$(this).after(text);
								return false;
							}
						});
					}
				</script>
				<?
				CRocketstudioDPDExtAdmin::AdminSaleOrder($form);
			}			
		}
	}
	
	function MyOnAdminContextMenuShow(&$items)
	{	
		if($GLOBALS["APPLICATION"]->GetCurPage() == "/bitrix/admin/sale_order.php")
		{
			CRocketstudioDPDExtAdmin::AdminSaleOrderContextMenuShow($items);
		}
	}
	
	function SaleComponentOrderOneStepComplete($ID, $arOrder)
    {
	}
	 
	function OrderAdd($ID, $arOrder)
	{
		//Передаем заказ в систему DPD
		CRocketstudioDPDExtOrder::CreateNewOrder($ID, $arOrder);
	}
	
	function OrderUpdate($ID, $arOrder)
	{
		
	}
		
	function OrderDelete($ID)
	{
		
	}
	
	
}
?>