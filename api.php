<?php

function register_gutenberg_template($template_name, $args = [])
{
    global $wp_gutenberg_templates;
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

function get_gutenberg_templates($post_type)
{
    global $wp_gutenberg_templates;
    return $wp_gutenberg_templates[$post_type] ?? null;
}

function get_gutenberg_template($template)
{
    global $wp_gutenberg_templates;
    foreach ($wp_gutenberg_templates as $post_type => $templates) {
        if (isset($templates[$template])) {
            return $templates[$template];
        }
    }
}

function get_gutenberg_template_by_file($template_file)
{
    global $wp_gutenberg_templates;
    foreach ($wp_gutenberg_templates as $post_type => $templates) {
        foreach ($templates as $template => $args) {
            if ($args['template_file'] === $template_file) {
                return $args;
            }
        }
    }
}
