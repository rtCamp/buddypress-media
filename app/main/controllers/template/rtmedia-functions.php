<?php

/**
 * Checks at any point of time any media is left to be processed in the db pool
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      bool
 */
function have_rtmedia() {

	global $rtmedia_query;

	return $rtmedia_query->have_media();

}

/**
 * Rewinds the db pool of media album and resets it to beginning
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      bool
 */
function rewind_rtmedia() {

	global $rtmedia_query;

	return $rtmedia_query->rewind_media();

}

/**
 * moves ahead in the loop of media within the album
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      object
 */
function rtmedia() {

	global $rtmedia_query;

	return $rtmedia_query->rtmedia();

}

/**
 * echo the title of the media
 *
 * @global      array       $rtmedia_backbone
 * @global      object      $rtmedia_media
 *
 * @return      string
 */
function rtmedia_title() {

	global $rtmedia_backbone;

	if ( $rtmedia_backbone['backbone'] ) {
		echo '<%= media_title %>';
	} else {
		global $rtmedia_media;

		return stripslashes( esc_html( $rtmedia_media->media_title ) );
	}

}

/**
 * echo the album name of the media
 *
 * @global      object          $rtmedia_media
 *
 * @return      bool/string
 */
function rtmedia_album_name() {

	global $rtmedia_media;

	if ( $rtmedia_media->album_id ) {
		if ( 'album' === rtmedia_type( $rtmedia_media->album_id ) ) {
			return get_rtmedia_title( $rtmedia_media->album_id );
		} else {
			return false;
		}
	} else {
		return false;
	}

}

/**
 * Get Title for RTMedia Gallery
 *
 * @global      RTMediaQuery    $rtmedia_query
 * @global      RTMedia         $rtmedia
 *
 * @return bool|string
 */
function get_rtmedia_gallery_title() {

	global $rtmedia_query, $rtmedia;

	$title = false;

	if ( isset( $rtmedia_query->query['media_type'] ) && 'album' === $rtmedia_query->query['media_type'] && isset( $rtmedia_query->media_query['album_id'] ) && '' !== $rtmedia_query->media_query['album_id'] ) {
		$id    = $rtmedia_query->media_query['album_id'];
		$title = get_rtmedia_title( $id );
	} elseif ( isset( $rtmedia_query->media_query['media_type'] ) && ! is_array( $rtmedia_query->media_query['media_type'] ) && '' !== $rtmedia_query->media_query['media_type'] ) {
		$current_media_type = $rtmedia_query->media_query['media_type'];

		if ( ! empty( $current_media_type ) && is_array( $rtmedia->allowed_types ) && isset( $rtmedia->allowed_types[ $current_media_type ] ) && is_array( $rtmedia->allowed_types[ $current_media_type ] ) && isset( $rtmedia->allowed_types[ $current_media_type ]['plural_label'] ) ) {
			$title = sprintf( '%s %s', esc_html__( 'All', 'buddypress-media' ), $rtmedia->allowed_types[ $current_media_type ]['plural_label'] );
		}
	}

	$title = apply_filters( 'rtmedia_gallery_title', $title );

	return $title;

}

/**
 * Get media title using media ID
 *
 * @param       int         $id     media id
 *
 * @return      string
 */
function get_rtmedia_title( $id ) {

	$rtmedia_model = new RTMediaModel();
	$title         = $rtmedia_model->get( array(
		'id' => $id,
	) );

	return $title[0]->media_title;

}

/**
 * Media author's profile pic
 *
 * @global      array       $rtmedia_backbone
 * @global      object      $rtmedia_media
 *
 * @param       bool        $show_link
 * @param       bool        $echo
 * @param       bool        $author_id
 *
 * @return      string
 */
function rtmedia_author_profile_pic( $show_link = true, $echo = true, $author_id = false ) {

	global $rtmedia_backbone;

	if ( $rtmedia_backbone['backbone'] ) {
		echo '';
	} else {
		if ( empty( $author_id ) ) {
			global $rtmedia_media;

			$author_id = $rtmedia_media->media_author;
		}

		$show_link   = apply_filters( 'rtmedia_single_media_show_profile_picture_link', $show_link );
		$profile_pic = '';

		if ( $show_link ) {
			$profile_pic .= "<a href='" . esc_url( get_rtmedia_user_link( $author_id ) ) . "' title='" . esc_attr( rtmedia_get_author_name( $author_id ) ) . "'>";
		}

		$size = apply_filters( 'rtmedia_single_media_profile_picture_size', 90 );

		if ( function_exists( 'bp_get_user_has_avatar' ) ) {
			if ( bp_core_fetch_avatar( array(
					'item_id' => $author_id,
					'object'  => 'user',
					'no_grav' => false,
					'html'    => false,
			) ) !== bp_core_avatar_default()
			) {
				$profile_pic .= bp_core_fetch_avatar( array(
					'item_id' => $author_id,
					'object'  => 'user',
					'no_grav' => false,
					'html'    => true,
					'width'   => $size,
					'height'  => $size,
				) );
			} else {
				$profile_pic .= "<img src='" . esc_url( bp_core_avatar_default() ) . "' width='" . esc_attr( $size ) . "'  height='" . esc_attr( $size ) . "' />";
			}
		} else {
			$profile_pic .= get_avatar( $author_id, $size );
		}

		if ( $show_link ) {
			$profile_pic .= '</a>';
		}

		if ( $echo ) {
			echo $profile_pic; // @codingStandardsIgnoreLine
		} else {
			return $profile_pic;
		}
	}// End if().

}

/**
 * Media's author link
 *
 * @global      array       $rtmedia_backbone
 * @global      object      $rtmedia_media
 *
 * @param       bool        $show_link
 */
function rtmedia_author_name( $show_link = true ) {

	global $rtmedia_backbone;

	if ( $rtmedia_backbone['backbone'] ) {
		echo apply_filters( 'rtmedia_media_author_backbone', '', $show_link ); // @codingStandardsIgnoreLine
	} else {
		global $rtmedia_media;

		$show_link = apply_filters( 'rtmedia_single_media_show_profile_name_link', $show_link );

		if ( $show_link ) {
			echo "<a href='" . esc_url( get_rtmedia_user_link( $rtmedia_media->media_author ) ) . "' title='" . esc_attr( rtmedia_get_author_name( $rtmedia_media->media_author ) ) . "'>";
		}

		echo esc_html( rtmedia_get_author_name( $rtmedia_media->media_author ) );

		if ( $show_link ) {
			echo '</a>';
		}
	}

}

/**
 * Get media author name using user ID
 *
 * @param       $user_id
 *
 * @return      string
 */
function rtmedia_get_author_name( $user_id ) {
	if ( function_exists( 'bp_core_get_user_displayname' ) ) {
		return bp_core_get_user_displayname( $user_id );
	} else {
		$user = get_userdata( $user_id );

		if ( $user ) {
			return $user->display_name;
		}
	}

}

/**
 * Media Gallery CSS classes
 *
 * @global      RTMediaQuery    $rtmedia_query
 */
function rtmedia_media_gallery_class() {

	global $rtmedia_query;

	$classes = '';

	if ( isset( $rtmedia_query->media_query ) && isset( $rtmedia_query->media_query['context_id'] ) ) {
		$classes = 'context-id-' . esc_attr( $rtmedia_query->media_query['context_id'] );
	}

	echo esc_attr( apply_filters( 'rtmedia_gallery_class_filter', $classes ) );
}

/**
 * Get RTMedia ID using Post(Media) ID
 *
 * @global      array           $rtmedia_backbone
 * @global      object          $rtmedia_media
 *
 * @param       bool|int            $media_id
 *
 * @return      bool|string
 */
function rtmedia_id( $media_id = false ) {

	global $rtmedia_backbone;

	if ( $rtmedia_backbone['backbone'] ) {
		return '<%= id %>';
	}

	if ( $media_id ) {
		$model = new RTMediaModel();
		$media = $model->get_media( array(
			'media_id' => $media_id,
		), 0, 1 );

		if ( isset( $media ) && count( $media ) > 0 ) {
			return $media[0]->id;
		}

		return false;
	} else {
		global $rtmedia_media;

		return $rtmedia_media->id;
	}

}

/**
 * Get Post(Media) ID using RTMedia ID
 *
 * @global      object      $rtmedia_media
 *
 * @param       bool|int    $id
 *
 * @return      int
 */
function rtmedia_media_id( $id = false ) {

	if ( $id ) {
		$model = new RTMediaModel();
		$media = $model->get_media( array(
			'id' => $id,
		), 0, 1 );

		return $media[0]->media_id;
	} else {
		global $rtmedia_media;

		return $rtmedia_media->media_id;
	}

}

/**
 * Get Media extension using ID
 *
 * @global      object      $rtmedia_media
 *
 * @param       bool|int    $id
 *
 * @return      string
 */
function rtmedia_media_ext( $id = false ) {

	if ( $id ) {
		$model = new RTMediaModel();
		$media = $model->get_media( array(
			'id' => $id,
		), 0, 1 );

		if ( isset( $media[0] ) ) {
			$filepath = get_attached_file( $media[0]->media_id );
			$filetype = wp_check_filetype( $filepath );

			return $filetype['ext'];
		}
	} else {
		global $rtmedia_media;

		$filepath = get_attached_file( $rtmedia_media->media_id );
		$filetype = wp_check_filetype( $filepath );

		return $filetype['ext'];
	}

}

/**
 * Get Activity ID using Media ID
 *
 * @global      object      $rtmedia_media
 *
 * @param       bool|int    $id
 *
 * @return      int
 */
function rtmedia_activity_id( $id = false ) {

	if ( $id ) {
		$model = new RTMediaModel();
		$media = $model->get_media( array(
			'id' => $id,
		), 0, 1 );

		return $media[0]->activity_id;
	} else {
		global $rtmedia_media;

		return $rtmedia_media->activity_id;
	}

}

/**
 * Get Media type using Media ID
 *
 * @global      object          $rtmedia_media
 *
 * @param       bool|int        $id
 *
 * @return      bool|string
 */
function rtmedia_type( $id = false ) {

	if ( $id ) {
		$model = new RTMediaModel();
		$media = $model->get_media( array(
			'id' => $id,
		), 0, 1 );

		if ( isset( $media[0] ) && isset( $media[0]->media_type ) ) {
			return $media[0]->media_type;
		} else {
			return false;
		}
	} else {
		global $rtmedia_media;

		return $rtmedia_media->media_type;
	}

}

/**
 * Get cover art using Media ID
 *
 * @global      object          $rtmedia_media
 *
 * @param       bool|int        $id
 *
 * @return      string
 */
function rtmedia_cover_art( $id = false ) {

	if ( $id ) {
		$model = new RTMediaModel();
		$media = $model->get_media( array(
			'id' => $id,
		), 0, 1 );

		return $media[0]->cover_art;
	} else {
		global $rtmedia_media;

		return $rtmedia_media->cover_art;
	}

}

/**
 * echo parmalink of the media
 *
 * @global      array           $rtmedia_backbone
 *
 * @param       bool|int        $media_id
 */
function rtmedia_permalink( $media_id = false ) {

	global $rtmedia_backbone;

	if ( $rtmedia_backbone['backbone'] ) {
		echo '<%= rt_permalink %>';
	} else {
		echo esc_url( get_rtmedia_permalink( rtmedia_id( $media_id ) ) );
	}

}

/**
 * echo parmalink of the album
 *
 * @global      object      $rtmedia_media
 */
function rtmedia_album_permalink() {

	global $rtmedia_media;

	echo esc_url( get_rtmedia_permalink( $rtmedia_media->album_id ) );

}

/**
 * Get media
 *
 * @global      object          $rtmedia_media
 * @global      RTMedia         $rtmedia
 *
 * @param       bool            $size_flag
 * @param       bool            $echo
 * @param       string          $media_size
 *
 * @return      bool|string
 */
