jQuery(document).ready(function($) {
    getLocation();
    $('.hover_bkgr_fricc').show();
    $(".trigger_popup_fricc").click(function() {
        $('.hover_bkgr_fricc').show();
    });
    $('.hover_bkgr_fricc').click(function() {
        $('.hover_bkgr_fricc').hide();
    });
    $('.popupCloseButton').click(function() {
        $('.hover_bkgr_fricc').hide();
    });

    /*Geo location Start */
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        }
    }

    function showPosition(position) {
        //x.innerHTML = "Latitude: " + position.coords.latitude + 
        //"<br>Longitude: " + position.coords.longitude;
        var printLati = position.coords.latitude;
        var printLongi = position.coords.longitude;

        $("#txtLati").val(printLati);
        $("#txtlongi").val(printLongi);
        $.getJSON('https://ipapi.co/json/', function(data) {
            $("#txtinfo").val(JSON.stringify(data, null, 2));
        });
    }
    /*Geo Location Ends */
    $('#allowme').click(function(e) {
        e.preventDefault();
        var usdinfo;
        usdinfo = $('#txtinfo').val();

        var data = {
            action: 'users_details_callback',
            user_ip: $('#txtinfo').val(),
            user_lati: $('#txtLati').val(),
            user_longi: $('#txtlongi').val(),
            nonce: myAjax.nonce
        };
        if (usdinfo != null) {
            $.ajax({
                url: myAjax.url,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(response) {
                    $('.hover_bkgr_fricc').hide();
                }
            });
        }

    });
});