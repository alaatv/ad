let mix = require('laravel-mix');


mix.sass('resources/scss/adEngine.scss', 'public/css')
    .scripts(
        [
            'resources/js/adEngine.js',
        ],'public/js/adEngine.js'
    )
    .version();
