<?php
/*
Plugin Name: Spam statistics
Plugin URI: http://www.jenruno.com/SpamTask/
Description: Show spam statistics on your blog.
Author: Jenruno
Version: 0.8.1
Author URI: http://www.jenruno.com/
*/

$statisticsSettings = array('24 hours', '48 hours', '7 days', '15 days', '30 days');

if(!get_option('spamtask_api_key')) {
	add_action('admin_notices', create_function('', "echo '<div class=\"error\"><p>';_e('Your blog will need the latest <a href=\"http://wordpress.org/extend/plugins/spamtask/\" title=\"SpamTask\">SpamTask</a> version to be able to show spam statistics.'); echo '</p></div>';"));
} else {
	if(!get_option('spamtask_statistics_mode')) {
		add_option('spamtask_statistics_mode', 'total', FALSE, 'yes');
		foreach($statisticsSettings AS $s) {
			add_option('spamtask_statistics_'.str_replace(' ', '', $s).'_text', '[count] spam caught the last '.$s.'.', FALSE, 'yes');
		}
		add_option('spamtask_statistics_total_text', '[count] spam caught.', FALSE, 'yes');
	}
}

if(!class_exists("SpamStatistics")) {
	class SpamStatistics {
		function ShowCount() {
			if(get_option('spamtask_statistics_mode')) {
				$mode = get_option('spamtask_statistics_mode');
				$count = get_option('spamtask_statistics_'.$mode);
				$text = get_option('spamtask_statistics_'.$mode.'_text');
				if(!$count) { $count = '0'; }
				echo '<center>'.str_replace('[count]', $count, $text).'</center>';
			}
		}

		function addPages() {

			function spam_statistics_filter_plugin_actions($links, $file) {
				static $this_plugin;
				if(!$this_plugin) $this_plugin = plugin_basename(__FILE__);

				if($file == $this_plugin) {
				    $settings_link = '<a href="options-general.php?page=spam_statistics_settings">' . __('Settings') . '</a>';
				    array_unshift($links, $settings_link);
				}
				return $links;
			}

			add_options_page('Spam statistics', 'Spam statistics', 8, 'spam_statistics_settings', 'Spam_Statistics_Settings');
			add_filter('plugin_action_links', 'spam_statistics_filter_plugin_actions', 10, 2 );

			function Spam_Statistics_Settings() {
				global $statisticsSettings;
				?>
				<div class="wrap" style="margin: 0 10px;">

					<h2>Configure spam statistics</h2>
					<small>Spam statistics requires the latest version of <a href="http://wordpress.org/extend/plugins/spamtask/" title="SpamTask">SpamTask</a> installed.</small>
					<p>Spam statistics places simple text in the footer of your blog, showing how many spam messages have been caught in a specific period of time. The statistics update every time a spam comment or relevant comment is submitted to your blog.</p>
					<p>You can change the text for the options below. <i>[count]</i> is replaced with the appropriate amount of checked spam comments.</p>
				
					<form method="post" action="options.php">
					<?php
						wp_nonce_field('update-options');
						foreach($statisticsSettings AS $s) {
					?>
						<br />
						<label for="<?php echo str_replace(' ', '', $s); ?>"><input type="radio" id="<?php echo str_replace(' ', '', $s); ?>_mode" name="spamtask_statistics_mode" value="<?php echo str_replace(' ', '', $s); ?>"<?php if(get_option('spamtask_statistics_mode') == str_replace(' ', '', $s)) { echo ' checked="checked"'; } ?> /> <b>Last <?php echo $s; ?></b> - <i><? echo str_replace('[count]', get_option('spamtask_statistics_'.str_replace(' ', '', $s)), get_option('spamtask_statistics_'.str_replace(' ', '', $s).'_text')); ?></i></label><br />
						<textarea id="<?php echo str_replace(' ', '', $s); ?>_text" name="spamtask_statistics_<?php echo str_replace(' ', '', $s); ?>_text" cols="65" rows="1"><?php echo get_option('spamtask_statistics_'.str_replace(' ', '', $s).'_text'); ?></textarea><br />
					<? } ?>
					<br />
					<label for="total"><input type="radio" id="total_mode" name="spamtask_statistics_mode" value="total"<?php if(get_option('spamtask_statistics_mode') == 'total') { echo ' checked="checked"'; } ?> /> <b>Total spam messages</b> - <i><? echo str_replace('[count]', get_option('spamtask_statistics_total'), get_option('spamtask_statistics_total_text')); ?></i></label><br />
					<textarea id="total_text" name="spamtask_statistics_total_text" cols="65" rows="1"><?php echo get_option('spamtask_statistics_total_text'); ?></textarea><br />

					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="page_options" value="spamtask_statistics_total_text, <?php foreach($statisticsSettings AS $s) { echo ', spamtask_statistics_'.str_replace(' ', '', $s).'_text'; } ?>, spamtask_statistics_mode" />
					<p class="submit"><input type="submit" name="submit" class="submit" value="<?php _e('Save Changes') ?>" /></p>

					</form>
				</div>
				<?
			}
		}
	}
}

if(class_exists("SpamStatistics")) { $SpamStatistics = new SpamStatistics(); }

if(isset($SpamStatistics)) {
	add_action('wp_footer', array(&$SpamStatistics, 'ShowCount'));
	add_action('admin_menu', array(&$SpamStatistics, 'addPages'));
}

?>