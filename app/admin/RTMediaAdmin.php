<?php
/**
 * Description of RTMediaAdmin.
 *
 * @package    RTMedia
 * @subpackage Admin
 */

if ( ! class_exists( 'RTMediaAdmin' ) ) {

	/**
	 * RTMediaAdmin class.
	 */
	class RTMediaAdmin {
                /**
                 * Member variables
                 * 
                 * @var public $rtmedia_upgrade
                 */
		public $rtmedia_upgrade;
                /**
                 * Member variables
                 * 
                 * @var public $rtmedia_settings
                 */
		public $rtmedia_settings;
                /**
                 * Member variables
                 * 
                 * @var public $rtmedia_encoding
                 */
		public $rtmedia_encoding;
                /**
                 * Member variables
                 * 
                 * @var public $rtmedia_support
                 */
		public $rtmedia_support;
                /**
                 * Member variables
                 * 
                 * @var public $rtmedia_feed
                 */
		public $rtmedia_feed;

		/**
		 * Constructor - get the plugin hooked in and ready.
		 *
		 * @access public
		 */
		public function __construct() {
			global $rtmedia;

			// Actions and filters.
			add_action( 'init', array( $this, 'video_transcoding_survey_response' ) );
			add_filter( 'plugin_action_links_' . RTMEDIA_BASE_NAME, array( &$this, 'plugin_add_settings_link' ) );

			$this->rtmedia_support = new RTMediaSupport();
			add_action( 'wp_ajax_rtmedia_select_request', array( $this->rtmedia_support, 'get_form' ), 1 );

			add_action( 'wp_ajax_rtmedia_cancel_request', array( $this->rtmedia_support, 'rtmedia_cancel_request' ), 1 );

			add_action( 'wp_ajax_rtmedia_submit_request', array( $this->rtmedia_support, 'submit_request' ), 1 );

			add_action( 'wp_ajax_rtmedia_linkback', array( $this, 'linkback' ), 1 ); // fixme : is it being used ?
			add_action( 'wp_ajax_rtmedia_rt_album_deactivate', 'BPMediaAlbumimporter::bp_album_deactivate', 1 );
			add_action( 'wp_ajax_rtmedia_rt_album_import', 'BPMediaAlbumimporter::bpmedia_ajax_import_callback', 1 );
			add_action( 'wp_ajax_rtmedia_rt_album_import_favorites', 'BPMediaAlbumimporter::bpmedia_ajax_import_favorites', 1 );
			add_action( 'wp_ajax_rtmedia_rt_album_import_step_favorites', 'BPMediaAlbumimporter::bpmedia_ajax_import_step_favorites', 1 );
			add_action( 'wp_ajax_rtmedia_rt_album_cleanup', 'BPMediaAlbumimporter::cleanup_after_install' );
			add_action( 'wp_ajax_rtmedia_convert_videos_form', array( $this, 'convert_videos_mailchimp_send' ), 1 ); // fixme : is it being used ?
			add_action( 'wp_ajax_rtmedia_correct_upload_filetypes', array( $this, 'correct_upload_filetypes' ), 1 );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_meta_premium_addon_link' ), 1, 2 );
			add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ), 0 );
			add_filter( 'attachment_fields_to_edit', array( $this, 'edit_video_thumbnail' ), null, 2 );
			add_filter( 'attachment_fields_to_save', array( $this, 'save_video_thumbnail' ), null, 1 );
			add_action( 'wp_ajax_rtmedia_hide_video_thumb_admin_notice', array(
				$this,
				'rtmedia_hide_video_thumb_admin_notice',
			), 1 );
			add_action( 'wp_ajax_rtmedia_hide_addon_update_notice', array(
				$this,
				'rtmedia_hide_addon_update_notice',
			), 1 );
			add_filter( 'media_row_actions', array( $this, 'modify_medialibrary_permalink' ), 10, 2 );

			$obj_encoding = new RTMediaEncoding( true );

			if ( $obj_encoding->api_key ) {
				add_filter( 'media_row_actions', array( $this, 'add_reencode_link' ), null, 2 );
				add_action( 'admin_head-upload.php', array( $this, 'add_bulk_actions_regenerate' ) );
				add_action( 'admin_footer', array( $this, 'rtmedia_regenerate_thumb_js' ) );
				add_action( 'admin_action_bulk_video_regenerate_thumbnails', array( $this, 'bulk_action_handler' ) );
				add_action( 'admin_action_-1', array( $this, 'bulk_action_handler' ) );
			}

			add_action( 'wp_ajax_rt_media_regeneration', array( $this, 'rt_media_regeneration' ), 1 );

			if ( ! isset( $rtmedia->options ) ) {
				$rtmedia->options = rtmedia_get_site_option( 'rtmedia-options' );
			}

			$rtmedia_option = filter_input( INPUT_POST, 'rtmedia-options', FILTER_DEFAULT, FILTER_SANITIZE_NUMBER_INT );
			if ( isset( $rtmedia_option ) ) {
				if ( isset( $rtmedia_option['general_showAdminMenu'] ) && 1 === intval( $rtmedia_option['general_showAdminMenu'] ) ) {
					add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 100, 1 );
				}
			} else {
				if ( 1 === intval( $rtmedia->options['general_showAdminMenu'] ) ) {
					add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 100, 1 );
				}
			}

			if ( is_admin() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'ui' ) );
				add_action( 'admin_menu', array( $this, 'menu' ), 1 );
				add_action( 'init', array( $this, 'bp_admin_tabs' ) );

				if ( is_multisite() ) {
					add_action( 'network_admin_edit_rtmedia', array( $this, 'save_multisite_options' ) );
				}
			}

			$this->rtmedia_settings = new RTMediaSettings();
			$this->rtmedia_encoding = new RTMediaEncoding();

			if ( ! class_exists( 'BuddyPress' ) ) {
				add_action( 'admin_init', array( $this, 'check_permalink_admin_notice' ) );
			}

			add_action( 'wp_ajax_rtmedia_hide_template_override_notice', array(
				$this,
				'rtmedia_hide_template_override_notice',
			), 1 );
			add_action( 'admin_init', array( $this, 'rtmedia_bp_add_update_type' ) );
			add_action( 'wp_ajax_rtmedia_hide_inspirebook_release_notice', array(
				$this,
				'rtmedia_hide_inspirebook_release_notice',
			), 1 );
			add_action( 'wp_ajax_rtmedia_hide_social_sync_notice', array(
				$this,
				'rtmedia_hide_social_sync_notice',
			), 1 );
			add_action( 'wp_ajax_rtmedia_hide_pro_split_notice', array( $this, 'rtmedia_hide_pro_split_notice' ), 1 );

			new RTMediaMediaSizeImporter(); // do not delete this line. We only need to create object of this class if we are in admin section.
			if ( class_exists( 'BuddyPress' ) ) {
				new RTMediaActivityUpgrade();
			}
			add_action( 'admin_notices', array( $this, 'rtmedia_admin_notices' ) );
			add_action( 'network_admin_notices', array( $this, 'rtmedia_network_admin_notices' ) );
			add_action( 'admin_init', array( $this, 'rtmedia_addon_license_save_hook' ) );
			add_action( 'admin_init', array( $this, 'rtmedia_migration' ) );

			add_filter( 'removable_query_args', array( $this, 'removable_query_args' ), 10, 1 );

			add_action( 'admin_footer', array( $this, 'rtm_admin_templates' ) );
		}

		function rtm_admin_templates() {
			foreach ( glob( RTMEDIA_PATH . 'app/admin/templates/*.php' ) as $filename ) {
				$slug = rtrim( basename( $filename ), '.php' );

				echo '<script type="text/html" id="' . esc_attr( $slug ) . '">';
				include $filename;
				echo '</script>';
			}
		}

		function modify_medialibrary_permalink( $action, $post ) {
			$rtm_id = rtmedia_id( $post->ID );

			if ( $rtm_id ) {
				$link           = get_rtmedia_permalink( $rtm_id );
				$title          = _draft_or_post_title( $post->post_parent );
				$action['view'] = '<a href="' . esc_url( $link ) . '" title="' . esc_attr( sprintf( esc_html__( 'View &#8220;%s&#8221;', 'buddypress-media' ), $title ) ) . '" rel="permalink">' . esc_html__( 'View', 'buddypress-media' ) . '</a>';
			}

			return $action;
		}

		function rtmedia_migration() {
			new RTMediaMigration();
		}

		function rtmedia_addon_license_save_hook() {
			do_action( 'rtmedia_addon_license_save_hook' );
		}

		/**
		 * Show rtmedia network admin notices.
		 *
		 * @access public
		 */
		public function rtmedia_network_admin_notices() {
			if ( is_multisite() ) {
				$this->upload_filetypes_error();
			}
		}

		/**
		 * Show rtmedia admin notices.
		 *
		 * @access public
		 */
		public function rtmedia_admin_notices() {
			if ( current_user_can( 'list_users' ) ) {
				$this->upload_filetypes_error();
				$this->rtmedia_regenerate_thumbnail_notice();
				$this->rtmedia_addon_update_notice();
				$this->rtmedia_update_template_notice();

				if ( ! is_rtmedia_vip_plugin() ) {
					$this->rtmedia_inspirebook_release_notice();
					$this->rtmedia_social_sync_release_notice();

					if ( ! defined( 'RTMEDIA_PRO_PATH' ) ) {
						$this->rtmedia_pro_split_release_notice();
					}
				}
			}
		}

		/**
		 * Admin notice for rtMedia Pro split release.
		 */
		public function rtmedia_pro_split_release_notice() {
			$site_option = rtmedia_get_site_option( 'rtmedia_pro_split_release_notice' );

			if ( ( ! $site_option || 'hide' !== $site_option ) ) {
				rtmedia_update_site_option( 'rtmedia_pro_split_release_notice', 'show' );
				?>
				<div class="updated rtmedia-pro-split-notice">
					<p>
						<span>
							<b><?php esc_html_e( 'rtMedia: ', 'buddypress-media' ); ?></b>
							<?php esc_html_e( 'We have released 30+ premium add-ons for rtMedia plugin. Read more about it ', 'buddypress-media' ); ?>
							<a href="https://rtmedia.io/blog/rtmedia-pro-splitting-major-change/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media"
							   target="_blank"><?php esc_html_e( 'here', 'buddypress-media' ) ?></a>.
						</span>
						<a href="#"
						   onclick="rtmedia_hide_pro_split_notice('<?php echo esc_js( wp_create_nonce( 'rtcamp_pro_split' ) ); ?>');"
						   style="float:right">Dismiss</a>
					</p>
				</div>
				<script type="text/javascript">
					function rtmedia_hide_pro_split_notice(nonce) {
						var data = {action: 'rtmedia_hide_pro_split_notice', _rtm_nonce: nonce };
						jQuery.post(ajaxurl, data, function (response) {
							response = response.trim();

							if (response === "1")
								jQuery('.rtmedia-pro-split-notice').remove();
						});
					}
				</script>
				<?php
			}
		}

		/**
		 * Hide pro split release notice.
		 */
		function rtmedia_hide_pro_split_notice() {
			if ( check_ajax_referer( 'rtcamp_pro_split', '_rtm_nonce' ) && rtmedia_update_site_option( 'rtmedia_pro_split_release_notice', 'hide' ) ) {
				echo '1';
			} else {
				echo '0';
			}
			die();
		}

		/**
		 * Show social sync release notice admin notice.
		 */
		function rtmedia_social_sync_release_notice() {
			$site_option                         = rtmedia_get_site_option( 'rtmedia_social_sync_release_notice' );
			$check_rtmedia_social_sync_installed = file_exists( trailingslashit( WP_PLUGIN_DIR ) . 'rtmedia-social-sync/index.php' );

			if ( ( ! $site_option || 'hide' !== $site_option ) && ! $check_rtmedia_social_sync_installed ) {
				rtmedia_update_site_option( 'rtmedia_social_sync_release_notice', 'show' );
				?>
				<div class="updated rtmedia-social-sync-notice">
					<p>
						<span>
						    <b><?php esc_html_e( 'rtMedia: ', 'buddypress-media' ); ?></b>
							<?php esc_html_e( 'Meet ', 'buddypress-media' ); ?>
							<a href="https://rtmedia.io/products/rtmedia-social-sync/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media"
							   target="_blank">
								<b><?php esc_html_e( 'rtMedia Social Sync', 'buddypress-media' ) ?></b>
							</a>
							<?php esc_html_e( ' which allows you to import media from your Facebook account.', 'buddypress-media' ); ?>
						</span>
						<a href="#"
						   onclick="rtmedia_hide_social_sync_notice('<?php echo esc_js( wp_create_nonce( 'social_sync' ) ); ?>')"
						   style="float:right">Dismiss</a>
					</p>
				</div>
				<script type="text/javascript">
					function rtmedia_hide_social_sync_notice(nonce) {
						var data = {action: 'rtmedia_hide_social_sync_notice', _rtm_nonce: nonce};
						jQuery.post(ajaxurl, data, function (response) {
							response = response.trim();
							if (response === "1")
								jQuery('.rtmedia-social-sync-notice').remove();
						});
					}
				</script>
				<?php
			}
		}

		/**
		 * Hide social sync release notice.
		 */
		function rtmedia_hide_social_sync_notice() {
			if ( check_ajax_referer( 'social_sync', '_rtm_nonce' ) && rtmedia_update_site_option( 'rtmedia_social_sync_release_notice', 'hide' ) ) {
				echo '1';
			} else {
				echo '0';
			}
			die();
		}

		/**
		 * Show rtmedia inspirebook release notice.
		 *
		 * @access public
		 */
		public function rtmedia_inspirebook_release_notice() {
			$site_option = rtmedia_get_site_option( 'rtmedia_inspirebook_release_notice' );
			if ( ( ! $site_option || 'hide' !== $site_option ) && ( 'inspirebook' !== get_stylesheet() ) ) {
				rtmedia_update_site_option( 'rtmedia_inspirebook_release_notice', 'show' );
				?>
				<div class="updated rtmedia-inspire-book-notice">
					<p>
						<span>
							<a href="https://rtmedia.io/products/inspirebook/"
							   target="_blank">
								<b><?php esc_html_e( 'Meet InspireBook', 'buddypress-media' ) ?></b>
							</a>
							<?php esc_html_e( ' - First official rtMedia premium theme.', 'buddypress-media' ); ?>
						</span>
						<a href="#" onclick="rtmedia_hide_inspirebook_notice()" style="float:right">Dismiss</a>
						<?php wp_nonce_field( '_rtmedia_hide_inspirebook_notice_','rtmedia_hide_inspirebook_nonce' ); ?>
					</p>
				</div>
				<script type="text/javascript">
					function rtmedia_hide_inspirebook_notice() {
						var data = {
							action: 'rtmedia_hide_inspirebook_release_notice',
							_rtm_nonce: jQuery('#rtmedia_hide_inspirebook_nonce').val()
						};
						jQuery.post(ajaxurl, data, function (response) {
							response = response.trim();
							if (response === "1")
								jQuery('.rtmedia-inspire-book-notice').remove();
						});
					}
				</script>
				<?php
			}
		}

		/**
		 * Hide rtmedia inspirebook release notice.
		 *
		 * @access public
		 */
		public function rtmedia_hide_inspirebook_release_notice() {

			if ( check_ajax_referer( '_rtmedia_hide_inspirebook_notice_', '_rtm_nonce' ) && rtmedia_update_site_option( 'rtmedia_inspirebook_release_notice', 'hide' ) ) {
				echo '1';
			} else {
				echo '0';
			}
			die();
		}

		/**
		 * Set rtmedia buddypress update type.
		 *
		 * @access public
		 */
		public function rtmedia_bp_add_update_type() {
			if ( class_exists( 'BuddyPress' ) && function_exists( 'bp_activity_set_action' ) ) {
				bp_activity_set_action( 'rtmedia_update', 'rtmedia_update', 'rtMedia Update' );
			}
		}

		/**
		 * Show rtmedia check permalink admin notice.
		 *
		 * @access public
		 */
		public function check_permalink_admin_notice() {
			global $wp_rewrite;
			if ( empty( $wp_rewrite->permalink_structure ) ) {
				add_action( 'admin_notices', array( $this, 'rtmedia_permalink_notice' ) );
			}
		}

		/**
		 * Define rtmedia permalink notice.
		 *
		 * @access public
		 */
		public function rtmedia_permalink_notice() {
			echo '<div class="error rtmedia-permalink-change-notice">
		    <p> <b>' . esc_html__( 'rtMedia:', 'buddypress-media' ) . '</b> ' . esc_html__( ' You must', 'buddypress-media' ) . ' <a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">' . esc_html__( 'update permalink structure', 'buddypress-media' ) . '</a> ' . esc_html__( 'to something other than the default for it to work.', 'buddypress-media' ) . ' </p>
		    </div>';
		}

		/**
		 * Define rtmedia addon update notice.
		 *
		 * @access public
		 */
		public function rtmedia_addon_update_notice() {

			$site_option = rtmedia_get_site_option( 'rtmedia-addon-update-notice-3_8' );
			if ( is_rt_admin() && ( ! $site_option || 'hide' !== $site_option ) ) {

				if ( ! $this->check_for_addon_update_notice() ) {
					return;
				}
				rtmedia_update_site_option( 'rtmedia-addon-update-notice-3_8', 'show' );
				?>
				<div class="error rtmedia-addon-upate-notice">
					<p>
						<strong><?php esc_html_e( 'rtMedia:', 'buddypress-media' ) ?></strong>
						<?php esc_html_e( 'Please update all premium add-ons that you have purchased from rtCamp from', 'buddypress-media' ) ?>
						<a href="https://rtmedia.io/my-account/"
						   target="_blank"><?php esc_html_e( 'your account', 'buddypress-media' ) ?></a>.
						<a href="#" onclick="rtmedia_hide_addon_update_notice()"
						   style="float:right"><?php esc_html_e( 'Dismiss', 'buddypress-media' ) ?></a>
						<?php wp_nonce_field( 'rtmedia-addon-update-notice-3_8', 'rtmedia-addon-notice' ); ?>
					</p>
				</div>
				<script type="text/javascript">
					function rtmedia_hide_addon_update_notice() {
						var data = {
							action: 'rtmedia_hide_addon_update_notice',
							_rtm_nonce: jQuery('#rtmedia-addon-notice').val();
					};
						jQuery.post(ajaxurl, data, function (response) {
							response = response.trim();
							if (response === "1")
								jQuery('.rtmedia-addon-upate-notice').remove();
						});
					}
				</script>
				<?php
			}
		}

		/**
		 * Show rtmedia addon update notice.
		 *
		 * @access public
		 * @return bool $return_falg
		 */
		public function check_for_addon_update_notice() {
			$return_falg = false;

			// check for rtMedia Instagram version.
			if ( defined( 'RTMEDIA_INSTAGRAM_PATH' ) ) {
				$plugin_info = get_plugin_data( RTMEDIA_INSTAGRAM_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '2.1.14' ) ) ) {
					$return_falg = true;
				}
			} elseif ( defined( 'RTMEDIA_PHOTO_TAGGING_PATH' ) ) {
				// check for rtMedia Photo Tagging version.
				$plugin_info = get_plugin_data( RTMEDIA_PHOTO_TAGGING_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '2.2.14' ) ) ) {
					$return_falg = true;
				}
			} elseif ( defined( 'RTMEDIA_FFMPEG_PATH' ) ) {
				// check for rtMedia FFPMEG version.
				$plugin_info = get_plugin_data( RTMEDIA_FFMPEG_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '2.1.14' ) ) ) {
					$return_falg = true;
				}
			} elseif ( defined( 'RTMEDIA_KALTURA_PATH' ) ) {
				// check for rtMedia Kaltura version.
				$plugin_info = get_plugin_data( RTMEDIA_KALTURA_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '3.0.16' ) ) ) {
					$return_falg = true;
				}
			} elseif ( defined( 'RTMEDIA_PRO_PATH' ) ) {
				// check for rtMedia Pro version.
				$plugin_info = get_plugin_data( RTMEDIA_PRO_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '2.6' ) ) ) {
					$return_falg = true;
				}
			} elseif ( defined( 'RTMEDIA_SOCIAL_SYNC_PATH' ) ) {
				// check for rtMedia Social Sync version.
				$plugin_info = get_plugin_data( RTMEDIA_SOCIAL_SYNC_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '1.3.1' ) ) ) {
					$return_falg = true;
				}
			} elseif ( defined( 'RTMEDIA_MEMBERSHIP_PATH' ) ) {
				// check for rtMedia Membership version.
				$plugin_info = get_plugin_data( RTMEDIA_MEMBERSHIP_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '2.1.5' ) ) ) {
					$return_falg = true;
				}
			} elseif ( defined( 'RTMEDIA_WATERMARK_PATH' ) ) {
				// check for rtMedia Photo Watermak version.
				$plugin_info = get_plugin_data( RTMEDIA_WATERMARK_PATH . 'index.php' );
				if ( isset( $plugin_info['Version'] ) && ( - 1 === version_compare( $plugin_info['Version'], '1.1.8' ) ) ) {
					$return_falg = true;
				}
			}

			return $return_falg;
		}

		/**
		 * Show buddypress admin tabs.
		 *
		 * @access public
		 */
		public function bp_admin_tabs() {
			if ( current_user_can( 'manage_options' ) ) {
				add_action( 'bp_admin_tabs', array( $this, 'tab' ) );
			}
		}

		/**
		 * Show rtmedia advertisement.
		 *
		 * @access public
		 */
		public function rtmedia_advertisement() {
			$src = RTMEDIA_URL . 'app/assets/admin/img/rtMedia-pro-ad.png'
			?>
			<div class='rtmedia-admin-ad'>
				<a href='https://rtmedia.io/products/rtmedia-pro/' target='_blank' title='rtMedia Pro'>
					<img src='<?php echo esc_url( $src ); ?>' alt="<?php esc_attr_e( 'rtMedia Pro is released', 'buddypress-media' ); ?>"/>
				</a>
			</div>
			<?php
		}

		/**
		 * Create the function to output the contents of our Dashboard Widget
		 *
		 * @access public
		 */
		public function rtmedia_dashboard_widget_function() {
			?>

			<div class="clearfix">

				<div class="rtm-column alignleft">
					<h4 class="sub"><?php esc_html_e( 'Media Stats', 'buddypress-media' ); ?></h4>

					<table>
						<tbody>
						<?php
						$rtmedia_model = new RTMediaModel();
						global $wpdb;
						$results = wp_cache_get( 'rt-stats', 'rt-dashboard' );
						if ( false === $results ) {
							$results = $wpdb->get_results( $wpdb->prepare( "select media_type, count(id) as count from {$rtmedia_model->table_name} where blog_id=%d group by media_type", get_current_blog_id() ) ); // @codingStandardsIgnoreLine
							wp_cache_set( 'stats', $results, 'rt-dashboard', HOUR_IN_SECONDS );
						}
						if ( $results ) {
							foreach ( $results as $media ) {
								if ( defined( strtoupper( 'RTMEDIA_' . $media->media_type . '_PLURAL_LABEL' ) ) ) {
									?>
									<tr>
										<td class="b"> <?php echo esc_html( $media->count ); ?> </td>
										<td class="t"><?php echo esc_html( constant( strtoupper( 'RTMEDIA_' . $media->media_type . '_PLURAL_LABEL' ) ) ); ?></td>
									</tr>
									<?php
								}
							}
						}
						?>
						</tbody>
					</table>
				</div>

				<div class="rtm-column alignright">
					<h4 class="sub"><?php esc_html_e( 'Usage Stats', 'buddypress-media' ); ?></h4>

					<table>
						<tbody> <?php
						$total_count = wp_cache_get( 'total_count', 'rt-dashboard' );
						if ( false === $total_count ) {
							$total_count = $wpdb->get_var( "select count(*) from {$wpdb->users}" );
							wp_cache_set( 'total_count', $total_count, 'rt-dashboard', HOUR_IN_SECONDS );
						}
						?>
						<tr>
							<td class="b"> <?php echo esc_html( $total_count ); ?> </td>
							<td class="t"><?php esc_html_e( 'Total ', 'buddypress-media' ) ?></td>
						</tr>
						<?php
						$with_media_count = wp_cache_get( 'with_media', 'rt-dashboard' );
						if ( false === $with_media_count ) {
							$with_media_count = $wpdb->get_var( "select count(distinct media_author) from {$rtmedia_model->table_name}" ); // @codingStandardsIgnoreLine
							wp_cache_set( 'with_media', $with_media_count, 'rt-dashboard', HOUR_IN_SECONDS );
						}
						?>
						<tr>
							<td class="b"> <?php echo esc_html( $with_media_count ); ?> </td>
							<td class="t"><?php esc_html_e( 'With Media', 'buddypress-media' ) ?></td>
						</tr>
						<?php
						$comments = wp_cache_get( 'comments', 'rt-dashboard' );
						if ( false === $comments ) {
							$comments = $wpdb->get_var( "select count(*) from {$wpdb->comments} where comment_post_ID in ( select media_id from {$rtmedia_model->table_name} )" ); // @codingStandardsIgnoreLine
							wp_cache_set( 'comments', $comments, 'rt-dashboard', HOUR_IN_SECONDS );
						}
						?>
						<tr>
							<td class="b"> <?php echo esc_html( $comments ); ?> </td>
							<td class="t"><?php esc_html_e( 'Comments ', 'buddypress-media' ) ?></td>
						</tr>
						<?php
						$likes = wp_cache_get( 'likes', 'rt-dashboard' );
						if ( false === $likes ) {
							$likes = $wpdb->get_var( "select sum(likes) from {$rtmedia_model->table_name}" ); // @codingStandardsIgnoreLine
							wp_cache_set( 'likes', $likes, 'rt-dashboard', HOUR_IN_SECONDS );
						}
						?>
						<tr>
							<td class="b"> <?php echo esc_html( $likes ); ?> </td>
							<td class="t"><?php esc_html_e( 'Likes', 'buddypress-media' ) ?></td>
						</tr>
						</tbody>
					</table>
				</div>

			</div>

			<div class="rtm-meta-container">
				<ul class="rtm-meta-links">
					<li><b><?php esc_html_e( 'rtMedia Links:', 'buddypress-media' ); ?></b></li>
					<li><a href="https://rtmedia.io/"><?php esc_html_e( 'Homepage', 'buddypress-media' ); ?></a></li>
					<li>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=rtmedia-support#rtmedia-general' ) ); ?>"><?php esc_html_e( 'Free Support', 'buddypress-media' ); ?></a>
					</li>
					<li>
						<a href="https://rtmedia.io/products/category/plugins/"><?php esc_html_e( 'Premium Addons', 'buddypress-media' ); ?></a>
					</li>
				</ul>
			</div>
			<?php
		}

		/**
		 * Create the function use in the action hook
		 *
		 * @access public
		 */
		public function add_dashboard_widgets() {
			wp_add_dashboard_widget( 'rtmedia_dashboard_widget', esc_html__( 'Right Now in rtMedia', 'buddypress-media' ), array(
				&$this,
				'rtmedia_dashboard_widget_function',
			) );
			global $wp_meta_boxes;

			// Get the regular dashboard widgets array.
			// (which has our new widget already but at the end).
			$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

			// Backup and delete our new dashboard widget from the end of the array.
			$example_widget_backup = array( 'rtmedia_dashboard_widget' => $normal_dashboard['rtmedia_dashboard_widget'] );
			unset( $normal_dashboard['rtmedia_dashboard_widget'] );

			// Merge the two arrays together so our widget is at the beginning.
			$sorted_dashboard = array_merge( $example_widget_backup, $normal_dashboard );

			// Save the sorted array back into the original metaboxes.
			$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
		}

		/**
		 * Add the plugin settings links
		 *
		 * @access public
		 *
		 * @param  array $links Array of Deactivate & Edit rtMedia plugin links.
		 *
		 * @return array $links Added new links for rtMedia setting & support.
		 */
		public function plugin_add_settings_link( $links ) {
			$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=rtmedia-settings' ) ) . '">' . esc_html__( 'Settings', 'buddypress-media' ) . '</a>';
			array_push( $links, $settings_link );
			$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=rtmedia-support' ) ) . '">' . esc_html__( 'Support', 'buddypress-media' ) . '</a>';
			array_push( $links, $settings_link );

			return $links;
		}

		/**
		 * Add the reencode link
		 *
		 * @access public
		 *
		 * @param  array $actions  
		 * @param  object $post
		 *
		 * @return array $actions
		 */
		public function add_reencode_link( $actions, $post ) {

			$mime_type_array = explode( '/', $post->post_mime_type );
			if ( is_array( $mime_type_array ) && '' !== $mime_type_array && 'video' === $mime_type_array[0] ) {
				$actions['reencode'] = '<a class="submitdelete" onclick="return rtmedia_regenerate_thumbs(' . esc_attr( $post->ID ) . ')" href="#">' . esc_html_e( 'Regenerate Thumbnail', 'buddypress-media' ) . '</a>';
			}

			return $actions;
		}

		/**
		 * Do the bulk video/media handler.
		 *
		 * @access public
		 */
		public function bulk_action_handler() {
			$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
			$request_media = filter_input( INPUT_GET, 'media', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

			if ( 'bulk_video_regenerate_thumbnails' === $action && '' !== $request_media ) {
				wp_safe_redirect( esc_url_raw( add_query_arg( array( 'media_ids' => urlencode( implode( ',', array_map( 'intval', $request_media ) ) ) ), admin_url( 'admin.php?page=rtmedia-regenerate' ) ) ) );
				exit;
			}
		}

		/**
		 * Add admin bar menu
		 *
		 * @access public
		 *
		 * @param  object $admin_bar
		 *
		 * @return void
		 */
		public function admin_bar_menu( $admin_bar ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$admin_bar->add_menu( array(
					'id'    => 'rtMedia',
					'title' => 'rtMedia',
					'href'  => admin_url( 'admin.php?page=rtmedia-settings' ),
					'meta'  => array(
						'title' => esc_html__( 'rtMedia', 'buddypress-media' ),
					),
				)
			);
			$admin_bar->add_menu( array(
					'id'     => 'rt-media-dashborad',
					'parent' => 'rtMedia',
					'title'  => esc_html__( 'Settings', 'buddypress-media' ),
					'href'   => admin_url( 'admin.php?page=rtmedia-settings' ),
					'meta'   => array(
						'title'  => esc_html__( 'Settings', 'buddypress-media' ),
						'target' => '_self',
					),
				)
			);
			if ( ! is_rtmedia_vip_plugin() ) {
				$admin_bar->add_menu( array(
						'id'     => 'rt-media-addons',
						'parent' => 'rtMedia',
						'title'  => esc_html__( 'Addons', 'buddypress-media' ),
						'href'   => admin_url( 'admin.php?page=rtmedia-addons' ),
						'meta'   => array(
							'title'  => esc_html__( 'Addons', 'buddypress-media' ),
							'target' => '_self',
						),
					)
				);
			}
			$admin_bar->add_menu( array(
					'id'     => 'rt-media-support',
					'parent' => 'rtMedia',
					'title'  => esc_html__( 'Support', 'buddypress-media' ),
					'href'   => admin_url( 'admin.php?page=rtmedia-support' ),
					'meta'   => array(
						'title'  => esc_html__( 'Support', 'buddypress-media' ),
						'target' => '_self',
					),
				)
			);
			if ( ! is_rtmedia_vip_plugin() ) {
				$admin_bar->add_menu( array(
						'id'     => 'rt-media-themes',
						'parent' => 'rtMedia',
						'title'  => esc_html__( 'Themes', 'buddypress-media' ),
						'href'   => admin_url( 'admin.php?page=rtmedia-themes' ),
						'meta'   => array(
							'title'  => esc_html__( 'Themes', 'buddypress-media' ),
							'target' => '_self',
						),
					)
				);
			}
			if ( ! is_rtmedia_vip_plugin() ) {
				$admin_bar->add_menu( array(
						'id'     => 'rt-media-hire-us',
						'parent' => 'rtMedia',
						'title'  => esc_html__( 'Hire Us', 'buddypress-media' ),
						'href'   => admin_url( 'admin.php?page=rtmedia-hire-us' ),
						'meta'   => array(
							'title'  => esc_html__( 'Hire Us', 'buddypress-media' ),
							'target' => '_self',
						),
					)
				);
			}
			if ( has_filter( 'rtmedia_license_tabs' ) || has_action( 'rtmedia_addon_license_details' ) ) {
				$admin_bar->add_menu( array(
						'id'     => 'rt-media-license',
						'parent' => 'rtMedia',
						'title'  => esc_html__( 'Licenses', 'buddypress-media' ),
						'href'   => admin_url( 'admin.php?page=rtmedia-license' ),
						'meta'   => array(
							'title'  => esc_html__( 'Licenses', 'buddypress-media' ),
							'target' => '_self',
						),
					)
				);
			}
		}

		/**
		 * Generates the Admin UI.
		 *
		 * @access public
		 *
		 * @param  string $hook
		 */
		public function ui( $hook ) {
			$admin_pages = array(
				'rtmedia_page_rtmedia-migration',
				'rtmedia_page_rtmedia-kaltura-settings',
				'rtmedia_page_rtmedia-ffmpeg-settings',
				'toplevel_page_rtmedia-settings',
				'rtmedia_page_rtmedia-addons',
				'rtmedia_page_rtmedia-support',
				'rtmedia_page_rtmedia-themes',
				'rtmedia_page_rtmedia-hire-us',
				'rtmedia_page_rtmedia-importer',
				'rtmedia_page_rtmedia-regenerate',
			);

			if ( has_filter( 'rtmedia_license_tabs' ) || has_action( 'rtmedia_addon_license_details' ) ) {
				$admin_pages[] = 'rtmedia_page_rtmedia-license';
			}

			$admin_pages = apply_filters( 'rtmedia_filter_admin_pages_array', $admin_pages );
			$suffix      = ( function_exists( 'rtm_get_script_style_suffix' ) ) ? rtm_get_script_style_suffix() : '.min';

			if ( in_array( $hook, $admin_pages, true ) || strpos( $hook, 'rtmedia-migration' ) ) {

				$admin_ajax = admin_url( 'admin-ajax.php' );

				/* Only one JS file should enqueue */
				if ( '' === $suffix ) {
					wp_enqueue_script( 'rtmedia-admin-tabs', RTMEDIA_URL . 'app/assets/admin/js/vendors/tabs.js', array( 'backbone' ), RTMEDIA_VERSION );
					wp_enqueue_script( 'rtmedia-admin-scripts', RTMEDIA_URL . 'app/assets/admin/js/scripts.js', array( 'backbone' ), RTMEDIA_VERSION );
					wp_enqueue_script( 'rtmedia-admin', RTMEDIA_URL . 'app/assets/admin/js/settings.js', array( 'backbone', 'wp-util' ), RTMEDIA_VERSION );
				} else {
					wp_enqueue_script( 'rtmedia-admin', RTMEDIA_URL . 'app/assets/admin/js/admin.min.js', array( 'backbone', 'wp-util' ), RTMEDIA_VERSION );
				}

				wp_localize_script( 'rtmedia-admin', 'rtmedia_on_label', esc_html__( 'ON', 'buddypress-media' ) );
				wp_localize_script( 'rtmedia-admin', 'rtmedia_off_label', esc_html__( 'OFF', 'buddypress-media' ) );
				wp_localize_script( 'rtmedia-admin', 'rtmedia_admin_ajax', $admin_ajax );
				wp_localize_script( 'rtmedia-admin', 'rtmedia_admin_url', admin_url() );
				wp_localize_script( 'rtmedia-admin', 'rtmedia_admin_url', admin_url() );

				$rtmedia_admin_strings = array(
					'no_refresh'           	  		=> esc_html__( 'Please do not refresh this page.', 'buddypress-media' ),
					'something_went_wrong' 	  		=> esc_html__( 'Something went wrong. Please ', 'buddypress-media' ) .  '<a href onclick="location.reload();">' . esc_html__( 'refresh', 'buddypress-media' ) . '</a>' . esc_html__( ' page.', 'buddypress-media' ),
					'are_you_sure'         	  		=> esc_html__( 'This will subscribe you to the free plan.', 'buddypress-media' ),
					'disable_encoding'     	  		=> esc_html__( 'Are you sure you want to disable the encoding service?', 'buddypress-media' ),
					'enable_encoding'      	  		=> esc_html__( 'Are you sure you want to enable the encoding service?', 'buddypress-media' ),
					'settings_changed'	   	  		=> esc_html__( 'Settings have changed, you should save them!', 'buddypress-media' ),
					'video_thumbnail_error' 		=> esc_html__( 'Number of video thumbnails to be generated should be greater than 0 in media sizes settings. Setting it to default value 2.', 'buddypress-media' ),
					'video_thumbnail_invalid_value' => esc_html__( 'Invalid value for number of video thumbnails in media sizes settings. Setting it to round value', 'buddypress-media' ),
					'jpeg_quality_negative_error'	=> esc_html__( 'Number of percentage in JPEG image quality should be greater than 0 in media sizes settings. Setting it to default value 90.', 'buddypress-media' ),
					'jpeg_quality_percentage_error'	=> esc_html__( 'Number of percentage in JPEG image quality should be less than 100 in media sizes settings. Setting it to 100.', 'buddypress-media' ),
					'jpeg_quality_invalid_value'	=> esc_html__( 'Invalid value for percentage in JPEG image quality in media sizes settings. Setting it to round value', 'buddypress-media' ),
					'per_page_media_negative_value'	=> esc_html__( 'Please enter positive integer value only. Setting number of media per page value to default value 10.', 'buddypress-media' ),
					'per_page_media_positive_error'	=> esc_html__( 'Please enter positive integer value only. Setting number of media per page value to round value', 'buddypress-media' ),
					'request_failed'				=> esc_html__( 'Request failed.', 'buddypress-media' ),
				);

				wp_localize_script( 'rtmedia-admin', 'rtmedia_admin_strings', $rtmedia_admin_strings );
				wp_localize_script( 'rtmedia-admin', 'settings_url', esc_url( add_query_arg( array( 'page' => 'rtmedia-settings' ), ( is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) ) ) ) . '#privacy_enabled' );
				wp_localize_script( 'rtmedia-admin', 'settings_rt_album_import_url', esc_url( add_query_arg( array( 'page' => 'rtmedia-settings' ), ( is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) ) ) ) );

				$rtmedia_admin_support_strings = array(
					'wp_admin_username_error' => esc_html__( 'Please enter WP Admin Login.', 'buddypress-media' ),
					'wp_admin_pwd_error' 	  => esc_html__( 'Please enter WP Admin password.', 'buddypress-media' ),
					'ssh_ftp_host_error' 	  => esc_html__( 'Please enter SSH / FTP host.', 'buddypress-media' ),
					'ssh_ftp_username_error'  => esc_html__( 'Please enter SSH / FTP login.', 'buddypress-media' ),
					'ssh_ftp_pwd_error'  	  => esc_html__( 'Please enter SSH / FTP password.', 'buddypress-media' ),
					'all_fields_error'  	  => esc_html__( 'Please fill all the fields.', 'buddypress-media' ),
				);

				wp_localize_script( 'rtmedia-admin', 'rtmedia_admin_support_strings', $rtmedia_admin_support_strings );

				/* Only one CSS file should enqueue */
				wp_enqueue_style( 'rtmedia-admin', RTMEDIA_URL . 'app/assets/admin/css/admin' . $suffix . '.css', '', RTMEDIA_VERSION );
			} else {

				/* This CSS is using for "Right Now in rtMedia" Widget section on Dashboard */
				wp_enqueue_style( 'rtmedia-widget', RTMEDIA_URL . 'app/assets/admin/css/widget' . $suffix . '.css', '', RTMEDIA_VERSION );
			}
		}

		/**
		 * Add Admin Menu.
		 *
		 * @access public
		 * @global string 'buddypress-media'
		 */
		public function menu() {
			add_menu_page( 'rtMedia', 'rtMedia', 'manage_options', 'rtmedia-settings', array(
				$this,
				'settings_page',
			), RTMEDIA_URL . 'app/assets/admin/img/rtmedia-logo.png', '40.1111' );
			add_submenu_page( 'rtmedia-settings', esc_html__( 'Settings', 'buddypress-media' ), esc_html__( 'Settings', 'buddypress-media' ), 'manage_options', 'rtmedia-settings', array(
				$this,
				'settings_page',
			) );

			add_submenu_page( 'rtmedia-settings', esc_html__( 'Addons', 'buddypress-media' ), esc_html__( 'Addons', 'buddypress-media' ), 'manage_options', 'rtmedia-addons', array(
				$this,
				'addons_page',
			) );

			add_submenu_page( 'rtmedia-settings', esc_html__( 'Support', 'buddypress-media' ), esc_html__( 'Support', 'buddypress-media' ), 'manage_options', 'rtmedia-support', array(
				$this,
				'support_page',
			) );

			if ( ! is_rtmedia_vip_plugin() ) {
				add_submenu_page( 'rtmedia-settings', esc_html__( 'Themes', 'buddypress-media' ), esc_html__( 'Themes', 'buddypress-media' ), 'manage_options', 'rtmedia-themes', array(
					$this,
					'theme_page',
				) );
			}

			if ( ! is_rtmedia_vip_plugin() ) {
				add_submenu_page( 'rtmedia-settings', esc_html__( 'Hire Us', 'buddypress-media' ), esc_html__( 'Hire Us', 'buddypress-media' ), 'manage_options', 'rtmedia-hire-us', array(
					$this,
					'hire_us_page',
				) );
			}

			if ( has_filter( 'rtmedia_license_tabs' ) || has_action( 'rtmedia_addon_license_details' ) ) {
				add_submenu_page( 'rtmedia-settings', esc_html__( 'Licenses', 'buddypress-media' ), esc_html__( 'Licenses', 'buddypress-media' ), 'manage_options', 'rtmedia-license', array(
					$this,
					'license_page',
				) );
			}

			$obj_encoding = new RTMediaEncoding( true );

			if ( $obj_encoding->api_key ) {
				add_submenu_page( 'rtmedia-settings', esc_html__( 'Regenerate Thumbnail', 'buddypress-media' ), esc_html__( 'Regen. Thumbnail ', 'buddypress-media' ), 'manage_options', 'rtmedia-regenerate', array(
					$this,
					'rt_regenerate_thumbnail',
				) );
			}
		}

		/**
		 * Define regenerate thumbnail functionality.
		 *
		 * @access public
		 */
		public function rt_regenerate_thumbnail() {
			$prog = new rtProgress();
			$done = $total = 0;
			?>
			<div class="wrap">
				<h2> <?php esc_html_e( 'rtMedia: Regenerate Video Thumbnails', 'buddypress-media' ); ?> </h2>
				<?php
				$request_mediaids = filter_input( INPUT_GET, 'media', FILTER_DEFAULT, FILTER_SANITIZE_STRING );
				if ( isset( $request_mediaids ) && trim( $request_mediaids ) !== '' ) {
					$requested = false;
					$media_ids = explode( ',', $request_mediaids );
					$total     = count( $media_ids );
				} else {
					$media_ids = get_video_without_thumbs();
					if ( is_array( $media_ids ) ) {
						$total     = count( $media_ids );
					}
				}
				?>
				<script>
					var rt_thumb_all_media = <?php echo wp_json_encode( $media_ids ); ?>;
				</script>
				<?php
				if ( ! isset( $requested ) ) {
					?>
					<br/>
					<p>
						<?php esc_html_e( 'You can see this page because you have ', 'buddypress-media' ) ?>
						<a href='<?php echo esc_url( admin_url( 'admin.php?page=rtmedia-addons' ) ); ?>'><?php esc_html_e( 'subscribed', 'buddypress-media' ) ?></a>
						<?php esc_html_e( ' for ', 'buddypress-media' ) ?>
						<a href='<?php echo esc_url( 'https://rtmedia.io/audio-video-encoding-service/' ); ?>' target='_blank'><?php esc_html_e( 'rtMedia audio/video encoding service', 'buddypress-media' ) ?></a>.
					</p>
					<p>
						<?php esc_html_e( 'You can regenerate thumbnails of a specific video by visiting ', 'buddypress-media' ) ?>
						<a href='<?php echo esc_url( admin_url( 'upload.php?post_mime_type=video' ) ); ?>'><?php esc_html_e( 'media page', 'buddypress-media' ); ?></a>
						<?php esc_html_e( ' and clicking the ', 'buddypress-media' ) ?>
						<b><?php esc_html_e( 'Regenerate Thumbnail', 'buddypress-media' ) ?></b>
						<?php esc_html_e( ' option for that particular video.', 'buddypress-media' ) ?>
					</p>
					<p>
						<?php esc_html_e( 'Click ', 'buddypress-media' ) ?>
						<b><?php esc_html_e( 'Regenerate Pending Thumbnails', 'buddypress-media' ) ?></b>
						<?php esc_html_e( ' to regenerate thumbnails of pending videos.', 'buddypress-media' ) ?>
					</p>
					<p>
						<input type="button" class="button button-primary" id="rt-start-media-regenerate"
						       value="<?php echo esc_attr__( 'Regenerate Pending Thumbnails', 'buddypress-media' ) . ' (' . esc_attr( $total ) . ')'; ?>"/>
						<?php wp_nonce_field( '_rt-start-media-regenerate_', 'rt-regenerate-nonce' ); ?>
					</p>
					<?php
				}
				?>
				<div id="rt-migration-progress">
					<br/> <br/>
					<?php
					$temp = $prog->progress( $done, $total );
					$prog->progress_ui( $temp, true );
					?>
					<p> <?php esc_html_e( 'Total Videos', 'buddypress-media' ) ?> : <span
							class='rt-total'><?php echo esc_html( $total ); ?></span>
					</p>

					<p> <?php esc_html_e( 'Sent of regenerate thumbails', 'buddypress-media' ) ?> : <span
							class='rt-done'>0</span></p>

					<p> <?php esc_html_e( 'Fail to regenerate thumbails', 'buddypress-media' ) ?> : <span
							class='rt-fail'>0</span></p>

				</div>
				<script>

					var db_done = 0;
					var db_fail = 0;
					var db_total = <?php echo esc_js( $total ); ?>;
					var indx = 0;
					function db_start_regenrate() {
						if (indx < db_total) {
							jQuery.ajax({
								url: rtmedia_admin_ajax,
								type: 'post',
								data: {
									"action": "rt_media_regeneration",
									"media_id": rt_thumb_all_media[indx++],
									"_rtm_nonce": jQuery('#rt-regenerate-nonce').val()
								},
								success: function (data) {
									data = JSON.parse(data);

									if (data.status == false) {
										handle_regenrate_fail();
									} else {
										db_done++;
										var progw = Math.ceil(( db_done / db_total ) * 100);
										if (progw > 100) {
											progw = 100;
										}
										jQuery('#rtprogressbar>div').css('width', progw + '%');
										jQuery('span.rt-done').html(db_done);
										db_start_regenrate();
									}
								},
								error: function () {
									handle_regenrate_fail();
								}
							});
						} else {
							alert("<?php esc_html_e( 'Regenerate Video Thumbnails Done', 'buddypress-media' ); ?>");
						}
					}
					function handle_regenrate_fail() {
						db_fail++;
						jQuery('span.rt-fail').html(db_fail);
						db_start_regenrate();
					}
					$media_regenerate = jQuery("#rt-start-media-regenerate");
					if ($media_regenerate.length > 0) {
						jQuery("#rt-migration-progress").hide();
						$media_regenerate.click(function () {
							jQuery(this).hide();
							jQuery("#rt-migration-progress").show();
							db_start_regenrate();
						})
					} else {
						db_start_regenrate();
					}

				</script>


			</div> <?php
		}

		/**
		 * Generate rtmedia thumbnail notice.
		 *
		 * @access public
		 */
		public function rtmedia_regenerate_thumbnail_notice() {
			$obj_encoding = new RTMediaEncoding( true );
			if ( $obj_encoding->api_key ) {
				$site_option = rtmedia_get_site_option( 'rtmedia-video-thumb-notice' );
				if ( ! $site_option || 'hide' !== $site_option ) {
					rtmedia_update_site_option( 'rtmedia-video-thumb-notice', 'show' );
					$videos_without_thumbs = get_video_without_thumbs();
					if ( isset( $videos_without_thumbs ) && is_array( $videos_without_thumbs ) && count( $videos_without_thumbs ) > 0 ) {
						?>
						<div class="error rtmedia-regenerate-video-thumb-error">
							<p>
								<?php printf( esc_html__( 'You have %s videos without thumbnails. Click ', 'buddypress-media' ), esc_html( count( $videos_without_thumbs ) ) );?>
								<a href='<?php echo esc_url( admin_url( 'admin.php?page=rtmedia-regenerate' ) ); ?>'><?php esc_html_e( 'here', 'buddypress-media' ); ?></a>
								<?php esc_html_e( ' to generate thumbnails.', 'buddypress-media' ) ?>
								<a href='#' onclick='rtmedia_hide_video_thumb_notice()' style='float:right'>Hide</a>.
								<?php wp_nonce_field( '_rtmedia_hide_video_thumb_notice_', 'rtm_hide_video_thumb_notice' ); ?>
							</p>
						</div>
						<script type="text/javascript">
							function rtmedia_hide_video_thumb_notice() {
								var data = {action: 'rtmedia_hide_video_thumb_admin_notice', _rtm_nonce:jQuery('#rtm_hide_video_thumb_notice').val()};
								jQuery.post(ajaxurl, data, function (response) {
									response = response.trim();
									if (response === "1")
										jQuery('.rtmedia-regenerate-video-thumb-error').remove();
								});
							}
						</script>
						<?php
					}
				}
			}
		}

		/**
		 * Hide rtmedia video thumb admin notice.
		 *
		 * @access public
		 */
		public function rtmedia_hide_video_thumb_admin_notice() {
			if ( check_ajax_referer( '_rtmedia_hide_video_thumb_notice_', '_rtm_nonce' ) && rtmedia_update_site_option( 'rtmedia-video-thumb-notice', 'hide' ) ) {
				echo '1';
			} else {
				echo '0';
			}
			die();
		}

		/**
		 * Hide rtmedia addon update notice.
		 *
		 * @access public
		 */
		public function rtmedia_hide_addon_update_notice() {
			if ( check_ajax_referer( 'rtmedia-addon-update-notice-3_8', '_rtm_nonce' ) && rtmedia_update_site_option( 'rtmedia-addon-update-notice-3_8', 'hide' ) ) {
				echo '1';
			} else {
				echo '0';
			}
			die();
		}

		/**
		 * Define rt_media_regeneration.
		 *
		 * @access public
		 */
		public function rt_media_regeneration() {
			if ( ! check_ajax_referer( '_rt-start-media-regenerate_', '_rtm_nonce' ) ) {
				wp_send_json( false );
			}
			$media_id = sanitize_text_field( intval( $_POST['media_id'] ) );
			if ( isset( $media_id ) ) {
				$model      = new RTMediaModel();
				$media      = $model->get_media( array( 'media_id' => $media_id ), 0, 1 );
				$media_type = $media[0]->media_type;
				$response   = array();
				if ( 'video' === $media_type ) {
					$obj_rtmedia_encoding = new RTMediaEncoding( true );
					$autoformat         = 'thumbnails';
					$obj_rtmedia_encoding->reencoding( $media_id, $autoformat );
					$response['status'] = true;
				} else {
					$response['status']  = false;
					$response['message'] = esc_html__( 'not a video ...', 'buddypress-media' );
				}
				echo wp_json_encode( $response );
				die();
			}
		}

		/**
		 * Render the BuddyPress Media Settings page.
		 *
		 * @access public
		 */
		public function settings_page() {
			$this->render_page( 'rtmedia-settings', 'buddypress-media' );
		}

		/**
		 * Render the BuddyPress Privacy Settings page.
		 *
		 * @access public
		 */
		public function privacy_page() {
			$this->render_page( 'rtmedia-privacy' );
		}

		/**
		 * Render the rtmedia Importer Page.
		 *
		 * @access public
		 */
		public function rt_importer_page() {
			$this->render_page( 'rtmedia-importer' );
		}

		/**
		 * Render the rtmedia convert videos page.
		 *
		 * @access public
		 */
		public function convert_videos_page() {
			$this->render_page( 'rtmedia-convert-videos' );
		}

		/**
		 * Render the BuddyPress Media Addons page.
		 *
		 * @access public
		 */
		public function addons_page() {
			$this->render_page( 'rtmedia-addons' );
		}

		/**
		 * Render the BuddyPress Media Support page.
		 *
		 * @access public
		 */
		public function support_page() {
			$this->render_page( 'rtmedia-support' );
		}

		/**
		 * Render the rtmedia theme page.
		 *
		 * @access public
		 */
		public function theme_page() {
			$this->render_page( 'rtmedia-themes' );
		}

		/**
		 * Render the rtmedia hire us page.
		 *
		 * @access public
		 */
		public function hire_us_page() {
			$this->render_page( 'rtmedia-hire-us' );
		}
                /**
                 * Render the rtMedia license page.
                 */
		public function license_page() {
			$this->render_page( 'rtmedia-license' );
		}

		/**
		 * Render the rtmedia hire us page.
		 *
		 * @access static
		 */
		static function get_current_tab() {
			$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
			return isset( $page ) ? $page : 'rtmedia-settings';
		}

		/**
		 * Render BPMedia Settings.
		 *
		 * @access public
		 * @global      string 'buddypress-media'
		 *
		 * @param  string $page
		 * @param  array $option_group
		 *
		 * @return void
		 */
		public function render_page( $page, $option_group = null ) {
			?>

			<div class="wrap bp-media-admin <?php echo esc_attr( $this->get_current_tab() ); ?>">
				<div id="icon-buddypress-media" class="icon32"><br></div>
				<div>
					<h2 class="nav-tab-wrapper"><?php $this->rtmedia_tabs(); ?>
						<span class="alignright by">
							<a class="rt-link"
							   href="https://rtmedia.io/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media"
							   target="_blank"
							   title="rtCamp : <?php esc_attr_e( 'Empowering The Web With WordPress', 'buddypress-media' ); ?>">
								<img src="<?php echo esc_url( RTMEDIA_URL ); ?>app/assets/admin/img/rtcamp-logo.png" alt="rtCamp"/>
							</a>
						</span>
					</h2>
				</div>

				<div class="clearfix rtm-row-container">

					<div id="bp-media-settings-boxes" class="bp-media-settings-boxes-container rtm-setting-container">

						<?php
						if ( 'rtmedia-settings' === $page ) {
							?>
							<form id="bp_media_settings_form" name="bp_media_settings_form" method="post"
							      enctype="multipart/form-data">
							<div class="bp-media-metabox-holder">
								<div class="rtm-button-container top">
									<?php $is_setting_save = filter_input( INPUT_GET, 'settings-saved', FILTER_VALIDATE_BOOLEAN );
									if ( ! empty( $is_setting_save ) ) { ?>
										<div class="rtm-success rtm-fly-warning rtm-save-settings-msg">
											<?php esc_html_e( 'Settings saved successfully!', 'buddypress-media' ); ?>
										</div>
									<?php } ?>
									<input type="hidden" name="rtmedia-options-save" value="true">
									<input type="submit"
									       class="rtmedia-settings-submit button button-primary button-big"
									       value="<?php esc_attr_e( 'Save Settings', 'buddypress-media' ); ?>">
								</div>
								<?php
								settings_fields( $option_group );
								if ( 'rtmedia-settings' === $page ) {
									echo '<div id="rtm-settings-tabs">';
									$sub_tabs = $this->settings_sub_tabs();
									RTMediaFormHandler::rtForm_settings_tabs_content( $page, $sub_tabs );
									echo '</div>';
								} else {
									do_settings_sections( $page );
								}
								?>

								<div class="rtm-button-container bottom">
									<div class="rtm-social-links alignleft">
										<a href="http://twitter.com/rtcamp" class="twitter" target="_blank"><span
												class="dashicons dashicons-twitter"></span></a>
										<a href="https://www.facebook.com/rtCamp.solutions" class="facebook"
										   target="_blank"><span class="dashicons dashicons-facebook"></span></a>
										<a href="http://profiles.wordpress.org/rtcamp" class="wordpress"
										   target="_blank"><span class="dashicons dashicons-wordpress"></span></a>
										<a href="https://rtmedia.io/feed/" class="rss" target="_blank"><span
												class="dashicons dashicons-rss"></span></a>
									</div>

									<input type="hidden" name="rtmedia-options-save" value="true">
									<input type="submit"
									       class="rtmedia-settings-submit button button-primary button-big"
									       value="<?php esc_attr_e( 'Save Settings', 'buddypress-media' ); ?>">
								</div>
							</div>
							</form><?php
						} else {
							?>
							<div class="bp-media-metabox-holder">
								<?php
								if ( 'rtmedia-addons' === $page ) {
									RTMediaAddon::render_addons( $page );
								} else if ( 'rtmedia-support' === $page ) {
									$rtmedia_support = new RTMediaSupport( false );
									$rtmedia_support->render_support( $page );
								} else if ( 'rtmedia-themes' === $page ) {
									RTMediaThemes::render_themes( $page );
								} else {
									if ( 'rtmedia-license' === $page ) {
										RTMediaLicense::render_license( $page );
									} else {
										do_settings_sections( $page );
									}
								}
								do_action( 'rtmedia_admin_page_insert', $page );
								?>
							</div>
							<?php
							do_action( 'rtmedia_admin_page_append', $page );
						}
						?>
					</div>

					<div class="metabox-holder bp-media-metabox-holder rtm-sidebar">
						<?php $this->admin_sidebar(); ?>
					</div>

				</div>

			</div><!-- .bp-media-admin --><?php
		}

		/**
		 * Adds a tab for Media settings in the BuddyPress settings page
		 *
		 * @access public
		 *
		 * @return string $tabs_html
		 */
		public function tab() {

			$tabs_html    = '';
			$idle_class   = 'nav-tab';
			$active_class = 'nav-tab nav-tab-active';
			$tabs         = array();

			// Check to see which tab we are on.
			$tab = $this->get_current_tab();
			/* rtMedia */
			$tabs[] = array(
				'href'  => get_admin_url( null, esc_url( add_query_arg( array( 'page' => 'rtmedia-settings' ), 'admin.php' ) ) ),
				'title' => esc_html__( 'rtMedia', 'buddypress-media' ),
				'name'  => esc_html__( 'rtMedia', 'buddypress-media' ),
				'class' => ( 'rtmedia-settings' === $tab || 'rtmedia-addons' === $tab || 'rtmedia-support' === $tab || 'rtmedia-importer' === $tab ) ? $active_class : $idle_class,
			);

			foreach ( $tabs as $tab ) {
				echo '<a id="bp-media" title= "' . esc_attr( $tab['title'] ) . '"  href="' . esc_url( $tab['href'] ) . '" class="' . esc_attr( $tab['class'] ) . '">' . esc_html( $tab['name'] ) . '</a>';
			}
		}

		/**
		 * Create core admin tabs.
		 *
		 * @access public
		 */
		public function rtmedia_tabs() {
			// Declare local variables.
			$idle_class   = 'nav-tab';
			$active_class = 'nav-tab nav-tab-active';

			// Setup core admin tabs.
			$tabs = array(
				array(
					'href' => get_admin_url( null, esc_url( add_query_arg( array( 'page' => 'rtmedia-settings' ), 'admin.php' ) ) ),
					'name' => esc_html__( 'Settings', 'buddypress-media' ),
					'slug' => 'rtmedia-settings',
				),
				array(
					'href' => get_admin_url( null, esc_url( add_query_arg( array( 'page' => 'rtmedia-addons' ), 'admin.php' ) ) ),
					'name' => esc_html__( 'Addons', 'buddypress-media' ),
					'slug' => 'rtmedia-addons',
				),
			);

			if ( ! is_rtmedia_vip_plugin() ) {
				$tabs[] = array(
					'href' => get_admin_url( null, esc_url( add_query_arg( array( 'page' => 'rtmedia-themes' ), 'admin.php' ) ) ),
					'name' => esc_html__( 'Themes', 'buddypress-media' ),
					'slug' => 'rtmedia-themes',
				);

				$tabs[] = array(
					'href' => get_admin_url( null, esc_url( add_query_arg( array( 'page' => 'rtmedia-hire-us' ), 'admin.php' ) ) ),
					'name' => esc_html__( 'Hire Us', 'buddypress-media' ),
					'slug' => 'rtmedia-hire-us',
				);
			}

			$tabs[] = array(
				'href' => get_admin_url( null, esc_url( add_query_arg( array( 'page' => 'rtmedia-support' ), 'admin.php' ) ) ),
				'name' => esc_html__( 'Support', 'buddypress-media' ),
				'slug' => 'rtmedia-support',
			);

			if ( has_filter( 'rtmedia_license_tabs' ) || has_action( 'rtmedia_addon_license_details' ) ) {
				$tabs[] = array(
					'href' => get_admin_url( null, esc_url( add_query_arg( array( 'page' => 'rtmedia-license' ), 'admin.php' ) ) ),
					'name' => esc_html__( 'Licenses', 'buddypress-media' ),
					'slug' => 'rtmedia-license',
				);
			}

			$tabs = apply_filters( 'media_add_tabs', $tabs );

			// Loop through tabs and build navigation.
			foreach ( array_values( $tabs ) as $tab_data ) {
				$is_current = (bool) ( $tab_data['slug'] === $this->get_current_tab() );
				$tab_class  = $is_current ? $active_class : $idle_class;

				if ( isset( $tab_data['class'] ) && is_array( $tab_data['class'] ) ) {
					$tab_class .= ' ' . implode( ' ', $tab_data['class'] );
				}

				echo '<a href="' . esc_url( $tab_data['href'] ) . '" class="' . esc_attr( $tab_class ) . '">' . esc_html( $tab_data['name'] ) . '</a>';
			}
		}

		/**
		 * Create settings content tabs.
		 *
		 * @access public
		 *
		 * @param  string $page
		 *
		 * @return void
		 */
		public function settings_content_tabs( $page ) {
			global $wp_settings_sections, $wp_settings_fields;

			if ( ! isset( $wp_settings_sections ) || ! isset( $wp_settings_sections[ $page ] ) ) {
				return;
			}

			foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
				if ( $section['title'] ) {
					?><h3><?php esc_html( $section['title'] ); ?></h3><?php
				}

				if ( $section['callback'] ) {
					call_user_func( $section['callback'], $section );
				}

				if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
					continue;
				}

				echo '<table class="form-table">';
				do_settings_fields( $page, $section['id'] );
				echo '</table>';
			}
		}

		/**
		 * Adds a sub tabs to the BuddyPress Media settings page
		 *
		 * @access public
		 *
		 * @return array $tabs
		 */
		public function settings_sub_tabs() {
			$tabs      = array();

			// Check to see which tab we are on.
			$tab = $this->get_current_tab();
			/* rtMedia */

			$tabs[7] = array(
				'href'     => '#rtmedia-display',
				'icon'     => 'dashicons-desktop',
				'title'    => esc_html__( 'Display', 'buddypress-media' ),
				'name'     => esc_html__( 'Display', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'display_content' ),
			);

			if ( class_exists( 'BuddyPress' ) ) {
				$tabs[20] = array(
					'href'     => '#rtmedia-bp',
					'icon'     => 'dashicons-groups',
					'title'    => esc_html__( 'rtMedia BuddyPress', 'buddypress-media' ),
					'name'     => esc_html__( 'BuddyPress', 'buddypress-media' ),
					'callback' => array( 'RTMediaFormHandler', 'buddypress_content' ), // Change it to BuddyPress Content.
				);
			}

			$tabs[30] = array(
				'href'     => '#rtmedia-types',
				'icon'     => 'dashicons-editor-video',
				'title'    => esc_html__( 'rtMedia Types', 'buddypress-media' ),
				'name'     => esc_html__( 'Types', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'types_content' ),
			);

			$tabs[40] = array(
				'href'     => '#rtmedia-sizes',
				'icon'     => 'dashicons-editor-expand',
				'title'    => esc_html__( 'rtMedia Sizes', 'buddypress-media' ),
				'name'     => esc_html__( 'Media Sizes', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'sizes_content' ),
			);

			$tabs[50] = array(
				'href'     => '#rtmedia-privacy',
				'icon'     => 'dashicons-lock',
				'title'    => esc_html__( 'rtMedia Privacy', 'buddypress-media' ),
				'name'     => esc_html__( 'Privacy', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'privacy_content' ),
			);
			$tabs[60] = array(
				'href'     => '#rtmedia-custom-css-settings',
				'icon'     => 'dashicons-clipboard',
				'title'    => esc_html__( 'rtMedia Custom CSS', 'buddypress-media' ),
				'name'     => esc_html__( 'Custom CSS', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'custom_css_content' ),
			);

			$tabs = apply_filters( 'rtmedia_add_settings_sub_tabs', $tabs, $tab );

			$tabs[] = array(
				'href'     => '#rtmedia-general',
				'icon'     => 'dashicons-admin-tools',
				'title'    => esc_html__( 'Other Settings', 'buddypress-media' ),
				'name'     => esc_html__( 'Other Settings', 'buddypress-media' ),
				'callback' => array( 'RTMediaFormHandler', 'general_content' ),
			);

			return $tabs;
		}

		/**
		 * Multisite Save Options - http://wordpress.stackexchange.com/questions/64968/settings-api-in-multisite-missing-update-message#answer-72503
		 *
		 * @access public
		 * @global type $rtmedia_admin
		 */
		public function save_multisite_options() {
			global $rtmedia_admin;
			do_action( 'rtmedia_sanitize_settings', wp_unslash( $_POST ) ); // @codingStandardsIgnoreLine

			$rtmedia_options = filter_input( INPUT_POST, 'rtmedia_options' );
			if ( isset( $rtmedia_options ) ) {
				// Todo: How we can sanitize array?
				rtmedia_update_site_option( 'rtmedia_options', $rtmedia_options );
				
				// Redirect to settings page in network.
				wp_redirect( esc_url_raw( add_query_arg( array(
					'page'    => 'rtmedia-settings',
					'updated' => 'true',
				), ( is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) ) ) ) );
				exit;
			}
		}

		/**
		 * Admin Sidebar
		 *
		 * @access public
		 */
		public function admin_sidebar() {
			do_action( 'rtmedia_before_default_admin_widgets' );
			$current_user = wp_get_current_user();
			$message      = sprintf( esc_html__( 'I use @rtMediaWP http://rt.cx/rtmedia on %s', 'buddypress-media' ), home_url() );
			$addons       = '<div id="social" class="rtm-social-share">
							<p><a href="http://twitter.com/home/?status=' . esc_attr( $message ) . '" class="button twitter" target= "_blank" title="' . esc_attr__( 'Post to Twitter Now', 'buddypress-media' ) . '">' . esc_html__( 'Post to Twitter', 'buddypress-media' ) . '<span class="dashicons dashicons-twitter"></span></a></p>
							<p><a href="https://www.facebook.com/sharer/sharer.php?u=https://rtmedia.io/" class="button facebook" target="_blank" title="' . esc_attr__( 'Share on Facebook Now', 'buddypress-media' ) . '">' . esc_html__( 'Share on Facebook', 'buddypress-media' ) . '<span class="dashicons dashicons-facebook"></span></a></p>
							<p><a href="http://wordpress.org/support/view/plugin-reviews/buddypress-media?rate=5#postform" class="button wordpress" target= "_blank" title="' . esc_attr__( 'Rate rtMedia on Wordpress.org', 'buddypress-media' ) . '">' . esc_html__( 'Rate on Wordpress.org', 'buddypress-media' ) . '<span class="dashicons dashicons-wordpress"></span></a></p>
							<p><a href="' . sprintf( '%s', 'https://rtmedia.io/feed/' ) . '" class="button rss" target="_blank" title="' . esc_attr__( 'Subscribe to our Feeds', 'buddypress-media' ) . '">' . esc_html__( 'Subscribe to our Feeds', 'buddypress-media' ) . '<span class="dashicons dashicons-rss"></span></a></p>
							</div>';

			new RTMediaAdminWidget( 'spread-the-word', esc_html__( 'Spread the Word', 'buddypress-media' ), $addons );

			$branding = '<form action="http://rtcamp.us1.list-manage1.com/subscribe/post?u=85b65c9c71e2ba3fab8cb1950&amp;id=9e8ded4470" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
							<div class="mc-field-group">
								<input type="email" value="' . esc_attr( $current_user->user_email ) . '" name="EMAIL" placeholder="Email" class="required email" id="mce-EMAIL">
								<input style="display:none;" type="checkbox" checked="checked" value="1" name="group[1721][1]" id="mce-group[1721]-1721-0">
								<input type="submit" value="' . esc_attr__( 'Subscribe', 'buddypress-media' ) . '" name="subscribe" id="mc-embedded-subscribe" class="button">
								<div id="mce-responses" class="clear">
									<div class="response" id="mce-error-response" style="display:none"></div>
									<div class="response" id="mce-success-response" style="display:none"></div>
								</div>
							</div>
						</form>';
			new RTMediaAdminWidget( 'branding', esc_html__( 'Subscribe', 'buddypress-media' ), $branding );

			do_action( 'rtmedia_after_default_admin_widgets' );
		}

		public function linkback() {
                    
			// Fixme: remove code looks like old setting save code new code at app/helper/RTMediaSettings.php.
			$linkback = filter_input( INPUT_POST, 'linkback' );
			if ( isset( $linkback ) && $linkback ) {
				return rtmedia_update_site_option( 'rtmedia-add-linkback', true );
			} else {
				return rtmedia_update_site_option( 'rtmedia-add-linkback', false );
			}
		}

		public function convert_videos_mailchimp_send() {
                    
			// Todo: nonce required.
			$interested = filter_input( INPUT_POST, 'linkback', FILTER_SANITIZE_STRING );
			$choice = filter_input( INPUT_POST, 'choice', FILTER_SANITIZE_STRING );
			$url = filter_input( INPUT_POST, 'url', FILTER_SANITIZE_URL );
			$email = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_EMAIL );
			if ( 'Yes' === $interested && ! empty( $choice ) ) {
				wp_remote_get( esc_url_raw( add_query_arg( array(
					'rtmedia-convert-videos-form' => 1,
					'choice'                      => $choice,
					'url'                         => $url,
					'email'                       => $email,
				), 'https://rtmedia.io/' ) ) );
			} else {
				rtmedia_update_site_option( 'rtmedia-survey', 0 );
			}
			esc_html_e( 'Thank you for your time.', 'buddypress-media' );
			wp_die();
		}

		public function video_transcoding_survey_response() {
			$survey_done = filter_input( INPUT_GET, 'survey-done', FILTER_SANITIZE_STRING );
			if ( isset( $survey_done ) && ( md5( 'survey-done' ) === $survey_done ) ) {
				rtmedia_update_site_option( 'rtmedia-survey', 0 );
			}
		}

		public function plugin_meta_premium_addon_link( $plugin_meta, $plugin_file ) {
			if ( plugin_basename( RTMEDIA_PATH . 'index.php' ) === $plugin_file ) {
				$plugin_meta[] = '<a href="https://rtmedia.io/products/?utm_source=dashboard&#038;utm_medium=plugin&#038;utm_campaign=buddypress-media" title="' . esc_attr__( 'Premium Add-ons', 'buddypress-media' ) . '">' . esc_html__( 'Premium Add-ons', 'buddypress-media' ) . '</a>';
			}

			return $plugin_meta;
		}

		public function upload_filetypes_error() {
			global $rtmedia;
			$upload_filetypes = rtmedia_get_site_option( 'upload_filetypes', 'jpg jpeg png gif' );
			$upload_filetypes = explode( ' ', $upload_filetypes );
			$flag             = false;
			if ( isset( $rtmedia->options['images_enabled'] ) && $rtmedia->options['images_enabled'] ) {
				$not_supported_image = array_diff( array( 'jpg', 'jpeg', 'png', 'gif' ), $upload_filetypes );
				if ( ! empty( $not_supported_image ) ) {
					?>
					<div class="error upload-filetype-network-settings-error">
						<p>
							<?php wp_nonce_field( '_rtm_file_type_error_', 'rtm-file-type-error' ); ?>
							<?php printf( esc_html__( 'You have images enabled on rtMedia but your network allowed filetypes do not permit uploading of %s. Click ', 'buddypress-media' ), esc_html( implode( ', ', $not_supported_image ) ) ) ?>
							<a href='<?php echo esc_url( network_admin_url( 'settings.php#upload_filetypes' ) );?>'><?php esc_html_e( 'here', 'buddypress-media' ) ?></a>
							<?php esc_html_e( ' to change your settings manually.', 'buddypress-media' ) ?>
							<br />
							<strong><?php esc_html_e( 'Recommended:', 'buddypress-media' ); ?></strong>
							<input type="button" class="button update-network-settings-upload-filetypes" value="<?php esc_attr_e( 'Update Network Settings Automatically', 'buddypress-media' );?>">
							<img style="display:none;" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" />
						</p>
					</div>
					<?php
					$flag = true;
				}
			}
			if ( isset( $rtmedia->options['videos_enabled'] ) && $rtmedia->options['videos_enabled'] ) {
				if ( ! in_array( 'mp4', $upload_filetypes, true ) ) {
					?>
					<div class="error upload-filetype-network-settings-error">
						<p>
							<?php esc_html_e( 'You have video enabled on BuddyPress Media but your network allowed filetypes do not permit uploading of mp4. Click ', 'buddypress-media' ) ?>
							<a href='<?php echo esc_url( network_admin_url( 'settings.php#upload_filetypes' ) );?>'><?php esc_html_e( 'here', 'buddypress-media' ) ?></a>
							<?php esc_html_e( ' to change your settings manually.', 'buddypress-media' ) ?>
							<br />
							<strong><?php esc_html_e( 'Recommended:', 'buddypress-media' );?></strong>
							<input type="button" class="button update-network-settings-upload-filetypes" value="<?php esc_attr_e( 'Update Network Settings Automatically', 'buddypress-media' ); ?>">
							<img style="display:none;" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" />
						</p>
					</div>
					<?php
					$flag = true;
				}
			}
			if ( isset( $rtmedia->options['audio_enabled'] ) && $rtmedia->options['audio_enabled'] ) {
				if ( ! in_array( 'mp3', $upload_filetypes, true ) ) {
					?>
					<div class="error upload-filetype-network-settings-error">
						<p>
							<?php esc_html_e( 'You have audio enabled on BuddyPress Media but your network allowed filetypes do not permit uploading of mp3. Click ', 'buddypress-media' ) ?>
							<a href='<?php echo esc_url( network_admin_url( 'settings.php#upload_filetypes' ) );?>'><?php esc_html_e( 'here', 'buddypress-media' ) ?></a>
							<?php esc_html_e( ' to change your settings manually.', 'buddypress-media' ) ?>
							<br />
							<strong><?php esc_html_e( 'Recommended:', 'buddypress-media' ); ?></strong>
							<input type="button" class="button update-network-settings-upload-filetypes" value="<?php esc_attr_e( 'Update Network Settings Automatically', 'buddypress-media' ); ?>">
							<img style="display:none;" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" />
						</p>
					</div>
					<?php
					$flag = true;
				}
			}
			if ( $flag ) {
				?>
				<script type="text/javascript">
					jQuery('.upload-filetype-network-settings-error').on('click', '.update-network-settings-upload-filetypes', function () {
						jQuery('.update-network-settings-upload-filetypes').siblings('img').show();
						jQuery('.update-network-settings-upload-filetypes').prop('disabled', true);
						jQuery.post(ajaxurl, {action: 'rtmedia_correct_upload_filetypes', _rtm_nonce: jQuery('rtm-file-type-error').val()}, function (response) {
							if (response) {
								jQuery('.upload-filetype-network-settings-error:first').after('<div style="display: none;" class="updated rtmedia-network-settings-updated-successfully"><p><?php esc_html_e( 'Network settings updated successfully.', 'buddypress-media' ); ?></p></div>');
								jQuery('.upload-filetype-network-settings-error').remove();
								jQuery('.bp-media-network-settings-updated-successfully').show();
							}
						});
					});
				</script>
				<?php
			}
		}

		public function correct_upload_filetypes() {
			if ( ! check_ajax_referer( '_rtm_file_type_error_', '_rtm_nonce' ) ) {
				wp_send_json( false );
			}
			global $rtmedia;
			$upload_filetypes_orig = $upload_filetypes = rtmedia_get_site_option( 'upload_filetypes', 'jpg jpeg png gif' );
			$upload_filetypes      = explode( ' ', $upload_filetypes );
			if ( isset( $rtmedia->options['images_enabled'] ) && $rtmedia->options['images_enabled'] ) {
				$not_supported_image = array_diff( array( 'jpg', 'jpeg', 'png', 'gif' ), $upload_filetypes );
				if ( ! empty( $not_supported_image ) ) {
					$update_image_support = null;
					foreach ( $not_supported_image as $ns ) {
						$update_image_support .= ' ' . $ns;
					}
					if ( $update_image_support ) {
						$upload_filetypes_orig .= $update_image_support;
						rtmedia_update_site_option( 'upload_filetypes', $upload_filetypes_orig );
					}
				}
			}
			if ( isset( $rtmedia->options['videos_enabled'] ) && $rtmedia->options['videos_enabled'] ) {
				if ( ! in_array( 'mp4', $upload_filetypes, true ) ) {
					$upload_filetypes_orig .= ' mp4';
					rtmedia_update_site_option( 'upload_filetypes', $upload_filetypes_orig );
				}
			}
			if ( isset( $rtmedia->options['audio_enabled'] ) && $rtmedia->options['audio_enabled'] ) {
				if ( ! in_array( 'mp3', $upload_filetypes, true ) ) {
					$upload_filetypes_orig .= ' mp3';
					rtmedia_update_site_option( 'upload_filetypes', $upload_filetypes_orig );
				}
			}
			echo true;
			wp_die();
		}

		function edit_video_thumbnail( $form_fields, $post ) {
			if ( isset( $post->post_mime_type ) ) {
				$media_type = explode( '/', $post->post_mime_type );
				if ( is_array( $media_type ) && 'video' === $media_type[0] ) {
					$media_id         = $post->ID;
					$thumbnail_array  = get_post_meta( $media_id, 'rtmedia_media_thumbnails', true );
					$rtmedia_model    = new RTMediaModel();
					$rtmedia_media    = $rtmedia_model->get( array( 'media_id' => $media_id ) );
					$video_thumb_html = '';
					if ( is_array( $thumbnail_array ) ) {
						$video_thumb_html .= '<ul> ';

						foreach ( $thumbnail_array as $key => $thumbnail_src ) {
							$checked = checked( $thumbnail_src, $rtmedia_media[0]->cover_art, false );
							$count   = $key + 1;
							$video_thumb_html .= '<li style="width: 150px;display: inline-block;">
									<label for="rtmedia-upload-select-thumbnail-' . esc_attr( $count ) . '">
									<input type="radio" ' . esc_attr( $checked ) . ' id="rtmedia-upload-select-thumbnail-' . esc_attr( $count ) . '" value="' . esc_url( $thumbnail_src ) . '" name="rtmedia-thumbnail" />
									<img src=" ' . esc_url( $thumbnail_src ) . '" style="max-height: 120px;max-width: 120px; vertical-align: middle;" />
									</label></li> ';
						}

						$video_thumb_html .= '  </ul>';
						$form_fields['rtmedia_video_thumbnail'] = array(
							'label' => 'Video Thumbnails',
							'input' => 'html',
							'html'  => $video_thumb_html,
						);
					}
				}
			}

			return $form_fields;
		}

		function save_video_thumbnail( $post ) {
			$rtmedia_thumbnail = filter_input( INPUT_POST, 'rtmedia-thumbnail', FILTER_SANITIZE_STRING );
			$id = filter_input( INPUT_POST, 'ID', FILTER_SANITIZE_NUMBER_INT );
			if ( isset( $rtmedia_thumbnail ) ) {
				$rtmedia_model = new RTMediaModel();
				$model         = new RTMediaModel();
				$media         = $model->get( array( 'media_id' => $id ) );
				$media_id      = $media[0]->id;
				$rtmedia_model->update( array( 'cover_art' => $rtmedia_thumbnail ), array( 'media_id' => $id ) );
				update_activity_after_thumb_set( $media_id );
			}

			return $post;
		}

		function rtmedia_regenerate_thumb_js() {
			global $pagenow;

			if ( 'upload.php' === $pagenow ) {
				?>
				<script type="text/javascript">
					function rtmedia_regenerate_thumbs(post_id) {
						if (post_id != "") {
							var data = {
								action: 'rt_media_regeneration',
								media_id: post_id
							};
							jQuery.post(ajaxurl, data, function (data) {
								data = JSON.parse(data);
								if (data.status === true) {
									alert("<?php esc_html_e( 'Video is sent to generate thumbnails.', 'buddypress-media' ) ?>");
								} else {
									alert("<?php esc_html_e( 'Video cannot be sent to generate thumbnails.', 'buddypress-media' ) ?>");
								}
							});
						}
					}
				</script>
				<?php
			}
		}

		function add_bulk_actions_regenerate() {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					$('select[name^="action"] option:last-child').before('<option value="bulk_video_regenerate_thumbnails"><?php esc_html_e( 'Regenerate Video Thumbnails', 'buddypress-media' ); ?></option>');
				});
			</script>
			<?php
		}

		function rtmedia_update_template_notice() {
			$site_option = rtmedia_get_site_option( 'rtmedia-update-template-notice-v3_9_4' );

			if ( ! $site_option || 'hide' !== $site_option ) {
				rtmedia_update_site_option( 'rtmedia-update-template-notice-v3_9_4', 'show' );
				if ( is_dir( get_template_directory() . '/rtmedia' ) ) {
					echo '<div class="error rtmedia-update-template-notice"><p>' . esc_html__( 'Please update rtMedia template files if you have overridden the default rtMedia templates in your theme. If not, you can ignore and hide this notice.', 'buddypress-media' ) . '<a href="#" onclick="rtmedia_hide_template_override_notice(\''.esc_js( wp_create_nonce( 'rtmedia_template_notice' ) ).'\')" style="float:right">' . esc_html__( 'Hide', 'buddypress-media' ) . '</a></p></div>';
					?>
					<script type="text/javascript">
						function rtmedia_hide_template_override_notice( rtmedia_template_notice_nonce ) {
							var data = {action: 'rtmedia_hide_template_override_notice', _rtm_nonce: rtmedia_template_notice_nonce };
							jQuery.post(ajaxurl, data, function (response) {
								response = response.trim();
								if ('1' === response)
									jQuery('.rtmedia-update-template-notice').remove();
							});
						}
					</script>
					<?php
				}
			}
		}

		function rtmedia_hide_template_override_notice() {

			if ( check_ajax_referer( 'rtmedia_template_notice', '_rtm_nonce' ) && rtmedia_update_site_option( 'rtmedia-update-template-notice-v3_9_4', 'hide' ) ) {
				echo '1';
			} else {
				echo '0';
			}
			die();
		}

		public static function render_admin_ui( $page, $sub_tabs, $args = array() ) {

			// Wrapper class.
			$wrapper_class = '';
			if ( ! empty( $args['wrapper_class'] ) && is_array( $args['wrapper_class'] ) ) {
				$wrapper_class = implode( ' ', $args['wrapper_class'] );
			}

			// Tabs.
			if ( 'rtmedia-settings' === $page ) {
				$sub_tabs = apply_filters( 'rtmedia_pro_settings_tabs_content', $sub_tabs );
				ksort( $sub_tabs );
			}
			$tab_position_class = 'rtm-vertical-tabs';
			if ( 'rtmedia-addons' === $page ) {
				$tab_position_class = 'rtm-horizotanl-tabs';
			}
			?>

			<div
				class="clearfix <?php echo esc_attr( $tab_position_class ); ?> rtm-admin-tab-container <?php echo esc_attr( $wrapper_class ); ?>">
				<ul class="rtm-tabs">
					<?php
					$i = 1;
					foreach ( $sub_tabs as $tab ) {

						// Tab status.
						$active_class = '';
						if ( 1 === $i ) {
							$active_class = 'active';
						}

						// Tab icon.
						$icon = '';
						if ( isset( $tab['icon'] ) && ! empty( $tab['icon'] ) ) {
							$icon = '<i class="' . esc_attr( $tab['icon'] ) . ' dashicons rtmicon"></i>';
						}
						?>
						<li class="<?php echo esc_attr( $active_class ) ?>">
							<a id="tab-<?php echo esc_attr( substr( $tab['href'], 1 ) ) ?>" title="<?php echo esc_attr( $tab['title'] ); ?>"
							   href="<?php echo esc_url( $tab['href'] ); ?>"
							   class="rtmedia-tab-title <?php echo esc_attr( sanitize_title( $tab['name'] ) ); ?>">
								<?php echo $icon ; // @codingStandardsIgnoreLine ?><span><?php echo esc_html( $tab['name'] ); ?></span>
							</a>
						</li>
						<?php
						$i ++;
					}
					?>
				</ul>

				<div class="tabs-content rtm-tabs-content">
					<?php
					$k = 1;
					foreach ( $sub_tabs as $tab ) {
						$active_class = '';
						if ( 1 === $k ) {
							$active_class = ' active';
						}
						$k ++;
						if ( isset( $tab['icon'] ) && ! empty( $tab['icon'] ) ) {
							$icon = '<i class="' . esc_attr( $tab['icon'] ) . '"></i>';
						}
						$tab_without_hash = explode( '#', $tab['href'] );
						$tab_without_hash = $tab_without_hash[1];
						echo '<div class="rtm-content' . esc_attr( $active_class ) . '" id="' . esc_attr( $tab_without_hash ) . '">';
						if ( isset( $tab['args'] ) ) {
							call_user_func( $tab['callback'], $page, $tab['args'] );
						} else {
							call_user_func( $tab['callback'], $page );
						}
						echo '</div>';
					}
					?>
				</div>

			</div>
			<?php
		}

		/**
		 * To remove setting saved parameter from url once satting saved
		 * Add parameter to this array WP will remove variable from Query string
		 *
		 * @param array $removable_query_args
		 *
		 * @return array $removable_query_args
		 */
		function removable_query_args( $removable_query_args ) {
			$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
			if ( isset( $page ) && 'rtmedia-settings' === $page ) {
				$removable_query_args[] = 'settings-saved';
			}

			return $removable_query_args;
		}
	}

}
