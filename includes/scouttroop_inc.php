<?php
/* Common functions */
function first_last_init($id){
	$user_info = get_user_by('id', $id);
	$first_last_initial = $user_info->first_name.' '.$user_info->last_name[0];
	return $first_last_initial;
}
function is_adult($id){
	$ptn_scouttroop_roles = get_user_meta($id,'wp_capabilities',true);
	if (array_key_exists("adult",$ptn_scouttroop_roles)){
		return true;
	}
	return false; 
}
function is_scout($id){
	$ptn_scouttroop_roles = get_user_meta($id,'wp_capabilities',true);
	if (array_key_exists("scout",$ptn_scouttroop_roles)){
		return true;
	}
	return false; 
}
/* End Common Functions */ 
?>