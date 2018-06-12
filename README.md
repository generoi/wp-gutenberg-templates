# wp-gutenberg-templates

> Add page template support to Gutenberg


## Features

Allow switching the Gutenberg block template based on the page template attribute.

## API

```php
add_action('init', function () {
    register_gutenberg_template('foobar', [
        'post_type' => 'page',
        'name' => __('Foobar'),
        'template' => [
            ['genero/banner'],
            ['core/paragraph'],
        ],
        'template_lock' => 'all',
    ]);
});
```

## Development

Install dependencies

    composer install
    npm install

Run the tests

    npm run test

Build assets

    # Minified assets which are to be committed to git
    npm run build

    # Watch for changes and re-compile while developing the plugin
    npm run watch
