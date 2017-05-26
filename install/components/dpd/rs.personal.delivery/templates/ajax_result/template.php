<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<tr>
	<td><?=GetMessage("NUMBER_ORDER")?></td><td><?=$arResult['DPD']['orderNumberInternal']?></td>
</tr>
<? if (!empty($arResult['DPD']['errorMessage'])):?>
<tr>
	<td><?=GetMessage("MESSAGE")?></td><td><?=$arResult['DPD']['errorMessage']?></td>
</tr>
<? endif;?>
<tr>
	<td><?=GetMessage("STATUS")?></td><td><?=GetMessage($arResult['DPD']['status'])?></td>
</tr>
