<?php

/**
 *
 */
class Monarch_Public
{

	//protected  $table_name = 'monarch_membership';

    public function __construct()
	{
		 //Custom css and js load
		add_action('wp_enqueue_scripts',array($this,'custom_css'));


		//add_filter( 'page_template',array($this,'monarch_membership_view_page') );
          //end page Template

		  // Gravity Form validation
		add_filter( 'gform_field_validation_15_18',  array($this,'custom_validation'), 10, 4);

		add_filter( 'gform_field_validation_15_3',  array($this,'custom_validation_for_email'), 10, 4);
		 // Gravity Form validation

		//others check user validation and save data by gf form
 		register_activation_hook( __FILE__,array($this,'user_role_create'));

		add_action( 'gform_after_submission', array($this,'save_data_to_wp'), 10, 2 );

		add_action( 'gform_after_submission', array($this,'edit_data_to_wp'), 10, 2 );

	    add_filter('wp_authenticate_user',  array($this,'check_validation_status'), 10, 2);

		add_filter( 'login_redirect',array($this,'login_redirect'), 10, 3 );

		add_action( 'template_redirect',  array($this,'custom_check_view_profile'));


	}

    public function custom_css(){


		wp_register_style ( 'datatables', 'https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css' );
		wp_register_style ( 'magnific-popup', plugins_url ( 'css/magnific-popup.css', __FILE__ ) );
	    wp_register_style ( 'monarch_style', plugins_url ( 'css/monarach_style.css', __FILE__ ) );
	    wp_register_style ( 'dropzone', plugins_url ( 'css/dropzone.css', __FILE__ ) );
	    wp_register_style ( 'gravity_form', plugins_url ( 'css/gravity_form.css', __FILE__ ) );
 //		wp_register_style ( 'fontawesome', 'https://use.fontawesome.com/releases/v5.0.6/css/all.css' );

	    wp_enqueue_style('magnific-popup');
	    wp_enqueue_style('datatables');
		wp_enqueue_style('monarch_style');
		wp_enqueue_style('gravity_form');
	    wp_enqueue_style('dropzone');

 //		wp_enqueue_style('fontawesome');


	    wp_register_script( 'sweetalert','https://unpkg.com/sweetalert/dist/sweetalert.min.js' );
	    wp_enqueue_script('sweetalert');
	    wp_register_script( 'magnific-popup',plugins_url ( 'js/jquery.magnific-popup.min.js', __FILE__ ),array('sweetalert'),'',true );
	    wp_enqueue_script('magnific-popup');
	    wp_register_script( 'dropzonejs',plugins_url ( 'js/dropzone.js', __FILE__ ),array('jquery'),'',true );
	    wp_enqueue_script('dropzonejs');
	    wp_register_script( 'datatables','https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js',array('jquery'),'',true );
	    wp_enqueue_script('datatables');
//	    wp_register_script( 'monarch_js',plugins_url ( 'js/monarch.js', __FILE__ ),array('jquery'),'',true );
//	    wp_enqueue_script('monarch_js');
	    wp_enqueue_script('monarch_js',plugins_url ( 'js/monarch.js', __FILE__ ),array('jquery'),'',true );

	    wp_localize_script( 'jquery', 'ajax_object',
		    array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );

	}


