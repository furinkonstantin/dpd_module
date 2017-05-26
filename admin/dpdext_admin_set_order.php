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
if (!empty($_POST["ORDER_MAKE"])) {
		$orderDPD = CRocketstudioDPDExtOrder::CreateNewOrder($_REQUEST["ID"]);
}?>
	<div class="adm-workarea cbx-adm-workarea" style="width: 600px;">
		<div class="cbx-adm-workarea__head">
				<?
			if($_REQUEST['ADD_AGAIN']):
						echo GetMessage("ROCKETSTUDIO_DPDEXT_POPUP_ORDER_SET_AGAIN_TITLE");
					else:
						echo GetMessage("ROCKETSTUDIO_DPDEXT_POPUP_ORDER_SET_TITLE");
			endif;
			?>	
			</div>
		<?
			$arData = CRocketstudioDPDExtOrder::CompleteDataOrder($_REQUEST["ID"]);
			$saleProperties = CRocketstudioDPDExtOrder::$sale_properties;
			foreach($saleProperties as $i=>$saleProperty) {
				if ($saleProperty == "terminalCode") {
					unset($saleProperties[$i]);
				}
			}
			$readOnlyProperties = array(
				"city",
				"region",
				"countryName"
			);
		?>
		<div class="cbx-adm-workarea__body">
			<? if (!empty($orderDPD)):?>
					<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_ID")?>: <?=$orderDPD["orderNumberInternal"]?><br/>
					<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_DPD")?>: <?=$orderDPD["orderNum"]?><br/>
					<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_STATUS")?>: <?=GetMessage($orderDPD["status"])?><br/>
					<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_MESSAGE")?>: <?=$orderDPD["errorMessage"]?>
			<? endif;?>
			<form id="formOrderSetDPD" action="" method="POST">
					<table>
						<tr>
								<td width="50%">&nbsp;</td>
									<td>&nbsp;</td>
							</tr>             
							<?=CRocketstudioDPDExtAdmin::ShowDateOrder($arData["ORDER_PROPS_FOR_DETAIL"]["datePickup"]);?>
							
							<?=CRocketstudioDPDExtAdmin::ShowHtmlTableRow('', '<input type="hidden" name="orderID" value="'.$_REQUEST["ID"].'" /><input type="hidden" id="add-form-order-dpd-popup-orderNumberInternal" name="orderNumberInternal" value="" /><input type="hidden" name="terminalCode" id="add-form-order-dpd-popup-terminalCode" value="" /><input type="hidden" name="terminalOn" id="add-form-order-dpd-popup-terminalOn" value="" /><input type="hidden" name="serviceVariant" id="add-form-order-dpd-popup-serviceVariant" value="" />')?>           
				<?=CRocketstudioDPDExtAdmin::ShowPropertyPopup(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_PICKUP_TIME_PERIOD"), '', $arData["ORDER_PROPS_FOR_DETAIL"]["pickupTimePeriod"], 'add-form-order-dpd-popup-pickup_time_period', 'pickupTimePeriod', 'dpd-width90', '', '', '', true)?>			
				<?=CRocketstudioDPDExtAdmin::ShowPropertyPopup(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_SERVICE_CODE"), '', $arData["ORDER_PROPS_FOR_DETAIL"]["serviceCode"], 'add-form-order-dpd-popup-serviceCode', 'serviceCode', 'dpd-width90', '', '', '', true)?> 
           
				<?=CRocketstudioDPDExtAdmin::ShowPropertyPopup(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_SERVICE_VARIANT"), '', $arData["ORDER_PROPS_FOR_DETAIL"]["serviceVariant"], 'add-form-order-dpd-popup-serviceVariantFull', 'serviceVariant', 'dpd-width90', '', '', '', true)?>    
					<tr id="dpd-admin-order-terminal-code" class="dpd-block-show dpd-admin-order-terminal-code">
						<td>
							Код терминала:
						</td>
						<td>
							<select name="terminalCode">
								<option value="">Не выбрано</option>
								<? foreach($arData["TERMINALS"] as $arTerminal):?>
									<? if ($arData["TERMINAL_CODE"] == $arTerminal["terminalCode"]) {
										$selected = "selected";
									} else {
										$selected = "";
									}?>
									<option <?=$selected?> value="<?=$arTerminal["terminalCode"]?>"><?=$arTerminal["terminalCode"]." - ".$arTerminal["terminalName"]?></option>
								<? endforeach;?>
							</select>
						</td>
					</tr>
					
					<tr id="dpd-admin-order-cargo-registered" class="dpd-block-show dpd-admin-order-cargo-registered">
						<td>
							Является ли груз ценным:
						</td>
						<td>
							<? if ($arData["ORDER_PROPS_FOR_DETAIL"]["cargoRegistered"] == "Y") {
								$checked = "checked";
							} else {
								$checked = "";
							}?>
							<input <?=$checked?> type="checkbox" value="Y" name="cargoRegistered"> 
						</td>
					</tr>
							<?=CRocketstudioDPDExtAdmin::ShowPropertyPopup(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_CARGO_WEIGHT"), 'input', $arData["ORDER_PROPS_FOR_DETAIL"]["cargoWeight"], 'add-form-order-dpd-popup-cargoWeight', 'cargoWeight', 'dpd-width90')?>
							<?=CRocketstudioDPDExtAdmin::ShowPropertyPopup(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_CARGO_CATEGORY"), 'input', $arData["ORDER_PROPS_FOR_DETAIL"]["cargoCategory"], 'add-form-order-dpd-popup-cargoCategory', 'cargoCategory', 'dpd-width90')?>
							<?=CRocketstudioDPDExtAdmin::ShowPropertyPopup(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_CARGO_NUM_PACK"), 'input', $arData["ORDER_PROPS_FOR_DETAIL"]["cargoNumPack"], 'add-form-order-dpd-popup-cargoNumPack', 'cargoNumPack', 'dpd-width90')?>
							<?=CRocketstudioDPDExtAdmin::ShowPropertyPopup(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_CARGO_VALUE"), 'input', $arData["ORDER_PROPS_FOR_DETAIL"]["cargoValue"], 'add-form-order-dpd-popup-cargoValue', 'cargoValue', 'dpd-width90')?>           
							<? foreach($saleProperties as $saleProperty):?>
								<? if (in_array($saleProperty, $readOnlyProperties)) {
									$readOnly = true;
								} else {
									$readOnly = false;
								}?>
								<?=CRocketstudioDPDExtAdmin::ShowPropertyPopup(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_".strtoupper($saleProperty)), 'input', $arData["ORDER_PROPS_FOR_DETAIL"][$saleProperty], 'add-form-order-dpd-popup-'.$saleProperty, $saleProperty, 'dpd-width90', '', '', '',$readOnly)?>
							<? endforeach;?>          
					</table>
					<div class="cbx-adm-workarea__btns">        	
						<input id="dpd-admin-set-order-btn-save" type="submit" class="adm-btn-save" name="ORDER_MAKE" value="<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_SET_SAVE")?>" title="<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_SET_SAVE_TITLE")?>">
							<input id="dpd-admin-set-order-btn-cansel" name="CLOSE" type="button" class="cbx-close-on" value="<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_SET_CANCEL")?>">
					</div>
			</form>    
			</div>    
	</div>