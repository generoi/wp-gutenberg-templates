<?php
/*
Plugin Name:        Gutenberg Templates
Plugin URI:         http://genero.fi
Description:        Add page template support to Gutenberg
Version:            1.0.0
Author:             Genero
Author URI:         http://genero.fi/
License:            MIT License
License URI:        http://opensource.org/licenses/MIT
*/
namespace GeneroWP\Gutenberg\Templates;

use Puc_v4_Factory;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use GeneroWP\Common\Singleton;
use GeneroWP\Common\Assets;

if (!defined('ABSPATH')) {
    exit;
}

if (file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
    require_once $composer;
}


class Plugin
{
    use Singleton;
    use Assets;

    public $version = '1.0.0';
    public $plugin_name = 'wp-gutenberg-templates';
    public $plugin_path;
    public $plugin_url;
    public $github_url = 'https://github.com/generoi/wp-gutenberg-templates';

    public function __construct()
    {
        require_once __DIR__ . '/api.php';

        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);

        Puc_v4_Factory::buildUpdateChecker($this->github_url, __FILE__, $this->plugin_name);

        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init()
    {
        add_filter('theme_templates', [$this, 'templates'], 100, 4);
        add_action('rest_api_init', [$this, 'restApiEndpoints']);
        add_action('enqueue_block_editor_assets', [$this, 'block_editor_assets']);
        add_action('init', [$this, 'load_textdomain']);
    }

    public function templates($templates, $theme, $post, $post_type)
    {
        foreach (\get_gutenberg_templates($post_type) as $template => $args) {
            $name = $args['name'];
            $file = $args['template_file'];
            $templates[$file] = $name;
        }
        return $templates;
    }

    public function restApiEndpoints()
    {
        register_rest_route('gutenberg-templates/v1', '/template', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'getTemplate'],
            'args' => [
                'template' => ['required' => true, 'validate_callback' => [$this, 'validateTemplateFile']],
            ],
        ]);
    }

    public function validateTemplateFile(string $template)
    {
        return !!preg_match('/^[a-zA-Z0-9-_\/]+\.php$/', $template);
    }

    public function getTemplate(WP_REST_Request $request)
    {
        $template = \get_gutenberg_template_by_file($request['template']);
        if (!$template) {
            return new \WP_Error('no_template', 'Invalid template', ['status' => 404]);
        }
        return $template;
    }

    public function block_editor_assets()
    {
        $this->enqueueScript("{$this->plugin_name}/js", 'dist/index.js', ['wp-editor', 'wp-data', 'wp-blocks', 'wp-components', 'wp-i18n', 'wp-api-request']);
        wp_set_script_translations("{$this->plugin_name}/js", $this->plugin_name);
    }

    public function load_textdomain()
    {
        // WP Performance Pack
        include __DIR__ . '/languages/javascript.php';

        load_plugin_textdomain($this->plugin_name, false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

Plugin::getInstance();