function rtmedia_media( $size_flag = true, $echo = true, $media_size = 'rt_media_single_image' ) {

	$size_flag = true;

	global $rtmedia_media, $rtmedia;

	if ( isset( $rtmedia_media->media_type ) ) {
		if ( 'photo' === $rtmedia_media->media_type ) {
			$src  = wp_get_attachment_image_src( $rtmedia_media->media_id, $media_size );
			$html = "<img src='" . esc_url( $src[0] ) . "' alt='" . esc_attr( $rtmedia_media->post_name ) . "' />";
		} elseif ( 'video' === $rtmedia_media->media_type ) {
			$height = $rtmedia->options['defaultSizes_video_singlePlayer_height'];
			$height = ( $height * 75 ) / 640;
			$size   = ' width="' . esc_attr( $rtmedia->options['defaultSizes_video_singlePlayer_width'] ) . '" height="' . esc_attr( $height ) . '%" ';
			$html   = "<div id='rtm-mejs-video-container' style='width:" . esc_attr( $rtmedia->options['defaultSizes_video_singlePlayer_width'] ) . 'px;height:' . esc_attr( $height ) . "%;  max-width:96%;max-height:80%;'>";
			$html   .= '<video poster="" src="' . esc_url( wp_get_attachment_url( $rtmedia_media->media_id ) ) . '" ' . esc_attr( $size ) . ' type="video/mp4" class="wp-video-shortcode" id="bp_media_video_' . esc_attr( $rtmedia_media->id ) . '" controls="controls" preload="true"></video>';
			$html   .= '</div>';
		} elseif ( 'music' === $rtmedia_media->media_type ) {
			$width = $rtmedia->options['defaultSizes_music_singlePlayer_width'];
			$width = ( $width * 75 ) / 640;
			$size  = ' width= ' . esc_attr( $width ) . '% height=30 ';

			if ( ! $size_flag ) {
				$size = '';
			}

			$html = '<audio src="' . esc_url( wp_get_attachment_url( $rtmedia_media->media_id ) ) . '" ' . esc_attr( $size ) . ' type="audio/mp3" class="wp-audio-shortcode" id="bp_media_audio_' . esc_attr( $rtmedia_media->id ) . '" controls="controls" preload="none"></audio>';
		} else {
			$html = false;
		}
	} else {
		$html = false;
	}

	do_action( 'rtmedia_after_' . $rtmedia_media->media_type, $rtmedia_media->id );

	$html = apply_filters( 'rtmedia_single_content_filter', $html, $rtmedia_media );

	if ( $echo ) {
		echo $html; // @codingStandardsIgnoreLine
	} else {
		return $html;
	}

}

/**
 * Get media src
 *
 * @global      array                   $rtmedia_backbone
 * @global      object                  $rtmedia_media
 * @global      RTMedia                 $rtmedia
 *
 * @param       string                  $size
 * @param       bool|int                $id
 * @param       bool                    $recho
 *
 * @return      bool|int|string|void
 */
function rtmedia_image( $size = 'rt_media_thumbnail', $id = false, $recho = true ) {

	global $rtmedia_backbone;

	if ( $rtmedia_backbone['backbone'] ) {
		echo '<%= guid %>';

		return;
	}

	if ( $id ) {
		$model = new RTMediaModel();
		$media = $model->get_media( array(
			'id' => $id,
		), false, false );

		if ( isset( $media[0] ) ) {
			$media_object = $media[0];
		} else {
			return false;
		}
	} else {
		global $rtmedia_media;

		$media_object = $rtmedia_media;
	}

	$thumbnail_id = 0;

	if ( isset( $media_object->media_type ) ) {
		if ( 'album' === $media_object->media_type || 'photo' !== $media_object->media_type || 'video' === $media_object->media_type ) {
			$thumbnail_id = ( isset( $media_object->cover_art )
			                  && ( ( false !== filter_var( $media_object->cover_art, FILTER_VALIDATE_URL ) )   // Cover art might be an absolute URL
				                  || ( 0 !== intval( $media_object->cover_art ) )    // Cover art might be a media ID
			                  ) ) ? $media_object->cover_art : false;
			$thumbnail_id = apply_filters( 'show_custom_album_cover', $thumbnail_id, $media_object->media_type, $media_object->id ); // for rtMedia pro users
		} elseif ( 'photo' === $media_object->media_type ) {
			$thumbnail_id = $media_object->media_id;
		} else {
			$thumbnail_id = false;
		}

		if ( 'music' === $media_object->media_type && empty( $thumbnail_id ) ) {
			$thumbnail_id = rtm_get_music_cover_art( $media_object );
		}

		if ( 'music' === $media_object->media_type && -1 === intval( $thumbnail_id ) ) {
			$thumbnail_id = false;
		}
	}

	if ( ! $thumbnail_id ) {
		global $rtmedia;

		// Getting the extension of the uploaded file
		$extension = rtmedia_get_extension();

		// Checking if custom thumbnail for this file extension is set or not
		if ( isset( $rtmedia->allowed_types[ $media_object->media_type ] ) && isset( $rtmedia->allowed_types[ $media_object->media_type ]['ext_thumb'] ) && isset( $rtmedia->allowed_types[ $media_object->media_type ]['ext_thumb'][ $extension ] ) ) {
			$src = $rtmedia->allowed_types[ $media_object->media_type ]['ext_thumb'][ $extension ];
		} else if ( isset( $rtmedia->allowed_types[ $media_object->media_type ] ) && isset( $rtmedia->allowed_types[ $media_object->media_type ]['thumbnail'] ) ) {
			$src = $rtmedia->allowed_types[ $media_object->media_type ]['thumbnail'];
		} elseif ( 'album' === $media_object->media_type ) {
			$src = rtmedia_album_image( $size, $id );
		} else {
			$src = false;
		}
	} else {
		if ( is_numeric( $thumbnail_id ) && 0 !== intval( $thumbnail_id ) ) {
			list( $src, $width, $height ) = wp_get_attachment_image_src( $thumbnail_id, $size );
		} else {
			$src = $thumbnail_id;
		}
	}

	$src = apply_filters( 'rtmedia_media_thumb', $src, $media_object->id, $media_object->media_type );

	if ( true === $recho ) {
		echo esc_url( $src );
	} else {
		return $src;
	}

}

/**
 * Get media alt
 *
 * @global      object      $rtmedia_media
 *
 * @param       bool|int    $id
 * @param       bool        $echo
 *
 * @return      string
 */
function rtmedia_image_alt( $id = false, $echo = true ) {

	if ( $id ) {
		$model = new RTMediaModel();
		$media = $model->get_media( array(
			'id' => $id,
		), false, false );

		if ( isset( $media[0] ) ) {
			$media_object = $media[0];
		} else {
			return false;
		}

		$post_object = get_post( $media_object->media_id );

		if ( isset( $post_object->post_name ) ) {
			$img_alt = $post_object->post_name;
		} else {
			$img_alt = ' ';
		}
	} else {
		global $rtmedia_media;

		if ( isset( $rtmedia_media->post_name ) ) {
			$img_alt = $rtmedia_media->post_name;
		} else {
			$img_alt = ' ';
		}
	}

	if ( $echo ) {
		echo esc_attr( $img_alt );
	} else {
		return $img_alt;
	}

}

/**
 * Get album image
 *
 * @global      object          $rtmedia_media
 * @global      RTMediaQuery    $rtmedia_query
 * @global      RTMedia         $rtmedia
 *
 * @param       string          $size
 * @param       bool|int        $id
 *
 * @return      string
 */
function rtmedia_album_image( $size = 'thumbnail', $id = false ) {

	global $rtmedia_media, $rtmedia_query;

	if ( false === $id ) {
		$id = $rtmedia_media->id;
	}

	$model = new RTMediaModel();

	if ( isset( $rtmedia_query->query['context_id'] ) && isset( $rtmedia_query->query['context'] ) && 'group' !== $rtmedia_query->query['context'] ) {
		if ( 'profile' === $rtmedia_query->query['context'] ) {
			$media = $model->get_media( array(
				'album_id'     => $id,
				'media_type'   => 'photo',
				'media_author' => $rtmedia_query->query['context_id'],
				'context'      => 'profile',
				'context_id'   => $rtmedia_query->query['context_id'],
			), 0, 1 );
		} else {
			$media = $model->get_media( array(
				'album_id'     => $id,
				'media_type'   => 'photo',
				'media_author' => $rtmedia_query->query['context_id'],
			), 0, 1 );
		}
	} else {
		if ( isset( $rtmedia_query->query['context_id'] ) && isset( $rtmedia_query->query['context'] ) && 'group' === $rtmedia_query->query['context'] ) {
			$media = $model->get_media( array(
				'album_id'   => $id,
				'media_type' => 'photo',
				'context_id' => $rtmedia_query->query['context_id'],
			), 0, 1 );
		} else {
			$media = $model->get_media( array(
				'album_id'   => $id,
				'media_type' => 'photo',
			), 0, 1 );
		}
	}

	if ( $media ) {
		$src = rtmedia_image( $size, $media[0]->id, false );
	} else {
		global $rtmedia;

		$src = $rtmedia->allowed_types['photo']['thumbnail'];
	}

	return $src;

}

/**
 * Get duration for media
 *
 * @global      array       $rtmedia_backbone
 * @global      object      $rtmedia_media
 *
 * @param       bool|int    $id
 *
 * @return      array|bool|mixed|null|string|void
 */
function rtmedia_duration( $id = false ) {

	global $rtmedia_backbone;

	if ( $rtmedia_backbone['backbone'] ) {
		echo '<%= duration %>';

		return;
	}

	if ( $id ) {
		$model = new RTMediaModel();
		$media = $model->get_media( array(
			'id' => $id,
		), false, false );

		if ( isset( $media[0] ) ) {
			$media_object = $media[0];
		} else {
			return false;
		}
	} else {
		global $rtmedia_media;

		$media_object = $rtmedia_media;
	}

	$duration = '';

	if ( ( 'video' === $media_object->media_type ) || ( 'music' === $media_object->media_type ) ) {
		$media_time = get_rtmedia_meta( $media_object->id, 'duration_time' );

		if ( false === $media_time || empty( $media_time ) ) {
			$filepath   = get_attached_file( $media_object->media_id );
			$media_tags = new RTMediaTags( $filepath );
			$duration   = $media_tags->duration;

			add_rtmedia_meta( $media_object->id, 'duration_time', $duration );
		} else {
			$duration = $media_time;
		}

		$duration = str_replace( '-:--', '', $duration );
		$duration = '<span class="rtmedia_time" >' . esc_attr( $duration ) . '</span>';
	}

	return $duration;

}

/**
 * Sanitizing object
 *
 * @param       array       $data
 * @param       array       $exceptions
 *
 * @return      array
 */
function rtmedia_sanitize_object( $data, $exceptions = array() ) {

	foreach ( $data as $key => $value ) {
		if ( ! in_array( $key, array_merge( RTMediaMedia::$default_object, $exceptions ), true ) ) {
			unset( $data[ $key ] );
		}
	}

	return $data;

}

/**
 * Checking if delete media is allowed
 *
 * @global      object      $rtmedia_media
 *
 * @return      bool
 */
function rtmedia_delete_allowed() {

	global $rtmedia_media;

	$flag = intval( $rtmedia_media->media_author ) === get_current_user_id();

	if ( ! $flag && isset( $rtmedia_media->context ) && 'group' === $rtmedia_media->context && function_exists( 'bp_group_is_admin' ) ) {
		$flag = ( bp_group_is_admin() || bp_group_is_mod() );
	}

	if ( ! $flag ) {
		$flag = is_super_admin();
	}

	$flag = apply_filters( 'rtmedia_media_delete_priv', $flag );

	return $flag;

}

/**
 * Checking if edit media is allowed
 *
 * @global      object      $rtmedia_media
 *
 * @return      bool
 */
function rtmedia_edit_allowed() {

	global $rtmedia_media;

	$flag = intval( $rtmedia_media->media_author ) === get_current_user_id();

	if ( ! $flag ) {
		$flag = is_super_admin();
	}

	$flag = apply_filters( 'rtmedia_media_edit_priv', $flag );

	return $flag;

}

/**
 * Get media action like edit, delete
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      string
 */
function rtmedia_request_action() {

	global $rtmedia_query;

	return $rtmedia_query->action_query->action;

}

/**
 * Get text-box for editing media title
 *
 * @global      object      $rtmedia_media
 */
function rtmedia_title_input() {

	global $rtmedia_media;

	$name  = 'media_title';
	$value = stripslashes( esc_html( $rtmedia_media->media_title ) );
	$html  = '';

	if ( 'edit' === rtmedia_request_action() ) {
		$html .= '<input type="text" class="rtmedia-title-editor" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
	} else {
		$html .= '<h2 name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '">' . esc_html( $value ) . '</h2>';
	}

	$html .= '';

	echo $html; // @codingStandardsIgnoreLine

}

