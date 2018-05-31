<?php

namespace GeneroWP\BlockBoilerplate\example_block;

use GeneroWP\BlockBoilerplate\Common;

class ExampleBlock
{
    use Common\Singleton;
    use Common\Twig;

    public function __construct()
    {
        register_block_type('genero/example-block', array(
            'attributes' => [
                'align' => [
                    'type' => 'string',
                    'default' => 'full',
                ],
            ],
            'render_callback' => [$this, 'render'],
        ));
    }

    public function render($attributes)
    {
        return $this->template('example-block', $attributes);
    }
}

ExampleBlock::get_instance();
