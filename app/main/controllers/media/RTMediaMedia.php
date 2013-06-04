<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaMedia
 *
 * @author Udit Desai <udit.desai@rtcamp.com>
 */
class RTMediaMedia {
	
	var $rt_media_model;
	
	public function __construct() {

		$this->rt_media_model = new RTMediaModel();
	}

	public function delete_hook() {
		add_action('delete_attachment',array($this,'delete_row'));
	}

	function add($uploaded, $file_object) {

        $attachments = $this->generate_post_array($uploaded, $file_object);

        $attachment_ids = $this->insert_attachment($attachments, $file_object);

		if($this->activity_enabled())
			$this->insert_activity($uploaded);

        $this->rt_insert_media($attachment_ids,$uploaded);

        return array_keys($attachment_ids);
    }

	function import_attachment() {
		
	}
	
	function move($media_id, $album_id) {
		$data = array( 'album_id' => $album_id );
		$where = array( 'media_id' => $media_id );
		$status = $this->rt_media_model->update($data, $where);
	}

	function update($media_id) {
		
	}
	
	function activity_enabled() {
		return false;
	}	
	
	function delete($media_id){
        $model = new RTMediaModel();
        $model->delete($media_id);
    }
	
	function generate_post_array($uploaded, $file_object) {
        foreach ($file_object as $file) {
            $attachments[] = array(
                'post_mime_type' => $file['type'],
                'guid' => $file['url'],
                'post_title' => $uploaded['title'] ? $uploaded['title'] : $file['name'],
                'post_content' => $uploaded['description'] ? $uploaded['description'] : '',
                'post_parent' => $uploaded['album_id'] ? $uploaded['album_id'] : 0,
            );
        }
        return $attachments;
    }

    function insert_attachment($attachments, $file_object) {
        foreach ($attachments as $key => $attachment) {
            $attachment_id = wp_insert_attachment($attachment, $file_object[$key]['file'], $attachment['post_parent']);
            if (!is_wp_error($attachment_id)) {
                add_filter('intermediate_image_sizes', array($this, 'rt_media_image_sizes'), 99);
                wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $file_object[$key]['file']));
            } else {
                unlink($file_object[$key]['file']);
                throw new Exception(__('Error creating attachment for the media file, please try again', 'buddypress-media'));
            }
            $updated_attachment_ids[$attachment_id] = $attachment;
        }

        return $updated_attachment_ids;
    }

    function rt_media_image_sizes($sizes) {
        return array('bp_media_thumbnail', 'bp_media_activity_image', 'bp_media_single_image');
    }

	function rt_insert_album($attributes) {

		$this->rt_media_model->insert($attributes);
	}
	
	function rt_update_media($media_id, $attributes) {
		
	}

    function rt_insert_media($attachment_ids,$uploaded){

        $blog_id = get_current_blog_id();

		foreach ($attachment_ids as $key => $attachment) {
            $mime_type = explode('/',$attachment['post_mime_type']);
            $this->rt_media_model->insert(
                    array(
                        'blog_id' => $blog_id,
                        'media_id' => $key,
                        'media_type' => $mime_type[0],
                        'context' => $uploaded['context'],
                        'context_id' => $uploaded['context_id'],
                        'activity_id' => $uploaded['activity_id'],
                        'privacy' => $uploaded['privacy']
            ));
        }
    }
    
    function insert_activity(){
        global $bp;
        if (function_exists('bp_activity_add')) {
            $update_activity_id = false;
            if (!is_object($media)) {
                try {
                    $media = new BPMediaHostWordpress($media);
                } catch (exception $e) {
                    return false;
                }
            }
            $activity_content = $media->get_media_activity_content();
            $args = array(
                'action' => apply_filters('bp_media_added_media', sprintf(__('%1$s added a %2$s', 'buddypress-media'), bp_core_get_userlink($media->get_author()), '<a href="' . $media->get_url() . '">' . $media->get_media_activity_type() . '</a>')),
                'content' => $activity_content,
                'primary_link' => $media->get_url(),
                'item_id' => $media->get_id(),
                'type' => 'activity_update',
                'user_id' => $media->get_author()
            );

            $hidden = apply_filters('bp_media_force_hide_activity', $hidden);

            if ($activity || $hidden) {
                $args['secondary_item_id'] = -999;
            } else {
                $update_activity_id = get_post_meta($media->get_id(), 'bp_media_child_activity', true);
                if ($update_activity_id) {
                    $args['id'] = $update_activity_id;
                    $args['secondary_item_id'] = false;
                }
            }

            if ($hidden && !$activity) {
                do_action('bp_media_album_updated', $media->get_album_id());
            }

            if ($group) {
                $group_info = groups_get_group(array('group_id' => $group));
                $args['component'] = $bp->groups->id;
                $args['item_id'] = $group;
                if ('public' != $group_info->status) {
                    $args['hide_sitewide'] = 1;
                }
            }

            $activity_id = BPMediaFunction::record_activity($args);

            if ($group)
                bp_activity_update_meta($activity_id, 'group_id', $group);

            if (!$update_activity_id)
                add_post_meta($media->get_id(), 'bp_media_child_activity', $activity_id);
        }
    }

	
}

?>
