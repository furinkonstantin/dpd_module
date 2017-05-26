<?

IncludeModuleLangFile(__FILE__);

class CRocketstudioDPDExtAdmin
{
	
	private static $module_id = 'rocketstudio.dpdext';
	
	function GetPropertyIDForPedding($personTypeId, $code) {
		$res = CSaleOrderProps::GetList(
				array("SORT" => "ASC"),
				array(
					"PERSON_TYPE_ID" => $personTypeId,
					"CODE" => $code,
				),
				false,
				false,
				array()
		)->Fetch();

		return $res["ID"];
	}
	
	function SetWorkWithOrdersDPD() {
		//Создание заказа
		if (!empty($_POST["ORDER_MAKE"])) {
			$orderDPD = CRocketstudioDPDExtOrder::CreateNewOrder($_POST["orderID"]);
			LocalRedirect("/bitrix/admin/sale_order_view.php?ID=".$_POST["orderID"]."&lang=".LANGUAGE_ID."&message_status_dpd=".GetMessage($orderDPD["status"]).'&message_error_dpd='.$orderDPD['errorMessage']);
		}
		//Отмена заказа
		if (!empty($_POST["ORDER_CANCEL"])) {
			$arData = array(
				"orderNum"=>$_POST["ORDER_DPD"],
				"orderNumberInternal"=>$_POST["ORDER_ID"]
			);
			$orderDPD = CRocketstudioDPDExtOrder::CancelOrder($arData);
			CRocketstudioDPDExtOrder::UpdateStatusByOrderId($arData['orderNumberInternal'],  $orderDPD["status"]);
			LocalRedirect("/bitrix/admin/sale_order_view.php?ID=".$_POST["ORDER_ID"]."&lang=".LANGUAGE_ID."&message_status_dpd=".GetMessage($orderDPD["status"]).'&message_error_dpd='.$orderDPD['errorMessage']);
		}
		//Обновление статуса
		if (!empty($_REQUEST["message_status_dpd"]) || !empty($_REQUEST["message_error_dpd"])) {
			$text = GetMessage($_REQUEST["message_status_dpd"]);
			if (!$text) {
				$text = $_REQUEST["message_status_dpd"];
			}
			$message = 
				GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_STATUS").$text.
				"\r\n".
				GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_ERROR").$_REQUEST["message_error_dpd"];
			if (empty($_REQUEST["message_error_dpd"])) {
				$message = 
				GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_STATUS").$text;
			}
			if (empty($text)) {
				$message = 
				GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_STATUS").$_REQUEST["message_error_dpd"];
			}
			echo CAdminMessage::ShowMessage(array(
				"MESSAGE"=>$message,
				"TYPE"=>"OK"
			));
		}
	}
	
	function serviceVariantFullFn($serviceVariant)
	{
		$serviceVariantFull = "";			
		switch($serviceVariant){
			case GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_TT"):
				$serviceVariantFull = GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_SERVICE_VARIANT_TT");
				break;
			case GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_TD"):
				$serviceVariantFull = GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_SERVICE_VARIANT_TD");
				break;
			case GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_DT"):
				$serviceVariantFull = GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_SERVICE_VARIANT_DT");
				break;
			case GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_DD"):
				$serviceVariantFull = GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_SERVICE_VARIANT_DD");
				break;			
		}					
		return $serviceVariantFull;	
	}
	
	function locationID($orderID)
	{
		$db_props = CSaleOrderPropsValue::GetOrderProps($orderID);
		$locaionID = "";
		while ($arProps = $db_props->Fetch())
		{
			if($arProps["TYPE"] == "LOCATION")
			{
				$locaionID = $arProps["VALUE"];
			}
		}		
		return $locaionID;
	}	
	
	function ShowHtmlTableRow($cell1, $cell2, $id, $class)
	{
		$id = ' id="'.$id.'" ';
		$content = '<tr '.$id.' class="'.$class.'"><td class="adm-detail-content-cell-l">';
		if($cell1)
			$content .= $cell1.': ';
		$content .= '</td><td class="adm-detail-content-cell-r">';
		$content .= $cell2;
		$content .= '</td></tr>';
		return $content; 	
	}
	
	function ShowBtn($name, $title, $green = false, $add = false, $id, $url, $class)
	{
		if($green)
		{
			$green = 'adm-btn-green';
		}
		else
		{
			$green = '';
		}
		
		if($add)
		{
			$add = 'adm-btn-add';
		}
		else
		{
			$add = '';
		}
			
		$btn = '<a id="'.$id.'" title="'.$title.'" class="adm-btn '.$green.' '.$add.' '.$class.' "  style="white-space:nowrap;" href="'.$url.'">'.$name.'</a>';
        
		return $btn;
  } 
	
