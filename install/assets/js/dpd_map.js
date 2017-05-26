//Main method for work for map, open/close objects on map
function dpdShowMap (self, targetSelector, componentName, componentTemplate, arParams) {
	var selectorMap = $(targetSelector);
	if(selectorMap.css('display')=='none'){
		if (selectorMap.html() == "") {
			BX.showWait();
			var data = {
				componentName: componentName,
				componentTemplate: componentTemplate,
				arParams: arParams,
			};
			$.ajax({
				url: '/ajax/dpd_ajax.php',
				data: data,
				type: "POST",
				success: function(data) {
					$(selectorMap).html(data);
					BX.closeWait();
					selectorMap.show();
					$(self).html('Скрыть карту');
				}
			});
		} else {
			selectorMap.show();
			$(self).html('Скрыть карту');
		}
	} else {
		selectorMap.hide();
		$(self).html('Показать на карте');
	}
}

function dpdChoosenTerminalOnMap (self) {
	var code = $(self).data("code");
	$("#TERMINAL_CODE").removeAttr("checked");
	$("#TERMINAL_CODE[value="+code+"]").prop("checked", "checked");
}