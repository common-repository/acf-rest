<?php

/*
Plugin Name: Advanced Custom Fields: Rest
Plugin URI: PLUGIN_URL
Description: Same as select, but options come from rest call
Version: 1.0.6
Author: Orestis Palampougioukis, Yarno van Oort
Author URI: AUTHOR_URL
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('acf_plugin_rest') ) :

class acf_plugin_rest {
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	17/02/2016
	*  @since	1.0.6
	*
	*  @param	n/a
	*  @return	n/a
	*/
	function __construct() {
		
		// vars
		$this->settings = array(
			'version'	=> '1.0.6',
			'url'		=> plugin_dir_url( __FILE__ ),
			'path'		=> plugin_dir_path( __FILE__ )
		);

		add_action('acf/include_field_types', 	array($this, 'include_field_types'));
        add_action('acf/input/admin_head', array($this, 'my_acf_admin_head'));
        add_action('acf/input/admin_footer', array($this, 'my_acf_input_admin_footer'));
    }

    /**
     *  Adds the script of the plugin in the footer
     */
    function my_acf_input_admin_footer()
    {
        wp_enqueue_script('acf-rest-js', plugin_dir_url('acf-rest.js') . 'acf-rest/assets/js/acf-rest.js', array(), false, true);
    }

    /**
     * Adds the styles of he plugin in the head
     */
    function my_acf_admin_head()
    {
        wp_enqueue_style('acf-rest-css', plugin_dir_url('acf-rest.css') . 'acf-rest/assets/css/acf-rest.css', array(), false);
    }
	
	
	/*
	*  include_field_types
	*
	*  This function will include the field type class
	*
	*  @type	function
	*  @date	17/02/2016
	*  @since	1.0.0
	*
	*  @param	$version (int) major ACF version. Defaults to false
	*  @return	n/a
	*/
	function include_field_types( $version = false ) {
		
		// support empty $version
		if( !$version ) $version = 4;

		// include
		include_once('fields/acf-rest-v' . $version . '.php');
		
	}
	
}


// initialize
new acf_plugin_rest();


// class_exists check
endif;
	
?>