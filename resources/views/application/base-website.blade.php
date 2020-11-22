<!DOCTYPE html>
<html lang="en">
<head>
    <title>ClanAOD.net </title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css"
          integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('site.css') }}"/>

    <script src="<?= get_template_directory_uri() . '/assets/js/modernizr.js' ?>"></script>

    <?php include(get_template_directory() . '/assets/partials/favicons.php'); ?>
    <?php include(get_template_directory() . '/assets/partials/site-meta.php'); ?>

</head>

<body>

<?php do_shortcode('[commo]'); ?>

@include('website.partials.apply-form')
