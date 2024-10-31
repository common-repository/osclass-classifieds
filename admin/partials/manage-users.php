<?php
class ManageUsers extends OscTable {
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

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'user',     //singular name of the listed records
            'plural'    => 'users',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
    }


    function column_default($item, $column_name){
        switch($column_name){
            case 's_email':
            case 'status':
            case 's_username':
            case 's_name':
            case 'dt_reg_date':
            case 'i_items':
            case 'dt_mod_date':
                return $item[$column_name];
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
    function column_s_title($user){
        
        $page = sanitize_text_field($_REQUEST['page']);
        //Build row actions
        $delete_url = wp_nonce_url( sprintf('?page=%s&action=delete&id=%s',$page,$user['pk_i_id']), 'osclass-classifieds-user-delete' );
        $actions = array(
            'delete'    => '<a href="' . $delete_url . '">'.__('Delete', 'osclass-classifieds').'</a>',
        );
        // Options of each row
        $options_more = array();
        if($user['b_active']) {
            $deactivate_url = wp_nonce_url( sprintf('?page=%s&action=status-inactive&value=%s&id=%s',$page,'deactivate',$user['pk_i_id']), 'osclass-classifieds-user-status-inactive' );
            $actions[] = '<a href="' . $deactivate_url . '">' . __('Deactivate', 'osclass-classifieds') .'</a>';
        } else {
            $activate_url = wp_nonce_url( sprintf('?page=%s&action=status-activate&value=%s&id=%s',$page,'activate',$user['pk_i_id']), 'osclass-classifieds-user-status-activate' );
            $actions[] = '<a href="' . $activate_url . '">' . __('Activate', 'osclass-classifieds') .'</a>';
        }
        if($user['b_enabled']) {
            $block_url = wp_nonce_url( sprintf('?page=%s&action=status-disable&value=%s&id=%s',$page,'disable',$user['pk_i_id']), 'osclass-classifieds-user-status-disable' );
            $actions[] = '<a href="' . $block_url . '">' . __('Block', 'osclass-classifieds') .'</a>';
        } else {
            $unblock_url = wp_nonce_url( sprintf('?page=%s&action=status-enable&value=%s&id=%s',$page,'enable',$user['pk_i_id']), 'osclass-classifieds-user-status-enable' );
            $actions[] = '<a href="' . $unblock_url . '">' . __('Unblock', 'osclass-classifieds') .'</a>';
        }
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $user['s_email'],
            /*$2%s*/ $user['pk_i_id'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    public function get_views() {
        $filters = array(
            'all' => 'all',
            'show_active' => 'show_active',
            'disabled' => 'disabled',
            'blocked' => 'blocked',
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
            's_title'     => 'Email',
            'status'     => 'Status',
            's_username'      => 'Username',
            's_name'  => 'Name',
            'dt_reg_date'  => 'Date',
            'i_items'      => 'Items',
            'dt_mod_date' => 'Update date',
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
            // 'date'    => array('date',true),
            // 'expiration'  => array('expiration',false)
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
            'status-disable'    => 'Block',
            'status-enable'     => 'Unblock',
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
            check_admin_referer('osclass-classifieds-user-delete');
            if(isset($_REQUEST['user'])) {
                $_REQUEST['id'] = sanitize_text_field( implode(',', $_REQUEST['user']) );
            }
            $id = sanitize_text_field($_REQUEST['id']);

            // create endpoint ----------
            $endpoint = osclass_oc_api_endpoint().'admin/users/delete/'.$id;
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
                $this->fm_ok = __("User has been deleted", 'osclass-classifieds');
            } else {
                $this->fm_error = __("No user have been deleted", 'osclass-classifieds');
            }

            if($this->fm_ok!==false) $_SESSION['admin_fm_ok'] = $this->fm_ok;
            if($this->fm_error!==false) $_SESSION['admin_fm_error'] = $this->fm_error;
            osclass_oc_redirect(admin_url().'admin.php?page=osc-manage-users');
        }
        // mover aqui todas las acciones de process_action() justo abajo
    }

    function process_action() {
        $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']): '';
        if($action!='' && !isset($_REQUEST['user'])) {
            check_admin_referer('osclass-classifieds-user-' . $action);
        } else if($action!='' && isset($_REQUEST['user'])) {
            $_REQUEST['id'] = sanitize_text_field( implode(',', $_REQUEST['user']) );
        }

        if($action=='status-activate') {
            $_REQUEST['value'] = 'activate';
            $action = 'status';
        }
        if($action=='status-inactive') {
            $_REQUEST['value'] = 'deactivate';
            $action = 'status';
        }
        if($action=='status-enable') {
            $_REQUEST['value'] = 'enable';
            $action = 'status';
        }
        if($action=='status-disable') {
            $_REQUEST['value'] = 'disable';
            $action = 'status';
        }

        if($action==='status') {
            $id = sanitize_text_field($_REQUEST['id']);
            $value = sanitize_text_field($_REQUEST['value']);
            // create endpoint ----------
            $endpoint = osclass_oc_api_endpoint().'admin/users/status/'.$id.'/'. $value;
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
                if($value=='activate') $this->fm_ok = __('One user have been activated', 'osclass-classifieds');
                if($value=='deactivate') $this->fm_ok = __('One user have been deactivated', 'osclass-classifieds');
                if($value=='enable') $this->fm_ok = __('One user have been unblocked', 'osclass-classifieds');
                if($value=='disable') $this->fm_ok = __('One user have been blocked', 'osclass-classifieds');
            } else {
                if($value=='activate') $this->fm_error = __('No users have been activated', 'osclass-classifieds');
                if($value=='deactivate') $this->fm_error = __('No users have been deactivated', 'osclass-classifieds');
                if($value=='enable') $this->fm_error = __('No users have been unblocked', 'osclass-classifieds');
                if($value=='disable') $this->fm_error = __('No users have been blocked', 'osclass-classifieds');
            }

            if($this->fm_ok!==false) $_SESSION['admin_fm_ok'] = $this->fm_ok;
            if($this->fm_error!==false) $_SESSION['admin_fm_error'] = $this->fm_error;

            osclass_oc_redirect(admin_url().'admin.php?page=osc-manage-users');
        }
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
        
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        // get args -----------------
        $page = $this->get_pagenum();
        if($page===0) { $page = 1; }

        $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : null;
        $order_type = isset($_REQUEST['order_type']) ? sanitize_text_field($_REQUEST['order_type']) : 'desc';

        // --------------------------
        $enabled = $active = null;
        if($filterby!==null) {
            if(($filterby==='disabled')) {
                $active = false;
            }
            if(($filterby==='blocked')) {
                $enabled = false;
            }
            if(($filterby==='show_active')) {
                $active  = true;
                $enabled = true;
            }
        }

        // --------------------------
        $endpoint = osclass_oc_api_endpoint().'admin/users';
        $client = new GuzzleHttp\Client();
        // get listings ----------
        $total_items = 0;
        $this->items = array();
        try{
            $res    = $client->request('POST', $endpoint, array('json' =>
                array(
                    'admin_user' =>  osclass_oc_api_admin_username(),
                    'admin_password' => osclass_oc_api_admin_password(),
                    'page' => $page-1,
                    'results_per_page' => $per_page,
                    'b_active' => $active,
                    'b_enabled' => $enabled
                    )
                )
            );
            $data = json_decode($res->getBody(), true);
            $this->items = $data['users'];;
            $total_items = $data['total'];
        } catch (Exception $e) {
            $this->auth_error = true;
        }
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