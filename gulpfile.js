const elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir.config.sourcemaps = false;

elixir(function (mix) {

    mix.sass('app.scss');

    mix.scripts(
        [
            'libs/jquery/jquery-2.1.1.min.js',
            'libs/jquery/jquery-ui.min.js',

            'libs/jquery/jquery.easing.min.js',
            'libs/jquery/jquery.bootstrap.wizard.min.js',
            'libs/jquery/jquery.powertip.min.js',

            'libs/bootstrap/bootstrap.min.js',
            'libs/bootstrap/bootstrap-multiselect.js',

            'libs/dataTables/jquery.dataTables.min.js',
            'libs/dataTables/dataTables.bootstrap.js',
            'libs/dataTables/dataTables.tableTools.min.js',
        ],

        'public/js/libs.js', 'resources/assets/js');

    mix.browserify('main.js');
    mix.browserify('platoon.js');
});


