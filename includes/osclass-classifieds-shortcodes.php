<?php

class Osclass_Classifieds_Shortcodes {
    public function __construct() {
    }

    public function home_func( $atts ) {
		$a = shortcode_atts( array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts );

		ob_start();
			include(OSCLASS_OC_DIR . '/public/partials/home.php');
			$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
}