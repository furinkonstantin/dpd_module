<?
$iModuleID = "rocketstudio.dpdext";

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
 
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$iModuleID."/include.php");

IncludeModuleLangFile(__FILE__);
$invoiceFile = CRocketstudioDPDExtOrder::GetInvoiceFileForOrder($_REQUEST["ORDER_DPD"]);
?>
<div class="adm-workarea cbx-adm-workarea" style="width: 600px;">
	<div class="cbx-adm-workarea__head">
    	<?=GetMessage("ROCKETSTUDIO_DPDEXT_POPUP_GET_INVOICE_TITLE");?>
    </div>
	<div class="cbx-adm-workarea__body">
		<? if (!empty($invoiceFile['url'])):?>
    	<br />
        <br />
    	<a href="<?=$invoiceFile['url']?>" target="_blank"><?=GetMessage('ROCKETSTUDIO_DPDEXT_PRINT')?></a>
        <br />
        <br />
		<? else:?>
			<?=GetMessage("ROCKETSTUDIO_DPDEXT_POPUP_NO_RESULTS");?>
		<? endif;?>
    </div>    
</div><!--adm-workarea-->