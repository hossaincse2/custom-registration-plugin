<?php
/**
 * Created by PhpStorm.
 * User: msi
 * Date: 2/14/2018
 * Time: 7:52 PM
 */

$users = get_users(array('role' => 'pending'));

function get_mailchimp_categories(){
	$api_key = esc_attr( get_option('mailchamp_api_key') );
	$list_id = esc_attr( get_option('mailchamp_list_id') );

	if(!$list_id || !$api_key){
	    return array();
    }

	$dc = substr($api_key,strpos($api_key,'-')+1); // us5, us8 etc
	$args = array(
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode( 'user:'. $api_key )
		)
	);

	$url = 'https://'.$dc.'.api.mailchimp.com/3.0/lists/'.$list_id.'/interest-categories';
// connect
	$response = wp_remote_get( $url, $args );

	if(is_wp_error($response)){
	    return array();
    }

// decode the response
	$body = json_decode( $response['body'] );

	if ( $response['response']['code'] == 200 ) {

	    return $body->categories;

	}
	return array();
}


function get_mailchimp_sub_categories(){
	$api_key = esc_attr( get_option('mailchamp_api_key') );
	$list_id = esc_attr( get_option('mailchamp_list_id') );
	$cat_id = esc_attr( get_option('mailchamp_categories_ids') );

	if(!$list_id || !$api_key || !$cat_id){
	    return array();
    }

	$dc = substr($api_key,strpos($api_key,'-')+1); // us5, us8 etc
	$args = array(
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode( 'user:'. $api_key )
		)
	);

	$url = 'https://'.$dc.'.api.mailchimp.com/3.0/lists/'.$list_id.'/interest-categories/' . $cat_id .'/interests';
// connect
	$response = wp_remote_get( $url, $args );

	if(is_wp_error($response)){
		return array();
	}

// decode the response
	$body = json_decode( $response['body'] );

	if ( $response['response']['code'] == 200 ) {

	    return $body->interests;

	}
	return array();
}

?>
<div class="wrap">
    <h1>Settings</h1>

    <form method="post" action="options.php">
		<?php settings_fields( 'get_monarch_settings_group' ); ?>
		<?php do_settings_sections( 'get_monarch_settings_group' ); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Mailchamp Api Key</th>
                <td><input type="text" name="mailchamp_api_key" value="<?php echo esc_attr( get_option('mailchamp_api_key') ); ?>" /></td>
            </tr>

            <tr valign="top">
                <th scope="row">Mailchamp List ID</th>
                <td><input type="text" name="mailchamp_list_id" value="<?php echo esc_attr( get_option('mailchamp_list_id') ); ?>" /></td>
            </tr>
	        <?php $cat_options = get_option( 'mailchamp_categories_ids' );
	        $all_cats = get_mailchimp_categories();
	        if(count($all_cats) > 0) {
		        ?>
                <tr valign="top">
                    <th scope="row">Mailchamp Category</th>
                    <td>
                        <?php foreach ($all_cats as $index=>$cat){ ?>
                        <label style="margin-right: 15px;"><input type="radio" name="mailchamp_categories_ids"
                               value="<?php echo $cat->id;?>" <?php checked($cat_options, $cat->id ); ?> /> <?php
                            echo $cat->title;?></label>
                        <?php } ?>
                    </td>
                </tr>
		        <?php
	        }
	        $scat_options = get_option( 'mailchamp_group_ids' );
	        $all_scats = get_mailchimp_sub_categories();
	        if(count($all_scats) > 0) {
            ?>
            <tr valign="top">
                <th scope="row">Mailchamp Sub Groups</th>
                <td>

	                <?php foreach ($all_scats as $index=>$scat){
	                    ?>
                        <label style="margin-right: 15px;"><input type="checkbox" name="mailchamp_group_ids[<?php echo $index;?>]" value="<?php
                            echo $scat->id;?>" <?php echo isset($scat_options[$index]) ? checked($scat_options[$index],
                                $scat->id, false) : ''; ?> /> <?php
			                echo $scat->name;?></label>

	                <?php } ?>
                </td>
	            <?php
	        }?>
            </tr>
        </table>

		<?php submit_button(); ?>

    </form>

    <div class="others">
        <h2>Short Documentation</h2>
        <h3>Short Code</h3>
        <p>For New user Short Code: <b>[monarach_members status='pending' view='']</b></p>
        <p>For Pending user Short Code: <b>[monarach_members status='pending']</b></p>
        <p>For Approved user Short Code: <b>[monarach_members status='subscriber']</b></p>
        <p>For Rejected user Short Code: <b>[monarach_members status='reject']</b></p>
        <p>For View user: <b>[monarach_view]</b></p>
        <p>For Edit User: <b>[monarach_edit]</b></p>
    </div>
    <h3>Dependency</h3>
    <p><b>Gravity Form, Visual Composer</b></p>
    <h3>Pages Templates</h3>
    <p><b>apply, approval, membership-view-page, member-edit</b></p>
</div>
<?php
    // create custom plugin settings menu
 	?>


