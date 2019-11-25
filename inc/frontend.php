<?php
class RWI_Frontend {
	function __construct() {
		add_action( 'wp_head', array( __CLASS__, 'show_assets' ) );
	}

	function show_assets() {
		global $wp_scripts, $wp_styles;

		$template = "\n\n<!--\n\n*****************************\n%s\n*****************************\n\n%s\n\n-->\n\n";

		printf( $template, 'REGISTERED SCRIPTS', print_r( $wp_scripts, true ) );
		printf( $template, 'REGISTERED STYLES', print_r( $wp_styles, true ) );
	}
}
