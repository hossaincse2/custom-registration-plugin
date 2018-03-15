<?php
/*
Plugin Name:  Monarach Membership
Plugin URI:   https://developer.wordpress.org/plugins/the-basics/
Description:  Vesper Sporting Club Upcoming Event
Version:      20160911
Author:       PureDev
Author URI:   https://developer.wordpress.org/
License:      GPL2
Text Domain:   monarach
*/

define('EVENT_VERSION','1.0');
define('FORM_ID','16');
define('MONARACE_PLUGIN_DIR',plugin_dir_path( __FILE__ ));
define('MONARACE_PLUGIN_URL', plugin_dir_url(__FILE__ ));

//Load Class
 require_once( MONARACE_PLUGIN_DIR . 'admin/class.monarch_admin.php' );
 require_once( MONARACE_PLUGIN_DIR . 'public/class.monarch_public.php' );
 require_once( MONARACE_PLUGIN_DIR . 'public/inc/ajax_actions.php' );



add_action('init', 'monarch_update_user_status');
 function monarch_update_user_status(){
	 if(isset($_GET['user_id'])&& isset($_GET['status'])){
		 $nonce = $_GET['_nw_nonce'];
 		 Monarch_Public::update_user_role($_GET['user_id'],$_GET['status'],$_GET['current'],$nonce);
	 }
 }

function mb_basename($file)
{
    $url = explode('/',$file);
	return end($url);
}



//Short code Create
 function shortCodeMonarchMembers($attr){

/// define attributes and their defaults
	 $options = shortcode_atts( array (
		 'status' => $attr,
		// 'view' => $attr,
 	 ), $attr );


	 $nonce = wp_create_nonce('new-user-action');

	 if(isset($attr['status'])){
		 $status = $attr['status'];
	 }else{
		 $status = 'pending';
	 }

	 if(isset($attr['view'])){
		 $view_profile = (int)$attr['view'];
	 }else{
		 $view_profile = '1';
	 }

	 $paged = ( get_query_var( 'paged' ) ) ? max( 1, get_query_var('paged') ) : 1;
	 $post_per_page = 10;
	 $args = array(
		 'role' => $status,
		 'meta_key' => 'user_view',
         'meta_value' => $view_profile,
		 'orderby' => 'registered',
         'order' => 'DESC',
		 'number' => $post_per_page,
//		 'number' => 1,
         'paged' => $paged,
	 );

//	 $users = get_users($args);
	 $users = new WP_User_Query( $args );
	 /*if($view_profile) {
		 var_dump( count( $users ) );
		 die();
	 }*/

	// include (MONARACE_PLUGIN_DIR . 'all_users.php');
     if(!is_admin()) {
	     ob_start();
	     ?>

         <table class="display user-data all_users" cellspacing="0" width="100%">
             <thead>
             <tr>
                 <th>Picture</th>
                 <th>Details</th>
                 <th>Action</th>
             </tr>
             </thead>

         </table>


	     <?php
	     ob_end_flush();
     }

 }
add_shortcode('monarach_members', 'shortCodeMonarchMembers');

//Short code 2 Create
 function shortCodeMonarchMemberView($attr){

/// define attributes and their defaults
	 $options = shortcode_atts( array (
		 'status' => $attr,
	 ), $attr );


	 if(isset($attr['status'])){
		 $status = $attr['status'];
	 }else{
		 $status = 'pending';
	 }
	 ob_start();
	 include ('public/member_profile.php');
	 return ob_get_clean();

 }
add_shortcode('monarach_view', 'shortCodeMonarchMemberView');


//Short code 2 Create
function shortCodeMonarchMemberEdit($attr){

/// define attributes and their defaults
	$options = shortcode_atts( array (
		'status' => $attr,
	), $attr );

//	$nonce = wp_create_nonce('new-user-action');
	ob_start();
	include ('public/edit_profile.php');
	return ob_get_clean();

}
add_shortcode('monarach_edit', 'shortCodeMonarchMemberEdit');

function add_slash_if_no_sttp($url){
    if($url == '#' || !$url){
        return $url;
    }
    if(strpos($url, '//') === false){
	    $url = '//' . $url;
    }
    return $url;
}

