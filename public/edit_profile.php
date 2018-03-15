<?php
if(isset($_GET['user_id'])){
    $user_id = $_GET['user_id'];
	if ( ! wp_verify_nonce( $_GET['_nw_nonce'], 'new-user-action' ) ) {
		echo "<script>document.location.href = '" . home_url() ."';</script>";
 		exit;
	}
	$user_data = Monarch_Public::user_view_data($_GET['user_id'],$_GET['_nw_nonce']);
	$user_roles=$user_data[0]->roles;
} else {
	return '';
}


$age_date = explode('/', $user_data['dob'][0]);
$aarray = array_column($user_data, 'first_name');

$first_name = isset($user_data['first_name'][0]) ? $user_data['first_name'][0] : '';
$last_name = isset($user_data['last_name'][0]) ? $user_data['last_name'][0] : '';
$user_email = isset($user_data[0]->user_email) ? $user_data[0]->user_email : '';
$description = isset($user_data['description'][0]) ? $user_data['description'][0] : '';
$phone = isset($user_data['phone'][0]) ? $user_data['phone'][0] : '';
$dob = isset($user_data['dob'][0]) ? $user_data['dob'][0] : '';
$gender = isset($user_data['gender'][0]) ? $user_data['gender'][0] : '';
$hospitality = isset($user_data['hospitality'][0]) ? $user_data['hospitality'][0] : '';
$before_work = isset($user_data['before_work'][0]) ? $user_data['before_work'][0] : '';
$facebook_profile = isset($user_data['facebook_profile'][0]) ? $user_data['facebook_profile'][0] : '';
$instagram_handle = isset($user_data['instagram_handle'][0]) ? $user_data['instagram_handle'][0] : '';
$linkedIn = isset($user_data['linkedIn'][0]) ? $user_data['linkedIn'][0] : '';
$profile_img = isset($user_data['picture'][0]) ? $user_data['picture'][0] : '';
$thumb_nail = str_replace(mb_basename($profile_img), '', $profile_img) . '_thumb_' . mb_basename($profile_img);
$gravityform_id = FORM_ID;
$site_url = site_url();

$gravity_form_shotcode = <<<DDD
[gravityform id={$gravityform_id} field_values="first_name={$first_name}&last_name={$last_name}&email={$user_email}&phone={$phone}&dob={$dob}&gender={$gender}&hospitality={$hospitality}&before_work={$before_work}&facebook={$facebook_profile}&instagram={$instagram_handle}&linkedIn={$linkedIn}&referrals={$description}"]
DDD;


	     echo do_shortcode( $gravity_form_shotcode );

//echo do_shortcode( "[gravityforms id=16 field_values='first_name=My Awesome Event']");
//&referrals={$description}
  echo "<script>

   var profile_image = '<img src={$thumb_nail} />';
   var profile_view =  '<p class=\"view_edit user-action custom_view\"><a class=\"action-view\" href=\"{$site_url}/membership-view-page?user_id={$user_id}\"><i class=\"fa fa-user fa-2x custom-question\"></i></a></p>';
  
 </script>";



