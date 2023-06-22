<?php

/** @param array<string,mixed> $args */
function register_gutenberg_template(string $template_name, array $args = []): void
{
    global $wp_gutenberg_templates;

    $defaults = [
        'post_type' => 'page',
        'name' => false,
        'template' => [],
        'template_file' => false,
        'template_lock' => false,
    ];
    $args = array_merge($defaults, $args);

    $post_type = $args['post_type'];

    if (!is_array($wp_gutenberg_templates)) {
        $wp_gutenberg_templates = [];
    }
    if (!isset($wp_gutenberg_templates[$post_type])) {
        $wp_gutenberg_templates[$post_type] = [];
    }

    if (empty($args['template_file'])) {
        $args['template_file'] = 'template-' . $template_name . '.php';
    }

    $wp_gutenberg_templates[$post_type][$template_name] = $args;
}

/**
 * @return array<string,mixed>
 */
function get_gutenberg_templates(string $post_type): array
{
    global $wp_gutenberg_templates;
    return $wp_gutenberg_templates[$post_type] ?? [];
}

/**
 * @return array<string,mixed>|null
 */
function get_gutenberg_template(string $template)
{
    global $wp_gutenberg_templates;
    if (empty($wp_gutenberg_templates)) {
        return null;
    }
    foreach ($wp_gutenberg_templates as $post_type => $templates) {
        if (isset($templates[$template])) {
            return $templates[$template];
        }
    }
    return null;
}

/**
 * @return array<string,mixed>|null
 */
function get_gutenberg_template_by_file(string $template_file): ?array
{
    global $wp_gutenberg_templates;
    if (empty($wp_gutenberg_templates)) {
        return null;
    }
    foreach ($wp_gutenberg_templates as $post_type => $templates) {
        foreach ($templates as $template => $args) {
            if ($args['template_file'] === $template_file) {
                return $args;
            }
        }
    }

    return null;
}
