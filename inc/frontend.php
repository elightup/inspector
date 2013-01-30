<?php

if ( ! class_exists( 'RWI_Frontend' ) )
{
	class RWI_Frontend
	{
		/**
		 * Class constructor
		 */
		function __construct()
		{
			// Show screen information
			add_action( 'wp_head', array( __CLASS__, 'show_screen_info' ) );
		}

		/**
		 * Show current admin screen information
		 *
		 * @return void
		 */
		function show_screen_info()
		{
			global $wp_scripts, $wp_styles;

			$template = "\n\n<!--\n\n*****************************\n%s\n*****************************\n\n%s\n\n-->\n\n";

			printf( $template, 'REGISTERED SCRIPTS', print_r( $wp_scripts, true ) );
			printf( $template, 'REGISTERED STYLES', print_r( $wp_styles, true ) );
		}
	}

	new RWI_Frontend;
}