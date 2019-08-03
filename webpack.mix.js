let mix = require('laravel-mix');


mix.sass('resources/scss/engine.scss', 'public/css')
    .scripts(
        [
            'resources/js/engine.js',
        ],'public/js/engine.js'
    )
    .version();
