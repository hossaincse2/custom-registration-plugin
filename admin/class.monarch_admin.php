<?php

/**
*
*/
class Monarch_Admin {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'addMonarchPage' ) );
	}


//admin page show
	function addMonarchPage() {
		add_menu_page( "Membership Display", "Membership", 1, "Membership Display", array( $this, 'getMonarch' ) );
		add_action( 'admin_init', array( $this, 'register_monarch_plugin_settings' ) );
	}


	function register_monarch_plugin_settings() {
		//register our settings
		register_setting( 'get_monarch_settings_group', 'mailchamp_api_key' );
		register_setting( 'get_monarch_settings_group', 'mailchamp_list_id' );
		register_setting( 'get_monarch_settings_group', 'mailchamp_categories_ids' );
		register_setting( 'get_monarch_settings_group', 'mailchamp_group_ids' );
	}

//Load Admin View
	function getMonarch() {
		include( dirname( __FILE__ ) . '/admin_membership.php' );
	}
}
new Monarch_Admin();