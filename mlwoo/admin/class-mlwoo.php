<?php
/**
 * Deals with setting and saving fields on the E-Commerce tab.
 *
 * @package MLWoo
 */

namespace MLWoo\Admin;

/**
 * Defines methods to set up the E-Commerce page.
 * Renders fields and logic to save data from the fields.
 */
class MLWoo {

	/**
	 * The current Tab slug.
	 *
	 * @var string
	 */
	public $tab = '';

	/**
	 * The current page slug.
	 *
	 * @var string
	 */
	public $page = '';

	/**
	 * Calls necessary hooks.
	 */
	public function init() {
		$this->tab  = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
		$this->page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

		add_filter( 'mobiloud_settings_tabs', array( $this, 'add_ecommerce_tab' ), 10, 1 );
		add_action( 'mobiloud_add_tab_details', array( $this, 'ecommerce_tab_setup' ), 10, 1 );
		add_action( 'init', array( $this, 'save_settings' ), 12 );
		add_filter( 'mobiloud_config_endpoint', array( $this, 'add_to_config' ) );
		add_action( 'mobiloud_admin_editors_before_support', array( $this, 'add_custom_css_editor' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'mlwoo_page_css_scripts' ) );
		add_action( 'wp_ajax_mlwoo_page_save_css', array( $this, 'mlwoo_page_save_css' ) );
	}

	/**
	 * Adds the `E-commerce` tabs under `MobiLoud` options page.
	 *
	 * @param array $tabs Array of tabs.
	 *
	 * @return array
	 */
	public function add_ecommerce_tab( $tabs ) {
		$tabs['ecommerce'] = array(
			'title'        => __( 'E-commerce', 'mlwoo' ),
			'form_wrap_id' => 'ml-ecommerce',
			'form_id'      => '',
		);

		return $tabs;
	}

	/**
	 * Sets up the fields for the `E-commerce` tab
	 * by calling necessary methods.
	 *
	 * @param string $tab Name of the current tab.
	 */
	public function ecommerce_tab_setup( $tab ) {
		if ( 'ecommerce' !== $tab ) {
			return;
		}

		self::render_view( 'settings_paywall', 'get_started' );
	}

	/**
	 * Renders the settings.
	 *
	 * @param string $view   Name of the View.
	 * @param string $parent Name of the parent.
	 */
	public function render_view( $view = '', $parent = '' ) {
		if ( 'get_started' === $parent ) {
			define( 'ml_with_sidebar', true );
			define( 'ml_with_form', true );
			if ( 'settings_editor' === $view ) {
				define( 'no_submit_button', true );
			}
		} elseif ( 'push' === $parent ) {
			define( 'ml_with_sidebar', true );
		}

		include MOBILOUD_PLUGIN_DIR . 'views/header.php';
		include MOBILOUD_PLUGIN_DIR . "views/header_{$parent}.php";
		include MLWOO_PATH . 'mlwoo/admin/views/ecommerce-settings.php';
		include MOBILOUD_PLUGIN_DIR . 'views/footer.php';
	}

