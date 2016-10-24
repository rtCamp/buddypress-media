<?php

/**
 * List of user actions
 */
function rtmedia_author_actions() {

	$options_start = $options_end = $option_buttons = $output = '';
	$options       = array();
	$options       = apply_filters( 'rtmedia_author_media_options', $options );

	if ( ! empty( $options ) ) {
		$options_start .= '<div class="click-nav rtm-media-options-list" id="rtm-media-options-list">
                <div class="no-js">
                <button class="clicker rtmedia-media-options rtmedia-action-buttons button">' . esc_html__( 'Options', 'buddypress-media' ) . '</button>
                <ul class="rtm-options">';

		foreach ( $options as $action ) {
			if ( ! empty( $action ) ) {
				$option_buttons .= '<li>' . $action . '</li>';
			}
		}

		$options_end = '</ul></div></div>';

		if ( ! empty( $option_buttons ) ) {
			$output = $options_start . $option_buttons . $options_end;
		}

		if ( ! empty( $output ) ) {
			echo $output; // @codingStandardsIgnoreLine
		}
	}
}

add_action( 'after_rtmedia_action_buttons', 'rtmedia_author_actions' );

/**
 * Adding media edit tab
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @param       string          $type
 */
function rtmedia_image_editor_title( $type = 'photo' ) {

	global $rtmedia_query;

	if ( isset( $rtmedia_query->media[0]->media_type ) && 'photo' === $rtmedia_query->media[0]->media_type && 'photo' === $type ) {
		echo '<li><a href="#panel2" class="rtmedia-modify-image"><i class="dashicons dashicons-format-image rtmicon"></i>' . esc_html__( 'Image', 'buddypress-media' ) . '</a></li>';
	}

}

add_action( 'rtmedia_add_edit_tab_title', 'rtmedia_image_editor_title', 12, 1 );

/**
 * Add the content for the image editor tab
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @param       string          $type
 */
