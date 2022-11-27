<!DOCTYPE html>
<html>

<head>
<meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Rudy-Api</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed&display=swap" rel="stylesheet">
        <!-- <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet"> -->
    <style>
        body {

            background-image: url('https://files.merudy.com/q-mix/api_laravel/1665391719202210100848393248.png');
        }

        #container {
            font-family: 'Roboto Condensed', sans-serif;
            box-shadow: 0 10px 20px 1px grey;
            background: rgba(255, 255, 255, 0.90);
            border-radius: 5px;
            overflow: hidden;
            margin: 5em auto;
            margin-top: 150px;
            height: 310px;
            width: 775px;
        }

        .card-inline {
            background: #F9FCFF;
            border-radius: 15px;
            overflow: hidden;
            margin-top: 15px;

            width: 300px;
        }

        .text-incard {
            text-align: left;
            margin-left: 20px;
            font-size: 24px;

        }

        .product-details {
            position: relative;
            overflow: hidden;
            padding: 30px;
            height: 100%;
            float: left;
            width: 40%;

        }

        .product-image {
            display: inline-block;
            margin-left: 25px;
        }

        .font-blue {
            color: #0066FF;
        }

        .text-left {
            text-align: left;
            margin-left: 20px;

        }

        hr.style1 {
            border-top: 1px solid #8c8b8b;
        }
    </style>
</head>

<body class="antialiased">

    <div id="container">
        <div class="product-details">
            <img src="https://files.merudy.com/q-mix/api_laravel/1665391719202210100848398171.png" class="text-rudy">
            <!-- <p style="color:#8c8b8b ;">Connecting to Rudy’s Powerful API</p> -->
            <div class="card-inline">
                <p class="text-incard font-blue">Merudy API</p>
                <p class="text-left">Connecting to Rudy’s Powerful API <br> Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</p>
            </div>
            <hr class="stye1">
            <p style="font-size: 14px; color:#415B9175;">contact us : <span class="font-blue">info@merudy.com</span></p>
        </div>

        <div class="product-image">
            <img src="https://files.merudy.com/q-mix/api_laravel/1665391719202210100848396543.png" class="">
        </div>

    </div>

</body>

</html>
