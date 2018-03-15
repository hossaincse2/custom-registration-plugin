<?php
add_action('wp_ajax_get_user_data', 'get_monarch_user_data');
//add_action('wp_ajax_nopriv_get_user_data', 'get_monarch_user_data');

function get_monarch_user_data(){


	/*Generating variables*/
	$data_arr = array();
	$data_arr['start'] = isset($_POST['start']) ? $_POST['start'] : 0;
	$data_arr['order_data'] = isset($_POST['order']) ? $_POST['order'] : array();
	$data_arr['search'] = isset($_POST['search']['value']) ? $_POST['search']['value'] : false;
	$data_arr['page_length'] = isset($_POST['length']) ? $_POST['length'] : 10;

	if(!$data_arr['start']){
		$data_arr['page'] = 1;
	} else {
		$data_arr['page'] = ($data_arr['start']/$data_arr['page_length']) + 1;
	}

	$data_arr['columns']=$_POST['columns'];



// 	$paged = ( get_query_var( 'paged' ) ) ? max( 1, get_query_var('paged') ) : 1;
 	$args = array(
		'role' => $_GET['role'],
		'meta_key' => 'user_view',
		'meta_value' => $_GET['meta_value'],
		'orderby' => 'registered',
		'order' => 'DESC',
		'number' => $data_arr['page_length'],
		'paged' => $data_arr['page'],
		'search'         => "*{$data_arr['search']}*",
		'search_columns' => array(
			'user_login',
			'user_nicename',
			'user_email',
			'user_url',
		),
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key'     => 'first_name',
				'value'   => $data_arr['search'],
				'compare' => 'LIKE'
			),
			array(
				'key'     => 'last_name',
				'value'   => $data_arr['search'],
				'compare' => 'LIKE'
			)
		)
  	);

