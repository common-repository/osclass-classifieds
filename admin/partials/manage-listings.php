<?php
class ManageListings extends OscTable {
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    var $auth_error;
    var $fm_ok;
    var $fm_error;
    public function __construct() {
        if ( function_exists('current_user_can') && !current_user_can('manage_options') )
        die(__('Cheatin&#8217; uh?', 'osclass-classifieds'));
        
        //Set parent defaults
        $this->fm_ok = false;
        $this->fm_error = false;
        $this->auth_error = false;
        if(isset($_SESSION['admin_fm_ok']))  {
            $this->fm_ok = $_SESSION['admin_fm_ok'];
            unset($_SESSION['admin_fm_ok']);
        }
        if(isset($_SESSION['admin_fm_error'])) {
            $this->fm_error = $_SESSION['admin_fm_error'];
            unset($_SESSION['admin_fm_error']);
        }
        parent::__construct( array(
            'singular'  => 'listing',     //singular name of the listed records
            'plural'    => 'listings',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
    }

    public function show_fm() {
        if($this->fm_error!==false) { ?>
        <div class="uk-alert uk-alert-danger"><?php echo $this->fm_error; ?></div>
        <?php } ?>
        <?php if($this->fm_ok!==false) { ?>
        <div class="uk-alert uk-alert-success"><?php echo $this->fm_ok; ?></div>
        <?php }
    }

    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 's_contact_name':
                return $item[$column_name]. '<br>('.$item['s_contact_email'].')';
            break;
            case 'status':
            case 's_title':
            case 's_category_name':
            case 'formated_location':
            case 'dt_pub_date':
            case 'expiration':
                return isset($item[$column_name]) ? $item[$column_name] : '';
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }


    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_s_title($item){
        
        $page = sanitize_text_field($_REQUEST['page']);
        //Build row actions
        $delete_url = wp_nonce_url( sprintf('?page=%s&action=delete&id=%s',$page,$item['pk_i_id']), 'osclass-classifieds-item-delete' );
        $actions = array(
            'delete'    => '<a href="' . $delete_url . '">'.__('Delete', 'osclass-classifieds').'</a>',
        );
        // Options of each row
        $options_more = array();
        if($item['b_active']) {
            $deactivate_url = wp_nonce_url( 
                sprintf('?page=%s&action=status-inactive&value=%s&id=%s',$page,'INACTIVE',$item['pk_i_id']), 
                'osclass-classifieds-item-status-inactive' 
            );
            $actions[] = '<a href="' . $deactivate_url . '">' . __('Deactivate', 'osclass-classifieds') .'</a>';
        } else {
            $activate_url = wp_nonce_url( 
                sprintf('?page=%s&action=status-activate&value=%s&id=%s',$page,'ACTIVE',$item['pk_i_id']), 
                'osclass-classifieds-item-status-activate' 
            );
            sprintf('?page=%s&action=status-activate&value=%s&id=%s',$page,'ACTIVE',$item['pk_i_id']);
            $actions[] = '<a href="' . $activate_url . '">' . __('Activate', 'osclass-classifieds') .'</a>';
        }
        if($item['b_enabled']) {
            $block_url = wp_nonce_url( 
                sprintf('?page=%s&action=status-disable&value=%s&id=%s',$page,'DISABLE',$item['pk_i_id']), 
                'osclass-classifieds-item-status-disable' 
            );
            $actions[] = '<a href="' . $block_url . '">' . __('Block', 'osclass-classifieds') .'</a>';
        } else {
            $unblock_url = wp_nonce_url( 
                sprintf('?page=%s&action=status-enable&value=%s&id=%s',$page,'ENABLE',$item['pk_i_id']), 
                'osclass-classifieds-item-status-enable' 
            );
            $actions[] = '<a href="' . $unblock_url . '">' . __('Unblock', 'osclass-classifieds') .'</a>';
        }
        if($item['b_spam']) {
            $unspam_url = wp_nonce_url( 
                sprintf('?page=%s&action=unmark-spam&id=%s&value=0',$page, $item['pk_i_id']), 
                'osclass-classifieds-item-unmark-spam' 
            );
            $actions[] = '<a href="' . $unspam_url . '">' . __('Unmark as spam', 'osclass-classifieds') .'</a>';
        } else {
            $spam_url = wp_nonce_url( 
                sprintf('?page=%s&action=mark-spam&id=%s&value=1',$page, $item['pk_i_id']), 
                'osclass-classifieds-item-mark-spam' 
            );
            $actions[] = '<a href="' . $spam_url . '">' . __('Mark as spam', 'osclass-classifieds') .'</a>';
        }

        //Return the title contents
        return sprintf('<a target="_blank" href="%4$s">%1$s <span style="color:silver">(id:%2$s)</span>%3$s</a>',
            /*$1%s*/ $item['s_title'],
            /*$2%s*/ $item['pk_i_id'],
            /*$3%s*/ $this->row_actions($actions),
            osclass_oc_item_url($item['pk_i_id'])
        );
    }

    public function get_views() {
        $filters = array(
            'all' => 'all',
            'show_active' => 'show_active',
            'disabled' => 'disabled',
            'blocked' => 'blocked',
            'spam' => 'spam',
        );

        $selected = 'all';
        if(isset($this->params['filterby']) ) {
            if(array_key_exists($this->params['filterby'], $filters)) {
                $selected = $filters[ $this->params['filterby'] ];
            }
        }

        // current params
        $helper_url = new OscUrlHelper();
        $views = array(
            'show_active' => array( __( 'Active', 'osclass-classifieds' ), $helper_url->url( array( 'filterby' => 'show_active', 'filter' => true ) ) ),
            'disabled' => array( __( 'Inactive', 'osclass-classifieds' ), $helper_url->url( array( 'filterby' => 'disabled', 'filter' => true ) ) ),
            'blocked' => array( __( 'Blocked', 'osclass-classifieds' ), $helper_url->url( array( 'filterby' => 'blocked', 'filter' => true ) ) ),
            'spam' => array( __( 'Spam', 'osclass-classifieds' ), $helper_url->url( array( 'filterby' => 'spam', 'filter' => true ) ) ),
            'all' => array( __( 'All', 'osclass-classifieds' ), $helper_url->url( array( 'filterby' => null ) ) ),
        );

        $return = array();
        foreach($views as $k => $_v) {
            $value = $_v[0];
            if($selected==$k) {
                $value = "<strong>".$_v[0]."</strong>";
            }
            $return[$k] = '<a href="'.$_v[1].'">' . $value. '</a>';
        }
        return $return;
    }

    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['pk_i_id']           //The value of the checkbox should be the record's id
        );
    }


    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            's_title'     => 'Title',
            'status'     => 'Status',
            's_contact_name'      => 'User',
            's_category_name'  => 'Category',
            'formated_location'  => 'Location',
            'dt_pub_date'      => 'Date',
            'expiration' => 'Expiration date',
        );
        return $columns;
    }


    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            // 'title'     => array('title',false),     //true means it's already sorted
            //'date'    => array('date',true),
            //'expiration'  => array('expiration',false)
        );
        return $sortable_columns;
    }


    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete',
            'status-activate'   => 'Activate',
            'status-inactive'   => 'Deactivate',
            'status-enable'     => 'Enable',
            'status-disable'    => 'Disable',
            'mark-spam'         => 'Mark as Spam',
            'unmark-spam'       => 'Unmark as Spam',
        );
        return $actions;
    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            if(isset($_REQUEST['listing']) || isset($_REQUEST['id'])) {
                if(isset($_REQUEST['listing'])) {
                    $_REQUEST['id'] = sanitize_text_field( implode(',', $_REQUEST['listing']) );
                } 
                $id = sanitize_text_field($_REQUEST['id']);

                // create endpoint ----------
                $endpoint = osclass_oc_api_endpoint().'admin/items/delete/'.$id;
                $client = new GuzzleHttp\Client();
                $res    = $client->request('POST', $endpoint, array('json' =>
                    array(
                        'admin_user' =>  osclass_oc_api_admin_username(),
                        'admin_password' => osclass_oc_api_admin_password(),
                        )
                    )
                );
                $data = json_decode($res->getBody(), true);
                if($data['success']) {
                    $this->fm_ok = __("Listings has been deleted", 'osclass-classifieds');
                } else {
                    $this->fm_error = __("No listings have been deleted", 'osclass-classifieds');
                }
                if($this->fm_ok!==false) $_SESSION['admin_fm_ok'] = $this->fm_ok;
                if($this->fm_error!==false) $_SESSION['admin_fm_error'] = $this->fm_error;
                osclass_oc_redirect(admin_url().'admin.php?page=osc-manage-listings');
            }
        }
        // mover aqui todas las acciones de process_action() justo abajo
    }

    function process_action() {
        $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']): '';
        if($action!='' && !isset($_REQUEST['listing'])) {
            check_admin_referer('osclass-classifieds-item-' . $action);
        } else if($action!='' && isset($_REQUEST['listing'])) {
            $_REQUEST['id'] = sanitize_text_field( implode(',', $_REQUEST['listing']) );
        }

        if($action=='status-activate') {
            $_REQUEST['value'] = 'ACTIVE';
            $action = 'status';
        }
        if($action=='status-inactive') {
            $_REQUEST['value'] = 'INACTIVE';
            $action = 'status';
        }
        if($action=='status-enable') {
            $_REQUEST['value'] = 'ENABLE';
            $action = 'status';
        }
        if($action=='status-disable') {
            $_REQUEST['value'] = 'DISABLE';
            $action = 'status';
        }
        if($action=='mark-spam') {
            $_REQUEST['value'] = '1';
            $action = 'status_spam';
        }
        if($action=='unmark-spam') {
            $_REQUEST['value'] = '0';
            $action = 'status_spam';
        }

        if($action==='status') {
            $id = sanitize_text_field($_REQUEST['id']);
            $value = sanitize_text_field($_REQUEST['value']); 
            // create endpoint ----------
            $endpoint = osclass_oc_api_endpoint().'admin/items/status/'.$value.'/'. $id;
            $client = new GuzzleHttp\Client();
            $res    = $client->request('POST', $endpoint, array('json' =>
                array(
                    'admin_user' =>  osclass_oc_api_admin_username(),
                    'admin_password' => osclass_oc_api_admin_password(),
                    )
                )
            );
            $data = json_decode($res->getBody(), true);
            if($data['success']) {
                if($value=='ACTIVE') $this->fm_ok = __('The listing has been activated', 'osclass-classifieds');
                if($value=='INACTIVE') $this->fm_ok = __('The listing has been deactivated', 'osclass-classifieds');
                if($value=='ENABLE') $this->fm_ok = __('The listing has been enabled', 'osclass-classifieds');
                if($value=='DISABLE') $this->fm_ok = __('The listing has been disabled', 'osclass-classifieds');
            } else if($data['success']===0) {
                if($value=='ACTIVE') $this->fm_error = __('The listing can\'t be activated because it\'s blocked', 'osclass-classifieds');
                if($value=='INACTIVE') $this->fm_error = __('An error has occurred', 'osclass-classifieds');
                if($value=='ENABLE') $this->fm_error = __('An error has occurred', 'osclass-classifieds');
                if($value=='DISABLE') $this->fm_error = __('An error has occurred', 'osclass-classifieds');
            } else if($data['success']===false) {
                $this->fm_error = __('An error has occurred', 'osclass-classifieds');
            }
            if($this->fm_ok!==false) $_SESSION['admin_fm_ok'] = $this->fm_ok;
            if($this->fm_error!==false) $_SESSION['admin_fm_error'] = $this->fm_error;
            osclass_oc_redirect(admin_url().'admin.php?page=osc-manage-listings');
        }
        

        if($action==='status_spam') {
            $id = sanitize_text_field($_REQUEST['id']);
            $value = sanitize_text_field($_REQUEST['value']); 
            // create endpoint ----------
            $endpoint = osclass_oc_api_endpoint().'admin/items/status_spam/'.$id.'/'.$value;
            error_log($endpoint);
            $client = new GuzzleHttp\Client();
            $res    = $client->request('POST', $endpoint, array('json' =>
                array(
                    'admin_user' =>  osclass_oc_api_admin_username(),
                    'admin_password' => osclass_oc_api_admin_password(),
                    )
                )
            );
            $data = json_decode($res->getBody(), true);
            ob_start();
            $out = ob_get_clean();
            error_log( $res->getBody() );
            if($data['success']) {
                $this->fm_ok = __('Changes have been applied', 'osclass-classifieds');
            } else {
                $this->fm_error = __('An error has occurred', 'osclass-classifieds');
            }
            if($this->fm_ok!==false) $_SESSION['admin_fm_ok'] = $this->fm_ok;
            if($this->fm_error!==false) $_SESSION['admin_fm_error'] = $this->fm_error;
            osclass_oc_redirect(admin_url().'admin.php?page=osc-manage-listings');
        }

    }


    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries
        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 10;
        
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
        $this->process_action();
        

        $orderby = (!empty($_REQUEST['orderby'])) ? sanitize_text_field($_REQUEST['orderby']) : 'title'; //If no sort, default to title
        $order = (!empty($_REQUEST['order'])) ? sanitize_text_field($_REQUEST['order']) : 'asc'; //If no order, default to asc
        $filterby = (!empty($_REQUEST['filterby'])) ? sanitize_text_field($_REQUEST['filterby']) : null; //If no order, default to asc
        
        
        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         * 
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         * 
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/
        // create endpoint ----------
        $endpoint = osclass_oc_api_endpoint().'admin/items';
        $client = new GuzzleHttp\Client();
        // --------------------------

        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        // get args -----------------
        $page = $this->get_pagenum();
        if($page===0) { $page = 1; }

        $pattern = get_query_var('pattern', null);
        $catId = isset($_REQUEST['catId']) ? sanitize_text_field($_REQUEST['catId']) : null;

        $min_price = get_query_var('min_price', null);
        $max_price = get_query_var('max_price', null);

        $country = get_query_var('countryId', null);
        $region = get_query_var('regionId', null);
        $city = get_query_var('cityId', null);

        $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : null;
        $order_type = isset($_REQUEST['order_type']) ? sanitize_text_field($_REQUEST['order_type']) : 'desc';

        // --------------------------
        $enabled = $active = $spam = $expire = null;
        if($filterby!==null) {
            if(($filterby==='disabled')) {
                $active = false;
            }
            if(($filterby==='blocked')) {
                $enabled = false;
            }
            if(($filterby==='spam')) {
                $spam = true;
            }
            if(($filterby==='show_active')) {
                $active = true;
                $enabled = true;
                $spam  = false;
                $expire = false;
            }
        }

        $total_items = 0;
        $this->items = array();
        // --------------------------
        // get listings ----------
        try{
            $res    = $client->request('POST', $endpoint, array('json' =>
                array(
                    'admin_user' =>  osclass_oc_api_admin_username(),
                    'admin_password' => osclass_oc_api_admin_password(),
                    'page' => $page-1,
                    'results_per_page' => $per_page,
                    'pattern' => $pattern,
                    'category' => $catId,
                    'min_price' => $min_price,
                    'max_price' => $max_price,
                    'country' => $country,
                    'region' => $region,
                    'city' => $city,
                    'order' => $order,
                    'order_type' => $order_type,
                    'b_enabled' => $enabled,
                    'b_active' => $active,
                    'b_spam' => $spam,
                    'expired' => $expire
                    )
                )
            );
            $data = json_decode($res->getBody(), true);
            $this->items = $data;
            // -----------------------

            // get count -------------
            $endpoint = osclass_oc_api_endpoint().'admin/items';
            $res = $client->request('POST', $endpoint, array('json' =>
                array(
                    'admin_user' =>  osclass_oc_api_admin_username(),
                    'admin_password' => osclass_oc_api_admin_password(),
                    'count' => true,
                    'pattern' => $pattern,
                    'category' => $catId,
                    'min_price' => $min_price,
                    'max_price' => $max_price,
                    'country' => $country,
                    'region' => $region,
                    'city' => $city,
                    'b_enabled' => $enabled,
                    'b_active' => $active,
                    'b_spam' => $spam,
                    'expired' => $expire
                )
            ));
            $total_items = json_decode($res->getBody(), true);
        } catch (Exception $e) {
            $this->auth_error = true;
        }
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */

        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}