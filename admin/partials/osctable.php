<?php
class OscTable extends WP_List_Table {
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    var $params;
    public function __construct($params) {
        wp_parse_str($_SERVER['QUERY_STRING'], $_params);
        $blacklist = osclass_oc_params_blacklist();
        $this->params = array_diff_key($_params, array_combine($blacklist, $blacklist));
        //Set parent defaults
        parent::__construct( $params );
    }

    function views() {
        $screen = get_current_screen();

        $views = $this->get_views();
        $views = apply_filters( 'views_' . $screen->id, $views );

        if ( empty( $views ) )
            return;

        echo "<ul class='subsubsub'>\n";
        foreach ( $views as $class => $view ) {
            $views[ $class ] = "\t<li class='$class'>$view";
        }
        echo implode( " |</li>\n", $views ) . "</li>\n";
        echo "</ul>";
    }
}