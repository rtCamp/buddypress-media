<?php

/**
 * Description of RTMediaUploadFile
 * Class responsible for uploading a file to the website.
 * 
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class RTMediaUploadFile {

    var $files;
    var $fake = false;

	/**
	 * Initialize the upload process 
	 * 
	 * @param type $files
	 * @return type
	 */
    function init($files) {

        $this->set_file($files);
        $this->unset_invalid_files();
        $uploaded_file = $this->process();
        return $uploaded_file;
    }

	/**
	 * core process of upload
	 */
    function process() {
        include_once(ABSPATH . 'wp-admin/includes/file.php');
        include_once(ABSPATH . 'wp-admin/includes/image.php');

        $upload_type = $this->fake ? 'wp_handle_sideload' : 'wp_handle_upload';
		foreach ($this->files as $key => $file) {

			$uploaded_file[] = $upload_type($file, array('test_form' => false));
			try {
                if (isset($uploaded_file[$key]['error']) || $uploaded_file[$key] === null) {
                    array_pop($uploaded_file);

                    throw new RTMediaUploadException(0, __('Error Uploading File', 'rt-media'));
                }
                $uploaded_file[$key]['name'] = $file['name'];
            } catch (RTMediaUploadException $e) {
                echo $e->getMessage();
            }

            if (strpos($file['type'], 'image') !== false) {
                if (function_exists('read_exif_data')) {
                    $file = $this->exif($uploaded_file[$key]);
                }
            }
        }

        return $uploaded_file;
    }

    function set_file($files) {
		/**
		 * if files parameter is provided then take th file details from that object
		 */
        if ($files) {
            $this->fake = true;
            $this->populate_file_array((array) $uploaded['files']);
		/**
		 * otherwise check for $_FILES global object from the form submitted
		 */
        } elseif (isset($_FILES['rt_media_file'])) {
            $this->populate_file_array(
                    $this->arrayify($_FILES['rt_media_file'])
            );
        } else {
			/**
			 * No files could be found to upload
			 */
            throw new RTMediaUploadException(UPLOAD_ERR_NO_FILE);
        }
    }

	/**
	 * gather the file information for upload process
	 * @param type $file_array
	 */
    function populate_file_array($file_array) {
        foreach ($file_array as $file) {
            $this->files[] = array(
                'name' => isset($file['name']) ? $file['name'] : '',
                'type' => isset($file['type']) ? $file['type'] : '',
                'tmp_name' => isset($file['tmp_name']) ? $file['tmp_name'] : '',
                'error' => isset($file['error']) ? $file['error'] : '',
                'size' => isset($file['size']) ? $file['size'] : 0,
            );
        }
    }

	/**
	 * Check for valid file types for rtMedia
	 * @global type $rt_media
	 * @param type $file
	 * @return boolean
	 * @throws RTMediaUploadException
	 */
    function is_valid_type($file) {
        try {
			global $rt_media;
			$allowed_types = array();
			foreach ($rt_media->allowed_types as $type) {
				$allowed_types[] = $type['name'];
			}

			if (!preg_match('/' . implode('|', $allowed_types) . '/i', $file['type'], $result) || !isset($result[0])) {
                throw new RTMediaUploadException(UPLOAD_ERR_EXTENSION);
            }
            $this->id3_validate_type($file);
        } catch (RTMediaUploadException $e) {
            echo $e->getMessage();
			return false;
        }
        return true;
    }

	/**
	 * Remove invalid files
	 */
    function unset_invalid_files() {
        $temp_array = $this->files;
        $this->files = null;
        foreach ($temp_array as $key => $file) {
            if ($this->is_valid_type($file)) {
                $this->files[] = $file;
            }
        }
    }

    function id3_validate_type($file) {
        switch ($file['type']) {
            case 'video/mp4' :
            case 'video/quicktime' :
                $type = 'video';
                include_once(trailingslashit(RT_MEDIA_PATH) . 'lib/getid3/getid3.php');
                try {
                    $getID3 = new getID3;
                    $vid_info = $getID3->analyze($file['tmp_name']);
                } catch (Exception $e) {
                    $this->safe_unlink($file['tmp_name']);
                    $activity_content = false;
                    throw new RTMediaUploadException(0, __('MP4 file you have uploaded is corrupt.', 'buddypress-media'));
                }
                if (is_array($vid_info)) {
                    if (!array_key_exists('error', $vid_info) && array_key_exists('fileformat', $vid_info) && array_key_exists('video', $vid_info) && array_key_exists('fourcc', $vid_info['video'])) {
                        if (!($vid_info['fileformat'] == 'mp4' && $vid_info['video']['fourcc'] == 'avc1')) {
                            $this->safe_unlink($file['tmp_name']);
                            $activity_content = false;
                            throw new RTMediaUploadException(0, __('The MP4 file you have uploaded is using an unsupported video codec. Supported video codec is H.264.', 'buddypress-media'));
                        }
                    } else {
                        $this->safe_unlink($file['tmp_name']);
                        $activity_content = false;
                        throw new RTMediaUploadException(0, __('The MP4 file you have uploaded is using an unsupported video codec. Supported video codec is H.264.', 'buddypress-media'));
                    }
                } else {
                    $this->safe_unlink($file['tmp_name']);
                    $activity_content = false;
                    throw new RTMediaUploadException(0, __('The MP4 file you have uploaded is not a video file.', 'buddypress-media'));
                }
                break;
            case 'audio/mpeg' :
            case 'audio/mp3' :
                include_once(trailingslashit(RT_MEDIA_PATH) . 'lib/getid3/getid3.php');
                try {
                    $getID3 = new getID3;
                    $file_info = $getID3->analyze($file['tmp_name']);
                } catch (Exception $e) {
                    $this->safe_unlink($file['tmp_name']);
                    $activity_content = false;
                    throw new RTMediaUploadException(0, __('MP3 file you have uploaded is currupt.', 'buddypress-media'));
                }
                if (is_array($file_info)) {
                    if (!array_key_exists('error', $file_info) && array_key_exists('fileformat', $file_info) && array_key_exists('audio', $file_info) && array_key_exists('dataformat', $file_info['audio'])) {
                        if (!($file_info['fileformat'] == 'mp3' && $file_info['audio']['dataformat'] == 'mp3')) {
                            $this->safe_unlink($file['tmp_name']);
                            $activity_content = false;
                            throw new RTMediaUploadException(0, __('The MP3 file you have uploaded is using an unsupported audio format. Supported audio format is MP3.', 'buddypress-media'));
                        }
                    } else {
                        $this->safe_unlink($file['tmp_name']);
                        $activity_content = false;
                        throw new RTMediaUploadException(0, __('The MP3 file you have uploaded is using an unsupported audio format. Supported audio format is MP3.', 'buddypress-media'));
                    }
                } else {
                    $this->safe_unlink($file['tmp_name']);
                    $activity_content = false;
                    throw new RTMediaUploadException(0, __('The MP3 file you have uploaded is not an audio file.', 'buddypress-media'));
                }
                $type = 'audio';
                break;
            case 'image/gif' :
            case 'image/jpeg' :
            case 'image/png' :
                $type = 'image';
                break;
            default :
                $this->safe_unlink($file['tmp_name']);
                $activity_content = false;
                throw new RTMediaUploadException(0, __('Media File you have tried to upload is not supported. Supported media files are .jpg, .png, .gif, .mp3, .mov and .mp4.', 'buddypress-media'));
        }

        return true;
    }

    function safe_unlink($file_path) {
        if (file_exists($file_path))
            unlink($file_path);
    }

    function exif($file) {
        $file_parts = pathinfo($file['file']);
        if (in_array(strtolower($file_parts['extension']), array('jpg', 'jpeg', 'tiff'))) {
            $exif = read_exif_data($file['file']);
            $exif_orient = isset($exif['Orientation']) ? $exif['Orientation'] : 0;
            $rotateImage = 0;

            if (6 == $exif_orient) {
                $rotateImage = 90;
                $imageOrientation = 1;
            } elseif (3 == $exif_orient) {
                $rotateImage = 180;
                $imageOrientation = 1;
            } elseif (8 == $exif_orient) {
                $rotateImage = 270;
                $imageOrientation = 1;
            }

            if ($rotateImage) {
                if (class_exists('Imagick')) {
                    $imagick = new Imagick();
                    $imagick->readImage($file['file']);
                    $imagick->rotateImage(new ImagickPixel(), $rotateImage);
                    $imagick->setImageOrientation($imageOrientation);
                    $imagick->writeImage($file['file']);
                    $imagick->clear();
                    $imagick->destroy();
                } else {
                    $rotateImage = -$rotateImage;

                    switch ($file['type']) {
                        case 'image/jpeg':
                            $source = imagecreatefromjpeg($file['file']);
                            $rotate = imagerotate($source, $rotateImage, 0);
                            imagejpeg($rotate, $file['file']);
                            break;
                        case 'image/png':
                            $source = imagecreatefrompng($file['file']);
                            $rotate = imagerotate($source, $rotateImage, 0);
                            imagepng($rotate, $file['file']);
                            break;
                        case 'image/gif':
                            $source = imagecreatefromgif($file['file']);
                            $rotate = imagerotate($source, $rotateImage, 0);
                            imagegif($rotate, $file['file']);
                            break;
                        default:
                            break;
                    }
                }
            }
        }
        return $file;
    }

    function arrayify($files) {
        if (isset($files['name']) && !is_array($files['name'])) {
            $updated_files[0] = $files;
        } else {
            foreach ($files as $key => $array) {
                foreach ($array as $index => $value)
                    $updated_files[$index][$key] = $value;
            }
        }
        return $updated_files;
    }

}

?>
