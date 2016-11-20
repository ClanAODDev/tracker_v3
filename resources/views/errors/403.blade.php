<html>
<head>
    <title>AOD | 404 Page not found</title>
    <link rel="stylesheet" type="text/css" href="//bootswatch.com/lumen/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/app.css') }}">
    <link href='//fonts.googleapis.com/css?family=Lato:100' rel='stylesheet' type='text/css'>
    <style>
        body {
            background-image: url('{{ asset("images/bg.jpg") }}');
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            color: #888;
            display: table;
            font-weight: 100;
        }

        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div class="container error">
    <div class="content">
        <div class="title">Unauthorized</div>
        <strong>You are not authorized to perform that action. You should probably
            <a href="{{ url('/home') }}">go home</a></strong>
    </div>
</div>
</body>
</html>
