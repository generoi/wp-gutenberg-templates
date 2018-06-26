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
    npm run production

    # Watch for changes and re-compile while developing the plugin
    npm run watch

## Translations

During compilation a `languages/javascript.pot` containing the translatable strings from JavaScript will be created. This needs to be converted to PHP which is done automatically in the next step.

Rebuild POT files (after this, copy to each language as languages/wp-gutenberg-backgrounds-<langcode>.po and translate it)

    npm run lang:pot

Compile MO files (requires msgfmt which is available with brew install gettext && brew link gettext --force)

    npm run lang:mo

Or run all of these with:

    npm run lang
