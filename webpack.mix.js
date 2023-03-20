let mix = require('laravel-mix');

mix.styles("resources/assets/css/style.css", "public/css/style.css");
mix.styles("resources/assets/css/site.css", "public/css/site.css");

mix.scripts(
    [

        "resources/js/libs/jquery/jquery-2.1.1.min.js",
        "resources/js/libs/jquery/jquery-ui.min.js",

        "resources/js/libs/jquery/jquery.bootstrap.wizard.min.js",
        "resources/js/libs/jquery/jquery.powertip.min.js",
        "resources/js/libs/jquery/jquery.repeater.min.js",
        "resources/js/libs/jquery/jquery.stickytabs.js",
        "resources/js/libs/jquery/jquery.bootcomplete.min.js",
        "resources/js/libs/jquery/jquery.select2.min.js",

        "resources/js/libs/jquery/jquery.flot.min.js",
        "resources/js/libs/jquery/jquery.flot.axislabels.js",
        "resources/js/libs/jquery/jquery.flot.resize.min.js",
        "resources/js/libs/jquery/jquery.flot.spline.js",
        "resources/js/libs/jquery/jquery.flot.pie.min.js",
        "resources/js/libs/jquery/jquery.flot.time.min.js",
        "resources/js/libs/jquery/jquery.flot.comments.min.js",
        "resources/js/libs/jquery/jquery.flot.tooltip.min.js",

        "resources/js/libs/bootstrap/bootstrap.min.js",
        "resources/js/libs/bootstrap/bootstrap-multiselect.js",

        "resources/js/libs/sparkline.js",
        "resources/js/libs/CopyClipboard.js",
        "resources/js/libs/prism.js",
        "resources/js/libs/codemirror.js",
        "resources/js/libs/twig.js"
    ],

    "public/js/libs.js");

mix.js("resources/assets/js/main.js", "public/js/main.js")
    .js("resources/assets/js/platoon.js", "public/js/platoon.js")
    .js("resources/assets/js/members.js", "public/js/members.js")
    .js("resources/assets/js/division.js", "public/js/division.js")
    .js("resources/assets/js/census-graph.js", "public/js/census-graph.js")
    .js("resources/assets/js/recruiting.js", "public/js/recruiting.js")
    .js("resources/assets/js/training.js", "public/js/training.js")
    .js("resources/assets/js/admin.js", "public/js/admin.js")
    .js("resources/assets/js/manage-member.js", "public/js/manage-member.js")
    .vue({version: 2});