	function ShowTerminalsSelect($selectTerminalCode)
	{		
		$res = CDeliveryDPD::GetTerminals();
		$class = "";
		if($res)
		{		
			$class = 'dpd-terminals-location dpd-terminals-xt';
		}		
		
		$select = '<select id="order_terminals_select" name="order_terminals_select">';
		
		$select .= '<option>'.GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_TAB_TERMINAL_SELECT_NON").'</option>';
		
		foreach($res as $item)
		{
			if($selectTerminalCode == $item["terminalCode"])
			{
				$select .= '<option data-code="'.$item["terminalCode"].'" selected="selected">'.$item["terminalAddress"].'</option>';
			}
			else
			{
				$select .= '<option data-code="'.$item["terminalCode"].'">'.$item["terminalAddress"].'</option>';
			}
		}
		$select .= '</select>';
				
		$cell1 = GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_TAB_TERMINAL_SELECT_TITLE");
		$cell2 = $select;
		$id = 'dpd-admin-order-terminals-select';		
		?>
        
        <script>			
		if(!window.dpdext__OrderSelectTerminal)
		{
			
			var selectParentId = "#<?=$id?>";
			var selectId = "#order_terminals_select";				
			
			window.dpdext__order_location = "<?=$locaionID?>";
			
			var HTMLString__OrderSelectTerminal = '<?=self::ShowHtmlTableRow($cell1, $cell2, $id, $class)?>';
				
			$(function(){
					setContentForDeiliveryDPD(HTMLString__OrderSelectTerminal);
			});			
			
			$(document).on('change', '#order_terminals_select', function(){
				var terminalAddress = $('#order_terminals_select').val();
				BX.showWait();
				$.post(
					"/bitrix/admin/dpdext_ajax.php?dpdext_ajax=addTerminal",
					{							
						orderID: '<?=$_REQUEST["ID"]?>',
						terminalAddress: terminalAddress
					},
					function(data){
						BX.closeWait();					
					},
					"json"		
				);
			});			
						
			var showTerminalsSelect = function()
			{
				if($(selectParentId).hasClass("dpd-terminals-xt") && $(selectParentId).hasClass("dpd-terminals-location"))
				{
					$(selectParentId).show();
				}
				else
				{
					$(selectParentId).hide();
				}
			}
			
			$(document).on('change', '#DELIVERY_ID', function(){
				var tempDeliveryID = $(this).val();				
				var devName = tempDeliveryID.slice(0, 3);
				var devT = tempDeliveryID.slice(-1);
				if(devName == "dpd" && devT == "T")
				{
					$(selectParentId).addClass("dpd-terminals-xt");
				}
				else
				{
					$(selectParentId).removeClass("dpd-terminals-xt");
				}
				if(devName == "dpd")
				{
					$(".dpd-block-show").show();
				}
				else
				{
					$(".dpd-block-show").hide();
				}
				showTerminalsSelect();				
			});		
			
			function getParams(str)
			{
				var tmp = new Array();
				var tmp2 = new Array();
				var param = new Array();
			
				var get = str;	
				if(get != '') {
					tmp = (get.substr(1)).split('&');
					for(var i=0; i < tmp.length; i++){
						tmp2 = tmp[i].split('=');
						param[tmp2[0]] = tmp2[1];
					}
				} else return false;
				return param;
			}
			
			var terminalsSelectReload = function(location)
			{
				
				var f = function(terminals)
				{
					var Select = $(selectId);					
					Select.empty();
					if(terminals)
					{
						option = "";
						$(selectParentId).addClass("dpd-terminals-location");
						for(var i = 0; i < terminals.length; i++)
						{
							option += "<option>";
							option += terminals[i]["terminalAddress"];
							option += "</option>";
						}
						option += "</option>";
						Select.wrapInner(option);
					}
					else
					{
						$(selectParentId).removeClass("dpd-terminals-location");
						option = "none";
						Select.wrapInner('<option>' + option + '</option>');
					}
					showTerminalsSelect();
				}
				
				$.post(
					"/bitrix/admin/dpdext_ajax.php?dpdext_ajax=getTerminals",
					{
						location: location
					},
					function(data){
						f(data);
					},
					"json"
				);
			}

			BX.addCustomEvent('onAjaxSuccess', function(i, k){					
				if(k['url'] == '/bitrix/admin/sale_order_new.php')
				{									
					var data = k['data'];
					var params = getParams(data);
					var location = params["location"];
					if(location && location != window.dpdext__order_location)
					{
						window.dpdext__order_location = location;
						terminalsSelectReload(location);
					}								
				}				
			});		
			
			window.dpdext__OrderSelectTerminal = true;
		}			
		</script>
        
        <?
	}
	
	function ShowDateOrder($date)
	{
		$date = date("d.m.Y",strtotime($date));
		$input = CalendarDate("datePickup", $date, "order_edit_info_form", "10");
		
		$cell1 = GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_TAB_SET_DATE_ORDER_TITLE");
		$cell2 = $input;
		$id = 'dpd-admin-order-set-date';
		$class = 'dpd-block-show dpd-admin-order-set-date';
		return self::ShowHtmlTableRow($cell1, $cell2, $id, $class);
	}
	
	function ShowStatusOrder()
	{			
		$cell1 = GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_TAB_STATUS_TITLE");
		$cell2 = '<span id="status-order-dpd"></span>';
		$id = 'dpd-admin-order-status';
		$class = 'dpd-block-show dpd-admin-order-status';
		?>        
		<script>			
		if(!window.dpdext__OrderStatus)
		{			
			var HTMLString__OrderStatus = '<?=self::showHtmlTableRow($cell1, $cell2, $id, $class)?>';
				
			$(function(){
					setContentForDeiliveryDPD(HTMLString__OrderStatus);
			});						
			
			window.dpdext__OrderStatus = true;
		}			
		</script>
        <?
	}
	
	function ShowOrderBtnAdd($orderDPD)
	{
		$br = "<br>";
		$btnAdd = self::ShowBtn(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_BTN_ADD_NAME"), GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_BTN_ADD_TITLE"), true, true, "add-order-dpd", "/bitrix/admin/dpdext_admin_set_order.php?ID=".$_REQUEST["ID"], 'dpd-order-no-create');
		
