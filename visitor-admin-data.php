<?php

if(is_admin()) {
    new SmartVisitorTracker_PLUGIN_WP_LIST_TABLE();
}

/**
 * SmartVisitorTracker_PLUGIN_WP_LIST_TABLE class will create the page to load the table
 */
class SmartVisitorTracker_PLUGIN_WP_LIST_TABLE {

    public function __construct() {
        add_action( 'admin_menu', array($this, 'add_menu_example_list_table_page' ));
    }


    public function add_menu_example_list_table_page() {
        add_menu_page( 
            'Visitor Details', //Page Title
            'Visitor Details', //Menu Title
            'edit_posts', 
            'visitor_details_panel', 
            array($this, 'list_table_page'), 
            'dashicons-shield' 
        );
    }

    public function list_table_page() {
        $exampleListTable = new SmartVisitorTracker_PLUGIN_TABLE_DATA();
        $exampleListTable->prepare_items();
        ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <h2>Visitor Details</h2>
                <?php add_thickbox(); $exampleListTable->display(); ?>
            </div>
        <?php
    }
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SmartVisitorTracker_PLUGIN_TABLE_DATA extends WP_List_Table {

    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = 50;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    public function get_columns() {
        $columns = array(
            'id'          => 'ID',
            'ipaddress'       => 'IP Address',
            'isp'       => 'Company/ISP',
            'latitude' => 'Latitude',
            'longitude'        => 'Longitude',
            'daterecorded'    => 'Date Recorded',
            'detail_data' => 'View Info'
        );

        return $columns;
    }

    public function get_hidden_columns() {
        return array();
    }

    public function get_sortable_columns() {
        return array('title' => array('title', false));
    }

    private function table_data() {
        $data = array();
        global $wpdb;
        $table_name = $wpdb->prefix . "vdcustom_data";
        $user_data = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC" ); 

        if (isset ($user_data) ) {
            foreach ($user_data as $customData) {
                $top_message = "<p>We hope you found that information useful, we can most probably give you more detail behind who your visitors are like a contact name, phone number and email address. If you’d like to find out more please contact us now at <a href='mailto:info@pulsemediagroup.co.uk '>info@pulsemediagroup.co.uk</a> and quote Visitor Profile Plug-in in the subject line.<br>Best wishes,<br>Pulse Media Group!</p>";
                $view_info_box = null;
                $link = "<br><a href='https://maps.google.com/?q={$customData->latitude},{$customData->longitude}' target='_blank'>View Location in GMaps</a>";
                $location = $customData->latitude.$link;
                $data_convert = unserialize($customData->detail_data);
                $isp = $data_convert['detailed_data']['org'];
                $country = $data_convert['detailed_data']['country_name'];
                $ip_info = $customData->ipaddress."<br><small><strong style='color:blue;'>{$country}</strong>,<br><i>{$isp}</i></small>";
                /*IP Information Data */
                $view_info_box .= '<div id="'.$customData->id.'" style="display:none;">'.$top_message.'<table class="widefat fixed" cellspacing="0"><tbody>';
                $view_info_box .= '<tr class="alternate"><td class="column-columnname" style="width:20%;"><strong>City</strong></td><td>'.$data_convert['detailed_data']['city'].'</td></tr>';
                $view_info_box .= '<tr><td class="column-columnname" style="width:20%;"><strong>Region</strong></td><td>'.$data_convert['detailed_data']['region'].'</td></tr>';
                $view_info_box .= '<tr class="alternate"><td class="column-columnname" style="width:20%;"><strong>Postal Code</strong></td><td>'.$data_convert['detailed_data']['postal'].'</td></tr>';
                $view_info_box .= '<tr><td class="column-columnname" style="width:20%;"><strong>TimeZone</strong></td><td>'.$data_convert['detailed_data']['timezone'].'</td></tr>';
                $view_info_box .= '<tr class="alternate"><td class="column-columnname" style="width:20%;"><strong>GMT</strong></td><td>'.$data_convert['detailed_data']['utc_offset'].'</td></tr>';
                $view_info_box .= '<tr><td class="column-columnname" style="width:20%;"><strong>URL</strong></td><td>'.$data_convert['user_session']['vds_current_url'].'</td></tr>';
                $view_info_box .= '</tbody></table></div>';
                 /*IP Information Data Ends */
                $get_info = $data_convert['user_session']['vds_domain'];
                $get_info = parse_url($get_info);
                $get_info['valid'] = $get_info['host'];
                $get_info['checker'] = $customData->ipaddress;
                $get_info = base64_encode(json_encode($get_info));
                $detail_link = '<a class="button-secondary" href="https://api.pulsemediagroup.co.uk/dataCheck.php?hash='.$get_info.'" target="_blank" style="width:100%; text-align:center;">View Detail Info</a>';
                $view_infobtn = '<a name="IP Info: '.$customData->ipaddress.'" href="#TB_inline?width=790&height=370&inlineId='.$customData->id.'" class="button-primary thickbox" style="width:100%;text-align:center; margin-bottom:5px;">View Information</a> '.$detail_link.$view_info_box;
                $data[] = array(
                    'id'          => $customData->id,
                    'ipaddress'       => $ip_info,
                    'isp'       => $isp,
                    'latitude' => $location,
                    'longitude'        => $customData->longitude,
                    'daterecorded'    => date("d M Y h:i A", strtotime($customData->daterecorded)),
                    'detail_data'      => $view_infobtn
                    );
            }  
        } else {
            $data = null;
        }
        return $data;
    }

    public function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'id':
            case 'ipaddress':
            case 'isp':
            case 'latitude':
            case 'longitude':
            case 'daterecorded':
            case 'detail_data':
                return $item[ $column_name ];

            default:
                return print_r( $item, true ) ;
        }
    }

    private function sort_data( $a, $b ) {
        // Set defaults
        $orderby = 'id';
        $order = 'desc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby'])) {
            $orderby = filter_var($_GET['orderby'], FILTER_SANITIZE_STRING);
        }

        // If order is set use this as the order
        if(!empty($_GET['order'])) {
            $order = filter_var($_GET['order'], FILTER_SANITIZE_STRING);
        }
        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc') {
            return $result;
        }

        return -$result;
    }
}
?>