/**
 * Get text-area when editing media
 *
 * @global      object      $rtmedia_media
 *
 * @param       bool        $editor
 * @param       bool        $echo
 *
 * @return      string
 */
function rtmedia_description_input( $editor = true, $echo = false ) {

	global $rtmedia_media;

	$name = 'description';

	if ( isset( $rtmedia_media->post_content ) ) {
		$value = $rtmedia_media->post_content;
	} else {
		$post_details = get_post( $rtmedia_media->media_id );
		$value        = $post_details->post_content;
	}

	$html = '';

	if ( $editor ) {
		if ( 'edit' === rtmedia_request_action() ) {
			ob_start();
			wp_editor( $value, $name, array(
				'media_buttons' => false,
				'textarea_rows' => 2,
				'quicktags'     => false,
			) );

			$html .= ob_get_clean();
		} else {
			$html .= '<div name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '">' . wp_kses_post( $value ) . '</div>';
		}
	} else {
		$html .= "<textarea name='" . esc_attr( $name ) . "' id='" . esc_attr( $name ) . "' class='rtmedia-desc-textarea'>" . esc_textarea( $value ) . '</textarea>';
	}

	$html .= '';

	if ( $echo ) {
		echo $html; // @codingStandardsIgnoreLine
	} else {
		return $html;
	}

}

/**
 * echo media description
 *
 * @param       bool        $echo
 *
 * @return      string
 */
function rtmedia_description( $echo = true ) {

	if ( $echo ) {
		// escape description for any html tags and reformat using `wpautop`
		echo rtmedia_get_media_description();
	} else {
		return rtmedia_get_media_description();
	}

}

/**
 * Get media description
 *
 * @global      object      $rtmedia_media
 *
 * @param       bool        $id
 *
 * @return      string
 */
function rtmedia_get_media_description( $id = false ) {

	if ( $id ) {
		$media_post_id = rtmedia_media_id( $id );
	} else {
		global $rtmedia_media;

		$media_post_id = $rtmedia_media->media_id;
	}

	/**
	 * This function will mostly be used in single media page.
	 * We are showing single media page using `the_content` filter and uses dummy post.
	 * If we use `the_content` filter again than media description won't work as
	 * this is already singe media request and hence using `wpautop` instead.
	 */
	return wpautop( get_post_field( 'post_content', $media_post_id ) );

}

/**
 * Get total media count in the album
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      int
 */
function rtmedia_count() {

	global $rtmedia_query;

	return $rtmedia_query->media_count;

}

/**
 * Get the page offset for the media pool
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      int
 */
function rtmedia_offset() {

	global $rtmedia_query;

	return ( $rtmedia_query->action_query->page - 1 ) * $rtmedia_query->action_query->per_page_media;

}

/**
 * Get number of media per page to be displayed
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      int
 */
function rtmedia_per_page_media() {

	global $rtmedia_query;

	return $rtmedia_query->action_query->per_page_media;

}

/**
 * Get the page number of media album in the pagination
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      int
 */
function rtmedia_page() {

	global $rtmedia_query;

	return $rtmedia_query->action_query->page;

}

/**
 * Get the current media number in the album pool
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      string
 */
function rtmedia_current_media() {

	global $rtmedia_query;

	return $rtmedia_query->current_media;

}

/**
 * rtMedia edit form
 *
 * @return      bool|string
 */
function rtmedia_edit_form() {

	if ( is_user_logged_in() && rtmedia_edit_allowed() ) {
		$edit_button = '<button type="submit" class="rtmedia-edit rtmedia-action-buttons" >' . esc_html__( 'Edit', 'buddypress-media' ) . '</button>';
		$edit_button = apply_filters( 'rtmedia_edit_button_filter', $edit_button );
		$button      = '<form action="' . esc_url( get_rtmedia_permalink( rtmedia_id() ) ) . 'edit/">' . $edit_button . '</form>';

		return $button;
	}

	return false;

}

/**
 * list of actions might be performed on media
 */
function rtmedia_actions() {

	$actions = array();

	if ( is_user_logged_in() && rtmedia_edit_allowed() ) {
		$edit_button = '<button type="submit" class="rtmedia-edit rtmedia-action-buttons button" >' . esc_html__( 'Edit', 'buddypress-media' ) . '</button>';
		$edit_button = apply_filters( 'rtmedia_edit_button_filter', $edit_button );
		$actions[]   = '<form action="' . esc_url( get_rtmedia_permalink( rtmedia_id() ) ) . 'edit/">' . $edit_button . '</form>';
	}

	$actions = apply_filters( 'rtmedia_action_buttons_before_delete', $actions );

	foreach ( $actions as $action ) {
		echo $action; // @codingStandardsIgnoreLine
	}

	$actions = array();

	if ( rtmedia_delete_allowed() ) {
		$actions[] = rtmedia_delete_form( $echo = false );
	}

	$actions = apply_filters( 'rtmedia_action_buttons_after_delete', $actions );

	foreach ( $actions as $action ) {
		echo $action; // @codingStandardsIgnoreLine
	}

	do_action( 'after_rtmedia_action_buttons' );

}

/**
 * Rendering comments section
 *
 * @global      object      $rtmedia_media
 *
 * @param       bool        $echo
 *
 * @return      string
 */
function rtmedia_comments( $echo = true ) {

	global $rtmedia_media;

	$html         = '<ul id="rtmedia_comment_ul" class="rtm-comment-list" data-action="' . esc_url( get_rtmedia_permalink( rtmedia_id() ) ) . 'delete-comment/">';
	$comments     = get_comments( array(
		'post_id' => $rtmedia_media->media_id,
		'order'   => 'ASC',
	) );
	$comment_list = '';
	$count = count( $comments );
	$i = 0;

	foreach ( $comments as $comment ) {
		$comment_list .= rmedia_single_comment( (array) $comment, $count, $i );
		$i++;
	}

	if ( ! empty( $comment_list ) ) {
		$html .= $comment_list;
	} else {
		$html .= "<li id='rtmedia-no-comments' class='rtmedia-no-comments'>" . apply_filters( 'rtmedia_single_media_no_comment_messege', esc_html__( 'There are no comments on this media yet.', 'buddypress-media' ) ) . '</li>';
	}

	$html .= '</ul>';

	if ( $html ) {
		echo $html; // @codingStandardsIgnoreLine
	} else {
		return $html;
	}

}

/**
 * Render single comment,
 * And display show all comment link to display all comment
 * @param  [array] $comment [comment]
 * @param  [int] $count   [default false other ways comment count]
 * @param  [int] $i       [default false other ways increment with loop]
 * By: Yahil
 */
function rmedia_single_comment( $comment, $count = false, $i = false ) {

	$html = '';
	$class = '';
	if ( isset( $count ) && $count ) {
		$hide = $count - 5;
		if ( $i < $hide ) {
			$class = 'hide';
			if ( 0 == $i ) {
				echo '<div class="rtmedia-like-info"><span id="rtmedia_show_all_comment"> ' . esc_html( 'Show all ' . $count . ' comments', 'rtmedia' ) . ' </span></div>';
			}
		}
	}
	global $allowedtags, $rtmedia_media;

	$html .= '<li class="rtmedia-comment ' . $class . ' ">';

	if ( $comment['user_id'] ) {
		$user_link   = "<a href='" . esc_url( get_rtmedia_user_link( $comment['user_id'] ) ) . "' title='" . esc_attr( rtmedia_get_author_name( $comment['user_id'] ) ) . "'>" . esc_html( rtmedia_get_author_name( $comment['user_id'] ) ) . '</a>';
		$user_name   = apply_filters( 'rtmedia_comment_author_name', $user_link, $comment );
		$profile_pic = rtmedia_author_profile_pic( $show_link = true, $echo = false, $comment['user_id'] );
	} else {
		$user_name   = 'Annonymous';
		$profile_pic = '';
	}

	if ( ! empty( $profile_pic ) ) {
		$html .= "<div class='rtmedia-comment-user-pic cleafix'>" . $profile_pic . '</div>';
	}

	$html .= "<div class='rtm-comment-wrap'><div class='rtmedia-comment-details'>";
	$html .= '<span class ="rtmedia-comment-author">' . $user_name . '</span>';
	$html .= '<span class ="rtmedia-comment-date"> ' . apply_filters( 'rtmedia_comment_date_format', rtmedia_convert_date( $comment['comment_date_gmt'] ), $comment ) . '</span>';

	$comment_content = $comment['comment_content'];
	$activity_comment_content = get_comment_meta( $comment['comment_ID'], 'activity_comment_content', true );
	if ( empty( $activity_comment_content ) ) {
		$activity_id = (int) get_comment_meta( $comment['comment_ID'], 'activity_id', true );
		if ( $activity_id ) {
			$rtmedia_activity_comment = rtmedia_activity_comment( $activity_id );
			if ( $rtmedia_activity_comment['content'] ) {
				$comment_content = $rtmedia_activity_comment['content'];
				update_comment_meta( $comment['comment_ID'], 'activity_comment_content', $rtmedia_activity_comment['content'] );
			}
		}
	} else {
		$comment_content = $activity_comment_content;
	}

	$comment_string = wp_kses( $comment_content, $allowedtags );

	$html .= '<div class="rtmedia-comment-content">' . wpautop( make_clickable( apply_filters( 'bp_get_activity_content', $comment_string ) ) ) . '</div>';
	$html .= '<div class="rtmedia-comment-extra">' . apply_filters( 'rtmedia_comment_extra', '', $comment ) . '</div>';

	if ( is_rt_admin() || ( isset( $comment['user_id'] ) && ( get_current_user_id() === intval( $comment['user_id'] ) || intval( $rtmedia_media->media_author ) === get_current_user_id() ) ) || apply_filters( 'rtmedia_allow_comment_delete', false ) ) { // show delete button for comment author and admins
		$html .= '<i data-id="' . esc_attr( $comment['comment_ID'] ) . '" class = "rtmedia-delete-comment dashicons dashicons-no-alt rtmicon" title="' . esc_attr__( 'Delete Comment', 'buddypress-media' ) . '"></i>';
	}

	$html .= '<div class="clear"></div></div></div></li>';

	return apply_filters( 'rtmedia_single_comment', $html, $comment );

}



/**
 * Get media comment count using media ID
 *
 * @global      wpdb        $wpdb
 * @global      object      $rtmedia_media
 *
 * @param       bool|int    $media_id
 *
 * @return      int
 */
function rtmedia_get_media_comment_count( $media_id = false ) {

	global $wpdb, $rtmedia_media;

	if ( ! $media_id ) {
		$post_id = $rtmedia_media->media_id;
	} else {
		$post_id = rtmedia_media_id( $media_id );
	}

	$query         = $wpdb->prepare( "SELECT count(*) FROM $wpdb->comments WHERE comment_post_ID = %d", $post_id );
	$comment_count = $wpdb->get_results( $query, ARRAY_N ); // @codingStandardsIgnoreLine

	if ( is_array( $comment_count ) && is_array( $comment_count[0] ) && isset( $comment_count[0][0] ) ) {
		return $comment_count[0][0];
	} else {
		return 0;
	}

}

/**
 * Get previous media link
 *
 * @global      object          $rtmedia_interaction
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      string
 */
function rtmedia_pagination_prev_link() {

	global $rtmedia_interaction, $rtmedia_query;

	$page_url    = ( ( rtmedia_page() - 1 ) === 1 ) ? '' : 'pg/' . ( rtmedia_page() - 1 );
	$site_url    = ( is_multisite() ) ? trailingslashit( get_site_url( get_current_blog_id() ) ) : trailingslashit( get_site_url() );
	$author_name = get_query_var( 'author_name' );
	$link        = '';

	if ( $rtmedia_interaction && isset( $rtmedia_interaction->context ) && 'profile' === $rtmedia_interaction->context->type ) {
		if ( function_exists( 'bp_core_get_user_domain' ) ) {
			$link .= trailingslashit( bp_core_get_user_domain( $rtmedia_query->media_query['media_author'] ) );
		} else {
			$link = $site_url . 'author/' . $author_name . '/';
		}
	} else {
		if ( $rtmedia_interaction && isset( $rtmedia_interaction->context ) && 'group' === $rtmedia_interaction->context->type ) {
			if ( function_exists( 'bp_get_current_group_slug' ) ) {
				$link .= $site_url . bp_get_groups_root_slug() . '/' . bp_get_current_group_slug() . '/';
			}
		} else {
			$post = get_post( get_post_field( 'post_parent', $rtmedia_query->media->media_id ) );
			$link .= $site_url . $post->post_name . '/';
		}
	}

	$link .= RTMEDIA_MEDIA_SLUG . '/';

	if ( isset( $rtmedia_query->action_query->media_type ) ) {
		$media_type_array = array( 'photo', 'music', 'video', 'album', 'playlist' );

		if ( in_array( $rtmedia_query->action_query->media_type, $media_type_array, true ) ) {
			$link .= $rtmedia_query->action_query->media_type . '/';
		}
	}

	return apply_filters( 'rtmedia_pagination_prev_link', $link . $page_url, $link, $page_url );

}

