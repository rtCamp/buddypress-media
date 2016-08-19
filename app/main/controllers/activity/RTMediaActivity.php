<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaActivity
 *
 * @author saurabh
 */
class RTMediaActivity {

	var $media = array();
	var $activity_text = '';
	var $privacy;

	/**
	 * @param $media
	 * @param int $privacy
	 * @param bool $activity_text
	 */
	function __construct( $media, $privacy = 0, $activity_text = false ) {
		if ( ! isset( $media ) ) {
			return false;
		}
		if ( ! is_array( $media ) ) {
			$media = array( $media );
		}

		$this->media         = $media;
		$this->activity_text = bp_activity_filter_kses( $activity_text );
		$this->privacy       = $privacy;
	}

	function create_activity_html() {

		$html = '';

		$html .= '<div class="rtmedia-activity-container">';

		if ( ! empty( $this->activity_text ) ) {
			$html .= '<div class="rtmedia-activity-text">';
			$html .= $this->activity_text;
			$html .= '</div>';
		}

		global $rtmedia;
		if ( isset( $rtmedia->options['buddypress_limitOnActivity'] ) ) {
			$limit_activity_feed = $rtmedia->options['buddypress_limitOnActivity'];
		} else {
			$limit_activity_feed = 0;
		}

		$mediaObj      = new RTMediaModel();
		$media_details = $mediaObj->get( array( 'id' => $this->media ) );

		if ( intval( $limit_activity_feed ) > 0 ) {
			$media_details = array_slice( $media_details, 0, $limit_activity_feed, true );
		}
		$rtmedia_activity_ul_class = apply_filters( 'rtmedia_activity_ul_class', 'rtm-activity-media-list' );
		$li_content                = '';
		$count                     = 0;
		foreach ( $media_details as $media ) {
			$li_content .= '<li class="rtmedia-list-item media-type-' . esc_attr( $media->media_type ) . '">';
			if ( 'photo' === $media->media_type ) {
				$li_content .= '<a href ="' . esc_url( get_rtmedia_permalink( $media->id ) ) . '">';
			}
			$li_content .= '<div class="rtmedia-item-thumbnail">';

			$li_content .= $this->media( $media );

			$li_content .= '</div>';

			$li_content .= '<div class="rtmedia-item-title">';
			$li_content .= '<h4 title="' . esc_attr( $media->media_title ) . '">';
			if ( 'photo' !== $media->media_type ) {
				$li_content .= '<a href="' . esc_url( get_rtmedia_permalink( $media->id ) ) . '">';
			}

			$li_content .= $media->media_title;
			if ( 'photo' !== $media->media_type ) {
				$li_content .= '</a>';
			}
			$li_content .= '</h4>';
			$li_content .= '</div>';
			if ( 'photo' === $media->media_type ) {
				$li_content .= '</a>';
			}

			$li_content .= '</li>';
			$count ++;
		}
		$html .= '<ul class="rtmedia-list ' . esc_attr( $rtmedia_activity_ul_class ) . ' rtmedia-activity-media-length-' . esc_attr( $count ) . '">';
		$html .= $li_content;
		$html .= '</ul>';
		$html .= '</div>';

		return bp_activity_filter_kses( $html );
	}

	/**
	 * @fixme me Why this function is required ?
	 */
	function actions() {

	}

	function media( $media ) {
		$html = false;
		if ( isset( $media->media_type ) ) {
			global $rtmedia;
			if ( 'photo' === $media->media_type ) {
				$thumbnail_id = $media->media_id;
				if ( $thumbnail_id ) {
					list( $src, $width, $height ) = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'rtmedia_activity_image_size', 'rt_media_activity_image' ) );
					$html = '<img alt="' . esc_attr( $media->media_title ) . '" src="' . esc_url( $src ) . '" />';
				}
			} elseif ( 'video' === $media->media_type ) {
				$cover_art = rtmedia_get_cover_art_src( $media->id );
				if ( $cover_art ) {
					$poster = 'poster = "' . esc_url( $cover_art ) . '"';
				} else {
					$poster = '';
				}
				$size = '" width="' . esc_attr( $rtmedia->options['defaultSizes_video_activityPlayer_width'] ) . '" height="' . esc_attr( $rtmedia->options['defaultSizes_video_activityPlayer_height'] ) . '"';
				$html = '[rt_media attachment_id="' . $media->media_id . '" id="rt_media_video_' . esc_attr( $media->id ) . '"' . $size . ']';
			} elseif ( 'music' === $media->media_type ) {
				$width = $rtmedia->options['defaultSizes_music_singlePlayer_width'];
				$width = ( $width * 75 ) / 640;
				$size  = ' style="width:' . esc_attr( $width ) . '%; height:30px;" ';
				if ( ! $size_flag ) {
					$size = '';
				}
				$html = '[rt_media attachment_id="' . $media->media_id . '" ' . $size . ']';
			}
		}

		return apply_filters( 'rtmedia_single_activity_filter', $html, $media, true );
	}
}
