<?php

namespace asw\oxygen;

use asw\oxygen\elements\I18N_Text;
use WP_Error;

class Oxygen {

	public static $text_domain   = 'oxy-i18n-text';
	public static $language_path = __DIR__ . '/languages/';
	private static $translations_buffer;

	public function __construct() {

		// Init members;
		self::$translations_buffer = array();
		// Init Oxygen addons
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		// Add a tool sub-page for translating stuff
		add_action( 'admin_menu', array( $this, 'register_tool_page' ) );
	}

	public function init() {

		if ( ! class_exists( 'OxygenElement' ) ) {
			return;
		}

		// New Elements
		require_once __DIR__ . '/elements/class-elements.php';
		require_once __DIR__ . '/elements/class-i18n-text.php';

		// Init instances
		$oxy_i18n_text = new I18N_Text();
	}

	public function register_tool_page() {
		add_submenu_page(
			'tools.php',
			'Translate Oxygen',
			'Translate Oxygen',
			'administrator',
			'oxygen-i18n-text-translation',
			array( $this, 'render_tool_page_template' ),
			PHP_INT_MAX
		);
	}

	public function render_tool_page_template() {

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : false;
		if ( $nonce !== false && wp_verify_nonce( $nonce, 'translate-oxygen' ) !== false ) {
			$result = $this->translate_all_templates();
			if ( $result === true ) {
				echo '<h3 class="success-msg">Done translations with success!</h3>';
			} else {
				echo '<h3 class="err-msg">' . esc_html( $result ) . '</h3>';
			}
		} else {
			echo "<form method='POST' style='padding:40px'>";
			wp_nonce_field(
				'translate-oxygen',
				'nonce'
			);
			echo '<h3>Translate every i18n-text component inside Oxygen templates.</h3>';
			echo '<button class="button button-primary button-large">Translate</button>';
			echo '</form>';
		}
		echo '<style>
			.success-msg{
				background-color: white;
				padding: 20px;
				border-left: 4px solid #68de7c;
			}
			.err-msg{
				background-color: white;
				padding: 20px;
				border-left: 4px solid red;
			}
		</style>';
	}

	private function translate_all_templates() {

		// Empty the output buffer
		self::$translations_buffer = array();

		$posts_ids = get_posts(
			array(
				'post_type'      => 'ct_template',
				'fields'         => 'ids', // Only get post IDs
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		foreach ( $posts_ids as $post_id ) {
			$this->put_strings_into_buffer( $post_id );
		}

		// Exclude duplicated strings
		self::$translations_buffer = array_unique( self::$translations_buffer );

		// Write translations to file
		$res = $this->write_translations_to_file();

		// Free the memory
		self::$translations_buffer = array();

		return $res;
	}

	// Put all translatable strings into the buffer
	private function put_strings_into_buffer( $post_id ) {

		if ( get_post_status( $post_id ) !== 'publish' ) {
			return;
		}

		// Get the json meta
		$meta = get_post_meta( $post_id, 'ct_builder_json', true );

		// Convert to json
		$elements = json_decode( $meta, true, 512 );

		// If no JSON return...
		if ( empty( $elements ) ) {
			return;
		}

		$strings = array();
		$this->get_translations( 'oxy-i18n-text_i18n_text', $elements, $strings );
		self::$translations_buffer = array_merge( self::$translations_buffer, $strings );
	}

	// Get all translations recursively
	private function get_translations( $needle_key, $elements, &$strings ) {

		foreach ( $elements as $key => $value ) {

			if ( $key === $needle_key ) {
				$strings[] = $value;
			}

			if ( is_array( $value ) ) {
				$this->get_translations( $needle_key, $value, $strings );
			}
		}
	}

	private function write_translations_to_file() {
		$output = '';
		foreach ( self::$translations_buffer as $translation ) {
			$output .= '__( "' . addcslashes( $translation, '"' ) . '", \'' . self::get_plugin_textdomain() . '\' );' . PHP_EOL;
		}

		$language_path = apply_filters( 'asw_oxygen_language_path', self::$language_path );

		// Create translation file ... if not exist
		if ( ! file_exists( $language_path . 'oxy-translation.php' ) ) {
			if ( ! touch( $language_path . 'oxy-translation.php' ) || ! chmod( $language_path . 'oxy-translation.php', 664 ) ) {
				return "Could not write the translation file to $language_path. Verify that this path has a write access!";
			}
		}

		if ( ! file_put_contents( $language_path . 'oxy-translation.php', '<?php' . PHP_EOL . $output ) ) {
			return "Could not write the translation file to $language_path/oxy-translation.php. Verify that this file has a write access!";
		}

		return true;
	}

	// Helper: Check if we are rendering a component inside the builder
	// About HTTP_REFERER: Not all user agents will set this, and some provide the ability to
	// modify HTTP_REFERER as a feature. In short, it cannot really be trusted.
	// TODO: Update this for a better solution.
	public static function rendering_component_inside_builder() {

		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			return str_contains( sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), 'ct_builder=true' );
		}

		return false;
	}

	// Helper: Check if the $REQUEST come from the builder
	public static function request_from_builder() {
		return isset( $_REQUEST['ct_builder'] ) && $_REQUEST['ct_builder'] === 'true';
	}

	// Helper: Get the plugin TextDomain
	public static function get_plugin_textdomain() {
		return apply_filters( 'asw_oxygen_textdomain', self::$text_domain );
	}

}
new Oxygen();
