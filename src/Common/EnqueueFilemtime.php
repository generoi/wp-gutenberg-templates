<?php

namespace GeneroWP\BlockBoilerplate\Common;

trait EnqueueFilemtime
{
    public function enqueue_style($handle, $path, $dependencies = [])
    {
        wp_enqueue_style("{$this->plugin_name}:{$handle}", $this->plugin_url . $path, $dependencies, filemtime($this->plugin_path . $path));
    }

    public function enqueue_script($handle, $path, $dependencies = [])
    {
        wp_enqueue_script("{$this->plugin_name}:{$handle}", $this->plugin_url . $path, $dependencies, filemtime($this->plugin_path . $path));
    }
}
