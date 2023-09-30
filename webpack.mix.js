const mix = require('laravel-mix');
require('laravel-mix-clean');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.clean()
    .js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css')
    .copy('resources/root/*.*','public')
    .copy('resources/root/.*','public')
    .copy('resources/img/*.*','public/img')
    .options({
        processCssUrls: false,
        postCss: [
            require('tailwindcss/nesting'),
            require('tailwindcss'),
            require('autoprefixer')
        ]
    });
