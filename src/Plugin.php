<?php

namespace GeneroWP\Gutenberg\Templates;

use WP_Post;
use WP_REST_Server;
use WP_REST_Request;
use WP_Theme;
use Debug_Bar_Panel;
use WP_REST_Response;

class Plugin
{
    public string $plugin_path;
    public string $plugin_url;

    protected static Plugin $instance;

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->plugin_path = plugin_dir_path(dirname(__DIR__) . '/plugin.php');
        $this->plugin_url = plugin_dir_url(dirname(__DIR__) . '/plugin.php');

        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init(): void
    {
        add_filter('theme_templates', [$this, 'templates'], 100, 4);
        add_filter('block_editor_settings', [$this, 'blockEditorSettings'], 10, 2);
        add_action('rest_api_init', [$this, 'restApiEndpoints']);
        add_action('enqueue_block_editor_assets', [$this, 'blockEditorAssets']);
        add_action('init', [$this, 'loadTextdomain']);
        add_action('debug_bar_panels', [$this, 'debugBar']);
    }

    /**
     * @param array<string,string> $templates
     * @return array<string,string>
     */
    public function templates(array $templates, WP_Theme $theme, ?WP_Post $post, string $post_type): array
    {
        foreach (\get_gutenberg_templates($post_type) as $template => $args) {
            $name = $args['name'];
            $file = $args['template_file'];
            $templates[$file] = $name;
        }
        return $templates;
    }

    /**
     * @param array<string,mixed> $settings
     * @return array<string,mixed>
     */
    public function blockEditorSettings(array $settings, WP_Post $post): array
    {
        $templateName = get_page_template_slug($post);
        if ($template = get_gutenberg_template_by_file($templateName)) {
            $settings['template'] = $template['template'];
            $settings['templateLock'] = $template['template_lock'];
        }
        return $settings;
    }

    public function restApiEndpoints(): void
    {
        register_rest_route('gutenberg-templates/v1', '/template', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'getTemplate'],
            'args' => [
                'template' => ['required' => true, 'validate_callback' => [$this, 'validateTemplateFile']],
            ],
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
    }

    public function validateTemplateFile(string $template): bool
    {
        return !!preg_match('/^[a-zA-Z0-9-_\/\.]+\.php$/', $template);
    }

    public function getTemplate(WP_REST_Request $request): WP_REST_Response
    {
        $template = \get_gutenberg_template_by_file($request['template']);
        if (!$template) {
            return new WP_REST_Response([
                'code' => 'no_template',
                'message' => 'Invalid template',
            ], 404);
        }
        $response = new WP_REST_Response($template);
        return $response;
    }

    /**
     * @param Debug_Bar_Panel[] $panels
     * @return Debug_Bar_Panel[]
     */
    public function debugBar(array $panels): array
    {
        // Cannot use namespaces and therefore no autoloading.
        require_once __DIR__ . '/DebugBar.php';
        $panels[] = new \GutenbergTemplates_DebugBar();
        return $panels;
    }

    public function blockEditorAssets(): void
    {
        wp_enqueue_script(
            'wp-gutenberg-templates/js',
            $this->plugin_url . '/dist/index.js',
            ['wp-editor', 'wp-data', 'wp-blocks', 'wp-components', 'wp-i18n', 'wp-api-request'],
            filemtime($this->plugin_path . '/dist/index.js'),
        );
        wp_set_script_translations('wp-gutenberg-templates/js', 'wp-gutenberg-templates');
    }

    public function loadTextdomain(): void
    {
        // WP Performance Pack
        include dirname(__DIR__) . '/languages/javascript.php';

        load_plugin_textdomain(
            'wp-gutenberg-templates',
            false,
            dirname(plugin_basename(dirname(__DIR__) . '/plugin.php')) . '/languages'
        );
    }
}
