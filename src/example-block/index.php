<?php

namespace GeneroWP\BlockBoilerplate\example_block;

use GeneroWP\Common\Singleton;
use GeneroWP\Common\Templating;

class ExampleBlock
{
    use Singleton;
    use Templating;

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
        return $this->template('gutenberg', 'views/example-block.php', $attributes);
    }
}

ExampleBlock::getInstance();
