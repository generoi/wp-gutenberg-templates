<?php
/*
Plugin Name:        Plugin Boilerplate
Plugin URI:         http://genero.fi
Description:        A boilerplate WordPress plugin
Version:            1.0.0
Author:             Genero
Author URI:         http://genero.fi/
License:            MIT License
License URI:        http://opensource.org/licenses/MIT
*/
namespace GeneroWP;

if (!defined('ABSPATH')) {
    exit;
}

class PluginBoilerplate
{

    private static $instance = null;
    public $version = '1.0.0';

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        register_activation_hook(__FILE__, [__CLASS__, 'activate']);
        register_deactivation_hook(__FILE__, [__CLASS__, 'deactivate']);

        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function register_assets()
    {
        $path = plugin_dir_url(__FILE__);
        wp_register_script('wp-plugin-boilerplate/js', $path . 'dist/main.js', ['jquery'], $this->version, true);
        wp_register_style('wp-plugin-boilerplate/css', $path . 'dist/main.css', [], $this->version);
    }

    public function enqueue_assets()
    {
        wp_enqueue_script('wp-plugin-boilerplate/js');
        wp_enqueue_style('wp-plugin-boilerplate/css');
    }

    public static function activate()
    {
        if (!is_plugin_active('gravityformsrestapi/restapi.php') && current_user_can('activate_plugins')) {
            wp_die('Sorry, but this plugin requires the Gravity Forms REST API plugin to be installed and active. <br><a href="' . admin_url('plugins.php') . '">&laquo; Return to Plugins</a>');
        }
    }

    public static function deactivate()
    {
    }
}

PluginBoilerplate::get_instance()->init();
