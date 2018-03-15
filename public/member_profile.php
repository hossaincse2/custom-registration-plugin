<?php

$nonce = wp_create_nonce('new-user-action');

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : get_current_user_id();


if($user_id){

    update_user_meta($user_id, 'user_view', 1);

	$user_data = Monarch_Public::user_view_data($user_id);

	  $status = isset($user_data[0]->roles[0]) ? $user_data[0]->roles[0] : '';
      $user_view = isset($user_data['user_view'][0]) ? $user_data['user_view'][0] : '';


 	$privious_user  = Monarch_Public::previous_url_id_generate($status,$user_id);
	$next_user  = Monarch_Public::next_url_id_generate($status,$user_id);
	$pev = isset($privious_user) ? $privious_user : $user_id;
	$next = isset($next_user) ? $next_user : $user_id;

	$male = Monarch_Public::gender_count_for_view_page('Male','subscriber',$user_view);
	$female = Monarch_Public::gender_count_for_view_page('Female','subscriber',$user_view);

//	if($_GET['status'] == 'subscriber'){
//		$next_url = Monarch_Public::set_transient_view_next_url($next);
//    }


//	echo $view_page_url = get_transient("view_page_url");
//	if(empty($view_page_url))
//	{
//	    echo 'sadsd';
//		set_transient("view_page_url", site_url().'/membership-view-pag?user_id='.$next, DAY_IN_SECONDS);
//	}else{
//		delete_transient("view_page_url");
//	}


 } else {
	return '';
}

$fb_url = !empty($user_data['facebook_profile'][0]) ? $user_data['facebook_profile'][0] : '#';
$inst_url = !empty($user_data['instagram_handle'][0]) ? 'https://www.instagram.com/' . str_replace('@', '', $user_data['instagram_handle'][0]) : '#';
$lnk_url = !empty($user_data['linkedIn'][0]) ? $user_data['linkedIn'][0] : '#';


$fb_url = add_slash_if_no_sttp($fb_url);
$inst_url = add_slash_if_no_sttp($inst_url);
$lnk_url = add_slash_if_no_sttp($lnk_url);
 ?>

<div id="wrapper" class="clearfix mydivs user_status_<?php echo $status;?>">
       <div class="view_aside">


	       <div class="profile_img" style="background-image: url('<?php echo $user_data['picture'][0];?>');">
	          <img class="member-img" src="<?php echo $user_data['picture'][0];?>" alt="">
               <?php
               if($status == 'pending' && $user_data['user_view'][0] == 0){
                   echo '<i class="fa fa-user fa-1x custom-like-on-image"></i>';
               }elseif ($status == 'pending'){
	               echo '<i class="fa fa-pause-circle fa-1x custom-like-on-image"></i>';
               }elseif ($status == 'subscriber'){
	               echo '<i class="fa fa-check-circle fa-1x custom-like-on-image"></i>';
               }elseif ($status == 'reject'){
	               echo '<i class="fa fa-close fa-1x custom-like-on-image"></i>';
               }
               if($next) { ?>
                   <a class="left_user" href="<?php echo site_url() . '/membership-view-pag?user_id=' . $next; ?>"><i
                               class="fa fa-angle-left fa-2x"></i></a>
               <?php }
               if($pev) { ?>
                   <a class="right_user" href="<?php echo site_url() . '/membership-view-page?user_id=' . $pev; ?>"><i
                               class="fa fa-angle-right fa-2x"></i></a>
               <?php } ?>

	       </div>
           <h3><?php echo isset($user_data['first_name'][0]) ? $user_data['first_name'][0] : '';
		       echo '  ';
		       echo isset($user_data['last_name'][0]) ? $user_data['last_name'][0] : ''; ?></h3>
	       <div class="view_social">
		       <a target="_blank" href="<?php echo $fb_url;?>"><i class="fa fa-facebook custome-icon-circle"></i></a>
               <a target="_blank" href="<?php echo $inst_url;?>"><i class="fa fa-instagram custome-icon-circle"></i></a>
		       <a target="_blank" href="<?php echo $lnk_url;?>"><i class="fa fa-linkedin custome-icon-circle"></i></a>
	       </div>

       </div>
	  <div class="view_content">

              <table class="view_all_data">
	              <tr>
		              <td class="text-left" >Email:</td>
		              <td class="text-right"><?php echo isset($user_data[0]->user_email)? $user_data[0]->user_email : ''; ?></td>
	              </tr>

	              <tr>
		              <td class="text-left">Phone:</td>
		              <td class="text-right"><?php echo isset($user_data['phone'][0])? $user_data['phone'][0] : ''; ?></td>
	              </tr>
	              <tr>
		              <td class="text-left">Date of Birth:</td>
		              <td class="text-right"><?php echo isset($user_data['dob'][0])? $user_data['dob'][0] : ''; ?></td>
	              </tr>
	              <tr>
		              <td class="text-left">Gender:</td>
		              <td class="text-right"><?php echo isset($user_data['gender'][0])? $user_data['gender'][0] : ''; ?></td>
	              </tr>
	              <tr>
		              <td class="text-left">Hospitality: </td>
		              <td class="text-right"><?php echo isset($user_data['hospitality'][0])? $user_data['hospitality'][0] : ''; ?></td>
	              </tr>
	              <tr>
		              <td class="text-left">Work: </td>
		              <td class="text-right"><?php echo isset($user_data['before_work'][0])? $user_data['before_work'][0] : ''; ?></td>
	              </tr>

	              <tr>
                      <td class="text-left" colspan="2">Good Fit: </td>
	              </tr>
	              <tr>
 		              <td class="text-left" style="padding-top: 0;"  colspan="2"><?php echo isset($user_data[0]->description)? $user_data[0]->description : ''; ?></td>
	              </tr>
              </table>

          <!--<div class="view_edit">
              <a class="edit-btn" href="<?php /*echo get_permalink().'/member-edit'; */?>?user_id=<?php /*echo $user_data[0]->ID. "&_nw_nonce={$nonce}"; */?>">Edit Profile</a>
          </div>-->

              <div class="view_edit user-action">
                  <?php if ( ( is_user_logged_in() && current_user_can( 'manage_options' ) ) ) { ?>
                    <a class="accepted  action-accept" data-url="<?php echo get_permalink(); ?>?user_id=<?php echo $user_id. "&_nw_nonce={$nonce}"; ?>&status=subscriber&current=<?php echo $status; ?>" href="#"><i class="fa fa-check fa-2x custom-like"></i></a>
                    <a  class="rejected action-reject"  data-url="<?php echo get_permalink(); ?>?user_id=<?php echo $user_id . "&_nw_nonce={$nonce}"; ?>&status=reject&current=<?php echo $status; ?>" href="#"><i class="fa fa-close fa-2x custom-unlike"></i></a>
                    <a  class="action-view"  href="<?php echo get_permalink().'/member-edit'; ?>?user_id=<?php echo $user_id . "&_nw_nonce={$nonce}"; ?>" ><i class="fa fa-pencil fa-2x custom-unlike"></i></a>
                  <?php }else{ ?>
                    <a  class="action-view"  href="<?php echo get_permalink().'/member-edit'; ?>?user_id=<?php echo $user_id . "&_nw_nonce={$nonce}"; ?>" ><i class="fa fa-pencil fa-2x custom-unlike"></i></a>
                  <?php } ?>
              </div>




	  </div>
</div>
<?php
echo "<script>

    var gender_count = '<span class=\'gender-count-view\'> M{$male}  - F{$female} </span>';

</script>";