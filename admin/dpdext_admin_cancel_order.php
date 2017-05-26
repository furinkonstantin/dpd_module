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
<? 
if (!empty($_POST["ORDER_CANCEL"])) {
	$arData = array(
		"orderNum"=>$_REQUEST["ORDER_DPD"],
		"orderNumberInternal"=>$_REQUEST["ID"]
	);
	$orderDPD = CRocketstudioDPDExtOrder::CancelOrder($arData);
	CRocketstudioDPDExtOrder::UpdateStatusByOrderId($arData['orderNumberInternal'],  $orderDPD["status"]);
}?>
<form method="POST" action="">
<div class="adm-workarea cbx-adm-workarea" style="width: 600px;">
	<div class="cbx-adm-workarea__head">
    	<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_CANCEL_TITLE")?>
    </div>
	<div class="cbx-adm-workarea__body">
		<? if (!empty($orderDPD)):?>
				<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_MESSAGE")?>: <?=$orderDPD["errorMessage"]?><br/>
				<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_STATUS")?>: <?=GetMessage($orderDPD["status"])?>
		<? endif;?>
        <table>
        	<tr>
            	<td width="50%">&nbsp;</td>
                <td>&nbsp;</td>
            </tr>    
            <?=CRocketstudioDPDExtAdmin::ShowPropertyOrderDPD($_REQUEST["ORDER_DPD"], $_REQUEST["ID"])?>
        </table>    
        <div class="cbx-adm-workarea__btns">
					<input type="hidden" value="<?=$_REQUEST["ORDER_DPD"]?>" name="ORDER_DPD">
					<input type="hidden" value="<?=$_REQUEST["ID"]?>" name="ORDER_ID">
        	<input name="ORDER_CANCEL" id="dpd-admin-cancel-order-btn" type="submit" class="adm-btn" value="<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_CANCEL_DELETE")?>" />
        	<input name="CLOSE" id="dpd-admin-cancel-order-btn-cansel" class="adm-btn cbx-close-on" type="submit" value="<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_CANCEL_CANCEL")?>">
        </div>
    </div>    
</div><!--adm-workarea-->
</form>