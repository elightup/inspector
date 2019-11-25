<?php
/**
 * Plugin Name: Inspector
 * Plugin URI:  https://elightup.com
 * Description: Inpect hidden information of your WordPress websites for debugging
 * Version:     1.2.10
 * Author:      eLightUp
 * Author URI:  https://elightup.com
 */

// Define plugin constants
define( 'RWI_URL', plugin_dir_url( __FILE__ ) );
define( 'RWI_JS', trailingslashit( RWI_URL . 'js' ) );
define( 'RWI_CSS', trailingslashit( RWI_URL . 'css' ) );
define( 'RWI_DIR', plugin_dir_path( __FILE__ ) );

if ( is_admin() ) {
	require __DIR__ . '/inc/backend.php';
	new RWI_Backend;
} else {
	require __DIR__ . '/inc/frontend.php';
	new RWI_Frontend;
}

add_action( 'shutdown', 'rwi_debug_print', 20 );

/**
 * Prints or exports the content of the global debug array at the 'shutdown' hook
 */
function rwi_debug_print() {
	if ( empty( $_SESSION['rwi'] ) || !current_user_can( 'manage_options' ) ) {
		return;
	}

	$html = '<h3>Debug:</h3><pre>';
	foreach ( $_SESSION['rwi'] as $debug ) {
		$html .= '<hr />';
		$html .= 'print' === $debug[1] ? print_r( $debug[0], true ) : var_export( $debug[0], true );
	}
	$html .= '</pre>';

	die( $html );
}

/**
 * Adds [whatever] to the global debug array
 *
 * @param mixed  $input Input value
 * @param string $type  'print' or 'export'
 *
 * @return array
 */
function rwi_debug( $input, $type = 'print' ) {
	if ( empty( $_SESSION['rwi'] ) ) {
		$_SESSION['rwi'] = array();
	}

	$_SESSION['rwi'][] = array( $input, $type );
}
