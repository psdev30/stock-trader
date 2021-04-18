<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
    <link rel="stylesheet" href="home.css" type="text/css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
</head>
<body>

<?php
session_start();
require 'environment.php';
require './vendor/autoload.php';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $APCA_API_ACCOUNT_URL);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'APCA-API-KEY-ID: PK8T6XSMADPA0FMN31ZY', 'APCA-API-SECRET-KEY: dxySExS9aqpVVIO0C1xR2jBg16nTQTHbMWNrVYFh'
));
$output = curl_exec($curl);


use Alpaca\Alpaca;

$alpaca = new Alpaca('PK8T6XSMADPA0FMN31ZY', 'dxySExS9aqpVVIO0C1xR2jBg16nTQTHbMWNrVYFh', true);

if ($_POST) {
    $inputTicker = $_POST['tickerSearch'];
    $iex = new \GuzzleHttp\Client();
    try {
        $request = $iex->request('GET', $IEX_CLOUD_SANDBOX_BASE_URL . '/stable/stock/' . $inputTicker . '/quote?token=' . $IEX_CLOUD_SANDBOX_API_KEY);
        $resp = $request->getBody()->getContents();
        $_SESSION['tickerInfo'] = $resp;
        header("Location: stock_info.php?ticker=$inputTicker");

    } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        echo "<script>alert('Sorry, you entered an invalid ticker. Try again!')</script>";
    }
}

?>

<!-- Always shows a header, even in smaller screens. -->
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <!-- Title -->
            <span class="mdl-layout-title">Stock Trader</span>
            <!-- Add spacer, to align navigation to the right -->
            <div class="mdl-layout-spacer"></div>
            <!-- Navigation. We hide it in small screens. -->
            <nav class="mdl-navigation mdl-layout--large-screen-only">
                <a class="mdl-navigation__link" href="">Watchlists</a>
                <a class="mdl-navigation__link" href="">Trade</a>
            </nav>
        </div>
    </header>
    <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">Title</span>
        <nav class="mdl-navigation">
            <a class="mdl-navigation__link" href="">Watchlists</a>
            <a class="mdl-navigation__link" href="">Trade</a>
        </nav>
    </div>
    <main class="mdl-layout__content">
        <div class="page-content">
            <div class="container fade-in-search">
                <h4 style="margin-bottom: 25px">Welcome to Stock Trader!</h4>
                <form action="home.php" method="post">
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <input class="mdl-textfield__input" type="text" id="tickerSearch" name="tickerSearch">
                        <label class="mdl-textfield__label" for="tickerSearch">Search for a stock!</label>
                    </div>
                    <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent search">
                        Search
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>

</body>
</html>


<?php
?>