<?php
class SmartVisitorTrackerVisitorPopup {
    // Primary Function
    function __construct() {
        add_action('init', 'register_script');
        function register_script() {
            //wp_register_script( 'myAjax', plugins_url('/assets/js/alerts.js', __FILE__), array('jquery'), '2.5.1' );
            wp_register_style( 'popupcss', plugins_url('/assets/css/alerts.css', __FILE__), false, '1.0.0', 'all');
            //wp_localize_script( 'popupjs', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
            wp_enqueue_script( 
                'ajax_script', 
                plugins_url( '/assets/js/alerts.js',__FILE__ ), 
                array('jquery'), 
                TRUE 
            );
            wp_localize_script( 
                'ajax_script', 
                'myAjax', 
                array(
                    'url'   => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( "users_details_callback_nonce" ),
                )
            );        
        }
        add_action( 'wp_ajax_users_details_callback', array( $this, 'users_details_callback' ) );
        add_action( 'wp_ajax_nopriv_users_details_callback', array( $this, 'users_details_callback' )  );
        add_action('wp_enqueue_scripts', 'enqueue_style');
        function enqueue_style(){
           //wp_enqueue_script('popupjs');
           wp_enqueue_style( 'popupcss' );
        }
        add_action('init', array( $this, 'vds_startSession' ), 1);
    }
    //End of Constructor

/* Get Current Location Start */
    function current_location() {
        if (isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /*Generate Session Start */
    function vds_startSession() {
        session_start();
        $expiry = 3600;//session expiry required after 30 mins
        if (isset($_SESSION['vds_last_action']) && (time() - $_SESSION['vds_last_action'] > $expiry)) {
            session_unset();
            session_destroy();   
            
        } else {
            $this->displayPopup();  
        }
    }

    function SessionRegenerate() {
        session_start();
        $_SESSION['vds_code'] = uniqid();
        $_SESSION['vds_code_time'] = time();
        $_SESSION['vds_domain'] = get_bloginfo('url');
        $_SESSION['vds_current_url'] = $this->current_location();   
        $_SESSION['vds_last_action'] = time(); 
    }
    /*Generate Session Ends */

    /* Popup Messages Function Starts */
    function displayPopup() {
        //Popup HTML Message Start
        session_start();
        if ( (isset($_SESSION['vds_code']) == null) ) {
        $this->SessionRegenerate(); 
        add_action('wp_footer', 'custompopuphtml');
        function custompopuphtml() {
?>
        <!-- <a class="trigger_popup_fricc">Click here to show the popup</a> -->
        <div class="hover_bkgr_fricc">
            <span class="helper"></span>
            <div>
                <div class="popupCloseButton">X</div>
                <p>Hello,<br />Your location data will be used to help identify you and allow us to offer you great products and services. Your consent is important for us to do this and you are not obliged in any way to share this information with us. Thank you</p>
                <div class="buttons_show">
                    <button id="allowme">Allow</button>
                    <button id="dontallow">Not Now</button>
                    <input type="hidden" name="txtLati" id="txtLati" value="0">
                    <input type="hidden" name="txtlongi" id="txtlongi" value="0">
                    <input type="hidden" name="txtinfo" id="txtinfo" value="0">
                </div>
            </div>
        </div>
<?php
        }
        //Popup HTML Message Ends
        }
    }
    /* Popup Messages Function Ends */


    /* Register Scripts */
    function users_details_callback() {
        /*echo "<pre>";
        print_r($_POST);
        echo "</pre>";*/
        session_start();
        $ipinfo = json_decode(stripslashes($_POST['user_ip']), true);
        $user_latitude = filter_var($_POST['user_lati'], FILTER_SANITIZE_STRING);
        $user_longitude = filter_var($_POST['user_longi'], FILTER_SANITIZE_STRING);
        $detected_ip = $ipinfo['ip'];
        $session_data = $_SESSION;
        $conversion_data = array(   "ip_address" => $detected_ip,
                                    "gps_latitude" => $user_latitude,
                                    "gps_longitude" => $user_longitude,
                                    "user_session" => $session_data,
                                    'info_daterecorded' => date("Y-m-d H:i:s"),
                                    "detailed_data" => $ipinfo);
        global $wpdb;
        $tablename=$wpdb->prefix.'vdcustom_data';
        $data=array(
            'ipaddress' => $detected_ip, 
            'latitude' => $user_latitude,
            'longitude' => $user_longitude, 
            'daterecorded' => date("Y-m-d H:i:s"),  
            'detail_data' => serialize($conversion_data) );
         $wpdb->insert( $tablename, $data);

        $url_server = 'https://api.pulsemediagroup.co.uk/sync.php';
        wp_remote_post( $url_server, array( 'body' => json_encode($conversion_data) ) );        
        wp_die();
    }
    /* Register Scripts */

}

new SmartVisitorTrackerVisitorPopup;
?>