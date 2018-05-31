<?php

namespace GeneroWP\BlockBoilerplate\Common;

use GeneroWP\BlockBoilerplate\Plugin;

trait Templating
{
    public function template($slug, $attributes)
    {
        $handle = Plugin::get_instance()->plugin_name;
        $templates = apply_filters("$handle/template_hierarchy", [
            "$slug.twig",
            "$slug.php",
        ]);

        $template = locate_template($templates);
        if ($template = apply_filters("$handle/template", $template)) {
            // If it's not twig, render as php
            if (substr($template, -5) !== '.twig') {
                return $this->renderPhpTemplate($template, $attributes);
            } else {
                // If the path is absolute, use the relative path from the theme.
                $template = str_replace(TEMPLATEPATH, '', $template);
                return Timber::fetch($template, $attributes);
            }
        }
        return $this->renderPhpTemplate(__DIR__ . "views/$slug.php");
    }

    public function renderPhpTemplate($path, $attributes)
    {
        extract($attributes);
        ob_start();
        include $path;
        return ob_get_clean();
    }
}