	public function edit_data_to_wp( $entry, $form ) {

		if(isset($_GET['user_id'])){
			$user_id = $_GET['user_id'];
			if ( ! wp_verify_nonce( $_GET['_nw_nonce'], 'new-user-action' ) ) {
				return false;
			}
 		} else {
			return '';
		}

		if($entry['form_id'] != FORM_ID){
			return false;
		}

		$age_date = explode('-', $entry['4']);

		if(!empty($age_date)){
			$age_date = $age_date[1] .'/' . $age_date[2] .'/'. $age_date[0];
		} else {
			$age_date = $entry['4'];
		}


		$img_url = str_replace('\\', '', $entry['8']);
		$img_url = str_replace('["', '', $img_url);
		$img_url = str_replace('"]', '', $img_url);


		$userdata = array(
			'ID'          => $user_id,
 			'user_login'  =>  $entry['2'],
			'first_name'  =>  $entry['1.3'],
			'last_name'    =>  $entry['1.6'],
			'user_email'    =>  $entry['2'],
			'user_pass'   =>  NULL,  // When creating an user, `user_pass` is expected.
			'description'   =>  $entry['12'],
 		);


		$user_id = wp_update_user( $userdata );

		// crop iamge and create thumbnail
		if($img_url){
			$this->create_thumbnail($img_url);
		}

		// Will return false if the previous value is the same as $new_value.
		update_user_meta( $user_id, 'phone', $entry['3'] );
		update_user_meta( $user_id, 'dob', $age_date );
		update_user_meta( $user_id, 'gender', $entry['5'] );
		update_user_meta( $user_id, 'hospitality', $entry['6'] );
		update_user_meta( $user_id, 'before_work', $entry['7'] );
		if(!empty($img_url)){
 			update_user_meta( $user_id, 'picture', $img_url );
		}
//	    update_user_meta( $user_id, 'referral', $entry['20'] );
		update_user_meta( $user_id, 'facebook_profile', $entry['9'] );
		update_user_meta( $user_id, 'instagram_handle', $entry['10'] );
		update_user_meta( $user_id, 'linkedIn', $entry['11'] );

		return true;
	}



 	public static function date_formate_process($date){
	  //  $age_date = explode('-', $date);

		if (isset($date)){
			$age_date = implode('/', $date);

			if(!empty($age_date)){
				return $age_date;
			}
		}

	    return false;
    }

	public function edit_image($old_image,$new_image,$user_id){
//		echo $old_image;
//		 print_r($new_image);
//		 die();
		if(isset($new_image) && !empty($new_image['name'])){

  			   $old_image_name = strpos($old_image, 'gravity_forms', 1);
			   $upload_dir = wp_upload_dir();

 	               $findstr =  substr($old_image,$old_image_name);
 	               $image_name = basename($findstr);
 	               $old_image_path = str_replace($image_name, '', $findstr);

 	               $new_image_name =$user_id.'_'.$new_image['name'];

	               $uploaddir = $upload_dir['basedir'].'/'.$old_image_path;
	                 //print_r($image_path);
	               $target_file = $uploaddir . $new_image_name;


//	               var_dump(move_uploaded_file($_FILES["file"]["tmp_name"], $target_file));
//	               die();

			      if(move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)){
				        $new_image_path = str_replace($image_name, '', $old_image).$new_image_name;
				        // create thumbnail
				        $this->create_thumbnail($new_image_path);
				        return $new_image_path;
			       }

          }