	/**
	 * Save settings on the E-Commerce tab.
	 */
	public function save_settings() {
		if ( ! isset( $_POST['submit'] ) ) {
			return;
		}

		if ( 'ecommerce' !== $this->tab ) {
			return;
		}

		check_admin_referer( 'ml-form-' . $this->tab );

		$ecommerce_status   = filter_input( INPUT_POST, 'ml-ecommerce-status', FILTER_SANITIZE_STRING );
		$ecommerce_platform = filter_input( INPUT_POST, 'ml-ecommerce-platform', FILTER_SANITIZE_STRING );
		$is_dev_mode        = filter_input( INPUT_POST, 'ml-ecommerce-dev-mode', FILTER_SANITIZE_STRING );
		$assets_version     = filter_input( INPUT_POST, 'ml-ecommerce-assets-version', FILTER_SANITIZE_STRING );
		$cart_icon_status   = filter_input( INPUT_POST, 'ml-ecommerce-toggle-cart-icon', FILTER_SANITIZE_STRING );
		$disabled_plugins   = filter_input( INPUT_POST, 'ml-ecommerce-active-plugins', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		if ( false !== $ecommerce_status ) {
			\Mobiloud::set_option( 'ml-ecommerce-status', $ecommerce_status );
		}

		if ( false !== $ecommerce_platform ) {
			\Mobiloud::set_option( 'ml-ecommerce-platform', $ecommerce_platform );
		}

		if ( false !== $is_dev_mode ) {
			\Mobiloud::set_option( 'ml-ecommerce-dev-mode', $is_dev_mode );
		}

		if ( false !== $assets_version ) {
			\Mobiloud::set_option( 'ml-ecommerce-assets-version', $assets_version );
		}

		if ( null === $cart_icon_status ) {
			\Mobiloud::set_option( 'ml-ecommerce-toggle-cart-icon', 'off' );
		} else {
			\Mobiloud::set_option( 'ml-ecommerce-toggle-cart-icon', $cart_icon_status );
		}

		if ( null !== $disabled_plugins ) {
			\Mobiloud::set_option( 'ml-ecommerce-active-plugins', $disabled_plugins );
		}
	}

	/**
	 * Filters the /config endpoint array.
	 *
	 * @param array $config_array Array of config data.
	 *
	 * @return array
	 */
	public function add_to_config( $config_array ) {
		$config_array['ecommerce_integration']       = '0' === \Mobiloud::get_option( 'ml-ecommerce-platform', '0' ) ? '' : \Mobiloud::get_option( 'ml-ecommerce-platform', '0' );
		$config_array['ecommerce_display_cart_icon'] = \MLWoo\Admin\Helper::is_cart_icon_active() ? '1' : '';
		return $config_array;
	}

	/**
	 * Template for Custom CSS editors for MobiLoud Commerce pages.
	 */
	public function add_custom_css_editor() {
		require_once 'views/custom-css-editor.php';
	}

	/**
	 * Loads JS assets to load Codemirror CSS editors.
	 */
	public function mlwoo_page_css_scripts() {
		if ( ! ( 'mobiloud' === $this->page && 'editor' == $this->tab ) ) {
			return;
		}

		$codeMirrorArgs = array(
			'codemirror' => array(
				'indentWithTabs' => 1,
				'indentUnit' => 4,
				'inputStyle' => 'contenteditable',
				'lineNumbers' => true,
				'lineWrapping' => 1,
				'styleActiveLine' => 1,
				'continueComments' => 1,
				'extraKeys' => array(
					'Ctrl-Space' => 'autocomplete',
					'Ctrl-/' => 'toggleComment',
					'Cmd-/' => 'toggleComment',
					'Alt-F' => 'findPersistent',
					'Ctrl-F' => 'findPersistent',
					'Cmd-F' => 'findPersistent',
				),
				'direction' => 'ltr',
				'gutters' => array(),
				'mode' => 'text/css',
				'lint' => '',
				'autoCloseBrackets' => 1,
				'matchBrackets' => 1,
			),
			'csslint' => array(
				'errors' => 1,
				'box-model' => 1,
				'display-property-grouping' => 1,
				'duplicate-properties' => 1,
				'known-properties' => 1,
				'outline-none' => 1,
			),
			'jshint' => array(
				'boss' => 1,
				'curly' => 1,
				'eqeqeq' => 1,
				'eqnull' => 1,
				'es3' => 1,
				'expr' => 1,
				'immed' => 1,
				'noarg' => 1,
				'nonbsp' => 1,
				'onevar' => 1,
				'quotmark' => 'single',
				'trailing' => 1,
				'undef' => 1,
				'unused' => 1,
				'browser' => 1,
				'globals' => array(
					'_' => '',
					'Backbone' => '',
					'jQuery' => '',
					'JSON' => '',
					'wp' => '',
				)
			),
			'htmlhint' => array(
				'tagname-lowercase' => 1,
				'attr-lowercase' => 1,
				'attr-value-doubl-quotes' => '',
				'doctyp-first' => '',
				'tag-pair' => 1,
				'spec-char-escape' => 1,
				'id-unique' => 1,
				'src-not-empty' => 1,
				'attr-no-duplication' => 1,
				'alt-require' => 1,
				'space-tab-mixed-disabled' => 'tab',
				'attr-unsafe-chars' => 1,
			),
		);

		wp_enqueue_script( 'mlwoo-pages-css-editors-style', MLWOO_URL . 'src/js/admin/mlwoo-css-editors.js', array( 'mobiloud-editor' ), '1.0', true );
		wp_localize_script( 'mlwoo-pages-css-editors-style', 'mlCodeMirror', $codeMirrorArgs );
	}

	/**
	 * Logic to save CSS styles saved through Codemirror editors
	 * for MobiLoud Commerce pages.
	 */
	public function mlwoo_page_save_css() {
		$editor = filter_input( INPUT_POST, 'editor', FILTER_SANITIZE_STRING );
		$value  = filter_input( INPUT_POST, 'value', FILTER_SANITIZE_STRING );

		if ( \Mobiloud::is_action_allowed_ajax( 'mlwoo_save_editor' ) ) {
			\Mobiloud::set_option( sanitize_text_field( $editor ), $value );
			wp_send_json_success();
		}
	}
}
