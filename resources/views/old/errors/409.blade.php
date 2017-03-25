<html>
<head>
    <title>AOD | Inactive Member</title>
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
<div class="wrap">
    <div class="container error">
        <div class="content">
            <div class="title">Sorry</div>
            You are attempting to edit a user that is no longer an AOD member, and that's no bueno. You should probably
            <a href="{{ url('/home') }}"><strong>go home</strong></a>.
        </div>
    </div>
</div>
</body>
</html>
