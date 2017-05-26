$(document).ready(function(){
    $('body').on("click", ".return_shipping",function(){
        var order_id = $(this).data('order_id');
        var dpd_order_id = $(this).data('dpd_order_id');
				var self = $(this);
        var arParams = {orderNumberInternal: order_id, orderNum: dpd_order_id, IS_DPD: true};
				BX.showWait();
        $.ajax({
            url: '/ajax/dpd_ajax.php',
            type: "POST",
            data: {componentName: 'dpd:rs.personal.delivery', componentTemplate: 'ajax_result', arParams: arParams, FUNCTION:'CancelOrder'},
            success: function(result) {
								self.closest('table').html(result);
								BX.closeWait();
            }
        });
    });
		
		$('body').on("click", ".query_status",function(){
        var order_id = $(this).data('order_id');
				var date_insert = $(this).data('date_insert');
				var self = $(this);
        var arParams = {orderNumberInternal: order_id, datePickup: date_insert, IS_DPD: true};
				BX.showWait();
        $.ajax({
            url: '/ajax/dpd_ajax.php',
            type: "POST",
            data: {componentName: 'dpd:rs.personal.delivery', componentTemplate: 'main', arParams: arParams},
            success: function(result) {
							self.closest('table').html(result);
							BX.closeWait();
            }
        });
    });
});


