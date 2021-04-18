<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
    <link rel="stylesheet" href="stock_info.css" type="text/css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
</head>
<body>
<?php
require './vendor/autoload.php';
require 'environment.php';
/*require 'db_connect.php';*/
session_start();


$quote = json_decode($_SESSION['tickerInfo'], true);
$ticker = $_GET['ticker'];
/*print_r($searchPayload);
echo "<br><br>";*/

$iex = new \GuzzleHttp\Client();
try {
    $request = $iex->request('GET', $IEX_CLOUD_SANDBOX_BASE_URL . '/stable/stock/' . $ticker . '/company?token=' . $IEX_CLOUD_SANDBOX_API_KEY);
    $companyDetails = json_decode($request->getBody()->getContents(), true);
} catch (GuzzleException $e) {
    echo "<script>alert('Sorry, the API request failed :(')</script>";
}

/*print_r($companyDetails);*/

?>

<!-- Simple header with fixed tabs. -->
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header
            mdl-layout--fixed-tabs">
    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <!-- Title -->
            <span class="mdl-layout-title"><?php echo $quote['companyName'] . ' ' . '(' . $quote['symbol'] . ')'; ?></span>
            <!-- Colored mini FAB button -->
            <button class="mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-button--colored"
                    id="addWatchlist"
                    style="position: absolute; right: 0; margin-right: 10px;">
                <i class="material-icons">add</i>
            </button>
            <!-- Large Tooltip -->
            <div class="mdl-tooltip mdl-tooltip" for="addWatchlist">
                Add to Watchlist
            </div>

        </div>
        <!-- Tabs -->
        <div class="mdl-layout__tab-bar mdl-js-ripple-effect">
            <a href="#generalInfo" class="mdl-layout__tab is-active">General Info</a>
            <a href="#fundamentalIndicators" class="mdl-layout__tab">Fundamental Indicators</a>
            <a href="#technicalIndicators" class="mdl-layout__tab">Technical Indicators</a>
        </div>
    </header>
    <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">Title</span>
        <nav class="mdl-navigation">
            <a class="mdl-navigation__link" href="home.php">Home</a>
            <a class="mdl-navigation__link" href="">Watchlists</a>
            <a class="mdl-navigation__link" href="">Trade</a>
        </nav>
    </div>
    <main class="mdl-layout__content">
        <section class="mdl-layout__tab-panel is-active" id="generalInfo">
            <div class="page-content">
                <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
                    <thead>
                    <tr>
                        <th class="mdl-data-table__cell--non-numeric">Category</th>
                    </tr>
                    </thead>

                    <tbody>
                    <tr>
                        <td class="mdl-data-table__cell--non-numeric">Headquarters</td>
                        <td class="mdl-data-table__cell--non-numeric"><?php echo $companyDetails['address'] . ' ' . $companyDetails['city'] . ', ' . $companyDetails['state'] ?></td>
                    </tr>
                    <tr>
                        <td class="mdl-data-table__cell--non-numeric">Website</td>
                        <td class="mdl-data-table__cell--non-numeric"><?php echo $companyDetails['website']; ?></td>

                    </tr>
                    <tr>
                        <td class="mdl-data-table__cell--non-numeric">CEO</td>
                        <td class="mdl-data-table__cell--non-numeric"><?php echo $companyDetails['CEO']; ?></td>

                    </tr>
                    <tr>
                        <td class="mdl-data-table__cell--non-numeric">Industry</td>
                        <td class="mdl-data-table__cell--non-numeric"><?php echo $companyDetails['industry']; ?></td>

                    </tr>
                    <tr>
                        <td class="mdl-data-table__cell--non-numeric">Ticker Symbol</td>
                        <td class="mdl-data-table__cell--non-numeric"><?php echo $quote['symbol']; ?></td>
                    </tr>
                    <tr>
                        <td class="mdl-data-table__cell--non-numeric">Market Cap</td>
                        <td class="mdl-data-table__cell--non-numeric"><?php echo $quote['marketCap']; ?></td>

                    </tr>
                    <tr>
                        <td class="mdl-data-table__cell--non-numeric">Primary Exchange</td>
                        <td class="mdl-data-table__cell--non-numeric"><?php echo $quote['primaryExchange']; ?></td>

                    </tr>

                    </tbody>
                </table>
            </div>
        </section>
        <section class="mdl-layout__tab-panel" id="fundamentalIndicators">
            <div class="page-content">
                <div style="text-align: center">

                </div>
            </div>
        </section>
        <section class="mdl-layout__tab-panel" id="technicalIndicators">
            <div class="page-content">
                <div style="text-align: center">

                </div>
            </div>
        </section>
    </main>
</div>

</body>
</html>




