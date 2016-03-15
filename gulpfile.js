var elixir = require('laravel-elixir');

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

            'libs/chartjs/chartjs.min.js',
            'libs/chartjs/Chart.Bar.js',
            'libs/chartjs/Chart.Core.js',
            'libs/chartjs/Chart.Doughnut.js',
            'libs/chartjs/Chart.Line.js',
            'libs/chartjs/Chart.PolarArea.js',
            'libs/chartjs/Chart.Radar.js',
        ],

        'public/js/libs.js', 'resources/assets/js');

    mix.scripts('app.js', 'public/js/app.js', 'resources/assets/js')
        .scripts('platoon.js', 'public/js/platoon.js', 'resources/assets/js')
});


