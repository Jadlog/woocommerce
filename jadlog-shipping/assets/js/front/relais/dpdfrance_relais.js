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
    if ($('.shipping_method:checked').val() == 'dpdfrance_relais' || $('.shipping_method option:selected').val() == 'dpdfrance_relais' || $('.shipping_method:hidden').val() == 'dpdfrance_relais')
        valideRelais();
    if ($('#shipping_method_0_dpdfrance_relais').is(':checked'))
        valideRelais();
    $('#shipping_method_0_dpdfrance_relais').change(function(){
        valideRelais();
    });
});

$(document).ready(function() {
    $("form[name='checkout']").bind("keydown",function(e) {
        var code = e.keyCode || e.which;
        if (code == 13) {
            e.preventDefault();
            return false;
        }
    });
    $("body").keyup(function() {
        if ($('.shipping_method:checked').val() == 'dpdfrance_relais' || $('.shipping_method option:selected').val() == 'dpdfrance_relais' || $('.shipping_method:hidden').val() == 'dpdfrance_relais')
            valideRelais();
        if ($('#shipping_method_0_dpdfrance_relais').is(':checked'))
            valideRelais();
    });
});

function initializeDpdfrance(baseurl,mapid,lat,longti) {
    var latlng = new google.maps.LatLng(lat, longti);
    
    var myOptions = {
      zoom      : 16,
      center    : latlng,
      mapTypeId : google.maps.MapTypeId.ROADMAP,
      styles:[{"featureType":"landscape","stylers":[{"visibility":"on"},{"color":"#e6e7e7"}]},{"featureType":"poi.sports_complex","stylers":[{"visibility":"on"}]},{"featureType":"poi.attraction","stylers":[{"visibility":"off"}]},{"featureType":"poi.government","stylers":[{"visibility":"on"}]},{"featureType":"poi.medical","stylers":[{"visibility":"on"}]},{"featureType":"poi.place_of_worship","stylers":[{"visibility":"on"}]},{"featureType":"poi.school","stylers":[{"visibility":"on"}]},{"featureType":"water","elementType":"geometry","stylers":[{"visibility":"on"},{"color":"#d2e4f3"}]},{"featureType":"water","elementType":"labels","stylers":[{"visibility":"on"}]},{"featureType":"transit","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#ffffff"}]},{"featureType":"road","elementType":"geometry.stroke","stylers":[{"visibility":"on"},{"color":"#e6e7e7"}]},{"elementType":"labels.text.fill","stylers":[{"visibility":"on"},{"color":"#666666"}]},{"featureType":"poi.business","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#dbdbdb"}]},{"featureType":"administrative.locality","elementType":"labels.text.fill","stylers":[{"visibility":"on"},{"color":"#808285"}]},{"featureType":"transit.station","stylers":[{"visibility":"on"}]},{"featureType":"transit","elementType":"geometry","stylers":[{"visibility":"on"},{"color":"#dbdbdb"}]},{"elementType":"labels.icon","stylers":[{"visibility":"on"},{"saturation":-100}]},{"featureType":"road","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"transit.line","elementType":"labels.text","stylers":[{"visibility":"off"}]}]
    };
    var map = new google.maps.Map(document.getElementById(mapid), myOptions);

    var marker = new google.maps.Marker({
       icon         : baseurl+"/assets/img/front/relais/logo-max-png.png",
       position     : latlng,
       animation    : google.maps.Animation.DROP,
       map          : map
    });
}

function openDpdfranceDialog(baseurl,id,mapid,lat,longti) {
    $("#dpdfrance_relais_filter").fadeIn(150, function() {$("#"+id).fadeIn(150);});
    window.setTimeout(function () {initializeDpdfrance(baseurl,mapid,lat,longti)},200);
}

function valideRelais() {
    var regex = new RegExp(/\(P\d{5}\)/);
    var company = document.getElementById('shipping_company');
    var PickupID = company.value.substr(-7, 6);
    if (regex.test(company.value) && $("#ship-to-different-address-checkbox").attr('checked') && $('input[name=dpdfrance_relay_id]:checked', document).val()){
        $("#place_order").removeAttr('disabled');
        $('#place_order').removeClass('button').addClass('button alt');
        $("#"+PickupID).attr('checked', 'checked');
        return true;
    }else{
        $("#place_order").attr('disabled', 'disabled');
        $('#place_order').removeClass('button alt').addClass('button');
        return false;
    }
    return false;
}

function dpdWriteRelaisValues(item) {
    if (!$("#ship-to-different-address-checkbox").is(':checked'))
        $("#ship-to-different-address-checkbox").click();
    value = $(item).attr('value');
    relais_data = $.parseJSON(value);
    
    if ($("#shipping_postcode").val() != relais_data.postal_code)
        changed = 1;
    else
        changed = 0;
    $("#shipping_first_name").val($("#billing_first_name").val());
    $("#shipping_last_name").val($("#billing_last_name").val());
    $("#shipping_company").val(relais_data.shop_name+' ('+relais_data.relay_id+')');
    $("#shipping_address_1").val(relais_data.address1);
    $("#shipping_address_2").val(relais_data.address2);
    $("#shipping_postcode").val(relais_data.postal_code);
    $("#shipping_city").val(relais_data.city);
    $(".shipping_address").show();
    if (changed == 1)
        $('#shipping_postcode').trigger({type: 'keydown', which: 13, keyCode: 13});
    valideRelais();
}

function dpdResetRelaisValues() {
    var regex = new RegExp(/\(P\d{5}\)/);
    var company = document.getElementById('shipping_company');
    var PickupID = company.value.substr(-7, 6);
    if (regex.test(company.value) && $("#ship-to-different-address-checkbox").attr('checked'))
    {
        $("#shipping_first_name").val($("#billing_first_name").val());
        $("#shipping_last_name").val($("#billing_last_name").val());
        $("#shipping_company").val($("#billing_company").val());
        $("#shipping_address_1").val($("#billing_address_1").val());
        $("#shipping_address_2").val($("#billing_address_2").val());
        $("#shipping_postcode").val($("#billing_postcode").val());
        $("#shipping_city").val($("#billing_city").val());
        $(".shipping_address").hide();
        document.getElementById('ship-to-different-address-checkbox').checked=false;
        $('#shipping_postcode').trigger({type: 'keydown', which: 13, keyCode: 13});
    }
}

function dpdfrance_relais_ajaxupdate(address, zipcode, city, action) {
    if ((zipcode && zipcode.length >= 5 && action == 'search') || action == 'reset') {
        $('#dpdfrance_reset_submit').after(' <img src="../wp-content/plugins/woocommerce-dpdfrance/assets/img/front/relais/loader.gif"/>');
        $.ajax('../wp-content/plugins/woocommerce-dpdfrance/ajax/getpoints.php?action=getajaxpoints', {
            data: {
                'address': address,
                'zipcode': zipcode,
                'city': city
            },
            success: function(data) {
                var $html = $(data);
                $('.dpdfrance_relaisbox').remove();
                $('#dpdfrance_relais_point_table').html($html);
            }
        });
        var expire=new Date();
        expire.setDate(expire.getDate()+1);
        document.cookie='dpdfrance_relais_search'+'='+escape(address+','+zipcode+','+city)+';expires='+expire.toGMTString();
    } else {
        $('#dpdfrance_search_zipcode').css('border', '1px solid #dc0032');
    }
}