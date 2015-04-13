<?php

/**
 *
 * @link              http://gavinr.com/mailchimp-subscriber-chiclet-for-wordpress
 * @since             1.0.0
 * @package           Mailchimp_Subscriber_Chiclet_For_Wordpress
 *
 * @wordpress-plugin
 * Plugin Name:       Mailchimp Subscriber Chiclet for WordPress
 * Plugin URI:        http://gavinr.com/mailchimp-subscriber-chiclet-for-wordpress
 * Description:       Plugin to show a MailChimp subscriber chiclet to show how many subscribers your list has.
 * Version:           1.0.0
 * Author:            Gavin Rehkemper
 * Author URI:        http://gavinr.com
 * Text Domain:       mailchimp-subscriber-chiclet
 * Domain Path:       /languages
 */
include "MailChimp.php";
function mailchimpSubscriberChiclet($listId, $color, $link, $postFixText) {
	$adminOptions = get_option( 'mc_settings' );
	$MailChimp = new MailChimp($adminOptions['mc_text_field_0']);
	$lists = $MailChimp->call('lists/list');
	$returnString = "";
	
	if($lists) {
		foreach ($lists[data] as $value) {

			if($value[id] == $listId) {
				$returnString .= '<div class="mailchimp-subscriber-chiclet-for-wordpress mailchimp-subscriber-chiclet-for-wordpress-wrapper" title="' . $value[name] . '">';
				if($link == 'true') {
					$returnString .= '<a href="' . $value[subscribe_url_short] . '" class="mainLink" target="_blank" title="Subscribe to ' . $value[name] . '">';
				}
				$returnString .= '<div class="mainButton" style="background-image: url(\''.plugin_dir_url( __FILE__ ).'/images/mailchimp_'.$color.'.png\');">';
				$num = (int)$value[stats][member_count];
				$returnString .= number_format($num) . ' ' . $postFixText;
				$returnString .= '</div>';
				if($link != '') {
					$returnString .= '</a>';
				}
				$returnString .= '</div>';
			}
		}
	}
	return $returnString;	
}
function echoSubscriberChiclet($atts) {
	$listId = isset($atts[listid])?$atts[listid]:'';
	$color = isset($atts[color])?$atts[color]:'blue';
	$showLink = isset($atts[showlink])?$atts[showlink]:'true';
	$postFixText = isset($atts[postfixtext])?$atts[postfixtext]:' Subscribers';
	return mailchimpSubscriberChiclet($listId, $color, $showLink, $postFixText); // b6ebcd28ea
}
function mscw_register_shortcodes(){
   add_shortcode('subscriber-chiclet', 'echoSubscriberChiclet');
}
function mscw_register_styles() {
	wp_enqueue_style( 'mscw-main-style', plugin_dir_url( __FILE__ ) . 'css/main.css' );

}
add_action( 'init', 'mscw_register_shortcodes');
add_action( 'init', 'mscw_register_styles');
add_filter('widget_text', 'do_shortcode');

function showGenerateShortcodeSection() {
	$adminOptions = get_option( 'mc_settings' );
	$apiKey = $adminOptions['mc_text_field_0'];

	if($apiKey) {
		$MailChimp = new MailChimp($apiKey ); 
		$lists = $MailChimp->call('lists/list');
		if($lists) {
			echo '1. Choose your list:<br />';
			echo '<select id="shortcodeSelectListId" class="shortcodeSelect">';
			echo '<option></option>';
			foreach ($lists[data] as $value) {
				echo '<option data-mc-code="'.$value[id].'">' . $value[name] . '</option>';
			} ?>
			</select><br /><br />

			2. Choose your settings:<br />
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">Color:</th>
						<td>
							<select id="shortcodeSelectColor" class="shortcodeSelect">
								<option value="blue">Blue</option>
								<option value="green">Green</option>
								<option value="black">Black</option>
								<option value="red">Red</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">Show Link:</th>
						<td><input type="checkbox" id='shortcodeShowLink' value="checked"  class="shortcodeSelect"></td>
					</tr>
					<tr>
						<th scope="row">Post Numeral Text:</th>
						<td><input type="text" id='shortcodePostfixText' value=" Subscribers"  class="shortcodeSelect"></td>
					</tr>
				</tbody>
			</table>
			<br /><br />
			3. Copy this shortcode and paste it into your page or post:<br />
			<input id="mscw_shortCodeResult" type="text"></input>
			<?php
		}
	} else {
		echo '(set api first)';
	}
	
}


// ADMIN MENU:
class mailchimp_subscriber_chiclet_options_page {
	function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'mc_settings_init' ) );
	}
	function admin_menu () {
		add_options_page('MailChimp Subscriber Chiclet Options', 'MailChimp Subscriber Chiclet', 'manage_options', 'mailchimp-subscriber-chiclet-options.php', array( $this, 'mc_options_page' ) );
	}
	function enqueueScripts() {
		wp_enqueue_script('mscw-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ));
		wp_enqueue_style( 'mscw-admin-style', plugin_dir_url( __FILE__ ) . 'css/admin.css' );
	}
	function mc_settings_init(  ) { 
		
		register_setting( 'mscwPluginPage', 'mc_settings' );

		add_settings_section(
			'mc_mscwPluginPage_section', 
			__( '', 'mc' ), 
			array( $this, 'mc_settings_section_callback'), 
			'mscwPluginPage'
		);

		add_settings_field( 
			'mc_text_field_0', 
			__( 'MailChimp API Key:', 'mc' ), 
			array( $this, 'mc_text_field_0_render'), 
			'mscwPluginPage', 
			'mc_mscwPluginPage_section' 
		);

		
	}
	function mc_text_field_0_render(  ) { 
		$options = get_option( 'mc_settings' );
		?>
		<input type='text' name='mc_settings[mc_text_field_0]' value='<?php echo $options['mc_text_field_0']; ?>'>
		<?php
	}
	function mc_settings_section_callback(  ) { 
		echo __( 'To get your MailChimp API key, login to your MailChimp account, click your username (top right), Account > Extras > API Keys. Click "Create a Key" and copy/paste the key here. Then click "Save Changes". Alternatively, click <a href="https://us7.admin.mailchimp.com/account/api-key-popup/" target="_blank">here</a> and copy/paste your key.', 'mc' );
	}

	

	function mc_options_page(  ) { 
		?>
		<form action='options.php' method='post'>
			<h1>MailChimp Subscriber Chiclet</h1>
			<h3>I. Settings</h3>
			<?php
			settings_fields( 'mscwPluginPage' );
			do_settings_sections( 'mscwPluginPage' );
			submit_button(); ?>
			<hr />
			<h3>II. Generate Shortcode</h3>
			<?php
			showGenerateShortcodeSection();
			?>
			
		</form>
		<?php
	}
}
new mailchimp_subscriber_chiclet_options_page;