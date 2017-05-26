<?
$iModuleID = "rocketstudio.dpdext";

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
 
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$iModuleID."/include.php");

IncludeModuleLangFile(__FILE__);

$label = CRocketstudioDPDExtOrder::CreateLabelFileForOrder($_REQUEST["ID"], $_REQUEST["ORDER_DPD"]);
?>
<div class="adm-workarea cbx-adm-workarea" style="width: 600px;">
	<div class="cbx-adm-workarea__head">
    	<?=GetMessage("ROCKETSTUDIO_DPDEXT_POPUP_LABEL_FILE_TITLE");?>
    </div>
	<div class="cbx-adm-workarea__body">
		<? if (!empty($label['url'])):?>
    	<br />
        <br />
    	<a href="<?=$label['url']?>" target="_blank"><?=GetMessage('ROCKETSTUDIO_DPDEXT_PRINT')?></a>
        <br />
        <br />
		<? else:?>
			<?=GetMessage("ROCKETSTUDIO_DPDEXT_POPUP_NO_RESULTS");?>
		<? endif;?>
   </div>    
</div><!--adm-workarea-->