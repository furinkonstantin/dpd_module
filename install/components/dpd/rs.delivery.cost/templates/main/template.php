<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
CJSCore::Init(array("jquery"));
?>

<div id="dpd_calculate">
    <span class="dpd_calculate_show"><?=GetMessage("RS_DPD_CALCULATE_PRICE_DELIVERY")?></span>
    <div class="dpd_calculate_input">
        <input type="text" placeholder="Город получения" name="CITY">
        <button type="button" id="dpd_calculate_button"><?=GetMessage("RS_DPD_CALCULATE")?></button>
    </div>
    <div class="dpd_calculate_output"></div>
</div>