//	 $users = get_users($args);
	$users = new WP_User_Query( $args );

	$new_user_data = [];
	foreach($users->get_results() as $user_id) {
		$metaData = get_user_meta( $user_id->ID );
//print_r($metaData); die();
  		$full_name = $metaData['first_name'][0] . ' ' . $metaData['last_name'][0];
//
 		$profile_img = isset($metaData['picture'][0]) ? $metaData['picture'][0] : '';
  		$thumb_nail = str_replace(mb_basename($profile_img), '', $profile_img) . '_thumb_' . mb_basename($profile_img);
//
		$metaData ['full_name'] = $full_name;
		$metaData ['profile_img'] = $profile_img;

		$metaData ['profile_img'] = <<<DDD
<div class='zoom-gallery'><a href='$profile_img' data-source='$profile_img' title='$full_name' class='profile-img-user' style="background-image: url('$thumb_nail')">
									 <img src='$thumb_nail' alt='$full_name'/>
								 </a></div>
DDD;

		$gender = $metaData['gender'][0];
		$dob = $metaData['dob'][0];
		$ageCalculation = Monarch_Public::age_calculation($dob, 'm/d/Y');
		$nonce = wp_create_nonce('new-user-action');
		$base_link = site_url();
		$status = $_GET['role'];
		$approved_by_name = isset($metaData['approved_by_name'][0]) ? $metaData['approved_by_name'][0] : '';

		if( $status == 'subscriber') {
			$button =     <<<DDD
<div class='user-action'>
	<a data-url="{$base_link}?user_id=$user_id->ID&_nw_nonce={$nonce}&status=pending&current=subscriber" 
	class='rejected action-reject action-pending' href='#'>
		<i class='fa fa-pause fa-2x custom-unlike'></i>
	</a>
	<a data-url="{$base_link}?user_id=$user_id->ID&_nw_nonce={$nonce}&status=reject&current=subscriber" class='rejected action-reject' href='#'>
		<i class='fa fa-close fa-2x custom-unlike'></i>
	</a>
	<a href="{$base_link}/member-edit?user_id=$user_id->ID&_nw_nonce={$nonce}" class='action-edit'>
		<i class='fa fa-pencil fa-2x custom-unlike'></i>
	</a>
</div>
DDD;

		}elseif ($status == 'reject'){
			$button =     <<<DDD
<div class='user-action'>
	<a data-url="{$base_link}?user_id=$user_id->ID&_nw_nonce={$nonce}&status=subscriber&current=reject" class='accepted  action-accept' href='#'>
	 	<i class='fa fa-check fa-2x custom-like'></i>
	</a>
	<a data-url="{$base_link}?user_id=$user_id->ID&_nw_nonce={$nonce}&status=pending&current=reject" class='rejected action-reject action-pending' href='#'>
		<i class='fa fa-pause fa-2x custom-unlike'></i>
	</a>	
	<a href="{$base_link}/member-edit?user_id=$user_id->ID&_nw_nonce={$nonce}" class='action-edit'>
		<i class='fa fa-pencil fa-2x custom-unlike'></i>
	</a>
</div>
DDD;

		} else{
			if(!isset($current_tab)){
				$current_tab = 'new';
				if(isset($metaData['user_view']) && $metaData['user_view'][0] == 1){
					$current_tab = 'pending';
				}
			}
			$button =     <<<DDD
<div class='user-action'>
	<a data-url="{$base_link}?user_id=$user_id->ID&_nw_nonce={$nonce}&status=subscriber&current={$current_tab}" 
	class='accepted  action-accept' href='#'>
	 	<i class='fa fa-check fa-2x custom-like'></i>
	</a>
	<a data-url="{$base_link}?user_id=$user_id->ID&_nw_nonce={$nonce}&status=pending&current={$current_tab}" class='rejected 
	action-reject pending action-pending' href='#'>
		<i class='fa fa-pause fa-2x custom-unlike'></i>
	</a>
	<a data-url="{$base_link}?user_id=$user_id->ID&_nw_nonce={$nonce}&status=reject&current={$current_tab}" class='rejected 
	action-reject' href='#'>
		<i class='fa fa-close fa-2x custom-unlike'></i>
	</a>
	<a href="{$base_link}/member-edit?user_id=$user_id->ID&_nw_nonce={$nonce}" class='action-edit'>
		<i class='fa fa-pencil fa-2x custom-unlike'></i>
	</a>
</div>
DDD;

		}



		if( $status == 'subscriber') {
			$details =  "<a href='$base_link/membership-view-page?user_id=$user_id->ID' class='user-list-data'>
							 <strong> $full_name </strong> $gender <br/>
							 $ageCalculation <br/> Approved By : $approved_by_name </a>";
		}else{
			$details =  "<a href='$base_link/membership-view-page?user_id=$user_id->ID' class='user-list-data'>
							 <strong> $full_name </strong> $gender <br/>
							 $ageCalculation </a>";
		}


		$metaData ['thumb_nail'] = $thumb_nail;
		$metaData ['gender'] = isset($metaData['gender'][0]) ? $metaData['gender'][0] : '';
		$metaData ['full_name_age'] = $details;


		$metaData ['button'] = $button;



		        $new_user_data[] =  $metaData;
		       // $new_user_data['new_user'] =  Monarch_Public::get_count_for_users('pending',0);
	}

	//$new_user_data['new_user'] =  Monarch_Public::get_count_for_users('pending',0);
	try {
//		$result = count_users();;
//		$new_user_total =  $users['total_users'];
		$args = array(
			'role' => $_GET['role'],
			'meta_key' => 'user_view',
			'meta_value' => $_GET['meta_value'],
			'search'         => "*{$data_arr['search']}*",
			'search_columns' => array(
				'user_login',
				'user_nicename',
				'user_email',
				'user_url',
			),
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'     => 'first_name',
					'value'   => $data_arr['search'],
					'compare' => 'LIKE'
				),
				array(
					'key'     => 'last_name',
					'value'   => $data_arr['search'],
					'compare' => 'LIKE'
				)
			)
		);
		$userCount = new WP_User_Query( $args );
		$new_user_total =  $userCount->get_total();
//		$new_user_total =  count($userCount->get_results());


	} catch (Exception $e){
//		var_dump($e->getMessage());
	}

//print_r ($db->trace);
//print_r($new_user_event_data);
//$data = [];
	$params = $columns = array();

	$params = $_REQUEST;


	$columns = array(
		"draw" =>  isset($params['draw']) ? $params['draw'] : '' ,
		"recordsTotal" => intval($new_user_total),
		"recordsFiltered" => intval($new_user_total),
		'data'=>  $new_user_data
	);
//  foreach ($new_user_event_data as $new_user_event) {
//      $columns = array(
//          'data'=>  $new_user_event
//      );
//
// }

	echo json_encode($columns);
	die();
}
add_action('wp_ajax_get_user_status', 'get_monarch_get_user_status');
//add_action('wp_ajax_nopriv_get_user_data', 'get_monarch_user_data');

function get_monarch_get_user_status(){

	$metaData ['new_user'] = Monarch_Public::get_count_for_users('pending',0);
	$metaData ['pending'] = Monarch_Public::get_count_for_users('pending');
	$metaData ['subscriber'] = Monarch_Public::get_count_for_users('subscriber');
	$metaData ['reject'] = Monarch_Public::get_count_for_users('reject');
	$metaData ['approved_male'] = Monarch_Public::get_count_for_users_gender('subscriber','Male');
	$metaData ['approved_female'] = Monarch_Public::get_count_for_users_gender('subscriber','Female');
 	wp_send_json($metaData);
}