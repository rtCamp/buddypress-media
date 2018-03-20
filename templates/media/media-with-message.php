<?php

//rtm_bp_message_media_add_button function will add Media attachment button to both Compose message tab and Send a reply in BuddyPress
function rtm_bp_message_media_add_button(){
	?>
	<label for="rtm_media_message_content"><?php _e( 'Attach Media ( Optional )', 'buddypress' ); ?></label>
	<input type="file" name="rtm_media_message_content" id="rtm_media_message_content" />

	<?php
}

//Adding Browse button under message in Compose tab
add_action( 'bp_after_messages_compose_content', 'rtm_bp_message_media_add_button' );

//Adding Browse button under message in Send reply tab
add_action( 'bp_after_message_thread_reply', 'rtm_bp_message_media_add_button' );