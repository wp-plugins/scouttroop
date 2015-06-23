<?php
/**
 * Plugin Name: ScoutTroop
 * Plugin URI: http://troop351.org/scouttroop-wordpress-plugin/
 * Description: A brief description of the plugin.
 * Version: 1.2.1
 * Author: Phil Newman
 * Author URI: http://getyourphil.net
 * License: GPL3 
 * License URI: http://www.gnu.org/licenses/gpl-3.0.en.html
 */
 
 
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include_once plugin_dir_path(__FILE__)."includes/shortcodes.php";
include_once plugin_dir_path(__FILE__)."includes/scouttroop_rank_assignment.php";
include_once plugin_dir_path(__FILE__)."includes/scouttroop_patrol_assignment.php";
include_once plugin_dir_path(__FILE__)."includes/scouttroop_leadership_assignment.php";
include_once plugin_dir_path(__FILE__)."includes/scouttroop_adult_assignment.php";
include_once plugin_dir_path(__FILE__)."includes/scouttroop_inc.php";


if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

register_activation_hook(__FILE__, 'ptn_scouttroop_activate');
function ptn_scouttroop_activate(){
	global $wp_roles;
	$sub = $wp_roles->get_role("subscriber");
	$wp_roles->add_role('scout', 'Scout', $sub->capabilities);
	$wp_roles->add_role('adult', 'Adult', $sub->capabilities);
}

add_action('admin_menu', 'ptn_scouttroop_setup_menu');
function ptn_scouttroop_setup_menu(){
	add_menu_page( 'ScoutTroop Plugin Page', 'Scout Troop', 'manage_options',  __FILE__, 'ptn_scouttroop_init', plugins_url( 'scouttroop/assets/icon-20x20.png'));	
	add_submenu_page( __FILE__, '', '', 'manage_options', __FILE__, '','',''); 
	add_submenu_page( __FILE__, 'Patrol Setup', 'Patrol Admin', 'manage_options', __FILE__.'/patrol_admin', 'ptn_scouttroop_patrol_option_page','ptn_scouttroop_patrol_option_page'); 
	add_submenu_page( __FILE__, 'Assign Patrols', 'Patrols', 'manage_options', __FILE__.'/patrols','tt_render_list_page','tt_render_list_page');
	add_submenu_page( __FILE__, 'Assign Ranks', ' Ranks', 'manage_options', __FILE__.'/rank_assignment','scouttroop_render_rank_page','scouttroop_render_rank_page');
	add_submenu_page( __FILE__, 'Assign Leadership Roles', 'Leadership Roles', 'manage_options', __FILE__.'/leadership','scouttroop_render_leadership_page','scouttroop_render_leadership_page');
	add_submenu_page( __FILE__, 'Assign Adult Roles', 'Adult Roles', 'manage_options', __FILE__.'/adult','scouttroop_render_adult_page','scouttroop_render_adult_page');
}

function ptn_scouttroop_init(){
	echo '<p>Instructions for use go here!</p>';
	echo '<p>And a donate now button.</p>';
?>	
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
	">
	<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
	
<?php		
}

/* Common Functions */
function ptn_scouttroop_get_patrol_list(){

	$ptn_troop_351_patrols = get_option('ptn_scouttroop_options');
	foreach ($ptn_troop_351_patrols as $ptn_scouttroop_patrol){
		if (!empty($ptn_scouttroop_patrol)){
			$patrol_list[$ptn_scouttroop_patrol] = $ptn_scouttroop_patrol;
		}
	}
	return $patrol_list;
}

/* Patrol Functionality */
add_action( 'admin_init', 'ptn_register_patrol_setting' );
function ptn_register_patrol_setting() {
	register_setting( 
		'ptn_scouttroop_options', 
		'ptn_scouttroop_options',
		''
	); 
	add_settings_section( 
		'ptn_scouttroop_main', 
		'', 
		'ptn_scouttroop_section_text', 
		'ptn_scouttroop'
	);
	add_settings_field(
		'ptn_scouttroop_patrol_text', 
		'Enter or edit list of patrols (10 max)', 
		'ptn_scouttroop_setting_input', 
		'ptn_scouttroop', 
		'ptn_scouttroop_main'
	);
} 

function ptn_scouttroop_patrol_option_page(){
	?>
	<div class="wrap">
		<h2>Patrol List</h2>
		<form action="options.php" method="post">
			<?php settings_fields('ptn_scouttroop_options'); ?>
			<?php do_settings_sections('ptn_scouttroop'); ?>
			<input name="Submit" type="submit" value="Save Changes" />
		</form>
	</div>
	<?php
}
function ptn_scouttroop_section_text(){
	echo '<p>Maintain patrol names here.</p>';
}
function ptn_scouttroop_setting_input() {
	$ptn_scouttroop_patrol_options = get_option('ptn_scouttroop_options');
	for ($i =0; $i <= 9; $i++){
		$ptn_scouttroop_patrol_nbr = 'patrol_'.$i;
		echo "<p><input id=$ptn_scouttroop_patrol_nbr name='ptn_scouttroop_options[$ptn_scouttroop_patrol_nbr]' type='text' value='$ptn_scouttroop_patrol_options[$ptn_scouttroop_patrol_nbr]'/></p>";
	}
}
?>
