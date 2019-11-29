/**
 * Plugin Name: DPD France
 * Plugin URI: http://www.dpd.fr/ecommerce
 * Description: Module de livraison DPD pour WooCommerce 2.x (France)
 * Version: 5.2.2
 * Author: DPD France S.A.S.
 * Author URI: http://www.dpd.fr/
 * License: Open Software License (OSL 3.0) - http://opensource.org/licenses/osl-3.0.php
 */

var $ = jQuery.noConflict(); 

$(document).ajaxComplete(function() {
    if ($('.shipping_method:checked').val() == 'dpdfrance_predict' || $('.shipping_method option:selected').val() == 'dpdfrance_predict' || $('.shipping_method:hidden').val() == 'dpdfrance_predict')
        valideGsm();
    if ($('#shipping_method_0_dpdfrance_predict').is(':checked'))
        valideGsm();
    $('#shipping_method_0_dpdfrance_predict').change(function(){
        valideGsm();
    });
});

$(document).ready(function() {
    $("#billing_phone").keyup(function() {
        if ($('.shipping_method:checked').val() == 'dpdfrance_predict' || $('.shipping_method option:selected').val() == 'dpdfrance_predict' || $('.shipping_method:hidden').val() == 'dpdfrance_predict')
            valideGsm();
        if ($('#shipping_method_0_dpdfrance_predict').is(':checked'))
            valideGsm();
    });
    $('#shipping_method_0_dpdfrance_predict').change(function(){
        valideGsm();
    });
});

function in_array(search, array){
    for (i = 0; i < array.length; i++){
        if(array[i] == search ){
            return false;
        }
    }
    return true;
}

function valideGsm(){
    var regex = new RegExp(/^((\+33|0)[67])(?:[ _.-]?(\d{2})){4}$/);
    var gsmDest = document.getElementById('billing_phone');
    var numbers = gsmDest.value.substr(-8);
    var pattern = new
    Array('00000000','11111111','22222222','33333333','44444444','55555555','66666666','77777777',
    '88888888','99999999','12345678','23456789','98765432');
    if (regex.test(gsmDest.value) && in_array(numbers, pattern)){
        $(".order-total").next('tr').remove();
        $(".order-total").after('<tr><td colspan="2"><div class="okmsg" id="dpdfrance_predict_div">{gsm_ok}</div></td></tr>'.replace('{gsm_ok}', objectL10n.gsm_ok));
        $("#place_order").removeAttr('disabled');
        $('#place_order').removeClass('button').addClass('button alt');
    }else{
        $(".order-total").next('tr').remove();
        $(".order-total").after('<tr><td colspan="2"><div class="warnmsg" id="dpdfrance_predict_div">{gsm_ko}</div></td></tr>'.replace('{gsm_ko}', objectL10n.gsm_ko));
        $("#place_order").attr('disabled', 'disabled');
        $('#place_order').removeClass('button alt').addClass('button');
    return false;
    }
}