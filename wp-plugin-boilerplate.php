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
namespace GeneroWP\PluginBoilerplate;

if (!defined('ABSPATH')) {
    exit;
}

class Plugin
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
        foreach ([
            'advanced-custom-fields-pro/acf.php' => 'Advanced Custom Fields PRO',
            'timber-library/timber.php' => 'Timber Library',
            // 'wp-timber-extended/wp-timber-extended.php' => 'WP Timber Extended',
        ] as $plugin => $name) {
            if (!is_plugin_active($plugin) && current_user_can('activate_plugins')) {
                wp_die(sprintf(
                    __('Sorry, but this plugin requires the %s plugin to be installed and active. <br><a href="%s">&laquo; Return to Plugins</a>', 'wp-hero'),
                    $name,
                    admin_url('plugins.php')
                ));
            }
        }
    }

    public static function deactivate()
    {
    }
}

if (file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
    require_once $composer;
}

Plugin::get_instance()->init();