		return false;
 	}

 	public static function user_view_update($user_id){
	    //$user_data = get_userdata( $user_id );
	    $user_meta_data = get_user_meta( $user_id );
	      //$user_view = get_post_meta($user_id, 'user_view', true );

	    if (empty($user_meta_data['user_view'][0])){

		    $userdata = array(
			    'ID'          => $user_id
		    );
		    $user_id = wp_update_user( $userdata );
		    // Will return false if the previous value is the same as $new_value.
		    update_user_meta( $user_id, 'user_view', 1 );
	    }

    }


  public function save_data_to_wp( $entry, $form ) {

 		if($entry['form_id'] != 15){
			return false;
		}

		$age_date = explode('-', $entry['18']);

 		if(!empty($age_date)){
		    $age_date = $age_date[1] .'/' . $age_date[2] .'/'. $age_date[0];
	    } else {
		    $age_date = $entry['18'];
	    }

	    $img_url = str_replace('\\', '', $entry['27']);
	    $img_url = str_replace('["', '', $img_url);
	    $img_url = str_replace('"]', '', $img_url);

//	    print_r($entry);
// 		 var_dump($img_url);
// 		 die();

	    $this->user_role_create();

		$userdata = array(
			'user_login'  =>  $entry['3'],
			'first_name'  =>  $entry['1.3'],
			'last_name'    =>  $entry['1.6'],
			'user_email'    =>  $entry['3'],
			'user_pass'   =>  NULL,  // When creating an user, `user_pass` is expected.
			'description'   =>  $entry['10'],
			'role'   =>  'pending'
		);

	 	  $user_id = wp_insert_user( $userdata ) ;

	  // crop iamge and create thumbnail
	  if($img_url){
		  $this->create_thumbnail($img_url);
	  }

	  // Will return false if the previous value is the same as $new_value.
	    update_user_meta( $user_id, 'phone', $entry['2'] );
	    update_user_meta( $user_id, 'dob', $age_date );
	    update_user_meta( $user_id, 'gender', $entry['17'] );
	    update_user_meta( $user_id, 'hospitality', $entry['14'] );
	    update_user_meta( $user_id, 'before_work', $entry['15'] );
	    update_user_meta( $user_id, 'picture', $img_url );
//	    update_user_meta( $user_id, 'referral', $entry['20'] );
	    update_user_meta( $user_id, 'facebook_profile', $entry['21'] );
	    update_user_meta( $user_id, 'instagram_handle', $entry['22'] );
	    update_user_meta( $user_id, 'linkedIn', $entry['23'] );
	    update_user_meta( $user_id, 'user_view', 0 );

	    return true;
 	}

 	public function create_thumbnail($img_url){
// 		return false;
	    $pos = strrpos($img_url, "wp-content");
	    if($pos !== false){
		    $img_path = ABSPATH . substr($img_url, $pos);
		    $img_folder_path = str_replace(basename($img_path), '', $img_path);
		    if(file_exists($img_path)){
			    include MONARACE_PLUGIN_DIR . 'includes/crop/ImageResize.php';
			    include MONARACE_PLUGIN_DIR . 'includes/crop/ImageResizeException.php';
			    try{
				    $image = new ImageResize($img_path);
				    $image->resizeToLongSide(250);
				    $image->save($img_folder_path . '_thumb_' .basename($img_path));
			    } catch (ImageResizeException $e){
				    //echo $e->getMessage();
			    }
		    }
	    }
    }
 	
  public  function user_role_create(){
	    add_role( 'pending', 'Pending', array( 'read' => true, 'level_0' => true ) );
	    add_role( 'reject', 'Reject', array( 'read' => true, 'level_0' => true ) );
     }

  public function custom_validation( $result, $value, $form, $field ) {

	  $nval = $value;
  	if(is_array($value)){
	    $nval = join('/', $value);
    }
		if ( $result['is_valid'] ) {

			$age = self::age_calculation($nval, 'm/d/Y');

			if ( intval( $age ) <= 21 ) {
				$result['is_valid'] = false;
				$result['message'] = 'Your age must be over 21';
			}

		}

		return $result;
	}

	public function custom_validation_for_email( $result, $value, $form, $field ) {

//		$nval = $value;
//		if(is_array($value)){
//			$nval = join('/', $value);
//		}
		if ( $result['is_valid'] ) {

			$exists = email_exists( $value );

			if ($exists) {
				$result['is_valid'] = false;
				$result['message'] = 'This email already exits. Please give another email';
			}

		}

		return $result;
	}

	public static function age_calculation($value, $format='Y-m-d'){

 		 $tz  = new DateTimeZone('Europe/Brussels');
		   $age = DateTime::createFromFormat($format, $value, $tz);
		if (is_a($age, 'DateTime')) {
			return $age->diff(new DateTime('now', $tz))->y;
		}

		 return '';
	}

	public static function update_user_role($user_id,$status='pending',$current='pending',$nonce){
//		  echo $user_id;
		if ( ! wp_verify_nonce( $nonce, 'new-user-action' ) ) {
			// This nonce is not valid.
			return  false;
		} else {

//		$current_users=get_user_meta($user_id);
		 $user_data = get_userdata($user_id);


		$user_id = wp_update_user( array( 'ID' => $user_data->ID, 'role' => $status ) );

			self::user_view_update($user_id);

		if ( is_wp_error( $user_id ) ) {
			// There was an error, probably that user doesn't exist.
		} else {
			if ( $status == 'subscriber' ) {
				self::mailchamp_intregration( $user_data->user_email, $user_data->ID );

				$admin_data     = wp_get_current_user();
				$admin_fullname = $admin_data->first_name . ' ' . $admin_data->last_name;
 				if ( ! $admin_data->first_name || ! $admin_data->last_name ) {

 					$admin_fullname = $admin_data->display_name;
				}

				update_user_meta( $user_data->ID, 'approved_by_id', $admin_data->ID );
				update_user_meta( $user_data->ID, 'approved_by_name', $admin_fullname );

				$to      = $user_data->user_email;
				$subject = 'Welcome to Your 2018 Monarch Membership';
				$headers = array( 'Content-Type: text/html; charset=UTF-8' );
//				$headers[] = 'From: Monarch Team <members@monarchphilly.com>';

				ob_start();
				include MONARACE_PLUGIN_DIR . 'public/email/approved.php';
				$body = ob_get_clean();

				wp_mail( $to, $subject, $body, $headers );
			}

			   // $view_page_url = get_transient( "view_page_url" );
			    $current_page  = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  			    $view_page = strpos($current_page,"membership-view-page");
			    //$url=$view_page_url;
			     $url = '';

			if (  $view_page !== false ) {
				if ( $status == 'subscriber' || $status == 'reject' ) {
					$next_user     = self::previous_url_id_generate( $current, $user_id );
					$url           = site_url() . '/membership-view-pag?user_id=' . $next_user;
 				}
			}else{
				    $url           = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] . '#' . $current : home_url( 'approval' ) . "#{$current}";
			}
			// self::set_transient_view_next_url( $url );
 			wp_redirect($url);
			exit;
// 			  if(empty($view_page_url))
//			{
// 				wp_redirect($url);
//				delete_transient("view_page_url");
//				exit;
//			}else{
//				wp_redirect($view_page_url);
//			}

//				$url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] . '#'.$current : home_url('approval') . "#{$current}";
//				wp_redirect($url);
		}
	}
	}
	public static function set_transient_view_next_url($url){
		  set_transient("view_page_url",$url , 3600);
	}

	public static function mailchamp_intregration($email, $user_id){

			if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL) === false){
				// MailChimp API credentials
				$apiKey =  get_option('mailchamp_api_key');
				$listID =  get_option('mailchamp_list_id');

				// MailChimp API URL
				$memberID = md5(strtolower($email));
				$dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
				$url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listID . '/members/' . $memberID;
				$interests = array();
				$scat_options = get_option( 'mailchamp_group_ids' );
				if(get_user_meta($user_id, 'gender', true) == 'Male'){
//					$interests['7dad5e1a8c'] = true;
					$interests[$scat_options[0]] = true;
				}
				if(get_user_meta($user_id, 'gender', true) == 'Female'){
//					$interests['a5d935818c'] = true;
					$interests[$scat_options[1]] = true;
				}
				if(get_user_meta($user_id, 'hospitality', true) == 'Yes'){
//					$interests['ac7ba8bde8'] = true;
					$interests[$scat_options[2]] = true;
				}

				$args = array(
					'email_address' => $email,
					'status'        => 'subscribed',
//					'interests'     => $interests
				);
				if(count($interests) > 0){
					$args['interests'] = $interests;
				}

				// member information
				$json = json_encode($args);

				// send a HTTP POST request with curl
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
				curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
				$result = curl_exec($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);

				// store the status message based on response code
				if ($httpCode == 200) {
					$_SESSION['msg'] = '<p style="color: #34A853">You have successfully subscribed to Monarch.</p>';
				} else {
					switch ($httpCode) {
						case 214:
							$msg = 'You are already subscribed.';
							break;
						default:
							$msg = 'Some problem occurred, please try again.';
							break;
					}
					$_SESSION['msg'] = '<p style="color: #EA4335">'.$msg.'</p>';
				}
			}else{
				$_SESSION['msg'] = '<p style="color: #EA4335">Please enter valid email address.</p>';
			}

	}

	public static function user_view_data($user_id){
//		$current_users=get_user_meta($user_id);
		$user_data = get_userdata( $user_id );
		$user_meta_data = get_user_meta( $user_id );

		$user_data = array($user_data);

		$all_user_data = array_merge($user_data,$user_meta_data);



		return $all_user_data;
	}

	public function custom_check_view_profile() {
		if ( is_page( array( 'membership-view-page', 'member-edit', 'monarch-membership-template', 'approval' ) ) ) {

			$user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : false;

			if ( ( is_user_logged_in() && current_user_can( 'manage_options' ) ) ) {

			} elseif ( $user_id == get_current_user_id() && is_page( array( 'membership-view-page', 'member-edit' ))){

			} else {
				wp_redirect( home_url() );
				exit;
			}
		}


	}

	public function check_validation_status($user, $password) {

		$roles = $user->roles ? $user->roles[0] : false;

		if(!$roles || $roles== 'reject' || $roles== 'pending') {
			$errors = new WP_Error();
			$errors->add('title_error', __('<strong>ERROR</strong>: This account has not been verified.'));
			return $errors;
		}

		return $user;
	}

	function login_redirect( $redirect_to, $request, $user ){

		$roles = isset($user->roles) ? $user->roles[0] : false;
		  if($roles == 'subscriber'){
			  $redirect_to = home_url( 'membership-view-page' );
			  $redirect_to = add_query_arg('user_id', $user->ID, $redirect_to);
		}
//		var_dump($redirect_to);
//		  die();

		return $redirect_to;
	}



	public static function get_count_for_users($role='pending',$user_view=1){
		global $wpdb;
		$sql = "SELECT count(DISTINCT {$wpdb->users}.user_login) as total FROM {$wpdb->users} INNER JOIN $wpdb->usermeta ON ( $wpdb->users.ID
 = $wpdb->usermeta.user_id )  INNER JOIN $wpdb->usermeta AS mt1 ON ( $wpdb->users.ID = mt1.user_id )  INNER JOIN $wpdb->usermeta AS mt2 ON ( $wpdb->users.ID = mt2.user_id ) WHERE 1=1 AND (
  (

      ( $wpdb->usermeta.meta_key = 'user_view' AND $wpdb->usermeta.meta_value = $user_view )

    AND
    (
      (
        ( mt2.meta_key = '{$wpdb->prefix}capabilities' AND mt2.meta_value LIKE '%\"{$role}\"%' )
      )
    )
  )
) AND (user_login LIKE '%%' OR user_nicename LIKE '%%' OR user_email LIKE '%%' OR user_url LIKE '%%') ORDER BY user_registered DESC";

		  $result = $wpdb->get_results($sql);
 		  return (int)$result[0]->total;

	}

	public static function get_count_for_users_gender($role='subscriber',$gender='Male'){
		global $wpdb;
		$sql = "SELECT count(DISTINCT {$wpdb->users}.user_login) as total FROM {$wpdb->users} INNER JOIN $wpdb->usermeta ON ( $wpdb->users.ID
 = $wpdb->usermeta.user_id )  INNER JOIN $wpdb->usermeta AS mt1 ON ( $wpdb->users.ID = mt1.user_id )  INNER JOIN $wpdb->usermeta AS mt2 ON ( $wpdb->users.ID = mt2.user_id ) WHERE 1=1 AND (
  (
      ( $wpdb->usermeta.meta_key = 'gender' AND $wpdb->usermeta.meta_value = '$gender' AND mt1.meta_key = 'user_view' AND mt1.meta_value = '1') 
    AND
    (
      (
        ( mt2.meta_key = '{$wpdb->prefix}capabilities' AND mt2.meta_value LIKE '%\"{$role}\"%' )
      )
    )
  )
) AND (user_login LIKE '%%' OR user_nicename LIKE '%%' OR user_email LIKE '%%' OR user_url LIKE '%%') ORDER BY user_registered DESC";

		$result = $wpdb->get_results($sql);
		return (int)$result[0]->total;

	}

	public static function gender_count_for_view_page($gender,$role,$user_view=0){
		$user_query = new WP_User_Query( array(
			'role' => $role,
			'meta_key' => 'gender',
			'meta_value' => $gender,
			'meta_query' => array(
 				array(
					'key'     => 'user_view',
					'value'   => $user_view,
 				)
			)
		) );
		$users_count = (int) $user_query->get_total();
		return $users_count;
	}

	public static function previous_url_id_generate($status,$user_id){
		global $wpdb;
		$previous = $wpdb->get_results("select * from $wpdb->users INNER JOIN  $wpdb->usermeta ON ( $wpdb->users.ID = $wpdb->usermeta.user_id )
where $wpdb->users.id = (select max($wpdb->usermeta.user_id) from $wpdb->usermeta where $wpdb->usermeta.user_id < $user_id AND $wpdb->usermeta.meta_value LIKE '%$status%' ) LIMIT 1");

		return $previous[0]->ID;
	}
	public static function next_url_id_generate($status,$user_id){
		global $wpdb;
		$next = $wpdb->get_results("select * from $wpdb->users INNER JOIN  $wpdb->usermeta ON ( $wpdb->users.ID = $wpdb->usermeta.user_id )
where $wpdb->users.id = (select min($wpdb->usermeta.user_id) from $wpdb->usermeta where $wpdb->usermeta.user_id > $user_id AND $wpdb->usermeta.meta_value LIKE '%$status%' ) LIMIT 1");

		return $next[0]->ID;
	}



}
new Monarch_Public();

add_action('plugins_loaded', function(){
	if(isset($_GET['fix_images'])){

		ini_set("gd.jpeg_ignore_warning", 1);


		$directory = ABSPATH . "wp-content/uploads/gravity_forms/15-559061ed686b4fce9ed9c0172a202cae/2018/02/";

		$images = glob($directory . '*.png');
		include MONARACE_PLUGIN_DIR . 'includes/crop/ImageResize.php';
		include MONARACE_PLUGIN_DIR . 'includes/crop/ImageResizeException.php';
		$count = 1;
		foreach($images as $simage) {
			try{
//				unlink($simage);
				$new_name = $directory . '_thumb_' .basename($simage);
				echo '<br>';
				var_dump($simage);
				var_dump(is_file($new_name));
				echo '<br>';
				echo "<br>";
				if(is_file($new_name) || strpos($simage, '_thumb_') !== false){
					continue;
				}
				var_dump($new_name);

				echo ++$count . '<br>';
			$image = new ImageResize($simage);
			$image->resizeToLongSide(250);
				var_dump($image->save($new_name));
			} catch (ImageResizeException $e){
				echo $e->getMessage();
				echo '<br>';
				var_dump($simage);
				echo '<br>';
				var_dump($new_name);
				echo '<br>';
				echo copy($simage, $new_name);
				echo 'ssdd';
				echo "<br>";
			}
		}

		var_dump($count);
		echo '<br>';
		die();
	}

	if(isset($_GET['add_view'])) {
		$args = array(
			'role'       => 'pending',
			'orderby'    => 'registered',
			'order'      => 'DESC',
		);

		$users = get_users( $args );
		if ( ! empty( $users ) ) {
			foreach ( $users as $key => $user_data ) {
				update_user_meta( $user_data->ID, 'user_view', 1 );
			}
		}
		die();
	}

	if(isset($_GET['show_info'])){
		echo phpinfo();
		die();
	}

});