function rtmedia_image_editor_content( $type = 'photo' ) {

	global $rtmedia_query;

	if ( isset( $rtmedia_query->media ) && is_array( $rtmedia_query->media ) && isset( $rtmedia_query->media[0]->media_type ) && 'photo' === $rtmedia_query->media[0]->media_type && 'photo' === $type ) {
		$media_id      = $rtmedia_query->media[0]->media_id;
		$id            = $rtmedia_query->media[0]->id;
		$modify_button = $nonce = '';

		if ( current_user_can( 'edit_posts' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/image-edit.php' );
			$nonce         = wp_create_nonce( "image_editor-$media_id" );
			$modify_button = '<p><input type="button" class="button rtmedia-image-edit" id="imgedit-open-btn-' . esc_attr( $media_id ) . '" onclick="imageEdit.open( \'' . esc_attr( $media_id ) . '\', \'' . esc_attr( $nonce ) . '\' )" value="' . esc_attr__( 'Modify Image', 'buddypress-media' ) . '"> <span class="spinner"></span></p>';
		}

		$image_path = rtmedia_image( 'rt_media_activity_image', $id, false );

		echo '<div class="content" id="panel2">';
		echo '<div class="rtmedia-image-editor-cotnainer" id="rtmedia-image-editor-cotnainer" >';
		echo '<input type="hidden" id="rtmedia-filepath-old" name="rtmedia-filepath-old" value="' . esc_url( $image_path ) . '" />';
		echo '<div class="rtmedia-image-editor" id="image-editor-' . esc_attr( $media_id ) . '"></div>';

		$thumb_url = wp_get_attachment_image_src( $media_id, 'thumbnail', true );

		echo '<div id="imgedit-response-' . esc_attr( $media_id ) . '"></div>';
		echo '<div class="wp_attachment_image" id="media-head-' . esc_attr( $media_id ) . '">' . '<p id="thumbnail-head-' . esc_attr( $id ) . '"><img class="thumbnail" src="' . esc_url( set_url_scheme( $thumb_url[0] ) ) . '" alt="" /></p>' . $modify_button . '</div>'; // @codingStandardsIgnoreLine
		echo '</div>';
		echo '</div>';
	}

}

add_action( 'rtmedia_add_edit_tab_content', 'rtmedia_image_editor_content', 12, 1 );

/**
 * Provide drop-down to user to change the album of the media in media edit screen
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @param       string          $media_type
 */
function rtmedia_add_album_selection_field( $media_type ) {

	if ( is_rtmedia_album_enable() && isset( $media_type ) && 'album' != $media_type && apply_filters( 'rtmedia_edit_media_album_select', true ) ) {
		global $rtmedia_query;

		$curr_album_id = '';

		if ( isset( $rtmedia_query->media[0] ) && isset( $rtmedia_query->media[0]->album_id ) && ! empty( $rtmedia_query->media[0]->album_id ) ) {
			$curr_album_id = $rtmedia_query->media[0]->album_id;
		}
		?>
		<div class="rtmedia-edit-change-album rtm-field-wrap">
			<label for=""><?php esc_html_e( 'Album', 'buddypress-media' ); ?> : </label>
			<?php
			if ( isset( $rtmedia_query->query['context'] ) && 'group' === $rtmedia_query->query['context'] ) {
				//show group album list.
				$album_list = rtmedia_group_album_list( $selected_album_id = $curr_album_id );
			} else {
				//show profile album list
				$album_list = rtmedia_user_album_list( $get_all = false, $selected_album_id = $curr_album_id );
			} ?>
			<select name="album_id" class="rtmedia-merge-user-album-list"><?php echo $album_list; // @codingStandardsIgnoreLine?></select>
		</div>
		<?php
	}

}

add_action( 'rtmedia_add_edit_fields', 'rtmedia_add_album_selection_field', 14, 1 );

/**
 * Rendering gallery options
 */
function rtmedia_gallery_options() {

	$options_start = $options_end = $option_buttons = $output = '';
	$options       = array();
	$options       = apply_filters( 'rtmedia_gallery_actions', $options );

	if ( ! empty( $options ) ) {
		$options_start .= '<div class="click-nav rtm-media-options-list" id="rtm-media-options-list">
                <div class="no-js">
                <div class="clicker rtmedia-action-buttons"><i class="dashicons dashicons-admin-generic rtmicon"></i>' . apply_filters( 'rtm_gallary_option_label', __( 'Options', 'buddypress-media' ) ) . '</div>
                <ul class="rtm-options">';

		foreach ( $options as $action ) {
			if ( ! empty( $action ) ) {
				$option_buttons .= '<li>' . $action . '</li>';
			}
		}

		$options_end = '</ul></div></div>';

		if ( ! empty( $option_buttons ) ) {
			$output = $options_start . $option_buttons . $options_end;
		}

		if ( ! empty( $output ) ) {
			echo $output; // @codingStandardsIgnoreLine
		}
	}

}

add_action( 'rtmedia_media_gallery_actions', 'rtmedia_gallery_options', 80 );
add_action( 'rtmedia_album_gallery_actions', 'rtmedia_gallery_options', 80 );

/**
 * Rendering create an album markup
 *
 * @global      RTMediaQuery    $rtmedia_query
 */
function rtmedia_create_album_modal() {

	global $rtmedia_query;

	if ( is_rtmedia_album_enable() && isset( $rtmedia_query->query['context_id'] ) && isset( $rtmedia_query->query['context'] ) && ( ! ( isset( $rtmedia_query->is_gallery_shortcode ) && true === $rtmedia_query->is_gallery_shortcode ) ) || apply_filters( 'rtmedia_load_add_album_modal', false ) ) {
		?>
		<div class="mfp-hide rtmedia-popup" id="rtmedia-create-album-modal">
			<div id="rtm-modal-container">
				<?php do_action( 'rtmedia_before_create_album_modal' ); ?>
				<h2 class="rtm-modal-title"><?php esc_html_e( 'Create an Album', 'buddypress-media' ); ?></h2>
				<p>
					<label class="rtm-modal-grid-title-column" for="rtmedia_album_name"><?php esc_html_e( 'Album Title : ', 'buddypress-media' ); ?></label>
					<input type="text" id="rtmedia_album_name" value="" class="rtm-input-medium" />
				</p>
				<?php do_action( 'rtmedia_add_album_privacy' ); ?>
				<input type="hidden" id="rtmedia_album_context" value="<?php echo esc_attr( $rtmedia_query->query['context'] ); ?>">
				<input type="hidden" id="rtmedia_album_context_id" value="<?php echo esc_attr( $rtmedia_query->query['context_id'] ); ?>">
				<?php wp_nonce_field( 'rtmedia_create_album_nonce', 'rtmedia_create_album_nonce' ); ?>
				<p>
					<button type="button" id="rtmedia_create_new_album"><?php esc_html_e( 'Create Album', 'buddypress-media' ); ?></button>
				</p>
				<?php do_action( 'rtmedia_after_create_album_modal' ); ?>
			</div>
		</div>
		<?php
	}

}

add_action( 'rtmedia_before_media_gallery', 'rtmedia_create_album_modal' );
add_action( 'rtmedia_before_album_gallery', 'rtmedia_create_album_modal' );

/**
 * Rendering merge album markup
 *
 * @global      RTMediaQuery    $rtmedia_query
 */
function rtmedia_merge_album_modal() {

	if ( ! is_rtmedia_album() || ! is_user_logged_in() ) {
		return;
	}

	if ( ! is_rtmedia_album_enable() ) {
		return;
	}

	global $rtmedia_query;

	if ( is_rtmedia_group_album() ) {
		$album_list = rtmedia_group_album_list();
	} else {
		$album_list = rtmedia_user_album_list();
	}

	if ( $album_list && ! empty( $rtmedia_query->media_query['album_id'] ) ) {
		?>
		<div class="rtmedia-merge-container rtmedia-popup mfp-hide" id="rtmedia-merge">
			<div id="rtm-modal-container">
				<h2 class="rtm-modal-title"><?php esc_html_e( 'Merge Album', 'buddypress-media' ); ?></h2>
				<form method="post" class="album-merge-form" action="merge/">
					<p>
						<span><?php esc_html_e( 'Select Album to merge with : ', 'buddypress-media' ); ?></span>
						<?php echo '<select name="album" class="rtmedia-merge-user-album-list">' . $album_list . '</select>';// @codingStandardsIgnoreLine ?>
					</p>
					<?php wp_nonce_field( 'rtmedia_merge_album_' . $rtmedia_query->media_query['album_id'], 'rtmedia_merge_album_nonce' ); ?>
					<input type="submit" class="rtmedia-merge-selected" name="merge-album" value="<?php esc_html_e( 'Merge Album', 'buddypress-media' ); ?>"/>
				</form>
			</div>
		</div>
		<?php
	}

}

add_action( 'rtmedia_before_media_gallery', 'rtmedia_merge_album_modal' );
add_action( 'rtmedia_before_album_gallery', 'rtmedia_merge_album_modal' );

/**
 * Rendering checkboxes to select media
 *
 * @global      RTMediaQuery    $rtmedia_query
 * @global      array           $rtmedia_backbone
 */
function rtmedia_item_select() {

	global $rtmedia_query, $rtmedia_backbone;

	if ( $rtmedia_backbone['backbone'] ) {
		if ( isset( $rtmedia_backbone['is_album'] ) && $rtmedia_backbone['is_album'] && isset( $rtmedia_backbone['is_edit_allowed'] ) && $rtmedia_backbone['is_edit_allowed'] ) {
			echo '<span class="rtm-checkbox-wrap"><input type="checkbox" name="move[]" class="rtmedia-item-selector" value="<%= id %>" /></span>';
		}
	} else {
		if ( is_rtmedia_album() && isset( $rtmedia_query->media_query ) && 'edit' === $rtmedia_query->action_query->action ) {
			if ( isset( $rtmedia_query->media_query['media_author'] ) && get_current_user_id() === intval( $rtmedia_query->media_query['media_author'] ) ) {
				echo '<span class="rtm-checkbox-wrap"><input type="checkbox" class="rtmedia-item-selector" name="selected[]" value="' . esc_attr( rtmedia_id() ) . '" /></span>';
			}
		}
	}
}

add_action( 'rtmedia_before_item', 'rtmedia_item_select' );

/**
 * Album merge action
 *
 * @param       array       $actions
 *
 * @return      array
 */
function rtmedia_album_merge_action( $actions ) {

	$actions['merge'] = esc_html__( 'Merge', 'buddypress-media' );

	return $actions;

}

add_action( 'rtmedia_query_actions', 'rtmedia_album_merge_action' );

/**
 * Add upload button
 */
function add_upload_button() {
	if ( function_exists( 'bp_is_blog_page' ) && ! bp_is_blog_page() ) {
		/**
		 * Add filter to transfer "Upload" string,
		 * issue: http://git.rtcamp.com/rtmedia/rtMedia/issues/133
		 * By: Yahil
		 */
		$upload_string = apply_filters( 'rtmedia_upload_button_string', __( 'Upload', 'buddypress-media' ) );

		if ( function_exists( 'bp_is_user' ) && bp_is_user() && function_exists( 'bp_displayed_user_id' ) && bp_displayed_user_id() === get_current_user_id() ) {

			echo '<span class="primary rtmedia-upload-media-link" id="rtm_show_upload_ui" title="' .apply_filters( 'rtm_gallary_upload_title_label', __( 'Upload Media', 'buddypress-media' ) )  . '"><i class="dashicons dashicons-upload rtmicon"></i>' . apply_filters( 'rtm_gallary_upload_label', __( 'Upload', 'buddypress-media' ) ) . '</span>';
		} else {
			if ( function_exists( 'bp_is_group' ) && bp_is_group() ) {
				if ( can_user_upload_in_group() ) {
					echo '<span class="rtmedia-upload-media-link primary" id="rtm_show_upload_ui" title="' . apply_filters( 'rtm_gallary_upload_title_label', __( 'Upload Media', 'buddypress-media' ) )  . '"><i class="dashicons dashicons-upload rtmicon"></i>' . apply_filters( 'rtm_gallary_upload_label', __( 'Upload', 'buddypress-media' ) ) . '</span>';
				}
			}
		}
	}
}

add_action( 'rtmedia_media_gallery_actions', 'add_upload_button', 99 );
add_action( 'rtmedia_album_gallery_actions', 'add_upload_button', 99 );

/**
 * Add music cover art
 *
 * @param       array       $file_object
 * @param       object      $upload_obj
 */
function add_music_cover_art( $file_object, $upload_obj ) {

	$media_obj = new RTMediaMedia();
	$media     = $media_obj->model->get( array(
		'id' => $upload_obj->media_ids[0],
	) );

}

// add_action("rtemdia_after_file_upload_before_activity","add_music_cover_art" ,20 ,2);

/**
 * rtmedia link
 *
 * @global      RTMedia     $rtmedia
 */
function rtmedia_link_in_footer() {

	global $rtmedia;

	$option = $rtmedia->options;
	$link   = ( isset( $option['rtmedia_add_linkback'] ) ) ? $option['rtmedia_add_linkback'] : false;

	if ( $link ) {
		$aff_id = ( '' != $option['rtmedia_affiliate_id'] ) ? '&ref=' . $option['rtmedia_affiliate_id'] : '';
		$href   = 'https://rtmedia.io/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media' . $aff_id;
		?>
		<div class='rtmedia-footer-link'>
			<?php esc_html_e( 'Empowering your community with ', 'buddypress-media' ); ?>
			<a href='<?php echo esc_url( $href ) ?>' title='<?php esc_attr_e( 'The only complete media solution for WordPress, BuddyPress and bbPress', 'buddypress-media' ); ?> '>rtMedia</a>
		</div>
		<?php
	}
}

add_action( 'wp_footer', 'rtmedia_link_in_footer' );

/**
 * Add content before the media in single media page
 *
 * @global      bool    $rt_ajax_request
 */
function rtmedia_content_before_media() {

	global $rt_ajax_request;

	if ( $rt_ajax_request ) {
		?>
		<span class="rtm-mfp-close mfp-close dashicons dashicons-no-alt" title="<?php esc_attr_e( 'Close (Esc)', 'buddypress-media' ); ?>"></span>
		<?php
	}

}

add_action( 'rtmedia_before_media', 'rtmedia_content_before_media', 10 );

/**
 * Rendering custom CSS
 *
 * @global      RTMedia     $rtmedia
 */
function rtmedia_custom_css() {

	global $rtmedia;

	$options = $rtmedia->options;

	if ( ! empty( $options['styles_custom'] ) ) {
		echo "<style type='text/css'> " . stripslashes( $options['styles_custom'] ) . ' </style>'; // @codingStandardsIgnoreLine
	}

}

add_action( 'wp_head', 'rtmedia_custom_css' );

/**
 * Update the group media privacy according to the group privacy settings when group settings are changed
 *
 * @global      wpdb    $wpdb
 *
 * @param       int     $group_id
 */
function update_group_media_privacy( $group_id ) {

	if ( ! empty( $group_id ) && function_exists( 'groups_get_group' ) ) {
		//get the buddybress group
		$group = groups_get_group( array(
			'group_id' => $group_id,
		) );

		if ( isset( $group->status ) ) {
			global $wpdb;

			$model = new RTMediaModel();

			if ( 'public' !== $group->status ) {
				// when group settings are updated and is private/hidden, set media privacy to 20
				$update_sql = $wpdb->prepare( "UPDATE {$model->table_name} SET privacy = '20' where context='group' AND context_id=%d AND privacy <> 80 ", $group_id ); // @codingStandardsIgnoreLine
			} else {
				// when group settings are updated and is private/hidden, set media privacy to 0
				$update_sql = $wpdb->prepare( "UPDATE {$model->table_name} SET privacy = '0' where context='group' AND context_id=%d AND privacy <> 80 ", $group_id ); // @codingStandardsIgnoreLine
			}

			//update the medias
			$wpdb->query( $update_sql ); // @codingStandardsIgnoreLine
		}
	}

}

add_action( 'groups_settings_updated', 'update_group_media_privacy', 99, 1 );

/**
 * Function for no-popup class for rtmedia media gallery
 *
 * @param       string      $class
 *
 * @return      string
 */
function rtmedia_add_no_popup_class( $class = '' ) {

	return $class .= ' no-popup';

}

/**
 * This function is used in RTMediaQuery.php file for show title filter
 *
 * @param       bool    $flag
 *
 * @return      bool
 */
function rtmedia_gallery_do_not_show_media_title( $flag ) {

	return false;

}

/**
 * Remove all the shortcode related hooks that we had added in RTMediaQuery.php file after gallery is loaded
 */
function rtmedia_remove_media_query_hooks_after_gallery() {

	remove_filter( 'rtmedia_gallery_list_item_a_class', 'rtmedia_add_no_popup_class', 10, 1 );
	remove_filter( 'rtmedia_media_gallery_show_media_title', 'rtmedia_gallery_do_not_show_media_title', 10, 1 );

}

add_action( 'rtmedia_after_media_gallery', 'rtmedia_remove_media_query_hooks_after_gallery' );

/**
 * Sanitize media file name before uploading
 *
 * @param       string      $filename
 *
 * @return      string
 */
function sanitize_filename_before_upload( $filename ) {

	$info          	 = pathinfo( $filename );
	$ext             = empty( $info['extension'] ) ? '' : '.' . $info['extension'];
	$name            = basename( $filename, $ext );
	$final_file_name = $name;
	$special_chars   = array( '?', '[', ']', '/', '\\', '=', '<', '>', ':', ';', ',', "'", '"', '&', '$', '#', '*', '(', ')', '|', '~', '`', '!', '{', '}', chr( 0 ) );
	$special_chars   = apply_filters( 'sanitize_file_name_chars', $special_chars, $final_file_name );
	$string          = str_replace( $special_chars, '-', $final_file_name );
	$string          = preg_replace( '/\+/', '', $string );

	return remove_accents( $string ) . $ext;

}

/**
 * Removing special characters and replacing accent characters with ASCII characters in filename before upload to server
 */
function rtmedia_upload_sanitize_filename_before_upload() {

	add_action( 'sanitize_file_name', 'sanitize_filename_before_upload', 10, 1 );

}

add_action( 'rtmedia_upload_set_post_object', 'rtmedia_upload_sanitize_filename_before_upload', 10 );

/**
 * Admin pages content
 *
 * @param       string      $page
 */
function rtmedia_admin_pages_content( $page ) {

	if ( 'rtmedia-hire-us' === $page ) {
		?>
		<div class="rtm-hire-us-container rtm-page-container">
			<h3 class="rtm-setting-title rtm-show"><?php esc_html_e( 'You can consider rtMedia Team for following :', 'buddypress-media' ); ?></h3>
			<ol class="rtm-hire-points">
				<li><?php esc_html_e( 'rtMedia Customization ( in Upgrade Safe manner )', 'buddypress-media' ); ?></li>
				<li><?php esc_html_e( 'WordPress/BuddyPress Theme Design and Development', 'buddypress-media' ); ?></li>
				<li><?php esc_html_e( 'WordPress/BuddyPress Plugin Development', 'buddypress-media' ); ?></li>
			</ol>
			<div class="clearfix">
				<a href="https://rtmedia.io/contact" class="rtm-button rtm-success" target="_blank"><?php esc_html_e( 'Contact Us', 'buddypress-media' ); ?></a>
			</div>
		</div>
		<?php
	}

}

add_action( 'rtmedia_admin_page_insert', 'rtmedia_admin_pages_content', 99, 1 );

/**
 * Adds delete nonce for all template file before tempalte load
 */
function rtmedia_add_media_delete_nonce() {

	wp_nonce_field( 'rtmedia_' . get_current_user_id(), 'rtmedia_media_delete_nonce' );

}

add_action( 'rtmedia_before_template_load', 'rtmedia_add_media_delete_nonce' );

/**
 * 'rtmedia_before_template_load' will not fire for gallery shortcode
 * To add delete nonce in gallery shortcode use rtmedia_pre_template hook
 *
 * Adds delete nonce for gallery shortcode
 *
 * @global      RTMediaQuery    $rtmedia_query
 */
function rtmedia_add_media_delete_nonce_shortcode() {

	global $rtmedia_query;

	if ( isset( $rtmedia_query->is_gallery_shortcode ) && true === $rtmedia_query->is_gallery_shortcode ) {
		wp_nonce_field( 'rtmedia_' . get_current_user_id(), 'rtmedia_media_delete_nonce' );
	}

}
add_action( 'rtmedia_pre_template', 'rtmedia_add_media_delete_nonce_shortcode' );

/**
 * add function to display pagination on single media page with add_filter
 * By: Yahil
 */

if ( ! function_exists( 'rtmedia_single_media_pagination' ) ) {
	function rtmedia_single_media_pagination() {
		$disable = apply_filters( 'rtmedia_single_media_pagination', false );
		if ( true === $disable ) {
			return;
		}
		if ( rtmedia_id() ) {
			$model = new RTMediaModel();

			$media = $model->get_media( array(
				'id'	=> rtmedia_id(),
			), 0, 1 );

			if ( 'profile' == $media[0]->context ) {
				$media = $model->get_media( array(
					'media_author'	=> $media[0]->media_author,
					'context'		=> $media[0]->context,
				) );
			} else if ( 'group' == $media[0]->context ) {
				$media = $model->get_media( array(
					'media_author'	=> $media[0]->media_author,
					'context'		=> $media[0]->context,
					'context_id'	=> $media[0]->context_id,
				) );
			}

			for ( $i = 0; $i < count( $media ); $i++ ) {
				if ( rtmedia_id() == $media[ $i ]->id ) {
					if ( 0 != $i ) {
						$previous = $media[ $i - 1 ]->id;
					}
					if ( count( $media ) != $i + 1 ) {
						$next = $media[ $i + 1 ]->id;
					}
					break;
				}
			}
		}

		$html = '';
		if ( isset( $previous ) && $previous ) {
			$html .= '<div class="previous-pagination"><a href="' . esc_url( get_rtmedia_permalink( $previous ) ) . '" title="' . esc_html__( 'previous', 'buddypress-media' ) . '">' . esc_html__( 'previous', 'buddypress-media' ) . '</a></div>';
		}
		if ( isset( $next ) && $next ) {
			$html .= '<div class="next-pagination"><a href="' . esc_url( get_rtmedia_permalink( $next ) ) . '" title="' . esc_html__( 'next media', 'buddypress-media' ) . '">' . esc_html__( 'next', 'buddypress-media' ) . '</a></div>';
		}
		echo $html; // @codingStandardsIgnoreLine
	}
}

/**
 * @param $album_id
 *
 * @return array
 */
function rtm_get_album_media_count( $album_id ) {
	global $rtmedia_query;

	$args = array();
	if ( isset( $album_id ) && $album_id ) {
		$args['album_id'] = $album_id;
	}
	if ( isset( $rtmedia_query->query['context'] ) && $rtmedia_query->query['context'] ) {
		$args['context'] = $rtmedia_query->query['context'];
	}
	if ( isset( $rtmedia_query->query['context_id'] ) && $rtmedia_query->query['context_id'] ) {
		$args['context_id'] = $rtmedia_query->query['context_id'];
	}

	$rtmedia_model = new RTMediaModel();
	if ( $args ) {
		$count = $rtmedia_model->get( $args, false, false, 'media_id desc', true );
	}
	return $count;
}

/**
 * HTML markup for displaying Media Count of album in album list gallery
 */
function rtm_album_media_count() {
	?>
	<div class="rtmedia-album-media-count" title="<?php echo rtm_get_album_media_count( rtmedia_id() ) . ' ' . RTMEDIA_MEDIA_LABEL; ?>"><?php echo rtm_get_album_media_count( rtmedia_id() ); ?></div>
	<?php
}

add_action( 'rtmedia_after_album_gallery_item', 'rtm_album_media_count' );

/**
 * Get the information ( status, expiry date ) of all the installed addons and store in site option
 */
function rt_check_addon_status() {
	$addons = apply_filters( 'rtmedia_license_tabs', array() );

	if ( empty( $addons ) ) {
		return;
	}

	foreach ( $addons as $addon ) {
		if ( ! empty( $addon['args']['license_key'] ) && ! empty( $addon['name'] ) && ! empty( $addon['args']['addon_id'] ) ) {

			$license = $addon['args']['license_key'];

			$addon_name = $addon['name'];

			$addon_id = $addon['args']['addon_id'];

			$addon_active = get_option( 'edd_' . $addon_id . '_active' );

			// listen for activate button to be clicked

			/**
			 * Check if information about the addon in already fetched from the store
			 * If it's already fetched, then don't send the request again for the information
			 */
			if ( ! empty( $addon_active ) && ! isset( $_POST[ 'edd_' . $addon_id . '_license_activate' ] ) ) {
				continue;
			}

			// Get the store URL from the constant defined in the addon
			$store_url = constant( 'EDD_' . strtoupper( $addon_id ) . '_STORE_URL' );

			// If store URL not found in the addon, use the default store URL
			if ( empty( $store_url ) ) {
				$store_url = 'https://rtmedia.io/';
			}

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( $addon_name ), // the name of our product in EDD
				'url'        => home_url(),
			);

	        // Call the custom API.
			$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, $store_url ) ), array( 'timeout' => 15, 'sslverify' => false ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// Store the data in database
			update_option( 'edd_' . $addon_id . '_active', $license_data );
		}// End if().
	}// End foreach().
}

add_action( 'admin_init', 'rt_check_addon_status' );
