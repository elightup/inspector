<?php
class RWI_Backend {
	/**
	 * Array of pages where meta box is added
	 *
	 * @var array
	 */
	var $pages = array();

	function __construct() {
		add_action( 'admin_head', array( $this, 'show_screen_info' ) );
		add_action( 'admin_init', array( $this, 'get_custom_post_types' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Handle Ajax request
		add_action( 'wp_ajax_rwi_autocomplete', array( $this, 'wp_ajax_autocomplete' ) );
		add_action( 'wp_ajax_rwi_view', array( $this, 'wp_ajax_view' ) );
		add_action( 'wp_ajax_rwi_delete', array( $this, 'wp_ajax_delete' ) );

		// Show screen information in contextual help
		add_action( 'contextual_help', array( $this, 'screen_help' ), 10, 3 );
	}

	function show_screen_info() {
		global $menu, $submenu, $current_user;

		$template = "\n\n<!--\n\n*****************************\n%s\n*****************************\n\n%s\n\n-->\n\n";

		printf( $template, 'MENU', print_r( $menu, true ) );
		printf( $template, 'SUBMENU', print_r( $submenu, true ) );
		printf( $template, 'CURRENT USER', print_r( $current_user, true ) );
	}

	function get_custom_post_types() {
		$post_types = get_post_types( '', 'names' );
		foreach ( $post_types as $post_type ) {
			$this->pages[] = $post_type;
		}
	}

	function add_admin_menu() {
		$this->pages['option'] = add_management_page( 'Option Inspector', 'Option Inspector', 'manage_options', 'option-inspector', [$this, 'render_admin_page'] );
		add_action( 'load-' . $this->pages['option'], [$this, 'add_meta_boxes'] );
	}

	function render_admin_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Option Inspector', 'rwi' ); ?></h2>

			<div class="metabox-holder">
				<div class="postbox-container normal">
					<?php do_meta_boxes( $this->pages['option'], 'advanced', null ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	function add_meta_boxes() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		foreach ( $this->pages as $page ) {
			add_meta_box( 'inspector', 'Inspector', [$this, 'show_meta_box'], $page );
		}
	}

	function show_meta_box() {
		$screen = get_current_screen();
		foreach ( $this->pages as $type => $page ) {
			$type = 'option' === $type ? $type : 'post_meta';
			if ( $screen->id === $page ) {
				echo "<input type='hidden' id='rwi-type' value='{$type}' />";
			}
		}
		?>
		<p><?php _e( 'Enter a name in the text box and click the buttons below to view its value or delete it.', 'rwi' ); ?></p>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="rwi-name"><?php _e( 'Name', 'rwi' ); ?></label>
					</th>
					<td>
						<input type="text" id="rwi-name" class="regular-text" />
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<?php submit_button( __( 'View', 'rwi' ), 'primary', '', false, array( 'id' => 'rwi-view') ); ?>
			<?php submit_button( __( 'Delete', 'rwi' ), 'primary', '', false, array( 'id' => 'rwi-delete') ); ?>
			<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="loading" />
		</p>

		<div id="rwi-result"></div>
		<?php
	}

	function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( ! in_array( $screen->id, $this->pages ) ) {
			return;
		}

		// jQuery autocomplete
		wp_enqueue_style( 'jquery-ui', 'http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );

		// Plugin script and style
		wp_enqueue_style( 'inspector', RWI_CSS . 'style.css' );
		wp_enqueue_script( 'inspector', RWI_JS . 'script.js', array( 'jquery-ui-autocomplete', 'wp-ajax-response' ) );
		$params = array(
			'nonce_view'		 => wp_create_nonce( 'inspector-view' ),
			'nonce_delete'	     => wp_create_nonce( 'inspector-delete' ),
			'nonce_autocomplete' => wp_create_nonce( 'inspector-autocomplete' ),
		);
		wp_localize_script( 'inspector', 'RWI', $params );
	}

	function wp_ajax_autocomplete() {
		global $wpdb;

		check_ajax_referer( 'inspector-autocomplete' );

		$term = sanitize_text_field( $_POST['term'] );
		$type = sanitize_text_field( $_POST['type'] );

		switch ( $type ) {
			case 'post_meta':
			$post_id = $_POST['post_id'];
			$values  = $wpdb->get_col( "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE post_id = '{$post_id}' AND meta_key LIKE '{$term}%'" );
			break;
			case 'option':
			default:
			$values = $wpdb->get_col( "SELECT DISTINCT option_name FROM {$wpdb->options} WHERE option_name LIKE '{$term}%'" );
			break;
		}

		die( json_encode( $values ) );
	}

	function wp_ajax_view() {
		global $wpdb;

		check_admin_referer( 'inspector-view' );

		if ( empty( $_POST['name'] ) ) {
			$this->ajax_response( __( 'Name is required.', 'rwi' ), 'error' );
		}

		$name = sanitize_text_field( $_POST['name'] );
		$type = sanitize_text_field( $_POST['type'] );

		switch ( $type ) {
			case 'post_meta':
			$post_id = $_POST['post_id'];

			$value = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = '{$post_id}' AND meta_key = '{$name}'" );
			if ( empty( $value ) )
				$this->ajax_response( __( 'Meta key does not exists.', 'rwi' ), 'error' );
			elseif ( 1 === count( $value ) )
				$value = array_pop( $value );
			break;
			case 'option':
			default:
			if ( false === ( $value = get_option( $name ) ) )
				$this->ajax_response( __( 'Option does not exists.', 'rwi' ), 'error' );
			break;
		}

		$return = print_r( $value, true );

		// Try to unserialize the value
		$unserialized = @unserialize( $value );
		if ( 'b:0;' === $value || false !== $unserialized )
		$return = "Value Type: SERIALIZED\n" . print_r( $unserialized, true );

		$html = "<pre>{$return}</pre>";

		$this->ajax_response( $html, 'success' );
	}

	function wp_ajax_delete() {
		global $wpdb;

		check_admin_referer( 'inspector-delete' );

		if ( empty( $_POST['name'] ) ) {
			$this->ajax_response( __( 'Name is required.', 'rwi' ), 'error' );
		}

		$name = sanitize_text_field( $_POST['name'] );
		$type = sanitize_text_field( $_POST['type'] );

		switch ( $type ) {
			case 'post_meta':
			$post_id = $_POST['post_id'];

			$value = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = '{$post_id}' AND meta_key = '{$name}'" );
			if ( empty( $value ) ) {
				$this->ajax_response( __( 'Meta key does not exists.', 'rwi' ), 'error' );
			}

			delete_post_meta( $post_id, $name );
			$this->ajax_response( __( 'Meta key deleted successfully.', 'rwi' ), 'success' );
			break;
			case 'option':
			default:
			if ( false === ( $value = get_option( $name ) ) ) {
				$this->ajax_response( __( 'Option does not exists.', 'rwi' ), 'error' );
			}

			delete_option( $name );
			$this->ajax_response( __( 'Option deleted successfully.', 'rwi' ), 'success' );
			break;
		}
	}

	/**
	 * Format Ajax response
	 *
	 * @param string $message
	 * @param string $status
	 */
	function ajax_response( $message, $status ) {
		$response = array( 'what' => 'rwi' );
		$response['data'] = 'error' === $status ? new WP_Error( 'error', $message ) : $message;
		$x = new WP_Ajax_Response( $response );
		$x->send();
	}

	function screen_help( $contextual_help, $screen_id, $screen ) {
		global $hook_suffix;

		// List screen properties
		$variables = '<div style="width:50%;float:left;"><strong>Screen variables</strong><ul>'
		. sprintf( '<li>Screen id: <code>%s</code></li>', $screen_id )
		. sprintf( '<li>Screen base: <code>%s</code></li>', $screen->base )
		. sprintf( '<li>Parent base: <code>%s</code></li>', $screen->parent_base )
		. sprintf( '<li>Parent file: <code>%s</code></li>', $screen->parent_file )
		. sprintf( '<li>Hook suffix: <code>%s</code></li>', $hook_suffix )
		. '</ul></div>';

		// Append global $hook_suffix to the hook stems
		$hooks = array(
			"<code>load-{$hook_suffix}</code>",
			"<code>admin_print_styles-{$hook_suffix}</code>",
			"<code>admin_print_scripts-{$hook_suffix}</code>",
			"<code>admin_head-{$hook_suffix}</code>",
			"<code>admin_footer-{$hook_suffix}</code>"
		);

		// If add_meta_boxes or add_meta_boxes_{screen_id} is used, list these too
		if ( did_action( "add_meta_boxes_{$screen_id}" ) )
			$hooks[] = "add_meta_boxes_{$screen_id}";

		if ( did_action( 'add_meta_boxes' ) )
			$hooks[] = 'add_meta_boxes';

		// Get List HTML for the hooks
		$hooks = '<div style="width:50%;float:left;"><strong>Hooks</strong><ul><li>' . implode( '</li><li>', $hooks ) . '</li></ul></div>';

		// Combine $variables list with $hooks list.
		$help_content = $variables . $hooks;

		// Add help panel
		$screen->add_help_tab( array(
			'id'      => 'rwi-screen-help',
			'title'   => 'Screen Information',
			'content' => $help_content,
		));

		return $contextual_help;
	}
}
