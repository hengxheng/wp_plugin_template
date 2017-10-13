<?php

/*
Plugin Name: TP
Description: TP
Version: 1.0.0
*/


class MySettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'ARPC Settings', 
            'manage_options', 
            'my-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'arpc_options' );
        ?>
        <div class="wrap">
            <h1>My Settings</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'arpc_options_group' );
                do_settings_sections( 'my-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'arpc_options_group', // Option group
            'arpc_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'arpc_setting_section', // ID
            'ARPC Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );  

        add_settings_field(
            'threat_level',
            'Current threat level',
            array( $this, 'current_threat_callback'),
            'my-setting-admin',
            'arpc_setting_section'
        );

        // add_settings_field(
        //     'id_number', // ID
        //     'ID Number', // Title 
        //     array( $this, 'id_number_callback' ), // Callback
        //     'my-setting-admin', // Page
        //     'setting_section_id' // Section           
        // );      

        // add_settings_field(
        //     'title', 
        //     'Title', 
        //     array( $this, 'title_callback' ), 
        //     'my-setting-admin', 
        //     'setting_section_id'
        // );      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = absint( $input['id_number'] );

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );

        $new_input['threat_level'] = $input['threat_level'];
        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    // public function id_number_callback()
    // {
    //     printf(
    //         '<input type="text" id="id_number" name="my_option_name[id_number]" value="%s" />',
    //         isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
    //     );
    // }

    // public function title_callback()
    // {
    //     printf(
    //         '<input type="text" id="title" name="my_option_name[title]" value="%s" />',
    //         isset( $this->options['title'] ) ? esc_attr( $this->options['title']) : ''
    //     );
    // }

    public function current_threat_callback(){
        printf('<select name="arpc_options[threat_level]">
        <option value="%s">%s</option>
        <option value="Certain">Certain</option>
        <option value="Expected">Expected</option>
        <option value="Probable">Probable</option>
        <option value="Possible">Possible</option>
        <option value="Not expected">Not expected</option>
        </select>',isset( $this->options['threat_level']) ? esc_attr( $this->options['threat_level']) : '',
        isset( $this->options['threat_level']) ? esc_attr( $this->options['threat_level']) : ''
        ); 
    }
}

if( is_admin() )
    $my_settings_page = new MySettingsPage();

//   create postcode table

register_activation_hook(__FILE__, 'postcode_table_install');

function postcode_table_install(){
	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix."arpc_postcode";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$sql = "CREATE TABLE ".$table_name." (
		id bigint(20) NOT NULL AUTO_INCREMENT,	
		postcode varchar(10),
		state varchar(20),
		tier varchar(20),
		PRIMARY KEY (id)
	)". $charset_collate.";";

	dbDelta($sql);
}

?>
