<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
parse_str($arParams, $resultMap);
if (!$resultMap["IS_MAP"]) {
	$arResult['ITEMS'] = array();
	$arResult["TERMINALS"] = array();
	foreach($arParams["TERMINALS"] as $i=>$value){
		$arResult['ITEMS'][] = array(
				"nameinput" => $value['terminalName'],
				"terminalCode" => $value['terminalCode'],
				"coordinates" => $value["coords"],
				"address"=>$value['terminalAddress']
		);
		$arResult["TERMINALS"][$i] = $value;
	}
	$arResult["PROFILE"] = $arParams["PROFILE"];
	$arResult["ORDER"] = $arParams["ORDER"];
	$arResult["DATA_FOR_MAP"] = http_build_query(array(
			"START_COORDS"=>$arResult['ITEMS'][0]["coordinates"],
			"ITEMS"=>$arResult['ITEMS'],
			"IS_MAP"=>true
		)
	);
} else {
	if (empty($resultMap["START_COORDS"]["latitude"])) {
		$resultMap["START_COORDS"]["latitude"] = $resultMap["ATART_COORDS"]["latitude"];
	}
	$arResult['START_COORDS'] = json_encode($resultMap["START_COORDS"]);
	$ballonContent = "";
	foreach($resultMap["ITEMS"] as $i=>$arItem) {
		ob_start();
		$APPLICATION->IncludeFile($this->__path."/templates/ballon/template.php", compact("arItem"));
		$ballonContent = ob_get_contents();
		ob_end_clean();
		$resultMap['ITEMS'][$i]["content"] = str_replace('"', "", $ballonContent);
	}
	$arResult['ITEMS'] = json_encode($resultMap["ITEMS"]);
}
$this->IncludeComponentTemplate();