/**
 * Get next media link
 *
 * @global      object          $rtmedia_interaction
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      string
 */
function rtmedia_pagination_next_link() {

	global $rtmedia_interaction, $rtmedia_query;

	$page_url    = 'pg/' . ( rtmedia_page() + 1 );
	$site_url    = ( is_multisite() ) ? trailingslashit( get_site_url( get_current_blog_id() ) ) : trailingslashit( get_site_url() );
	$author_name = get_query_var( 'author_name' );
	$link        = '';

	if ( $rtmedia_interaction && isset( $rtmedia_interaction->context ) && 'profile' === $rtmedia_interaction->context->type ) {
		if ( function_exists( 'bp_core_get_user_domain' ) ) {
			if ( isset( $rtmedia_query->media_query['context'] ) && 'profile' === $rtmedia_query->media_query['context'] && isset( $rtmedia_query->media_query['context_id'] ) ) {
				$user_id = $rtmedia_query->media_query['context_id'];
			} else if ( isset( $rtmedia_query->media_query['media_author'] ) ) {
				$user_id = $rtmedia_query->media_query['media_author'];
			} else {
				$user_id = bp_displayed_user_id();
			}

			$link .= trailingslashit( bp_core_get_user_domain( $user_id ) );
		} else {
			$link .= $site_url . 'author/' . $author_name . '/';
		}
	} else {
		if ( $rtmedia_interaction && isset( $rtmedia_interaction->context ) && 'group' === $rtmedia_interaction->context->type ) {
			if ( function_exists( 'bp_get_current_group_slug' ) ) {
				$link .= $site_url . bp_get_groups_root_slug() . '/' . bp_get_current_group_slug() . '/';
			}
		} else {
			// if there are more media than number of media per page to show than $rtmedia_query->media->media_id will be set other wise take media_id of very first media
			// For more understanding why array became object check rewind_media() in RTMediaQuery.php file and check it's call
			$post_id = ( isset( $rtmedia_query->media->media_id ) ? $rtmedia_query->media->media_id : $rtmedia_query->media[0]->media_id );
			$post    = get_post( get_post_field( 'post_parent', $post_id ) );

			$link .= $site_url . $post->post_name . '/';
		}
	}

	$link .= RTMEDIA_MEDIA_SLUG . '/';

	if ( isset( $rtmedia_query->media_query['album_id'] ) && intval( $rtmedia_query->media_query['album_id'] ) > 0 ) {
		$link .= $rtmedia_query->media_query['album_id'] . '/';
	}

	if ( isset( $rtmedia_query->action_query->media_type ) ) {
		$media_type_array = array( 'photo', 'music', 'video', 'album', 'playlist' );

		if ( in_array( $rtmedia_query->action_query->media_type, $media_type_array, true ) ) {
			$link .= $rtmedia_query->action_query->media_type . '/';
		}
	}

	return apply_filters( 'rtmedia_pagination_next_link', $link . $page_url, $link, $page_url );

}

/**
 * get media page link
 *
 * @global      object          $rtmedia_interaction
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @param       string          $page_no
 *
 * @return      string
 */
function rtmedia_pagination_page_link( $page_no = '' ) {

	global $rtmedia_interaction, $rtmedia_query;

	$page_url    = 'pg/' . $page_no;
	$site_url    = ( is_multisite() ) ? trailingslashit( get_site_url( get_current_blog_id() ) ) : trailingslashit( get_site_url() );
	$author_name = get_query_var( 'author_name' );
	$link        = '';

	if ( $rtmedia_interaction && isset( $rtmedia_interaction->context ) && 'profile' === $rtmedia_interaction->context->type ) {
		if ( function_exists( 'bp_core_get_user_domain' ) && ! empty( $rtmedia_query->media_query['media_author'] ) ) {
			$link .= trailingslashit( bp_core_get_user_domain( $rtmedia_query->media_query['media_author'] ) );
		} else {
			$link .= $site_url . 'author/' . $author_name . '/';
		}
	} else {
		if ( $rtmedia_interaction && isset( $rtmedia_interaction->context ) && 'group' === $rtmedia_interaction->context->type ) {
			if ( function_exists( 'bp_get_current_group_slug' ) ) {
				$link .= $site_url . bp_get_groups_root_slug() . '/' . bp_get_current_group_slug() . '/';
			}
		} elseif ( isset( $rtmedia_query->media->media_id ) ) {
			$post = get_post( get_post_field( 'post_parent', $rtmedia_query->media->media_id ) );

			$link .= $site_url . $post->post_name . '/';
		}
	}

	$link .= RTMEDIA_MEDIA_SLUG . '/';

	if ( isset( $rtmedia_query->media_query['album_id'] ) && intval( $rtmedia_query->media_query['album_id'] ) > 0 ) {
		$link .= $rtmedia_query->media_query['album_id'] . '/';
	}

	if ( isset( $rtmedia_query->action_query->media_type ) ) {
		$media_type_array = array( 'photo', 'music', 'video', 'album', 'playlist' );

		if ( in_array( $rtmedia_query->action_query->media_type, $media_type_array, true ) ) {
			$link .= $rtmedia_query->action_query->media_type . '/';
		}
	}

	return apply_filters( 'rtmedia_pagination_page_link', $link . $page_url, $link, $page_url );

}

/**
 * Media pagination
 *
 * @global      array       $rtmedia_backbone
 */
function rtmedia_media_pagination() {

	global $rtmedia_backbone;

	if ( $rtmedia_backbone['backbone'] ) {
		echo '<%= pagination %>';
	} else {
		echo rtmedia_get_pagination_values(); // @codingStandardsIgnoreLine
	}

}

/**
 * Render pagination UI
 *
 * @global      RTMedia         $rtmedia
 * @global      RTMediaQuery    $rtmedia_query
 * @global      int             $paged
 *
 * @return      string
 */
function rtmedia_get_pagination_values() {

	global $rtmedia, $rtmedia_query, $paged;

	$general_options = $rtmedia->options;
	$per_page        = $general_options['general_perPageMedia'];

	if ( isset( $rtmedia_query->query['per_page'] ) ) {
		$per_page = $rtmedia_query->query['per_page'];
	}

	$per_page            = intval( $per_page );
	$range               = 1;
	$showitems           = ( $range * 2 ) + 1;
	$rtmedia_media_pages = '';

	if ( 0 === intval( rtmedia_offset() ) ) {
		$paged = 1;  // @codingStandardsIgnoreLine
	} else if ( intval( rtmedia_offset() ) === $per_page ) {
		$paged = 2; // @codingStandardsIgnoreLine
	} else {
		$paged = ceil( rtmedia_offset() / $per_page ) + 1; // @codingStandardsIgnoreLine
	}

	$pages = ceil( rtmedia_count() / $per_page );

	if ( ! $pages ) {
		$pages = 1;
	}

	$page_base_url = rtmedia_pagination_page_link();

	if ( 1 !== intval( $pages ) ) {
		$rtmedia_media_pages .= "<div class='rtm-pagination clearfix'>";
		$rtmedia_media_pages .= "<div class='rtmedia-page-no rtm-page-number'>";
		$rtmedia_media_pages .= "<span class='rtm-label'>";
		$rtmedia_media_pages .= esc_html( apply_filters( 'rtmedia_goto_page_label', esc_html__( 'Go to page no : ', 'buddypress-media' ) ) );
		$rtmedia_media_pages .= '</span>';
		$rtmedia_media_pages .= "<input type='hidden' id='rtmedia_first_page' value='1' />";
		$rtmedia_media_pages .= "<input type='hidden' id='rtmedia_last_page' value='" . esc_attr( $pages ) . "' />";
		$rtmedia_media_pages .= "<input type='number' value='" . esc_attr( $paged ) . "' min='1' max='" . esc_attr( $pages ) . "' class='rtm-go-to-num' id='rtmedia_go_to_num' />";
		$rtmedia_media_pages .= "<a class='rtmedia-page-link button' data-page-type='num' data-page-base-url='" . $page_base_url . "' href='#'>" . esc_html__( 'Go', 'buddypress-media' ) . '</a>';
		$rtmedia_media_pages .= "</div><div class='rtm-paginate'>";

		if ( $paged > 1 && $showitems < $pages ) {
			$page_url = ( ( rtmedia_page() - 1 ) == 1 ) ? '' : $page_base_url . ( rtmedia_page() - 1 );

			$rtmedia_media_pages .= "<a class='rtmedia-page-link' data-page-type='prev' href='" . esc_url( $page_url ) . "'><i class='dashicons dashicons-arrow-left-alt2'></i></a>";
		}

		if ( $paged > 2 && $paged > $range + 1 && $showitems < $pages ) {
			$page_url = $page_base_url . '1';

			$rtmedia_media_pages .= "<a class='rtmedia-page-link' data-page-type='page' data-page='1' href='" . esc_url( $page_url ) . "'>1</a>";
			if ( $paged > 3 ) {
				$rtmedia_media_pages .= '<span>...</span>';
			}
		}

		for ( $i = 1; $i <= $pages; $i ++ ) {
			if ( 1 != $pages && ( ! ( $i >= $paged + $range + 1 || $i <= $paged - $range - 1 ) || $pages <= $showitems ) ) {
				$page_url = $page_base_url . $i;

				$rtmedia_media_pages .= ( $paged == $i ) ? "<span class='current'>" . esc_html( $i ) . '</span>' : "<a class='rtmedia-page-link' data-page-type='page' data-page='" . esc_attr( $i ) . "' href='" . esc_url( $page_url ) . "' class='inactive' >" . esc_html( $i ) . '</a>';
			}
		}

		if ( $paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages ) {
			$page_url = $page_base_url . $pages;

			if ( $paged + 2 < $pages  ) {
				$rtmedia_media_pages .= '<span>...</span>';
			}

			$rtmedia_media_pages .= "<a class='rtmedia-page-link' data-page-type='page' data-page='" . esc_attr( $pages ) . "' href='" . esc_url( $page_url ) . "'>" . esc_html( $pages ) . '</a>';
		}

		if ( $paged < $pages && $showitems < $pages ) {
			$page_url = $page_base_url . ( rtmedia_page() + 1 );

			$rtmedia_media_pages .= "<a class='rtmedia-page-link' data-page-type='next' href='" . esc_url( $page_url ) . "'><i class='dashicons dashicons-arrow-right-alt2'></i></a>";
		}

		$rtmedia_media_pages .= "</div></div>\n";
	}// End if().

	return $rtmedia_media_pages;

}

/**
 * Checking if comments are enabled
 *
 * @global      RTMedia         $rtmedia
 *
 * @return      bool
 */
function rtmedia_comments_enabled() {

	global $rtmedia;

	return $rtmedia->options['general_enableComments'];

}

/**
 * Checking if it's a rtmedia gallery
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      bool
 */
function is_rtmedia_gallery() {

	global $rtmedia_query;

	if ( $rtmedia_query ) {
		return $rtmedia_query->is_gallery();
	} else {
		return false;
	}

}

/**
 * Checking if it's a album gallery
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      bool
 */
function is_rtmedia_album_gallery() {

	global $rtmedia_query;

	if ( $rtmedia_query ) {
		return $rtmedia_query->is_album_gallery();
	} else {
		return false;
	}

}

/**
 * Checking if it's a single media
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      bool
 */
function is_rtmedia_single() {

	global $rtmedia_query;

	if ( $rtmedia_query ) {
		return $rtmedia_query->is_single();
	} else {
		return false;
	}

}

/**
 * Checking if it's an album
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @param       bool|int        $album_id
 *
 * @return      bool
 */
