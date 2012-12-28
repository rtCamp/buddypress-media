<?php
/**
 * Description of BPMediaSettings
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!class_exists('BPMediaSettings')) {

    class BPMediaSettings {

        public function __construct() {
            add_action('admin_init', array($this, 'settings'));
        }

        /**
         * Register Settings
         * 
         * @global string $bp_media->text_domain
         */
        public function settings() {
            global $bp_media, $bp_media_addon;
            add_settings_section('bpm-settings', __('BuddyPress Media Settings', $bp_media->text_domain), '', 'bp-media-settings');
            add_settings_field('bpm-video', __('Video', $bp_media->text_domain), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array('option' => 'videos_enabled', 'desc' => __('Check to enable video upload functionality', $bp_media->text_domain)));
            add_settings_field('bpm-audio', __('Audio', $bp_media->text_domain), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array('option' => 'audio_enabled', 'desc' => __('Check to enable audio upload functionality', $bp_media->text_domain)));
            add_settings_field('bpm-image', __('Images', $bp_media->text_domain), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array('option' => 'images_enabled', 'desc' => __('Check to enable images upload functionality', $bp_media->text_domain)));
            add_settings_field('bpm-download', __('Download', $bp_media->text_domain), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array('option' => 'download_enabled', 'desc' => __('Check to enable download functionality', $bp_media->text_domain)));
            add_settings_section('bpm-spread-the-word', __('Spread the Word', $bp_media->text_domain), '', 'bp-media-settings');
            add_settings_field('bpm-spread-the-word-settings', __('Spread the Word', $bp_media->text_domain), array($this, 'radio'), 'bp-media-settings', 'bpm-spread-the-word', array('option' => 'remove_linkback', 'radios' => array(2 => __('Yes, I support BuddyPress Media', $bp_media->text_domain), 1 => __('No, I don\'t want to support BuddyPress Media', $bp_media->text_domain)), 'default' => 2));
            add_settings_section('bpm-other', __('BuddyPress Media Other Options', $bp_media->text_domain), '', 'bp-media-settings');
            add_settings_field('bpm-other-settings', __('Re-Count Media Entries', $bp_media->text_domain), array($this, 'button'), 'bp-media-settings', 'bpm-other', array('option' => 'refresh-count', 'name' => 'Re-Count', 'desc' => __('It will re-count all media entries of all users and correct any discrepancies.', $bp_media->text_domain)));
            $bp_media_addon = new BPMediaAddon();
            add_settings_section('bpm-addons', __('BuddyPress Media Addons for Audio/Video Conversion', $bp_media->text_domain), array($bp_media_addon, 'get_addons'), 'bp-media-addons');
            add_settings_section('bpm-support', __('Submit a request form', $bp_media->text_domain), '', 'bp-media-support');
            add_settings_field('bpm-request', __('Request Type', $bp_media->text_domain), array($this, 'dropdown'), 'bp-media-support', 'bpm-support', array('option' => 'select-request', 'none' => false, 'values' => array('premium_support' => 'Premium Support', 'new_feature' => 'Suggest a New Feature', 'bug_report' => 'Submit a Bug Report')));
            register_setting('bp_media', 'bp_media_options', array($this, 'sanitize'));
        }

        /**
         * Sanitizes the settings
         */
        public function sanitize($input) {
            global $bp_media, $bp_media_admin;
            if (isset($_POST['refresh-count'])) {
                if ($bp_media_admin->update_count())
                    add_settings_error('Recount Success', 'recount-success', __('Recounting of media files done successfully', $bp_media->text_domain), 'updated');
                else
                    add_settings_error('Recount Fail', 'recount-fail', __('Recounting Failed', $bp_media->text_domain));
            }
            return $input;
        }

        /**
         * Output a checkbox
         * 
         * @global type $bp_media
         * @param array $args
         */
        public function checkbox($args) {
            global $bp_media;
            $options = $bp_media->options;
            $defaults = array(
                'option' => '',
                'desc' => '',
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);
            if (empty($option)) {
                trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the follwoing format array( \'option\' => \'option_name\' ) ', $bp_media->text_domain));
                return;
            }
            if (!isset($options[$option]))
                $options[$option] = '';
            ?>
            <label for="<?php echo $option; ?>">
                <input<?php checked($options[$option]); ?> name="bp_media_options[<?php echo $option; ?>]" id="<?php echo $option; ?>" value="1" type="checkbox" />
                <?php echo $desc; ?>
            </label><?php
        }

        /**
         * Outputs Radio Buttons
         * 
         * @global type $bp_media
         * @param array $args
         */
        public function radio($args) {
            global $bp_media;
            $options = $bp_media->options;
            $defaults = array(
                'option' => '',
                'radios' => array(),
                'default' => '',
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);
            if (empty($option) || ( 2 > count($radios) )) {
                if (empty($option))
                    trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the follwoing format array( \'option\' => \'option_name\' )', $bp_media->text_domain));
                if (2 > count($radios))
                    trigger_error(__('Need to specify atleast to radios else use a checkbox instead', $bp_media->text_domain));
                return;
            }
            if (empty($options[$option])) {
                $options[$option] = $defaults;
            }
            foreach ($radios as $value => $desc) {
                    ?>
                <label for="<?php echo sanitize_title($desc); ?>"><input<?php checked($options[$option], $value); ?> value='<?php echo $value; ?>' name='bp_media_options[<?php echo $option; ?>]' id="<?php echo sanitize_title($desc); ?>" type='radio' /><?php echo $desc; ?></label><br /><?php
            }
        }

        /**
         * Outputs Dropdown
         * 
         * @global type $bp_media
         * @param array $args
         */
        public function dropdown($args) {
            global $bp_media;
            $options = $bp_media->options;
            global $bp_media;
            $defaults = array(
                'option' => '',
                'none' => true,
                'values' => ''
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);
            if (empty($option) || empty($values)) {
                if (empty($option))
                    trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the follwoing format array( \'option\' => \'option_name\' )', $bp_media->text_domain));
                if (empty($values))
                    trigger_error(__('Please provide some values to populate the dropdown. Format : array( \'value\' => \'option\' )', $bp_media->text_domain));
                return;
            }
                ?>
            <select name="<?php echo $option; ?>" id="<?php echo $option; ?>"><?php if ($none) { ?>
                    <option><?php __e('None', $bp_media->text_domain); ?></option><?php
            }
            foreach ($values as $value => $text) {
                    ?>
                    <option value="<?php echo $value; ?>"><?php echo $text; ?></option><?php }
                ?>
            </select><?php
        }

        /**
         * Outputs a Button
         * 
         * @global type $bp_media
         * @param array $args
         */
        public function button($args) {
            global $bp_media;
            $defaults = array(
                'option' => '',
                'name' => 'Save Changes',
                'desc' => '',
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);
            if (empty($option)) {
                trigger_error('Please provide "option" value ( Required ) in the argument. Pass argument to add_settings_field in the follwoing format array( \'option\' => \'option_name\', \'link\' => \'linkurl\' )');
                return;
            }
            submit_button($name, '', $option, false);
            if (!empty($desc)) {
                    ?>
                <span class="description"><?php echo $desc; ?></a><?php
            }
        }

    }

}
    ?>
