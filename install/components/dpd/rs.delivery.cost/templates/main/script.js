$(document).ready(function(){
    $('.dpd_calculate_show').toggle(
        function(){
            $('.dpd_calculate_input').slideDown(100);
        },
        function(){
            $('.dpd_calculate_input').slideUp(100);
        }
    );

    $('#dpd_calculate_button').click(function(){
        $('.dpd_calculate_output').show();
        $('.dpd_calculate_output').html('Загрузка...');
        var city = $('input[name="CITY"]').val();
        var arParams = {CITY: city};
        $.ajax({
            url: '/ajax/dpd_ajax.php',
            type: "POST",
            data: {componentName: 'dpd:rs.delivery.cost', componentTemplate: 'ajax_result', arParams: arParams},
            success: function(result) {
                $('.dpd_calculate_output').html(result);
            }
        });
    });
});