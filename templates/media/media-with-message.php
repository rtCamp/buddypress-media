<?php

//rtm_bp_message_media_add_button function will add Media attachment button to both Compose message tab and Send a reply in BuddyPress
function rtm_bp_message_media_add_button(){
	?>
	<label for="rtm_media_message_content"><?php _e( 'Attach Media ( Optional )', 'buddypress-media' ); ?></label>
	<input type="file" name="rtm_media_message_content" id="rtm_media_message_content" />

	<?php
}

//Adding Browse button under message in Compose tab
add_action( 'bp_after_messages_compose_content', 'rtm_bp_message_media_add_button' );

//Adding Browse button under message in Send reply tab
add_action( 'bp_after_message_reply_box', 'rtm_bp_message_media_add_button' );

function rtm_bp_message_media_show_media(){
    ?>
    <div class="rtm-media-message">
        <img src="https://static.easygenerator.com/wp-content/uploads/2013/09/demo.jpg">
        <img src="https://static.easygenerator.com/wp-content/uploads/2013/09/demo.jpg">
        <img src="https://static.easygenerator.com/wp-content/uploads/2013/09/demo.jpg">
        <img src="https://static.easygenerator.com/wp-content/uploads/2013/09/demo.jpg">
        <img src="https://static.easygenerator.com/wp-content/uploads/2013/09/demo.jpg">
        <img src="https://static.easygenerator.com/wp-content/uploads/2013/09/demo.jpg">
        <img src="https://static.easygenerator.com/wp-content/uploads/2013/09/demo.jpg">
    </div>
<?php
}

add_action( 'bp_after_message_content', 'rtm_bp_message_media_show_media' );