function is_rtmedia_album( $album_id = false ) {

	if ( $album_id ) {
		$rtmedia_model = new RTMediaModel();
		$media         = $rtmedia_model->get( array(
			'id' => $album_id,
		) );

		if ( is_array( $media ) && isset( $media[0] ) && isset( $media[0]->media_type ) && 'album' === $media[0]->media_type ) {
			return true;
		}

		return false;
	}

	global $rtmedia_query;

	if ( $rtmedia_query ) {
		return $rtmedia_query->is_album();
	} else {
		return false;
	}

}

/**
 * Checking if it's a group album
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      bool
 */
function is_rtmedia_group_album() {

	global $rtmedia_query;

	if ( $rtmedia_query ) {
		return $rtmedia_query->is_group_album();
	} else {
		return false;
	}

}

/**
 * Checking if edit is allowed
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @return      bool
 */
function is_rtmedia_edit_allowed() {

	global $rtmedia_query;

	if ( $rtmedia_query ) {
		if ( isset( $rtmedia_query->media_query['media_author'] ) && get_current_user_id() === intval( $rtmedia_query->media_query['media_author'] ) && 'edit' === $rtmedia_query->action_query->action ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}

}

/**
 * Updating activity after thumbnail set
 *
 * @global      wpdb            $wpdb
 * @global      BuddyPress      $bp
 *
 * @param       int             $id
 */
function update_activity_after_thumb_set( $id ) {
	$model       = new RTMediaModel();
	$media_obj   = new RTMediaMedia();
	$media       = $model->get( array(
		'id' => $id,
	) );
	$privacy     = $media[0]->privacy;
	$activity_id = rtmedia_activity_id( $id );

	if ( ! empty( $activity_id ) ) {
		$same_medias           = $media_obj->model->get( array(
			'activity_id' => $activity_id,
		) );
		$update_activity_media = array();

		foreach ( $same_medias as $a_media ) {
			$update_activity_media[] = $a_media->id;
		}

		$obj_activity = new RTMediaActivity( $update_activity_media, $privacy, false );

		global $wpdb, $bp;

		$activity_old_content = bp_activity_get_meta( $activity_id, 'bp_old_activity_content' );

		if ( ! empty( $activity_old_content ) ) {
			// get old activity content and save in activity meta
			$activity_get  = bp_activity_get_specific( array(
				'activity_ids' => $activity_id,
			) );
			$activity      = $activity_get['activities'][0];
			$activity_body = $activity->content;

			bp_activity_update_meta( $activity_id, 'bp_old_activity_content', $activity_body );

			//extract activity text from old content
			$activity_text = strip_tags( $activity_body, '<span>' );
			$activity_text = explode( '</span>', $activity_text );
			$activity_text = strip_tags( $activity_text[0] );

			bp_activity_update_meta( $activity_id, 'bp_activity_text', $activity_text );
		}

		$activity_text               = bp_activity_get_meta( $activity_id, 'bp_activity_text' );
		$obj_activity->activity_text = $activity_text;

		$wpdb->update( $bp->activity->table_name, array(
			'type'    => 'rtmedia_update',
			'content' => $obj_activity->create_activity_html(),
			), array(
			'id' => $activity_id,
		) );
	}// End if().

}

/**
 * Updating video poster
 *
 * @param       string      $html
 * @param       object      $media
 * @param       bool        $activity
 *
 * @return      string
 */
function update_video_poster( $html, $media, $activity = false ) {

	if ( 'video' === $media->media_type ) {
		$thumbnail_id = $media->cover_art;

		if ( $thumbnail_id ) {
			$thumbnail_info = wp_get_attachment_image_src( $thumbnail_id, 'full' );
			$html           = str_replace( '<video ', '<video poster="' . esc_url( $thumbnail_info[0] ) . '" ', $html );
		}
	}

	return $html;

}

/**
 * Get video without thumbnail
 *
 * @global      wpdb        $wpdb
 *
 * @return      string
 */
function get_video_without_thumbs() {

	global $wpdb;

	$rtmedia_model = new RTMediaModel();

	$sql     = $wpdb->prepare( "select media_id from {$rtmedia_model->table_name} where media_type = %s and blog_id = %d and cover_art is null", 'video', get_current_blog_id() ); // @codingStandardsIgnoreLine
	$results = $wpdb->get_col( $sql ); // @codingStandardsIgnoreLine

	return $results;

}

/**
 * Rendering single media comment form
 */
function rtmedia_comment_form() {

	if ( is_user_logged_in() ) {
		?>
		<form method="post" id="rt_media_comment_form" class="rt_media_comment_form" action="<?php echo esc_url( get_rtmedia_permalink( rtmedia_id() ) ); ?>comment/">
			<textarea style="width:100%" placeholder="<?php esc_attr_e( 'Type Comment...', 'buddypress-media' ); ?>" name="comment_content" id="comment_content"  class="bp-suggestions ac-input"></textarea>
			<input type="submit" id="rt_media_comment_submit" class="rt_media_comment_submit" value="<?php esc_attr_e( 'Comment', 'buddypress-media' ); ?>">
			<?php RTMediaComment::comment_nonce_generator(); ?>
		</form>
		<?php
	}

}

/**
 * Get cover srt using media ID
 *
 * @param       int             $id
 *
 * @return      bool|string
 */
function rtmedia_get_cover_art_src( $id ) {

	$model     = new RTMediaModel();
	$media     = $model->get( array(
		'id' => $id,
	) );
	$cover_art = $media[0]->cover_art;

	if ( ! empty( $cover_art ) ) {
		if ( is_numeric( $cover_art ) ) {
			$thumbnail_info = wp_get_attachment_image_src( $cover_art, 'full' );

			return $thumbnail_info[0];
		} else {
			return $cover_art;
		}
	} else {
		return false;
	}

}

/**
 * Rendering media delete form
 *
 * @param       bool            $echo
 *
 * @return      bool|string
 */
function rtmedia_delete_form( $echo = true ) {

	if ( rtmedia_delete_allowed() ) {
		$html = '<form method="post" action="' . esc_url( get_rtmedia_permalink( rtmedia_id() ) ) . 'delete/">';
		$html .= '<input type="hidden" name="id" id="id" value="' . esc_attr( rtmedia_id() ) . '">';
		$html .= '<input type="hidden" name="request_action" id="request_action" value="delete">';

		if ( $echo ) {
			echo $html; // @codingStandardsIgnoreLine

			RTMediaMedia::media_nonce_generator( rtmedia_id(), true );

			do_action( 'rtmedia_media_single_delete_form' );

			echo '<button type="submit" title="' . esc_attr__( 'Delete Media', 'buddypress-media' ) . '" class="rtmedia-delete-media rtmedia-action-buttons button">' . esc_html__( 'Delete', 'buddypress-media' ) . '</button></form>';
		} else {
			$output          = $html;
			$rtm_nonce       = RTMediaMedia::media_nonce_generator( rtmedia_id(), false );
			$rtm_nonce       = json_decode( $rtm_nonce );
			$rtm_nonce_field = wp_nonce_field( 'rtmedia_' . rtmedia_id(), $rtm_nonce->action, true, false );

			do_action( 'rtmedia_media_single_delete_form' );

			$output .= $rtm_nonce_field . '<button type="submit" title="' . esc_attr__( 'Delete Media', 'buddypress-media' ) . '" class="rtmedia-delete-media rtmedia-action-buttons button">' . esc_html__( 'Delete', 'buddypress-media' ) . '</button></form>';

			return $output;
		}
	}

	return false;

}

/**
 * Rendering RTMedia Uploader
 *
 * @param       array|string    $attr
 */
function rtmedia_uploader( $attr = '' ) {

	if ( rtmedia_is_uploader_view_allowed( true, 'media_gallery' ) ) {
		if ( function_exists( 'bp_is_blog_page' ) && ! bp_is_blog_page() ) {
			if ( function_exists( 'bp_is_user' ) && bp_is_user() && function_exists( 'bp_displayed_user_id' ) && bp_displayed_user_id() === get_current_user_id() ) {
				echo RTMediaUploadShortcode::pre_render( $attr ); // @codingStandardsIgnoreLine
			} else {
				if ( function_exists( 'bp_is_group' ) && bp_is_group() ) {
					if ( can_user_upload_in_group() ) {
						echo RTMediaUploadShortcode::pre_render( $attr ); // @codingStandardsIgnoreLine
					}
				}
			}
		}
	} else {
		echo "<div class='rtmedia-upload-not-allowed'>" . wp_kses( apply_filters( 'rtmedia_upload_not_allowed_message', esc_html__( 'You are not allowed to upload/attach media.', 'buddypress-media' ), 'media_gallery' ), RTMediaUpload::$wp_kses_allowed_tags ) . '</div>';
	}

}

/**
 * Rendering RTMedia Gallery
 *
 * @param       array|string    $attr
 */
function rtmedia_gallery( $attr = '' ) {

	echo RTMediaGalleryShortcode::render( $attr ); // @codingStandardsIgnoreLine

}

/**
 * Get meta data of media
 *
 * @param       bool|int        $id
 * @param       bool|string     $key
 *
 * @return bool
 */
function get_rtmedia_meta( $id = false, $key = false ) {

	if ( apply_filters( 'rtmedia_use_legacy_meta_function', false ) ) {
		$rtmediameta = new RTMediaMeta();

		return $rtmediameta->get_meta( $id, $key );
	} else {
		// check whether to get single value or multiple
		$single = ( false === $key ) ? false : true;

		// use WP's default get_metadata function replace column name from "media_id" to "id" in query
		add_filter( 'query', 'rtm_filter_metaid_column_name' );

		$meta = get_metadata( 'media', $id, $key, $single );

		remove_filter( 'query', 'rtm_filter_metaid_column_name' );

		return $meta;
	}

}

/**
 * Add media meta
 *
 * @param       bool|int        $id
 * @param       bool|string     $key
 * @param       bool|string     $value
 * @param       bool            $duplicate
 *
 * @return      bool|string
 */
function add_rtmedia_meta( $id = false, $key = false, $value = false, $duplicate = false ) {

	if ( apply_filters( 'rtmedia_use_legacy_meta_function', false ) ) {
		$rtmediameta = new RTMediaMeta( $id, $key, $value, $duplicate );

		return $rtmediameta->add_meta( $id, $key, $value, $duplicate );
	} else {
		// use WP's default get_metadata function replace column name from "media_id" to "id" in query
		add_filter( 'query', 'rtm_filter_metaid_column_name' );

		$meta = add_metadata( 'media', $id, $key, $value, ! $duplicate );

		remove_filter( 'query', 'rtm_filter_metaid_column_name' );

		return $meta;
	}

}

/**
 * Update media meta
 *
 * @param       bool|int        $id
 * @param       bool|string     $key
 * @param       bool|string     $value
 * @param       bool            $duplicate
 *
 * @return      bool|string
 */
function update_rtmedia_meta( $id = false, $key = false, $value = false, $duplicate = false ) {

	if ( apply_filters( 'rtmedia_use_legacy_meta_function', false ) ) {
		$rtmediameta = new RTMediaMeta();

		return $rtmediameta->update_meta( $id, $key, $value, $duplicate );
	} else {
		// use WP's default get_metadata function replace column name from "media_id" to "id" in query
		add_filter( 'query', 'rtm_filter_metaid_column_name' );

		$meta = update_metadata( 'media', $id, $key, $value, $duplicate );

		remove_filter( 'query', 'rtm_filter_metaid_column_name' );

		return $meta;
	}

}

/**
 * Delete media meta
 *
 * @param       bool|int        $id
 * @param       bool|string     $key
 *
 * @return      array|bool
 */
function delete_rtmedia_meta( $id = false, $key = false ) {

	if ( apply_filters( 'rtmedia_use_legacy_meta_function', false ) ) {
		$rtmediameta = new RTMediaMeta();

		return $rtmediameta->delete_meta( $id, $key );
	} else {
		// use WP's default get_metadata function replace column name from "media_id" to "id" in query
		add_filter( 'query', 'rtm_filter_metaid_column_name' );

		$meta = delete_metadata( 'media', $id, $key );

		remove_filter( 'query', 'rtm_filter_metaid_column_name' );

		return $meta;
	}
}

/**
 * Get global albums
 *
 * @return      array
 */
function rtmedia_global_albums() {

	return RTMediaAlbum::get_globals();

}

/**
 * Get global album list
 *
 * @param       bool|int        $selected_album_id
 *
 * @return      null|string
 */
function rtmedia_global_album_list( $selected_album_id = false ) {

	$model         = new RTMediaModel();
	$global_albums = rtmedia_global_albums();

	if ( false === $selected_album_id && ! empty( $global_albums ) && is_array( $global_albums ) ) {
		$selected_album_id = $global_albums[0];
	}

	$option        = null;
	$album_objects = $model->get_media( array(
		'id' => $global_albums,
	), false, false );

	if ( $album_objects ) {
		foreach ( $album_objects as $album ) {
			//if selected_album_id is provided, keep that album_id selected by default
			$selected = '';

			if ( ! empty( $selected_album_id ) && $selected_album_id === $album->id ) {
				$selected = 'selected="selected"';
			}

			$option .= '<option value="' . esc_attr( $album->id ) . '" ' . $selected . '>' . esc_html( $album->media_title ) . '</option>';
		}
	}

	return $option;

}

/**
 * Get user's album list
 *
 * @global      RTMediaQuery    $rtmedia_query
 *
 * @param       bool            $get_all
 * @param       bool|int        $selected_album_id
 *
 * @return      bool|string
 */
function rtmedia_user_album_list( $get_all = false, $selected_album_id = false ) {

	global $rtmedia_query;

	$model          = new RTMediaModel();
	$global_option  = rtmedia_global_album_list( $selected_album_id );
	$global_albums  = rtmedia_global_albums();
	$album_objects  = $model->get_media( array(
		'media_author' => get_current_user_id(),
		'media_type'   => 'album',
	), false, 'context' );
	$option_group   = '';
	$profile_option = '';

	if ( $album_objects ) {
		foreach ( $album_objects as $album ) {
			if ( ! in_array( $album->id, array_map( 'intval', $global_albums ), true ) && ( ( isset( $rtmedia_query->media_query['album_id'] ) && ( $album->id !== $rtmedia_query->media_query['album_id'] || $get_all ) ) || ! isset( $rtmedia_query->media_query['album_id'] ) ) ) {
				if ( 'profile' === $album->context ) {
					$profile_option .= '<option value="' . esc_attr( $album->id ) . '" ' . selected( $selected_album_id, $album->id, false ) . '>' . esc_html( $album->media_title ) . '</option>';
				}
			}
		}
	}

	$option = apply_filters( 'rtmedia_global_albums_in_uploader', "$global_option" );

	if ( '' != $profile_option ) {
		$option .= "<optgroup label='" . esc_attr__( 'Profile Albums', 'buddypress-media' ) . " ' value = 'profile'>$profile_option</optgroup>";
	}

	if ( '' != $option_group && class_exists( 'BuddyPress' ) ) {
		$option .= "<optgroup label='" . esc_attr__( 'Group Albums', 'buddypress-media' ) . "' value = 'group'>$option_group</optgroup>";
	}

	if ( $option ) {
		return $option;
	} else {
		return false;
	}

}

/**
 * Get group's album list
 *
 * @global      RTMediaQuery        $rtmedia_query
 *
 * @param       bool|int            $selected_album_id
 *
 * @return      bool|null|string
 */
function rtmedia_group_album_list( $selected_album_id = false ) {
	//by default, first album in list will be selected
	global $rtmedia_query;

	$model         = new RTMediaModel();
	$global_option = rtmedia_global_album_list( $selected_album_id );
	$global_albums = rtmedia_global_albums();
	$album_objects = $model->get_media( array(
		'context'    => $rtmedia_query->media_query['context'],
		'context_id' => $rtmedia_query->media_query['context_id'],
		'media_type' => 'album',
	), false, false );
	$option_group  = '';

	if ( $album_objects ) {
		foreach ( $album_objects as $album ) {
			if ( ! in_array( $album->id, $global_albums ) && ( ( isset( $rtmedia_query->media_query['album_id'] ) && ( $album->id != $rtmedia_query->media_query['album_id'] ) ) || ! isset( $rtmedia_query->media_query['album_id'] ) ) ) {
				$option_group .= '<option value="' . esc_attr( $album->id ) . '" ' . selected( $selected_album_id, $album->id ) . '>' . esc_html( $album->media_title ) . '</option>';
			}
		}
	}

	$option = $global_option;

	if ( ! empty( $option_group ) ) {
		$option .= "<optgroup label='" . esc_attr__( 'Group Albums', 'buddypress-media' ) . "' value = 'group'>$option_group</optgroup>";
	}

	if ( $option ) {
		return $option;
	} else {
		return false;
	}

}

/**
 * Checking if album creation is allowed
 *
 * @return      bool
 */
function rtm_is_album_create_allowed() {

	return apply_filters( 'rtm_is_album_create_enable', true );

}

/**
 * Checking if user has an access to create an album
 *
 * @param       bool|int    $user_id
 *
 * @return      bool
 */
function rtm_is_user_allowed_to_create_album( $user_id = false ) {

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	return apply_filters( 'rtm_display_create_album_button', true, $user_id );

}

/**
 * Checking if album is editable
 *
 * @global      RTMediaQuery        $rtmedia_query
 *
 * @return      bool
 */
function rtmedia_is_album_editable() {

	global $rtmedia_query;

	if ( isset( $rtmedia_query->query['context'] ) && 'profile' === $rtmedia_query->query['context'] ) {
		if ( isset( $rtmedia_query->media_query['media_author'] ) && get_current_user_id() === intval( $rtmedia_query->media_query['media_author'] ) ) {
			return true;
		}
	}

	if ( isset( $rtmedia_query->query['context'] ) && 'group' === $rtmedia_query->query['context'] ) {
		if ( isset( $rtmedia_query->album[0]->media_author ) && get_current_user_id() === intval( $rtmedia_query->album[0]->media_author ) ) {
			return true;
		}
	}

	return false;

}

/**
 * Rendering sub nav
 *
 * @global      RTMediaNav      $rtMediaNav
 */
function rtmedia_sub_nav() {

	global $rtMediaNav;

	$rtMediaNav = new RTMediaNav();

	$rtMediaNav->sub_nav();

}

/**
 * Checking if album is enable
 *
 * @global      RTMedia     $rtmedia
 *
 * @return      bool
 */
function is_rtmedia_album_enable() {

	global $rtmedia;

	if ( isset( $rtmedia->options['general_enableAlbums'] ) && 0 !== intval( $rtmedia->options['general_enableAlbums'] ) ) {
		return true;
	}

	return false;

}

/**
 * Loading right media template
 */
function rtmedia_load_template() {

	do_action( 'rtmedia_before_template_load' );

	include( RTMediaTemplate::locate_template() );

	do_action( 'rtmedia_after_template_load' );

}

/**
 * Checking if privacy is enabled
 *
 * @global      RTMedia     $rtmedia
 *
 * @return      bool
 */
function is_rtmedia_privacy_enable() {

	global $rtmedia;

	if ( isset( $rtmedia->options['privacy_enabled'] ) && 0 !== intval( $rtmedia->options['privacy_enabled'] ) ) {
		return true;
	}

	return false;

}

/**
 * Checking if user can override the existing privacy
 *
 * @global      RTMedia     $rtmedia
 *
 * @return      bool
 */
function is_rtmedia_privacy_user_overide() {

	global $rtmedia;

	if ( isset( $rtmedia->options['privacy_userOverride'] ) && 0 !== intval( $rtmedia->options['privacy_userOverride'] ) ) {
		return true;
	}

	return false;

}

/**
 * Rendering privacy UI
 *
 * @global      RTMediaQuery        $rtmedia_query
 *
 * @return      bool|string
 */
function rtmedia_edit_media_privacy_ui() {

	global $rtmedia_query;

	if ( isset( $rtmedia_query->query['context'] ) && 'group' === $rtmedia_query->query['context'] ) {
		//if context is group i.e editing a group media, dont show the privacy dropdown
		return false;
	}

	$privacymodel = new RTMediaPrivacy( false );
	$privacy      = $privacymodel->select_privacy_ui( $echo = false );

	if ( $privacy ) {
		return "<div class='rtmedia-edit-privacy rtm-field-wrap'><label for='privacy'>" . esc_html__( 'Privacy : ', 'buddypress-media' ) . '</label>' . $privacy . '</div>';
	}

}

/**
 * Get default privacy
 *
 * @global      RTMedia     $rtmedia
 *
 * @return      int
 */
function get_rtmedia_default_privacy() {

	global $rtmedia;

	if ( isset( $rtmedia->options['privacy_default'] ) ) {
		return $rtmedia->options['privacy_default'];
	}

	return 0;

}

/**
 * Checking if media is enabled in BuddyPress group
 *
 * @global      RTMedia     $rtmedia
 *
 * @return      bool
 */
function is_rtmedia_group_media_enable() {

	global $rtmedia;

	if ( isset( $rtmedia->options['buddypress_enableOnGroup'] ) && 0 !== intval( $rtmedia->options['buddypress_enableOnGroup'] ) ) {
		return true;
	}

	return false;
}

/**
 * Checking if media is enabled in BuddyPress Member's profile
 *
 * @global      RTMedia     $rtmedia
 *
 * @return      bool
 */
function is_rtmedia_profile_media_enable() {

	global $rtmedia;

	if ( isset( $rtmedia->options['buddypress_enableOnProfile'] ) && 0 !== intval( $rtmedia->options['buddypress_enableOnProfile'] ) ) {
		return true;
	}

	return false;

}

/**
 * Checking if user in group component
 *
 * @global      RTMediaQuery        $rtmedia_query
 *
 * @return      bool
 */
function is_rtmedia_bp_group() {

	global $rtmedia_query;

	if ( isset( $rtmedia_query->query['context'] ) && 'group' === $rtmedia_query->query['context'] ) {
		return true;
	}

	return false;

}

/**
 * Checking if user in profile component
 *
 * @global      RTMediaQuery        $rtmedia_query
 *
 * @return      bool
 */
function is_rtmedia_bp_profile() {

	global $rtmedia_query;

	if ( isset( $rtmedia_query->query['context'] ) && 'profile' == $rtmedia_query->query['context'] ) {
		return true;
	}

	return false;

}

/**
 * Checking if user can upload in BuddyPress group
 *
 * @return      bool
 */
function can_user_upload_in_group() {

	$group        = groups_get_current_group();
	$user_id      = get_current_user_id();
	$display_flag = false;

	if ( groups_is_user_member( $user_id, $group->id ) ) {
		$display_flag = true;
	}

	$display_flag = apply_filters( 'rtm_can_user_upload_in_group', $display_flag );

	return $display_flag;

}

/**
 * Checking if user can create an album in BuddyPress group
 *
 * @param       bool|int    $group_id
 * @param       bool|int    $user_id
 *
 * @return      bool
 */
function can_user_create_album_in_group( $group_id = false, $user_id = false ) {

	if ( false == $group_id ) {
		$group    = groups_get_current_group();
		$group_id = $group->id;
	}

	$upload_level = groups_get_groupmeta( $group_id, 'rt_media_group_control_level' );

	if ( empty( $upload_level ) ) {
		$upload_level = groups_get_groupmeta( $group_id, 'bp_media_group_control_level' );

		if ( empty( $upload_level ) ) {
			$upload_level = 'all';
		}
	}

	$user_id      = get_current_user_id();
	$display_flag = false;

	if ( groups_is_user_member( $user_id, $group_id ) ) {
		if ( 'admin' === $upload_level ) {
			if ( groups_is_user_admin( $user_id, $group_id ) > 0 ) {
				$display_flag = true;
			}
		} else {
			if ( 'moderators' === $upload_level ) {
				if ( groups_is_user_mod( $user_id, $group_id ) || groups_is_user_admin( $user_id, $group_id ) ) {
					$display_flag = true;
				}
			} else {
				$display_flag = true;
			}
		}
	}

	$display_flag = apply_filters( 'can_user_create_album_in_group', $display_flag );

	return $display_flag;

}

/**
 * Checking if video upload is allowed
 *
 * @global      RTMedia     $rtmedia
 *
 * @return      bool
 */
function is_rtmedia_upload_video_enabled() {

	global $rtmedia;

	if ( isset( $rtmedia->options['allowedTypes_video_enabled'] ) && 0 !== intval( $rtmedia->options['allowedTypes_video_enabled'] ) ) {
		return true;
	}

	return false;
}

/**
 * Checking if photo upload is allowed
 *
 * @global      RTMedia     $rtmedia
 *
 * @return      bool
 */
function is_rtmedia_upload_photo_enabled() {

	global $rtmedia;

	if ( isset( $rtmedia->options['allowedTypes_photo_enabled'] ) && 0 !== intval( $rtmedia->options['allowedTypes_photo_enabled'] ) ) {
		return true;
	}

	return false;

}

/**
 * Checking if music upload is allowed
 *
 * @global      RTMedia     $rtmedia
 *
 * @return      bool
 */
function is_rtmedia_upload_music_enabled() {

	global $rtmedia;

	if ( isset( $rtmedia->options['allowedTypes_music_enabled'] ) && 0 !== intval( $rtmedia->options['allowedTypes_music_enabled'] ) ) {
		return true;
	}

	return false;

}

/**
 * Get allowed media upload type
 *
 * @global      RTMedia     $rtmedia
 *
 * @return      string
 */
function get_rtmedia_allowed_upload_type() {

	global $rtmedia;

	$allow_type_str = '';
	$sep            = '';

	foreach ( $rtmedia->allowed_types as $type ) {
		if ( function_exists( 'is_rtmedia_upload_' . $type['name'] . '_enabled' ) && call_user_func( 'is_rtmedia_upload_' . $type['name'] . '_enabled' ) ) {
			foreach ( $type['extn'] as $extn ) {
				$allow_type_str .= $sep . $extn;

				$sep = ',';
			}
		}
	}

	return $allow_type_str;

}

/**
 * Checking if is admin
 *
 * @return      bool
 */
function is_rt_admin() {

	return current_user_can( 'list_users' );

}

/**
 * Get media like count
 *
 * @param       bool|int        $media_id
 *
 * @return      array|int
 */
function get_rtmedia_like( $media_id = false ) {

	$mediamodel = new RTMediaModel();
	$actions    = $mediamodel->get( array(
		'id' => rtmedia_id( $media_id ),
	) );

	if ( isset( $actions[0]->likes ) ) {
		$actions = intval( $actions[0]->likes );
	} else {
		$actions = 0;
	}

	return $actions;

}

/**
 * Show media like count
 *
 * @global      RTMedia     $rtmedia
 */
function show_rtmedia_like_counts() {

	global $rtmedia;

	$options = $rtmedia->options;
	$count   = get_rtmedia_like();

	if ( ! ( isset( $options['general_enableLikes'] ) && 0 === intval( $options['general_enableLikes'] ) ) ) {
		$class = '';

		if ( ! intval( $count ) ) {
			$class = 'hide';
		}
		?>
		<div class='rtmedia-like-info <?php echo $class; ?>'>
			<i class="rtmicon-thumbs-up rtmicon-fw"></i>
			<span class="rtmedia-like-counter-wrap">
				<?php
				if ( class_exists( 'RTMediaLike' ) && function_exists( 'rtmedia_who_like_html' ) ) {
					$rtmedialike = new RTMediaLike();
					echo rtmedia_who_like_html( $count, $rtmedialike->is_liked( rtmedia_id() ) );
				}
				?>
			</span>
			<?php
			?>
		</div>
		<?php
	}

}



/**
 * Print rtmedia who like html
 *
 * @param       int          $like_count ( Total like Count )
 * @param      bool|string  $user_like_it ( login user like it or not )
 *
 * @return      string  HTML
 */
if ( ! function_exists( 'rtmedia_who_like_html' ) ) {
	function rtmedia_who_like_html( $like_count, $user_like_it ) {
		$like_count = ( $like_count ) ? $like_count : false;
		$user_like_it = ( $user_like_it ) ? true : false;
		$like_count_new = $like_count;
		$html = '';
		if ( $like_count == 1 && $user_like_it ) {
			/**
			 * rtmedia you like text
			 * @param $html TEXT
			 * @param int $like_count Total Like
			 * @param int $user_like_it User Like it or Not
			 * @return html TEXT to  display
			*/
			$html = apply_filters( 'rtmedia_like_html_you_only_like', esc_html__( 'You like this', 'buddypress-media' ), $like_count, $user_like_it );
		} elseif ( $like_count ) {
			if ( $like_count > 1 && $user_like_it ) {
				/**
				* rtmedia you and
				 * @param $html TEXT
				 * @param int $like_count Total Like
				 * @param int $user_like_it User Like it or Not
				 * @return html TEXT to  display
				*/
				$html .= apply_filters( 'rtmedia_like_html_you_and_more_like', esc_html__( 'You and ', 'buddypress-media' ), $like_count, $user_like_it );
				$like_count_new--;
			}

			/**
			 * rtmedia Disaply count
			 * @param int $like_count Total Like
			 * @param int $user_like_it User Like it or Not
			 * @return INT Count to  display
			*/
			$html .= apply_filters( 'rtmedia_like_html_you_and_more_like', $like_count, $user_like_it );

			/**
			 * rtmedia person or people likes it
			 * @param $html TEXT
			 * @param int $like_count Total Like
			 * @param int $user_like_it User Like it or Not
			 * @return html TEXT to  display
			*/
			$html .= apply_filters( 'rtmedia_like_html_othe_likes_this', _n( ' person likes this', ' people like this', $like_count_new, 'buddypress-media' ) ,$like_count, $user_like_it );
		}

		/**
		 * rtmedia return whole HTML
		 * @param $html TEXT
		 * @param int $like_count Total Like
		 * @param int $user_like_it User Like it or Not
		 * @return html TEXT to  display
		*/
		$html = apply_filters( 'rtmedia_who_like_html', $html ,$like_count, $user_like_it );
		return $html;
	}
}

/**
 * Get music cover art
 *
 * @param       object          $media_object
 *
 * @return      bool|string
 */
function rtm_get_music_cover_art( $media_object ) {

	// return URL if cover_art already set.
	$url = $media_object->cover_art;

	if ( ! empty( $url ) && ! is_numeric( $url ) ) {
		return $url;
	}

	// return false if covert_art is already analyzed earlier
	if ( -1 === intval( $url ) ) {
		return false;
	}

	// Analyze media for the first time and set cover_art into database.
	$file       = get_attached_file( $media_object->media_id );
	$media_obj  = new RTMediaMedia();
	$media_tags = new RTMediaTags( $file );
	$title_info = $media_tags->title;
	$image_info = $media_tags->image;
	$image_mime = $image_info['mime'];
	$mime       = explode( '/', $image_mime );
	$id         = $media_object->id;

	if ( ! empty( $image_info['data'] ) ) {
		$thumb_upload_info = wp_upload_bits( $title_info . '.' . $mime[ count( $mime ) - 1 ], null, $image_info['data'] );

		if ( is_array( $thumb_upload_info ) && ! empty( $thumb_upload_info['url'] ) ) {
			$media_obj->model->update( array(
				'cover_art' => $thumb_upload_info['url'],
				), array(
				'id' => $id,
			) );

			return $thumb_upload_info['url'];
		}
	}

	$media_obj->model->update( array(
		'cover_art' => '-1',
		), array(
		'id' => $id,
	) );

	return false;

}

/**
 * "get_music_cover_art" is too generic function name. It shouldn't added in very first place.
 * It is renamed as "rtm_get_music_cover_art"
 *
 * @return      bool
 */
if ( ! function_exists( 'get_music_cover_art' ) ) {

	function get_music_cover_art( $file, $id ) {

		return false;

	}
}

/**
 * Get the media privacy symbol
 *
 * @param       bool|int    $rtmedia_id
 *
 * @return      string
 */
function get_rtmedia_privacy_symbol( $rtmedia_id = false ) {

	$mediamodel = new RTMediaModel();
	$actions    = $mediamodel->get( array(
		'id' => rtmedia_id( $rtmedia_id ),
	) );
	$privacy    = '';

	if ( intval( $actions[0]->privacy ) >= 0 ) {
		$title = $icon = '';

		switch ( $actions[0]->privacy ) {
			case 0: // public
				$title = esc_html__( 'Public', 'buddypress-media' );
				$icon  = 'dashicons dashicons-admin-site rtmicon';

				break;
			case 20: // users
				$title = esc_html__( 'All members', 'buddypress-media' );
				$icon  = 'dashicons dashicons-groups rtmicon';

				break;
			case 40: // friends
				$title = esc_html__( 'Your friends', 'buddypress-media' );
				$icon  = 'dashicons dashicons-networking rtmicon';

				break;
			case 60: // private
				$title = esc_html__( 'Only you', 'buddypress-media' );
				$icon  = 'dashicons dashicons-lock rtmicon';

				break;
			case 80: // private
				$title = esc_html__( 'Blocked temporarily', 'buddypress-media' );
				$icon  = 'dashicons dashicons-dismiss rtmicon';

				break;
		}

		if ( ! empty( $title ) && ! empty( $icon ) ) {
			$privacy = "<i class='" . esc_attr( $icon ) . "' title='" . esc_attr( $title ) . "'></i>";
		}
	}

	return $privacy;

}

/**
 * Get media uploaded gmt date
 *
 * @param       bool|int    $rtmedia_id
 *
 * @return      string
 */
function get_rtmedia_date_gmt( $rtmedia_id = false ) {

	$media     = get_post( rtmedia_media_id( rtmedia_id( $rtmedia_id ) ) );
	$date_time = '';

	if ( ! empty( $media->post_date_gmt ) ) {
		$date_time = rtmedia_convert_date( $media->post_date_gmt );
	}

	$date_time = apply_filters( 'rtmedia_comment_date_format', $date_time, null );

	return '<span>' . $date_time . '</span>';

}

/**
 * Convert comment datetime to "time ago" format
 *
 * @param       string      $_date
 *
 * @return      string
 */
function rtmedia_convert_date( $_date ) {

	$stf       = 0;
	$date      = new DateTime( $_date );
	$date      = $date->format( 'U' );
	$cur_time  = time();
	$diff      = $cur_time - $date;
	$time_unit = array( 'second', 'minute', 'hour' );
	$length    = array( 1, 60, 3600, 86400 );
	$ago_text  = esc_html__( '%s ago ', 'buddypress-media' );

	for ( $i = sizeof( $length ) - 1; ( $i >= 0 ) && ( ( $no = $diff / $length[ $i ] ) <= 1 ); $i -- ) {}

	if ( $i < 0 ) {
		$i = 0;
	}

	if ( $i <= 2 ) { //if posted in last 24 hours
		$_time = $cur_time - ( $diff % $length[ $i ] );
		$no    = floor( $no );

		switch ( $time_unit[ $i ] ) {
			case 'second':
				$time_unit_phrase = _n( '1 second', '%s seconds', $no, 'buddypress-media' );

				break;
			case 'minute':
				$time_unit_phrase = _n( '1 minute', '%s minutes', $no, 'buddypress-media' );

				break;
			case 'hour':
				$time_unit_phrase = _n( '1 hour', '%s hours', $no, 'buddypress-media' );

				break;
			default:
				// should not happen
				$time_unit_phrase = '%s unknown';
		}

		$value = sprintf( $time_unit_phrase . ' ', $no );

		if ( ( 1 === $stf ) && ( $i >= 1 ) && ( ( $cur_time - $_time ) > 0 ) ) {
			$value .= rtmedia_convert_date( $_time );
		}

		return sprintf( $ago_text, $value );
	} else {
		/* translators: date format, see http://php.net/date */
		return date_i18n( 'd F Y ', strtotime( $_date ), true );
	}

}

/**
 * Get media counts
 *
 * @global      RTMediaQuery        $rtmedia_query
 *
 * @return      array|void
 */
function get_media_counts() {

	global $rtmedia_query;

	$user_id = false;

	if ( function_exists( 'bp_displayed_user_id' ) ) {
		$user_id = bp_displayed_user_id();
	} else {
		if ( isset( $rtmedia_query ) && isset( $rtmedia_query->query['context_id'] ) && 'profile' === $rtmedia_query->query['context'] ) {
			$user_id = $rtmedia_query->query['context_id'];
		}
	}

	$media_nav = new RTMediaNav( false );
	$temp      = $media_nav->actual_counts( $user_id );

	return $temp;

}

/**
 * Checking if it is rtmedia's edit page
 *
 * @global      string      $pagenow
 *
 * @param       null        $new_edit
 *
 * @return      bool
 */
function rtmedia_is_edit_page( $new_edit = null ) {

	global $pagenow;

	//make sure we are on the backend
	if ( ! is_admin() ) {
		return false;
	}

	if ( 'edit' === $new_edit ) {
		return in_array( $pagenow, array( 'post.php' ), true );
	} elseif ( 'new' === $new_edit ) { //check for new post page
		return in_array( $pagenow, array( 'post-new.php' ), true );
	} else { //check for either new or edit
		return in_array( $pagenow, array( 'post.php', 'post-new.php' ), true );
	}

}

/**
 * Checking if it's a rtmedia page
 *
 * @global      object      $rtmedia_interaction
 *
 * @return      bool
 */
function is_rtmedia_page() {

	if ( ! defined( 'RTMEDIA_MEDIA_SLUG' ) ) {
		return false;
	}

	global $rtmedia_interaction;

	if ( ! isset( $rtmedia_interaction ) ) {
		return false;
	}

	if ( ! isset( $rtmedia_interaction->routes ) ) {
		return false;
	}

	return $rtmedia_interaction->routes[ RTMEDIA_MEDIA_SLUG ]->is_template();

}

/**
 * To be used in migration in importing
 *
 * @param       int         $seconds_left
 *
 * @return      string
 */
function rtmedia_migrate_formatseconds( $seconds_left ) {

	$minute_in_seconds = 60;
	$hour_in_seconds   = $minute_in_seconds * 60;
	$day_in_seconds    = $hour_in_seconds * 24;

	$days         = floor( $seconds_left / $day_in_seconds );
	$seconds_left = $seconds_left % $day_in_seconds;

	$hours        = floor( $seconds_left / $hour_in_seconds );
	$seconds_left = $seconds_left % $hour_in_seconds;

	$minutes = floor( $seconds_left / $minute_in_seconds );
	$seconds = $seconds_left % $minute_in_seconds;

	$time_components = array();

	if ( $days > 0 ) {
		$time_components[] = $days . ' day' . ( $days > 1 ? 's' : '' );
	}

	if ( $hours > 0 ) {
		$time_components[] = $hours . ' hour' . ( $hours > 1 ? 's' : '' );
	}

	if ( $minutes > 0 ) {
		$time_components[] = $minutes . ' minute' . ( $minutes > 1 ? 's' : '' );
	}

	if ( $seconds > 0 ) {
		$time_components[] = $seconds . ' second' . ( $seconds > 1 ? 's' : '' );
	}

	if ( count( $time_components ) > 0 ) {
		$formatted_time_remaining = implode( ', ', $time_components );
		$formatted_time_remaining = trim( $formatted_time_remaining );
	} else {
		$formatted_time_remaining = 'No time remaining.';
	}

	return $formatted_time_remaining;

}

/**
 * echo the size of the media file
 *
 * @global      array       $rtmedia_backbone
 * @global      object      $rtmedia_media
 *
 * @return      int
 */
function rtmedia_file_size() {

	global $rtmedia_backbone;

	if ( $rtmedia_backbone['backbone'] ) {
		echo '<%= file_size %>';
	} else {
		global $rtmedia_media;

		if ( isset( $rtmedia_media->file_size ) ) {
			return $rtmedia_media->file_size;
		} else {
			return filesize( get_attached_file( $rtmedia_media->media_id ) );
		}
	}

}

/**
 * Get rtmedia media type from file extension
 *
 * @global      RTMedia             $rtmedia
 *
 * @param       string              $extn
 *
 * @return      bool|int|string
 */
function rtmedia_get_media_type_from_extn( $extn ) {

	global $rtmedia;

	$allowed_type = $rtmedia->allowed_types;

	foreach ( $allowed_type as $type => $param ) {
		if ( isset( $param['extn'] ) && is_array( $param['extn'] ) && in_array( $extn, $param['extn'], true ) ) {
			return $type;
		}
	}

	return false;

}

/**
 * Get extension from media id
 *
 * @global      object      $rtmedia_media
 *
 * @param       bool|int    $media_id
 *
 * @return      bool
 */
function rtmedia_get_extension( $media_id = false ) {
	// If media_id is false then use global media_id
	if ( ! $media_id ) {
		global $rtmedia_media;

		if ( isset( $rtmedia_media->media_id ) ) {
			$media_id = $rtmedia_media->media_id;
		} else {
			return false;
		}
	}

	// Getting filename from media id
	$filename = basename( wp_get_attachment_url( $media_id ) );

	// Checking file type of uploaded document
	$file_type = wp_check_filetype( $filename );

	// return the extension of the filename
	return $file_type['ext'];

}

/**
 * Function to get permalink for current blog
 *
 * @param       string      $domain
 *
 * @return      string
 */
function rtmedia_get_current_blog_url( $domain ) {

	$domain = get_home_url( get_current_blog_id() );

	return $domain;

}

/**
 * Checking if album is global
 *
 * @param       int     $album_id
 *
 * @return      bool
 */
function rtmedia_is_global_album( $album_id ) {

	$rtmedia_global_albums = rtmedia_global_albums();

	if ( ! in_array( intval( $album_id ), $rtmedia_global_albums, true ) ) {
		return true;
	} else {
		return false;
	}

}

/**
 * Checking if uploader view is allowed
 *
 * @param       bool        $allow
 * @param       string      $section
 *
 * @return      bool
 */
function rtmedia_is_uploader_view_allowed( $allow, $section = 'media_gallery' ) {

	return apply_filters( 'rtmedia_allow_uploader_view', $allow, $section );

}

/**
 * Get rtMedia Encoding API Key
 *
 * @return      string
 */
function get_rtmedia_encoding_api_key() {

	return get_site_option( 'rtmedia-encoding-api-key' );

}

/**
 * Filter SQL query strings to swap out the 'meta_id' column.
 *
 * WordPress uses the meta_id column for commentmeta and postmeta, and so
 * hardcodes the column name into its *_metadata() functions. rtMedia
 * uses 'id' for the primary column. To make WP's functions usable for rtMedia,
 * we use this filter on 'query' to swap all 'meta_id' with 'id.
 *
 * @param       string      $q
 *
 * @return      string
 */
function rtm_filter_metaid_column_name( $q ) {

	/*
	 * Replace quoted content with __QUOTE__ to avoid false positives.
	 * This regular expression will match nested quotes.
	 */
	$quoted_regex = "/'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'/s";

	preg_match_all( $quoted_regex, $q, $quoted_matches );

	$q = preg_replace( $quoted_regex, '__QUOTE__', $q );
	$q = str_replace( 'meta_id', 'id', $q );

	// Put quoted content back into the string.
	if ( ! empty( $quoted_matches[0] ) ) {
		for ( $i = 0; $i < count( $quoted_matches[0] ); $i ++ ) {
			$quote_pos = strpos( $q, '__QUOTE__' );
			$q         = substr_replace( $q, $quoted_matches[0][ $i ], $quote_pos, 9 );
		}
	}

	return $q;

}

/**
 * Checking if SCRIPT_DEBUG constant is defined or not
 *
 * @return      string
 */
function rtm_get_script_style_suffix() {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && constant( 'SCRIPT_DEBUG' ) === true ) ? '' : '.min';

	return $suffix;

}

