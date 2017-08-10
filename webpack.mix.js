const mix = require('laravel-mix');

mix.sass('assets/styles/main.scss', 'dist/')
  .js('assets/scripts/main.js', 'dist/')
  .options({
    processCssUrls: false
  });
