<?php
/**
 * RTMediaUploadTermsAdmin.php
 * This file sets the admin settings and handle feature of Upload Terms at admin side.
 *
 * @package rtMedia
 * @author  Malav Vasita <malav.vasita@rtcamp.com>
 */

/**
 *  Class for Admin settings regarding Upload Terms
 */
class RTMediaUploadTermsAdmin {

	/**
	 * Message for lable on front end side.
	 *
	 * @var string
	 */
	public $upload_terms_message;
	/**
	 * Error message for lable on front end side.
	 *
	 * @var string
	 */
	public $upload_terms_error_message;

	/**
	 * Constructing settings for upload terms.
	 */
	public function __construct() {
		$this->upload_terms_message       = esc_html__( 'terms of services.', 'buddypress-media' );
		$this->upload_terms_error_message = esc_html__( 'Please check terms of service.', 'buddypress-media' );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ), 999 );
		add_filter( 'rtmedia_general_content_default_values', array( $this, 'add_admin_option_default_value' ), 10, 1 );
		add_filter( 'rtmedia_general_content_groups', array( $this, 'admin_setting_add_terms_section' ), 10, 1 );
		add_filter( 'rtmedia_general_content_add_itmes', array( $this, 'admin_setting_add_terms_option' ), 10, 2 );
	}

	/**
	 * Loads styles and scripts
	 *
	 * @global object $rtmedia
	 */
	public function enqueue_scripts_styles() {
		global $rtmedia;

		$suffix = ( function_exists( 'rtm_get_script_style_suffix' ) ) ? rtm_get_script_style_suffix() : '.min';

		wp_enqueue_script( 'rtmedia-upload-terms-main', RTMEDIA_URL . 'app/assets/js/admin-upload-terms' . $suffix . '.js', array( 'jquery' ), RTMEDIA_VERSION, true );
	}

	/**
	 * Default option value for admin settings
	 *
	 * @param  array $defaults Default values of rtMedia admin settings.
	 * @return array defaults
	 */
	public function add_admin_option_default_value( $defaults ) {

		$defaults[ 'general_enable_upload_terms' ]               = 0;
		$defaults[ 'general_upload_terms_show_pricacy_message' ] = 0;
		$defaults[ 'activity_enable_upload_terms' ]              = 0;
		$defaults[ 'general_upload_terms_page_link' ]            = '';

		/**
		 * If `Terms of Service Message` and `Error Message` and not set from admin setting then set default value
		 */
		global $rtmedia;

		if ( empty( $rtmedia->options ) ) {
			$rtmedia->options = rtmedia_get_site_option( 'rtmedia-options' );
		}

		if ( ! empty( $rtmedia->options ) ) {
			$update = 0;

			if ( empty( $rtmedia->options[ 'general_upload_terms_message' ] ) ) {

				$rtmedia->options[ 'general_upload_terms_message' ] = $this->upload_terms_message;

				$update = 1;
			}

			if ( empty( $rtmedia->options[ 'general_upload_terms_error_message' ] ) ) {

				$rtmedia->options[ 'general_upload_terms_error_message' ] = $this->upload_terms_error_message;

				$update = 1;
			}

			if ( 1 === $update ) {
				rtmedia_update_site_option( 'rtmedia-options', $rtmedia->options );
			}
		}
		return $defaults;
	}

	/**
	 * Add setting option in rtmedia settings
	 *
	 * @param  array $general_group Add group message.
	 * @return array $general_group
	 */
	public function admin_setting_add_terms_section( $general_group ) {
		$general_group[40] = esc_html__( 'Ask users to agree to your terms', 'buddypress-media' );

		return $general_group;
	}

	/**
	 * Configure admin options to render
	 *
	 * @param  array $render_options  Rendering according to selected options.
	 * @param  array $options         Options selected in settings.
	 * @return array $render_option
	 */
	public function admin_setting_add_terms_option( $render_options, $options ) {
		$render_options[ 'general_enable_upload_terms' ]    = array(
			'title'    => __( 'Show "Terms of Service" checkbox on upload screen', 'buddypress-media' ),
			'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
			'args'     => array(
				'key'   => 'general_enable_upload_terms',
				'value' => $options[ 'general_enable_upload_terms' ],
				'desc'  => __( 'User have to check the terms and conditions before uploading the media.', 'buddypress-media' ),
			),
			'group'    => 40,
			'class'    => 'aaaa',
		);
		$render_options['activity_enable_upload_terms']   = array(
			'title'    => __( 'Show "Terms of Service" checkbox on activity screen', 'buddypress-media' ),
			'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
			'args'     => array(
				'key'   => 'activity_enable_upload_terms',
				'value' => $options[ 'activity_enable_upload_terms' ],
				'desc'  => __( 'User have to check the terms and conditions before uploading the media.', 'buddypress-media' ),
			),
			'group'    => 40,
		);
		$render_options['general_upload_terms_page_link'] = array(
			'title'    => __( 'Link for "Terms of Service" page', 'buddypress-media' ),
			'callback' => array( 'RTMediaFormHandler', 'textbox' ),
			'args'     => array(
				'key'   => 'general_upload_terms_page_link',
				'value' => $options[ 'general_upload_terms_page_link' ],
				'desc'  => __( 'Link to the terms and condition page where user can read terms and conditions.', 'buddypress-media' ),
			),
			'group'    => 40,
		);
		// add extra field for admin setting.
		$render_options[ 'general_upload_terms_message' ]              = array(
			'title'    => __( 'Terms of Service Message', 'buddypress-media' ),
			'callback' => array( 'RTMediaFormHandler', 'textbox' ),
			'args'     => array(
				'key'   => 'general_upload_terms_message',
				'value' => isset( $options[ 'general_upload_terms_message' ] ) ? $options[ 'general_upload_terms_message' ] : $this->upload_terms_message,
				'desc'  => __( 'Add Terms of Service Message.', 'buddypress-media' ),
			),
			'group'    => 40,
		);
		$render_options['general_upload_terms_error_message']        = array(
			'title'    => __( 'Error Message', 'buddypress-media' ),
			'callback' => array( 'RTMediaFormHandler', 'textbox' ),
			'args'     => array(
				'key'   => 'general_upload_terms_error_message',
				'value' => isset( $options[ 'general_upload_terms_error_message' ] ) ? $options[ 'general_upload_terms_error_message' ] : $this->upload_terms_error_message,
				'desc'  => __( 'Display Error Message When User Upload Media Without selecting checkbox .', 'buddypress-media' ),
			),
			'group'    => 40,
		);
		$render_options['general_upload_terms_show_pricacy_message'] = array(
			'title'    => __( 'Show "Privacy Message" on website', 'buddypress-media' ),
			'callback' => array( 'RTMediaFormHandler', 'checkbox' ),
			'args'     => array(
				'key'   => 'general_upload_terms_show_pricacy_message',
				'value' => $options[ 'general_upload_terms_show_pricacy_message' ],
				'desc'  => __( 'User will see the privacy message on website.', 'buddypress-media' ),
			),
			'group'    => 40,
		);
		$render_options['general_upload_terms_privacy_message']      = array(
			'title'    => __( 'Privacy Message', 'buddypress-media' ),
			'callback' => array( 'RTMediaFormHandler', 'textarea' ),
			'args'     => array(
				'key'   => 'general_upload_terms_privacy_message',
				'value' => isset( $options[ 'general_upload_terms_privacy_message' ] ) ? $options[ 'general_upload_terms_privacy_message' ] : '',
				'desc'  => __( 'Display privacy message on your website.', 'buddypress-media' ),
			),
			'group'    => 40,
		);

		return $render_options;
	}
}
/**
 * Check if old plugin rtMedia upload terms is activate or not.
 */
function rtm_check_admin_upload_terms() {

	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	// Check rtMedia upload terms plugin is exist and active.
	if ( ! is_plugin_active( 'rtmedia-upload-terms/index.php' ) ) {
		// Instantiate object.
		new RTMediaUploadTermsAdmin();
	}
}

// Call to instantiate object.
rtm_check_admin_upload_terms();