/**
 * To get list of allowed types in rtMedia
 *
 * @since       3.8.16
 *
 * @global      RTMedia     $rtmedia
 *
 * @return      array
 */
function rtmedia_get_allowed_types() {

	global $rtmedia;

	$allowed_media_type = $rtmedia->allowed_types;
	$allowed_media_type = apply_filters( 'rtmedia_allowed_types', $allowed_media_type );

	return $allowed_media_type;

}

/**
 * To get list of allowed upload types in rtMedia
 *
 * @since       3.8.16
 *
 * @return      array
 */
function rtmedia_get_allowed_upload_types() {

	$allowed_types = rtmedia_get_allowed_types();

	foreach ( $allowed_types as $type => $type_detail ) {
		if ( ! ( function_exists( 'is_rtmedia_upload_' . $type . '_enabled' ) && call_user_func( 'is_rtmedia_upload_' . $type . '_enabled' ) ) ) {
			unset( $allowed_types[ $type ] );
		}
	}

	return $allowed_types;

}

/**
 * To get list of allowed upload type name in rtMedia
 *
 * @since       3.8.16
 *
 * @return      array
 */
function rtmedia_get_allowed_upload_types_array() {

	$allowed_types = rtmedia_get_allowed_upload_types();
	$types         = array_keys( $allowed_types );

	return $types;

}

