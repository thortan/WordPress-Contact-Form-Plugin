<?php 
/* 
 * Plugin Name: Contact Form
 * Plugin Description: This is a custom contact form, which can be used to upload files and contact website admin.
 * Version: 1.0
 * Author: Hamza Naeem
 * Author URI: https://yourwebsite.com
 * Plugin URI: https://yourwebsite.com
 * License: GPL2
*/

if(!defined('ABSPATH'))
    {
        exit;
    }

class CFPlugin{
    public function __construct()
    {
        $this -> define_constants();
        $this -> init_hooks();
        $this -> includes();
    }

    private function define_constants()
    {
        define("PLUGIN_VERSION", '1.0');
        define("PLUGIN_DIR_PATH", plugin_dir_path(__FILE__));
        define("PLUGIN_DIR_URL", plugin_dir_url(__FILE__));
    }

    private function init_hooks()
    {
        register_activation_hook(__FILE__, array($this, "activation"));
        register_deactivation_hook(__FILE__, array($this, "deactivation"));
        add_action("wp_enqueue_scripts", array($this, "enqueue_assets"));
    }

    private function includes()
    {
        require_once PLUGIN_DIR_PATH . 'includes/class_contact_form_plugin.php';
        new ContactForm_design();
    }

    public function enqueue_assets()
    {
        wp_enqueue_style("cf_style", PLUGIN_DIR_URL . 'includes/style.css', array(), PLUGIN_VERSION);
        wp_enqueue_script("cf_script", PLUGIN_DIR_URL . 'includes/script.js', array(), PLUGIN_VERSION, true);
    }

    public function activation()
    {
        return flush_rewrite_rules();
    }

    public function deactivation()
    {
        return flush_rewrite_rules();
    }
}

function cf_Plugin_init()
{
    return new CFPlugin();
}
cf_Plugin_init();