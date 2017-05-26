<?
$iModuleID = "rocketstudio.dpdext";

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
 
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$iModuleID."/include.php");

IncludeModuleLangFile(__FILE__);
?>
<? 
if (!empty($_POST["CLOSE"])) {
	LocalRedirect("/bitrix/admin/sale_order_view.php?ID={$_REQUEST["ID"]}&lang=ru&filter=Y&set_filter=Y");
}?>
<form method="POST" action="">
<div class="adm-workarea cbx-adm-workarea" style="width: 600px;">
	<div class="cbx-adm-workarea__head">
    	<?=GetMessage("ROCKETSTUDIO_DPDEXT_ORDER_GET_TITLE")?>
    </div>
	<div class="cbx-adm-workarea__body">
        <table>
        	<tr>
            	<td width="50%">&nbsp;</td>
                <td>&nbsp;</td>
            </tr>            
            <?=CRocketstudioDPDExtAdmin::ShowPropertyOrderDPD($_REQUEST["ORDER_DPD"], $_REQUEST["ID"])?>
        </table>    
        <div class="cbx-adm-workarea__btns">
        	<?php /*?><a href="/bitrix/admin/dpdext_admin_set_order.php?ID=<?=$_REQUEST["ID"]?>" id="dpd-admin-get-order-btn-save" type="button" class="adm-btn adm-btn-green adm-btn-add"><?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_GET_SAVE")?></a><?php */?>
        	<input name="CLOSE" id="dpd-admin-get-order-btn-cansel" class="cbx-close-on" type="submit" value="<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_GET_CANCEL")?>">
        </div>
    </div>    
</div><!--adm-workarea-->
</form>