/**
 * Upload and add media
 *
 * @param       array       $upload_params
 *
 * @return      bool|int
 */
function rtmedia_add_media( $upload_params = array() ) {

	if ( empty( $upload_params ) ) {
		$upload_params = $_POST; // @codingStandardsIgnoreLine
	}

	$upload_model = new RTMediaUploadModel();
	$upload_array = $upload_model->set_post_object( $upload_params );

	$rtupload = new RTMediaUpload( $upload_array );
	$media_id = isset( $rtupload->media_ids[0] ) ? $rtupload->media_ids[0] : false;

	return $media_id;

}

/**
 * Add multiple meta key and value for media.
 *
 * @param       int         $media_id
 * @param       string      $meta_key_val
 *
 * @return      array
 */
function rtmedia_add_multiple_meta( $media_id, $meta_key_val ) {

	$meta_ids = array();

	if ( ! empty( $media_id ) && ! empty( $meta_key_val ) ) {
		$media_meta = new RTMediaMeta();

		foreach ( $meta_key_val as $meta_key => $meta_val ) {
			$meta_ids[] = $media_meta->add_meta( $media_id, $meta_key, $meta_val );
		}
	}

	return $meta_ids;

}

/**
 * To get server variable
 *
 * @param       string      $server_key
 * @param       string      $filter_type
 *
 * @return      string
 */
