<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<input type="hidden" name="DPD_CITY_ID" value="<?=$arResult["ORDER"]["LOCATION_TO"]?>"><br />
<?  $nameinput = $arResult["PROFILE"].":".$arResult["ORDER"]["LOCATION_TO"]; ?>
<? foreach($arResult["TERMINALS"] as $i=>$terminal):?>
	<?
		$checked = '';
		$nameinputCode = $arResult["PROFILE"].":".$arResult["ORDER"]["LOCATION_TO"].'_'.$terminal["terminalCode"];

		if($i == 0 && empty($_REQUEST[$nameinput])) {
       $checked = 'checked="checked"';
    } elseif(!empty($_REQUEST[$nameinput])) {
			if($_REQUEST[$nameinput] == $terminal["terminalCode"]) $checked = 'checked="checked"';
    }
?>
		<div class="dpd_terminal_block">
			<input id="TERMINAL_CODE" name="TERMINAL_CODE" style="display: inline-block;" type="radio" name="<?=$nameinput?>" value="<?=$terminal["terminalCode"]?>" <?=$checked?> >
			<label for="<?=$arParams["DPD_TARIFF"]?>:<?=$arParams["profile"]?>">
				<b><?=$terminal["terminalName"]?></b><br />
				<small><?=$terminal["terminalAddress"]?></small>
			</label>
		</div>
<? endforeach;?>
<span class="show_dpd_map" onclick="dpdShowMap(this, '.dpd_map_popup_<?=$arResult["PROFILE"]?>', '<?=$this->__component->__name?>', 'map', '<?=$arResult["DATA_FOR_MAP"]?>');"><?=GetMessage("RS_DPD_SHOW_MAP")?></span>
<div class="dpd_map_popup_<?=$arResult["PROFILE"]?>" style="display:none;"></div>