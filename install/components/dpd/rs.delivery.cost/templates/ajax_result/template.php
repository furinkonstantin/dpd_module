<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<? if(!empty($arResult['COST'])): ?>
    <ul>
			<? foreach($arResult['COST'] as $arValue):?>
				<li><?=$arValue['serviceName']?>: <strong><?=$arValue['cost']?>&nbsp;</strong>&nbsp;<i><?=$arValue['days']?>&nbsp;<?=CRocketstudioDPDExt::Declension($arValue['days'], array(GetMessage("RS_DPD_DECLENSION_DAY_1"), GetMessage("RS_DPD_DECLENSION_DAY_2"), GetMessage("RS_DPD_DECLENSION_DAY_3")))?></i> <?=GetMessage("RS_DPD_DESCRIPTION_WEIGHT", array("weight"=>$arValue["WEIGHT"]));?></li>
			<? endforeach;?>
    </ul>
<? else: ?>
	<? if (empty($arParams['CITY'])):?>
    <font class="errortext"><?=GetMessage("RS_DPD_ERROR_EMPTY_CITY")?></font>
	<? else:?>
		<font class="errortext"><?=GetMessage("RS_DPD_ERROR")?></font>
	<? endif;?>
<? endif; ?>