function rtm_get_server_var( $server_key, $filter_type = 'FILTER_SANITIZE_STRING' ) {

	$server_val = '';

	if ( function_exists( 'filter_input' ) && filter_has_var( INPUT_SERVER, $server_key ) ) {
		$server_val = filter_input( INPUT_SERVER, $server_key, constant( $filter_type ) );
	} elseif ( isset( $_SERVER[ $server_key ] ) ) {
		$server_val = $_SERVER[ $server_key ];
	}

	return $server_val;

}

/**
 * Check if URL exists of a given media type (i.e mp4, ogg, wmv)
 *
 * @param       array       $medias
 * @param       string      $media_type
 *
 * @return      bool
 */
function rtt_is_video_exists( $medias, $media_type = 'mp4' ) {

	if ( empty( $medias ) || empty( $media_type ) ) {
		return false;
	}

	if ( isset( $medias[ $media_type ] ) && is_array( $medias[ $media_type ] ) && ! empty( $medias[ $media_type ][0] ) ) {
		return $medias[ $media_type ][0];
	}
}




/**
 * Return the buddpress activity  table content
 *
 * @param       int       $activity_id
 *
 * @return      array     buddpres_activity
 */
function rtmedia_activity_comment( $activity_id ) {
	$activity_id = ( $activity_id ) ? (int) $activity_id : false;
	$activity_comment_content = false;
	if ( $activity_id ) {
		global $wpdb;
		global $bp;
		$table_name = $bp->activity->table_name;
		$activity_comment_content = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $activity_id ), ARRAY_A );
	}
	return $activity_comment_content;
}

/**
 * Send the request to the rtmedia server for addon license validation
 * and activation
 *
 * @since 4.2
 *
 * @param array $addon 		Array containing the license_key, addon_id
 *                      	and addon name
 *
 * @return obejct|boolean 	Addon license data/status from server or the false on error
 */
function rtmedia_activate_addon_license( $addon = array() ) {

	if ( empty( $addon ) || ! is_array( $addon ) || count( $addon ) < 1 ) {
		return false;
	}

	if ( ! isset( $addon['args'] ) ) {
		return false;
	}

	if ( empty( $addon['args']['license_key'] ) || empty( $addon['name'] ) || empty( $addon['args']['addon_id'] ) ) {
		return false;
	}

	$license 	= $addon['args']['license_key'];

	$addon_name = $addon['name'];

	$addon_id 	= $addon['args']['addon_id'];

	$store_url = '';
	if ( defined( 'EDD_' . strtoupper( $addon_id ) . '_STORE_URL' ) ) {
		// Get the store URL from the constant defined in the addon
		$store_url 	= constant( 'EDD_' . strtoupper( $addon_id ) . '_STORE_URL' );
	}

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

	return $license_data;

}
