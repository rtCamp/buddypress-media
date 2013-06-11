<?php

/**
 * Description of RTMediaUploadException
 *
 * @author joshua
 */
class RTMediaUploadException extends Exception 
{ 
	var $upload_err_invalid_context = 9;
    public function __construct($code,$msg=false) { 
        $message = $this->codeToMessage($code,$msg); 
        parent::__construct($message, $code); 
    } 

    private function codeToMessage($code,$msg)
    { 
        switch ($code) { 
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE: 
                $message = apply_filters('bp_media_file_size_error', __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form','rt-media')); 
                break; 
            case UPLOAD_ERR_NO_FILE: 
                $message = apply_filters('bp_media_file_null_error', __('No file was uploaded','rt-media')); 
                break; 
            case UPLOAD_ERR_PARTIAL: 
            case UPLOAD_ERR_NO_TMP_DIR: 
            case UPLOAD_ERR_CANT_WRITE: $message = apply_filters('bp_media_file_internal_error', __('Uploade failed due to internal server error.','rt-media')); 
                break; 
            case UPLOAD_ERR_EXTENSION: 
                $message = apply_filters('bp_media_file_extension_error', __('File type not allowed.','rt-media')); 
                break; 
			
			case $this->upload_err_invalid_context:
				$message = apply_filters('rt_media_invalid_context_error', __('Invalid Context for upload.','rt-media')); 
				break;
            default: 
                $msg = $msg ? $msg : __('Unknown file upload error.','rt-media');
                $message = apply_filters('bp_media_file_unknown_error', $msg); 
                break; 
        } 
        return $message; 
    } 
} 

?>