		$btnGet = self::ShowBtn(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_BTN_GET_NAME"), GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_BTN_GET_TITLE"), false, false, "get-order-dpd", "/bitrix/admin/dpdext_admin_get_order.php?ID=".$_REQUEST["ID"]."&ORDER_DPD=".$orderDPD, 'dpd-order-create dpd-order-pending');
		
		$btnAddagain = self::ShowBtn(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_BTN_ADDAGAIN_NAME"), GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_BTN_ADDAGAIN_TITLE"), true, true, "addagain-order-dpd", "/bitrix/admin/dpdext_admin_set_order.php?ID=".$_REQUEST["ID"]."&ORDER_DPD=".$orderDPD."&ADD_AGAIN=1", 'dpd-order-create');
		
		$btnCancel = self::ShowBtn(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_BTN_CANCEL_NAME"), GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_BTN_CANCEL_TITLE"), false, false, "cancel-order-dpd", "/bitrix/admin/dpdext_admin_cancel_order.php?ID=".$_REQUEST["ID"]."&ORDER_DPD=".$orderDPD, 'dpd-order-create dpd-order-pending');
		
		$btnCreateLabel = self::ShowBtn(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_BTN_CREATELABEL_NAME"), GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_BTN_CREATELABEL_TITLE"), false, false, "create-label-dpd", "/bitrix/admin/dpdext_admin_label_file.php?ID=".$_REQUEST["ID"]."&ORDER_DPD=".$orderDPD, 'dpd-order-create');
				
		$btnGetInvoice = self::ShowBtn(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_BTN_GETINVOICE_NAME"), GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_BTN_GETINVOICE_TITLE"), false, false, "get-invoice-file", "/bitrix/admin/dpdext_admin_get_invoice.php?&ID=".$_REQUEST["ID"]."&ORDER_DPD=".$orderDPD, 'dpd-order-create');
		
		$btnStatusUpdate = self::ShowBtn(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_BTN_STATUS_UPDATE"), GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_BTN_STATUS_UPDATE_TITLE"), false, false, "dpd-update-status", "/bitrix/admin/dpdext_ajax.php?dpdext_ajax=status_update_order&ID=".$_REQUEST["ID"]."&ORDER_DPD=".$orderDPD, 'dpd-order-create');
		 
		$cell1 = '';
		$cell2 = $btnAdd.' '.$btnGet.' '.$btnStatusUpdate.' '.$btnCreateLabel.' '.$btnGetInvoice.' '.$br.$btnCancel;		
		$id = '';
		$class = 'dpd-block-show';
		?>        
		<script>			
		if(!window.dpdext__OrderBtnAdd)
		{			
			var HTMLString__OrderBtnAdd = '<?=self::showHtmlTableRow($cell1, $cell2, $id, $class)?>';
			$(function(){
				setContentForDeiliveryDPD(HTMLString__OrderBtnAdd);
				
				$("#add-order-dpd, #get-order-dpd, #create-label-dpd, #get-invoice-file, #cancel-order-dpd").click(function(e) {
					e.preventDefault();
					var href = $(this).attr("href");
					$.colorbox({
						href: href
					});
				});
			});		

			$(document).on('click', '.cbx-close-on', function(){
				$.colorbox.close();
				return false;
			});			
			
			window.dpdext__OrderBtnAdd = true;
		}			
		</script>
        <?
	}
	
	function ShowOrderError()
	{
		$error = '<span id="error-order-dpd" class="required"></span>';
		$cell1 = GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_ERROR_TITLE");
		$cell2 = $error;
		$id = 'dpd-admin-error';
		$class = 'dpd-admin-off dpd-admin-error';
		return self::showHtmlTableRow($cell1, $cell2, $id, $class);
	}	
	
	function AdminSaleOrder(&$form)
	{		
		CJSCore::Init(array("jquery"));
		
		ob_start();					
		$arOrderData = CRocketstudioDPDExtOrder::CompleteDataOrder($_REQUEST["ID"]);
		$arDataDPD = array(
			"order"=>array(
				"orderNumberInternal"=>$_REQUEST["ID"],
				"datePickup"=>$arOrderData["ORDER_PROPS_FOR_DETAIL"]["datePickup"]
			)
		);
		
		$dpdService = new DPD_service;
		$orderDPD = $dpdService->getOrderStatus($arDataDPD);
		self::ShowOrderBtnAdd($orderDPD->return->orderNum);
		$sContent = ob_get_contents();
		ob_end_clean();
		$GLOBALS['APPLICATION']->AddHeadString($sContent);
	}
	
	function ShowPropertyPopup($label, $type, $value, $idProp, $name, $classProp, $checked, $id,  $class, $readonly = false)
	{		 
		$cell1 = $label;
		
		if($idProp)
			$idProp = ' id="'.$idProp.'" ';
		else
			$idProp = ' ';
			
		if($classProp)
			$classProp = ' class="'.$classProp.'" ';
		else
			$classProp = ' ';
			
		if($checked)
			$checkedText = ' checked="checked" ';
		else
			$checkedText = '';
			
		if($readonly)		
			$readonly = ' readonly="readonly" ';
		else
			$readonly = '';		
				
		if($type == "")
		{
			$cell2 = '<input'.$idProp.$classProp.'type="text" value="'.$value.'" name="'.$name.'" '.$readonly.' />';
		}
		else if($type == "input")
		{
			$cell2 = '<input'.$idProp.$classProp.'type="text" value="'.$value.'" name="'.$name.'" '.$readonly.' />';
		}
		else if($type == "textarea")
		{
			$cell2 = '<textarea'.$idProp.$classProp.' name="'.$name.'" '.$readonly.'>'.$value.'</textarea>';
		}
		else if($type == "checkbox")
		{
			$classProp .= ' class="'.$classProp.'" ';
			$cell2 = '<input'.$idProp.$classProp.'type="checkbox" value="'.$value.'" name="'.$name.'" '.$checkedText.' />';
		}
		return self::showHtmlTableRow($cell1, $cell2, $id, $class);
	}
	
	function ShowPropertyOrderDPD($orderDPD, $orderID)
	{		
		$arData = CRocketstudioDPDExtOrder::CompleteDataOrder($orderID);
		$h = '';		
		$saleProperties = CRocketstudioDPDExtOrder::$sale_properties;
		foreach($saleProperties as $i=>$saleProperty) {
			if ($saleProperty == "terminalCode") {
				unset($saleProperties[$i]);
			}
		}
		$h .= self::showHtmlTableRow(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_ORDER_NUMBER_INTERNATIONAL"), htmlspecialchars($orderID, ENT_QUOTES));
		$h .= self::showHtmlTableRow(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_NUM"), htmlspecialchars($orderDPD, ENT_QUOTES));
		$h .= self::showHtmlTableRow(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_DATE"), htmlspecialchars($arData["ORDER_PROPS_FOR_DETAIL"]["datePickup"]), ENT_QUOTES);		
		$h .= self::showHtmlTableRow(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_SERVICE_CODE"), htmlspecialchars($arData["ORDER_PROPS_FOR_DETAIL"]["serviceCode"]), ENT_QUOTES);
		$h .= self::showHtmlTableRow(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_SERVICE_VARIANT"), htmlspecialchars(CRocketstudioDPDExtAdmin::serviceVariantFullFn($arData["ORDER_PROPS_FOR_DETAIL"]["serviceVariant"]), ENT_QUOTES));
		$h .= self::showHtmlTableRow(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_TERMINAL"), htmlspecialchars($arData["TERMINAL_NAME"], ENT_QUOTES));		
		$h .= self::showHtmlTableRow(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_CARGO_CATEGORY"), htmlspecialchars($arData["ORDER_PROPS_FOR_DETAIL"]["cargoCategory"]), ENT_QUOTES);
		$h .= self::showHtmlTableRow(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_CARGO_WEIGHT"), htmlspecialchars($arData["ORDER_PROPS_FOR_DETAIL"]["cargoWeight"]), ENT_QUOTES);
		$h .= self::showHtmlTableRow(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_CARGO_NUM_PACK"), htmlspecialchars($arData["ORDER_PROPS_FOR_DETAIL"]["cargoNumPack"]), ENT_QUOTES);
		$h .= self::showHtmlTableRow(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_CARGO_VALUE"), htmlspecialchars($arData["ORDER_PROPS_FOR_DETAIL"]["cargoValue"]), ENT_QUOTES);
		$h .= self::showHtmlTableRow(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_PICKUP_TIME_PERIOD"), htmlspecialchars($arData["ORDER_PROPS_FOR_DETAIL"]["pickupTimePeriod"]), ENT_QUOTES);
		
		foreach($saleProperties as $saleProperty) {
			$h .= self::showHtmlTableRow(GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_".strtoupper($saleProperty)), htmlspecialchars($arData["ORDER_PROPS_FOR_DETAIL"][$saleProperty]), ENT_QUOTES);
		}
		return $h;
	}
	
	function AddOrderScript($orderDPD)
	{							
		$arOrder = CSaleOrder::GetByID($_REQUEST["ID"]);		
				
		$getOrder = CRocketstudioDPDExtMySQLOrder::GetById($orderDPD);
				
		$orderProps = CRocketstudioDPDExt::OrderPropsValue($_REQUEST["ID"], $arOrder["PERSON_TYPE_ID"]);
		
		$locaionID = CRocketstudioDPDExtAdmin::locationID($_REQUEST["ID"]);
		$arLocs = CSaleLocation::GetByID($locaionID, LANGUAGE_ID);
		$region = $arLocs["REGION_NAME"];
		$region = CRocketstudioDPDExt::RegionBxToDpd($region);
		$street_abbr = GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_STREET_ABBR_1");
		$city = $arLocs["CITY_NAME"];
		
		$orderStatus = $getOrder["ORDER_STATUS"];
		$orderError = $getOrder["ORDER_ERROR"];
		$orderNum = $getOrder["ORDER_NUM"];		
		
		$cargoCategory = COption::GetOptionString(self::$module_id, "CARGO_CATEGORY");
		$cargoNumPack = COption::GetOptionString(self::$module_id, "CARGO_NUMPACK");		
		$pickupTimePeriod = COption::GetOptionString(self::$module_id, "PICKUP_TIME_PERIOD");
		
		$price = $arOrder["PRICE"];
		$price = round($price);
		$priceDelivery = $arOrder["PRICE_DELIVERY"];
		$priceDelivery = round($priceDelivery);
		$cargoValue = $price - $priceDelivery;
		$sum_npp = $price - $priceDelivery;
		
		$htmlFlags = '';
						
		$htmlOrder = '';			
		
		$htmlOrderIni = '<label>dpd-admin-order-ini-pickupTimePeriod</label><input name="dpd-admin-order-ini-pickupTimePeriod" id="dpd-admin-order-ini-pickupTimePeriod" type="text" value="'.htmlspecialchars($pickupTimePeriod, ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-cargoValue</label><input name="dpd-admin-order-ini-cargoValue" id="dpd-admin-order-ini-cargoValue" type="text" value="'.htmlspecialchars($cargoValue, ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-cargoNumPack</label><input name="dpd-admin-order-ini-cargoNumPack" id="dpd-admin-order-ini-cargoNumPack" type="text" value="'.htmlspecialchars($cargoNumPack, ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-cargoCategory</label><input name="dpd-admin-order-ini-cargoCategory" id="dpd-admin-order-ini-cargoCategory" type="text" value="'.htmlspecialchars($cargoCategory, ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-country</label><input name="dpd-admin-order-ini-country" id="dpd-admin-order-ini-country" type="text" value="'.htmlspecialchars($arLocs["COUNTRY_NAME"], ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-region</label><input name="dpd-admin-order-ini-region" id="dpd-admin-order-ini-region" type="text" value="'.htmlspecialchars($region, ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-city</label><input name="dpd-admin-order-ini-city" id="dpd-admin-order-ini-city" type="text" value="'.htmlspecialchars($arLocs["CITY_NAME"], ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-street</label><input name="dpd-admin-order-ini-street" id="dpd-admin-order-ini-street" type="text" value="'.htmlspecialchars($orderProps["STREET"], ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-streetAbbr</label><input name="dpd-admin-order-ini-streetAbbr" id="dpd-admin-order-ini-streetAbbr" type="text" value="'.htmlspecialchars($street_abbr, ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-cargoWeight</label><input name="dpd-admin-order-ini-cargoWeight" id="dpd-admin-order-ini-cargoWeight" type="text" value="" /><br><label>dpd-admin-order-ini-house</label><input name="dpd-admin-order-ini-house" id="dpd-admin-order-ini-house" type="text" value="'.htmlspecialchars($orderProps["HOUSE"], ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-korpus</label><input name="dpd-admin-order-ini-korpus" id="dpd-admin-order-ini-korpus" type="text" value="'.htmlspecialchars($orderProps["KORPUS"], ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-str</label><input name="dpd-admin-order-ini-str" id="dpd-admin-order-ini-str" type="text" value="'.htmlspecialchars($orderProps["STR"], ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-vlad</label><input name="dpd-admin-order-ini-vlad" id="dpd-admin-order-ini-vlad" type="text" value="'.htmlspecialchars($orderProps["VLAD"], ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-office</label><input name="dpd-admin-order-ini-office" id="dpd-admin-order-ini-office" type="text" value="'.htmlspecialchars($orderProps["OFFICE"], ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-flat</label><input name="dpd-admin-order-ini-flat" id="dpd-admin-order-ini-flat" type="text" value="'.htmlspecialchars($orderProps["FLAT"], ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-phone</label><input name="dpd-admin-order-ini-phone" id="dpd-admin-order-ini-phone" type="text" value="'.htmlspecialchars($orderProps["PHONE"], ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-name</label><input name="dpd-admin-order-ini-name" id="dpd-admin-order-ini-name" type="text" value="'.htmlspecialchars($orderProps["NAME"], ENT_QUOTES).'" /><br><br><label>dpd-admin-order-ini-price</label><input name="dpd-admin-order-ini-price" id="dpd-admin-order-ini-price" type="text" value="'.htmlspecialchars($price, ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-priceDelivery</label><input name="dpd-admin-order-ini-priceDelivery" id="dpd-admin-order-ini-priceDelivery" type="text" value="'.htmlspecialchars($priceDelivery, ENT_QUOTES).'" /><br><label>dpd-admin-order-ini-sum_npp</label><input name="dpd-admin-order-ini-sum_npp" id="dpd-admin-order-ini-sum_npp" type="text" value="'.htmlspecialchars($sum_npp, ENT_QUOTES).'" />';	
		
		$htmlOrderDrugoe = '';
		
		$cell1 = '';
		$cell2 = $htmlFlags.$htmlOrder.$htmlOrderIni.$htmlOrderDrugoe;
		$id = '';
		$class = 'dpd-admin-off';
		?>        
		<script>							
		if(!window.dpdext__AddOrderScript)
		{						
			
			var my_apply = function()
			{
				$('#order_edit_info_form').find('input[name="apply"]').trigger('click');
			}
			
			var errorBX = function()
			{
				return $('.adm-info-message-red').length;
			}
			
			var reloadPage = function()
			{
				location.reload();
			}
			
			var isset = function(v)
			{
				if(typeof v !== "undefined")
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			
			var empty = function(v)
			{
				if(typeof v !== "undefined")
				{
					if(v == "" || v == 0 || v == "0" || v == false || v == [])
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return true;
				}
			}
			
			var class_AdminBtnLoad = 'adm-btn-load';
			
			var btnAjaxClickOn = function(btn)
			{				
				BX.showWait();
				btn.attr("disabled", "disabled");
				$('.cbx-close-on').hide();
				$('#cboxClose').hide();
			}
			
			var btnAjaxClickOf = function(btn)
			{
				BX.closeWait();
				btn.removeAttr("disabled");
				$('.cbx-close-on').show();
				$('#cboxClose').show();
			}		
			
			$(document).ready(function(){										
					setContentForDeiliveryDPD('<?=self::showHtmlTableRow($cell1, $cell2, $id, $class)?>');
				
				var tempDeliveryID = $('#DELIVERY_ID').val();				
				var devName = tempDeliveryID.slice(0, 3);
				var devT = tempDeliveryID.slice(-1);
				if(devName == "dpd" && devT == "T")
				{
					$('#dpd-admin-order-terminals-select').addClass("dpd-terminals-xt");
				}
				else
				{
					$('#dpd-admin-order-terminals-select').removeClass("dpd-terminals-xt");
				}
				showTerminalsSelect();			
						
				
				if(devName == "dpd")
				{
					$(".dpd-block-show").show();
				}
				else
				{
					$(".dpd-block-show").hide();
				}			
				
				var OrderStatusText = function(orderStatus, orderError)
				{
					var status = $('#status-order-dpd');
					var statusBase = '';
					var orderNum = '<?=$orderNum?>';
					if(orderNum == 'undefined') orderNum = '';						
					
					if(orderStatus == "OK")
					{
						statusBase = '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_TAB_STATUS_ORDER_OK");?> <input type="text" value="<?=$orderNum?>" size="15" id="orderNum" name="orderNum" readonly="readonly" style="opacity: 1;" >';
					}
					else if(orderStatus == "OrderPending")
					{
						statusBase = '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_TAB_STATUS_ORDER_PENDING");?>';
					}
					else if(orderStatus == "OrderDuplicate")
					{
						statusBase = '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_TAB_STATUS_ORDER_DUPLICATE");?>';
					}
					else if(orderStatus == "CanceledPreviously")
					{
						statusBase = '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_TAB_STATUS_ORDER_CANCELED_PREVIOUSLY");?>' + '  ' + orderNum;
					}
					else if(orderStatus == "Canceled")
					{
						statusBase = '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_TAB_STATUS_ORDER_CANCELED");?>' + '  ' + orderNum;
						orderError = '';
					}
					else if(orderStatus)
					{
						statusBase = '"' + orderStatus + '"';
					}
					else
					{
						statusBase = '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_TAB_STATUS_NONE")?>';
					}
					
					statusBase = '<b>' + statusBase + '</b>' + '<br>' + orderError;
					
					status.html(statusBase);					
					
				}
				
				OrderStatusText('<?=$orderStatus?>', '<?=$orderError?>');				
				
				var OrderErrorText = function(error)
				{
					if(error)
					{
						$('#dpd-admin-error').show();
						$('#dpd-admin-error').removeClass('dpd-admin-off');
						$('#error-order-dpd').html(error);
					}
					else
					{
						$('#dpd-admin-error').hide();
						$('#dpd-admin-error').addClass('dpd-admin-off');
						$('#error-order-dpd').html('');
					}
				}
				
				var OrderBtnAddShowHide = function(orderStatus)
				{					
					if(orderStatus == "OrderNone" || orderStatus == "Canceled" || orderStatus == "")
					{
						$('.dpd-order-no-create').removeClass('dpd-admin-off');
						$('.dpd-order-create').addClass('dpd-admin-off');						
						$('#order_terminals_select').removeAttr('disabled');
					}
					else if(orderStatus == "OrderPending")
					{
						$('.dpd-order-no-create, .dpd-order-create').addClass('dpd-admin-off');
						$('.dpd-order-pending').removeClass('dpd-admin-off');						
						$('#order_terminals_select').attr('disabled', 'disabled');						
					}
					else					
					{							
						$('.dpd-order-no-create').addClass('dpd-admin-off');
						$('.dpd-order-create').removeClass('dpd-admin-off');						
						$('#order_terminals_select').attr('disabled', 'disabled');
					}
				}
				
				OrderBtnAddShowHide('<?=$orderStatus?>');
				
				var OrderGetCbxShow = function()
				{					
					$.colorbox({
						href: '/bitrix/admin/dpdext_admin_get_order.php?ID=<?=$orderDPD?>',
						loop: false,
						overlayClose: true,
						onComplete: function(){
						},
						onClose: function(){
							reloadPage();
						}
					});
				}				
				
				var SetOrder = function(data, orderSerialize)
				{													
					var error = data['error'];
					var orderError = '';
					var orderNum = '';
					
					if(data["order"] != null)
					{
						if(typeof data["order"]["errorMessage"] !== "undefined")
						{
							if(!empty(data["order"]["errorMessage"]))
							{
								orderError = data["order"]["errorMessage"];
								error = 'BX: ' + error + '  DPD: ' + orderError;
							}
						}
					}
					else
					{
						error = 'BX: ' + error + '  DPD: ' + 'DPD NO CONNECT!';
					}
					
					OrderErrorText(error);								
					
					var orderStatus = "";
					var orderNumberInternal = 0;
					if(data["order"] != null)
					{
						if(!empty(data["order"]["status"]))
						{
							orderStatus = data["order"]["status"];
						}
						if(!empty(data["order"]["orderNum"]))
						{
							orderNum = data["order"]["orderNum"];
						}
						if(!empty(data["order"]["orderNumberInternal"]))
						{
							orderNumberInternal = data["order"]["orderNumberInternal"] - 0;
						}
					}				
					
					if(orderStatus)
					{
						OrderBtnAddShowHide(orderStatus);
						
						if(orderStatus == "OK")
						{						
							addOrderDB(orderStatus, orderNum, orderSerialize, orderError);							
						}
						
						if(orderStatus == "OrderPending")
						{						
							addOrderDB(orderStatus, orderNum, orderSerialize, orderError);
						}
						
						if(orderStatus == "OrderDuplicate")
						{
							if(orderNumberInternal)
							{
								orderNumberInternal += 1 - 0;
								createOrder('#dpd-admin-set-order-btn-save', orderNumberInternal);
							}
						}
						
						OrderStatusText(orderStatus, orderError);
						
					}
					
				}
					
				$('#dpd-admin-order-ini-cargoWeight').val(orderWeight / 1000);
				
				var serviceVariantFn = function(serviceCodeFull)
				{
					var arr = [];					
					var serviceVariantEN = serviceCodeFull.slice(-2);
					var serviceVariant = '';
					var terminalOn = '0';					
					switch(serviceVariantEN){
						case 'TT':
							serviceVariant = '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_TT")?>';
							terminalOn = '1';
							break;
						case 'TD':
							serviceVariant = '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_TD")?>';
							break;
						case 'DT':
							serviceVariant = '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_DT")?>';
							terminalOn = '1';
							break;
						case 'DD':
							serviceVariant = '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_DD")?>';
							break;			
					}
					arr['serviceVariant'] = serviceVariant;
					arr['terminalOn'] = terminalOn;
					return arr;	
				}
				
				var serviceVariantFullFn = function(serviceVariant)
				{
					var serviceVariantFull = "";			
					switch(serviceVariant){
						case '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_TT")?>':
							serviceVariantFull = '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_SERVICE_VARIANT_TT")?>';
							break;
						case '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_TD")?>':
							serviceVariantFull = '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_SERVICE_VARIANT_TD")?>';
							break;
						case '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_DT")?>':
							serviceVariantFull = '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_SERVICE_VARIANT_DT")?>';
							break;
						case '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_DD")?>':
							serviceVariantFull = '<?=GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_SERVICE_VARIANT_DD")?>';
							break;			
					}					
					return serviceVariantFull;	
				}
							
				var orderParams = function()
				{
					var serviceCodeFull = $('#DELIVERY_ID').val();
					var serviceCodeDpd = serviceCodeFull.slice(0, 3);
					var serviceCode = serviceCodeFull.slice(-6, -3);
					if(serviceCodeDpd == "dpd")
					{
						var serviceVariant = serviceVariantFn(serviceCodeFull)["serviceVariant"];
						var serviceVariantFull = serviceVariantFullFn(serviceVariant);						
						var terminalOn = serviceVariantFn(serviceCodeFull)["terminalOn"];
						var cargoWeight = $('#dpd-admin-order-ini-cargoWeight').val();
						var terminalCode = $('#order_terminals_select option:selected').attr('data-code');
						var cargoCategory = $('#dpd-admin-order-ini-cargoCategory').val();
						var cargoNumPack = $('#dpd-admin-order-ini-cargoNumPack').val();
						var cargoValue = $('#dpd-admin-order-ini-cargoValue').val();
						var sum_npp = $('#dpd-admin-order-ini-sum_npp').val();
						var pickupTimePeriod = $('#dpd-admin-order-ini-pickupTimePeriod').val();
						var name = $('#dpd-admin-order-ini-name').val();
						var phone = $('#dpd-admin-order-ini-phone').val();
						var country = $('#dpd-admin-order-ini-country').val();
						var region = $('#dpd-admin-order-ini-region').val();
						var city = $('#dpd-admin-order-ini-city').val();
						var street = $('#dpd-admin-order-ini-street').val();
						var streetAbbr = $('#dpd-admin-order-ini-streetAbbr').val();
						var house = $('#dpd-admin-order-ini-house').val();
						var korpus = $('#dpd-admin-order-ini-korpus').val();
						var str = $('#dpd-admin-order-ini-str').val();
						var vlad = $('#dpd-admin-order-ini-vlad').val();
						var office = $('#dpd-admin-order-ini-office').val();
						var flat = $('#dpd-admin-order-ini-flat').val();
					}
					var res = {
						serviceCode: serviceCode,
						serviceVariant: serviceVariant,
						serviceVariantFull: serviceVariantFull,
						cargoWeight: cargoWeight,
						terminalOn: terminalOn,
						terminalCode: terminalCode,
						cargoCategory: cargoCategory,
						cargoNumPack: cargoNumPack,
						cargoValue: cargoValue,
						sum_npp: sum_npp,
						pickupTimePeriod: pickupTimePeriod,
						name: name,
						phone: phone,
						country: country,
						region: region,
						city: city,
						street: street,
						streetAbbr: streetAbbr,
						house: house,
						korpus: korpus,
						str: str,
						vlad: vlad,
						office: office,
						flat: flat							
					}
					return res;
				}
				
				var addOrderDB = function(orderStatus, orderNum, orderSerialize, orderError)
				{										
					$.post(
						"/bitrix/admin/dpdext_ajax.php?dpdext_ajax=addOrderDB&orderNum=" + orderNum + "&orderStatus=" + orderStatus + '&orderError=' + orderError + '&ID=' + '<?=$_REQUEST["ID"]?>',
						orderSerialize,						
						function(data){					
							if(orderStatus == "OK")
							{								
								$('#TRACKING_NUMBER').val(orderNum);
								$.post(
									"/bitrix/admin/dpdext_ajax.php?dpdext_ajax=ChangeStatusWhenDPDCreated",
									{
										ID: '<?=$_REQUEST["ID"]?>',
									},
									function(data){
										reloadPage();
									}
								);
							}
							else if(orderStatus == "OrderPending")
							{
								reloadPage();
							};
						},
						"json"
					);
				}							
				
				var popupOrderSetIni = function(){
					var params = orderParams();
					var terminalName = '';
					var terminalCode = '';
					if($("#dpd-admin-order-terminals-select").hasClass("dpd-terminals-xt"))
					{
						terminalName = $('#order_terminals_select').val();
						terminalCode = params['terminalCode'];
					}
					$('#add-form-order-dpd-popup-serviceCode').val(params['serviceCode']);
					$('#add-form-order-dpd-popup-serviceVariant').val(params['serviceVariant']);					
					$('#add-form-order-dpd-popup-serviceVariantFull').val( params['serviceVariantFull']);					
					$('#add-form-order-dpd-popup-terminalOn').val(params['terminalOn']);
					$('#add-form-order-dpd-popup-terminalCode').val(terminalCode);
					$('#add-form-order-dpd-popup-terminalName').val(terminalName);
					$('#add-form-order-dpd-popup-cargoWeight').val(params['cargoWeight']);
					$('#add-form-order-dpd-popup-cargoCategory').val(params['cargoCategory']);
					$('#add-form-order-dpd-popup-cargoNumPack').val(params['cargoNumPack']);
					$('#add-form-order-dpd-popup-cargoValue').val(params['cargoValue']);					
					$('#add-form-order-dpd-popup-pickupTimePeriod').val(params['pickupTimePeriod']);
					$('#add-form-order-dpd-popup-name').val(params['name']);
					$('#add-form-order-dpd-popup-phone').val(params['phone']);
					$('#add-form-order-dpd-popup-country').val(params['country']);
					$('#add-form-order-dpd-popup-region').val(params['region']);
					$('#add-form-order-dpd-popup-city').val(params['city']);
					$('#add-form-order-dpd-popup-street').val(params['street']);
					$('#add-form-order-dpd-popup-streetAbbr').val(params['streetAbbr']);
					$('#add-form-order-dpd-popup-house').val(params['house']);
					$('#add-form-order-dpd-popup-korpus').val(params['korpus']);
					$('#add-form-order-dpd-popup-str').val(params['str']);
					$('#add-form-order-dpd-popup-vlad').val(params['vlad']);
					$('#add-form-order-dpd-popup-office').val(params['office']);
					$('#add-form-order-dpd-popup-flat').val(params['flat']);
					
					
					var nppVisible = function()
					{
						if($('#add-form-order-dpd-popup-npp').is(':checked'))
						{							
							$('#add-form-order-dpd-popup-sum_npp').val(params['sum_npp']);
							$('#add-form-order-dpd-popup-sum_npp').removeAttr('readonly');
						}
						else
						{							
							$('#add-form-order-dpd-popup-sum_npp').val('0');
							$('#add-form-order-dpd-popup-sum_npp').attr('readonly', 'readonly');
						}
					}
					
					nppVisible();
					
					$(document).on('click', '#add-form-order-dpd-popup-npp', function(){
						nppVisible();
					});
										
				}; 
				
				$(document).on('click', '#add-order-dpd', function(){
					BX.localStorage.set('saveOrderAndAddOrderDPD', 1, 60 * 60);						
					my_apply();
					return false;
				});
				
				var saveOrderAndAddOrderDPD = function()
				{
					var ok = BX.localStorage.get('saveOrderAndAddOrderDPD');
					BX.localStorage.set('saveOrderAndAddOrderDPD', '', 60 * 60);
					
					var error = errorBX();
					
					if(ok && !error)
					{						
						var href = $('#add-order-dpd').attr('href');
						$.colorbox({
							href: href,
							loop: false,
							overlayClose: false,
							onComplete: function(){
								popupOrderSetIni();	
							}
						});												
					}
				}
				
				saveOrderAndAddOrderDPD();				
				
				$('#get-order-dpd').colorbox({
					loop: false,
					overlayClose: false,
					onComplete: function(){
					}					
				});
				
				$('#cancel-order-dpd').colorbox({
					loop: false,
					overlayClose: false,
					onComplete: function(){
					}					
				});
							
				$(document).on('click', '.cbx-close-on', function(){
					$.colorbox.close();
					return false;
				});
				
				var createOrder = function(btn, orderNumberInternal)
				{
					btnAjaxClickOn($(btn));
					if(empty(orderNumberInternal))
						orderNumberInternal = '';
					$('#add-form-order-dpd-popup-orderNumberInternal').val(orderNumberInternal);	
					var orderSerialize = $("#formOrderSetDPD").serialize();					
					$.post(
						"/bitrix/admin/dpdext_ajax.php?dpdext_ajax=createOrder",
						orderSerialize,
						function(data){
							btnAjaxClickOf($(btn));
							if(data)
							{								
								SetOrder(data, orderSerialize);						
							}
							return true;
						},
						"json"		
					);
				}
				
				$(document).on('click', '#dpd-admin-set-order-btn-save', function(){
					createOrder('#dpd-admin-set-order-btn-save');					
					return false;					
				});
												
				$(document).on('click', '#dpd-admin-cancel-order-btn', function(){
					var btn = $(this);
					btnAjaxClickOn(btn);					
					$.post(
						"/bitrix/admin/dpdext_ajax.php?dpdext_ajax=cancelOrder",
						{
							orderNumberInternal: '<?=$orderDPD?>',
							orderNum: '<?=$orderNum?>'
						},
						function(data){
							if(!empty(data["status"]))
							{							
								if(data["status"] == "Canceled")
								{
									CancelOrderDB('<?=$orderDPD?>');					
								}
								else if(data["status"] == "CanceledPreviously")
								{
									OrderErrorText(data["errorMessage"]);
									btnAjaxClickOf(btn);
								}
								else if(data["status"] == "CallDPD")
								{
									OrderErrorText(data["errorMessage"]);
									btnAjaxClickOf(btn);
								}
								else if(data["status"] != "")
								{
									OrderErrorText(data["errorMessage"]);
									btnAjaxClickOf(btn);
								}
								else
								{
									alert("Error Canceled order DPD!");
									btnAjaxClickOf(btn);
								}
							}
							else
							{
								alert("Error Canceled order DPD!");
								btnAjaxClickOf(btn);
							}
						},
						"json"		
					);
					
					var CancelOrderDB = function(orderDPD)
					{
						$.post(
							"/bitrix/admin/dpdext_ajax.php?dpdext_ajax=cancelOrderDB",
							{
								orderNumberInternal: orderDPD,
							},
							function(data){
								if(data)
								{										
									reloadPage();			
								}
							},
							"json"		
						);
					}
					
					return false;
					
				});
				
				$(document).on('click', '#create-label-dpd', function(){					
					$.post(
						"/bitrix/admin/dpdext_ajax.php?dpdext_ajax=createLabelFile",
						{
							orderNumberInternal: '<?=$orderDPD?>',
							orderNum: '<?=$orderNum?>',
							parcelsNumber: 1
						},
						function(data){
							if(data["url"])
							{								
								$.colorbox({
									href: '/bitrix/admin/dpdext_admin_label_file.php?link=' + data["url"],
									loop: false,
								});								
							}
							else
							{
								if(!empty(data["dpd"]["return"]["order"]["errorMessage"]))
								{
									alert(data["dpd"]["return"]["order"]["errorMessage"]);
								}
								else
								{
									alert("Error save file!");
								}
							}							
						},
						"json"		
					);
					return false;
				});
				
				$(document).on('click', '#get-invoice-file', function(){					
					$.post(
						"/bitrix/admin/dpdext_ajax.php?dpdext_ajax=getInvoiceFile",
						{							
							orderNum: '<?=$orderNum?>'
						},
						function(data){							
							if(data["url"])
							{								
								$.colorbox({
									href: '/bitrix/admin/dpdext_admin_get_invoice.php?link=' + data["url"],
									loop: false,
								});								
							}
							else
							{
								if(empty(data["dpd"]))
								{
									alert("Error save file!");
								}
							}							
						},
						"json"		
					);
					return false;
				});				
								
			});
			
			window.dpdext__AddOrderScript = true;
			
		}
		</script>
        <style>
		.dpd-admin-off {
			display: none !important;
		}
		.dpd-admin-error * {
			color: #f00 !important;
		}
		</style>
		<?	
	}	
		
	function AdminSaleOrderContextMenuShow(&$items)
	{
		$items[] = array(			
			"TEXT" => GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_TAB_BTN_STATUS_UPDATE"),
			"ICON" => "",
			"TITLE" => GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_TAB_BTN_STATUS_UPDATE_TITLE"),
			//"LINK" => "settings.php?lang=".LANGUAGE_ID,
			"LINK" => "/bitrix/admin/dpdext_ajax.php?dpdext_ajax=status_update"			
		);
	}
	
}
?>