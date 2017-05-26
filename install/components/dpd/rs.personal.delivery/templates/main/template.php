<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
CJSCore::Init(array("jquery"));
?>
<? if(!empty($arResult['DPD']->return)): ?>
    <table data-order_id="<?=$arResult['DPD']->return->orderNumberInternal?>">
        <tr>
            <td><?=GetMessage("RS_NUMBER_ORDER")?></td>
            <td><strong id="ORDER_NUM_DPD"><?=$arResult['DPD']->return->orderNumberInternal?></strong></td>
        </tr>
        <tr>
            <td><?=GetMessage("RS_STATUS")?></td>
            <td class="return_shipping_result">
							<? if (GetMessage($arResult['DPD']->return->status)):?>
               <?=GetMessage($arResult['DPD']->return->status);?>
							<? else:?>
								<?=$arResult['DPD']->return->status;?>
							<? endif;?>
            </td>
        </tr>
				<? if (!empty($arResult['DPD']->return->errorMessage)):?>
				<tr>
					<td><?=GetMessage("RS_REASON")?></td>
					<td><?=$arResult['DPD']->return->errorMessage?></td>
				</tr>
				<? endif;?>
				<? if ($arResult['DPD']->return->status == "OK"):?>
				<tr class="dpd_lc_buttons">
						<td><?=GetMessage("RS_ACTIONS")?></td>
						<td>
								<input type="button" data-dpd_order_id="<?=$arResult['DPD']->return->orderNum?>" data-order_id="<?=$arResult['DPD']->return->orderNumberInternal?>" value="<?=GetMessage("RS_CANCEL_ORDER")?>" class="return_shipping">
						</td>
				</tr>
				<? endif;?>
    </table>
<? elseif(empty($arParams['orderNumberInternal']) && empty($arParams['datePickup'])):?>
	<table>
			<tr>
				<td><input type="button" data-date_insert="<?=$arResult['DPD']["DATE_INSERT"]?>" data-order_id="<?=$arParams['ORDER_ID']?>" value="<?=GetMessage("RS_QUERY_ORDER")?>" class="query_status"></td>
			</tr>
	</table>
<? else:?>
	 <table>
			<tr>
				<td><strong><?=GetMessage("RS_QUERY_ORDER")?></strong></td>
			</tr>
		</table>
<? endif;?>