<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Portfolio</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
    <link rel="stylesheet" href="account.css" type="text/css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
</head>
<body>

<?php
require 'db_connect.php';
require 'environment.php';
require './vendor/autoload.php';
require 'portfolio_object.php';
use portfolio_object;
require 'user_account.php';
use user_account;
session_start();

/*
 * if user tried to search for stock history of something they've never owned, alert them of this
 */
if($_GET['error'] == 'invalid stock') {
    echo "<script>alert('You have never owned this stock, so there is no history!')</script>";
}

/*Account Info Tab */

// creates object storing attributes of a user account
$accountObj = new user_account();

// get all user info from user table to display in account info tab
$username = mysqli_real_escape_string($connection, $_SESSION['username']);

$queryGetUserID = "SELECT * FROM user WHERE username='$username'";
$queryGetUserIDRes = mysqli_query($connection, $queryGetUserID);
if(!$queryGetUserIDRes || $queryGetUserIDRes->num_rows == 0)
    die('Sorry, something went wrong :(');
while($row = mysqli_fetch_assoc($queryGetUserIDRes)) {
    $fk_user_id = intval($row['user_id']);
    $accountObj->username = $row['username'];
    $accountObj->buyingPower = round($row['buying_power'], 2);
    $accountObj->realizedPortfolioValue = round(($row['portfolio_value']), 2);
}


/* Portfolio Tab */

$iex = new GuzzleHttp\Client();

/*
 * gets all positions user currently has
 * stores properties of positions in portfolio object
 * uses IEX Cloud API to get current price of stock
 */

$portfolioObjects = array();

// get all the positions the user currently has
$queryGetPositions = "SELECT * FROM position WHERE fk_user_id=$fk_user_id";
$queryGetPositionsRes = mysqli_query($connection, $queryGetPositions);
if(!$queryGetPositionsRes)
    die('Sorry, positions could not be retrieved :(');
else {
    while($row = mysqli_fetch_assoc($queryGetPositionsRes)) {
        $portfolioObj = new portfolio_object();
        $portfolioObj->symbol = strtoupper($row['symbol']);
        $portfolioObj->shares = $row['shares'];
        $portfolioObj->avgSharePrice = $row['avg_share_price'];
        try {
            // get current price of stock from API
            $getSymbolPrice = $iex->request('GET', $IEX_CLOUD_API_BASE_URL . 'stable/stock/' . $portfolioObj->symbol . '/quote/latestPrice?token=' . $IEX_CLOUD_API_KEY);
            $getSymbolPriceRes = json_decode($getSymbolPrice->getBody()->getContents(), true);
        } catch(\GuzzleHttp\Exception\GuzzleException $e) {
            die('Sorry, current price of stock could not be retrieved :(');
        }
        $portfolioObj->profitLoss = round(((($getSymbolPriceRes) - ($portfolioObj->avgSharePrice)) * ($portfolioObj->shares)), 2);
        array_push($portfolioObjects, $portfolioObj);
    }
}
?>

<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header
            mdl-layout--fixed-tabs">
    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <span class="mdl-layout-title">Stock Trader</span>
            <div class="mdl-layout-spacer"></div>

            <nav class="mdl-navigation mdl-layout--large-screen-only">
                <a class="mdl-navigation__link" href="home.php">Home</a>
                <a class="mdl-navigation__link" href="watchlists.php">Watchlists</a>
                <a class="mdl-navigation__link" href="trade_buy.php">Buy Stocks</a>
                <a class="mdl-navigation__link" href="trade_sell.php">Sell Stocks</a>
                <a class="mdl-navigation__link" href="login.php?loggedIn=FALSE">Logout</a>
            </nav>
        </div>
        <div class="mdl-layout__tab-bar mdl-js-ripple-effect">
            <a href="#fixed-tab-1" class="mdl-layout__tab is-active">Portfolio</a>
            <a href="#fixed-tab-2" class="mdl-layout__tab">Account Info</a>
            <a href="#fixed-tab-3" class="mdl-layout__tab">History</a>
        </div>
    </header>
    <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">Stock Trader</span>
        <nav class="mdl-navigation">
            <a class="mdl-navigation__link" href="home.php">Home</a>
            <a class="mdl-navigation__link" href="watchlists.php">Watchlists</a>
            <a class="mdl-navigation__link" href="trade_buy.php">Buy Stocks</a>
            <a class="mdl-navigation__link" href="trade_sell.php">Sell Stocks</a>
            <a class="mdl-navigation__link" href="login.php?loggedIn=FALSE">Logout</a>
        </nav>
    </div>
    <main class="mdl-layout__content">
        <section class="mdl-layout__tab-panel is-active" id="fixed-tab-1">
            <div class="page-content">
                <div class="container fade-in-search" style="text-align: center">
                <?php
                // if user has no positions, suggest that they buy some stocks
                if(count($portfolioObjects) == 0) {
                    echo "<h4>You have no current positions! Buy some stocks and check again!</h4>";
                }
                ?>
                <table class="mdl-data-table mdl-js-data-table mdl-data-table mdl-shadow--2dp" style="margin: 10px auto auto;">
                    <thead>
                    <tr>
                        <th class="mdl-data-table__cell--non-numeric">Ticker Symbol</th>
                        <th>Quantity</th>
                        <th>Average Share Price</th>
                        <th>Unrealized Gain/Loss</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    // go through each position in portfolio and output in tabular format
                    for($i = 0; $i < count($portfolioObjects); $i++) {
                        echo "
                            <tr>
                            <td class='mdl-data-table__cell--non-numeric'>{$portfolioObjects[$i]->symbol}</td>
                            <td>{$portfolioObjects[$i]->shares}</td>
                            <td>$ {$portfolioObjects[$i]->avgSharePrice}</td>
                            <td>$ {$portfolioObjects[$i]->profitLoss}</td>
                            </tr>
                        ";

                    }
                    ?>
                    </tbody>
                </table>
                 <!-- refresh button to see if any price info has updated -->
                <a href="account.php" style="margin: auto; text-align: center">
                    <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent search" id="portfolioRefresh"
                            style="margin: 10px auto auto; text-align: center; display: block">
                        Refresh
                    </button>
                </a>
            </div>
            </div>
        </section>
        <!-- table to display basic account info (username, buying power, realized portfolio value) -->
        <section class="mdl-layout__tab-panel" id="fixed-tab-2">
            <div class="page-content">
                <div class="container fade-in-search" style="text-align: center">
                    <table class="mdl-data-table mdl-js-data-table mdl-data-table mdl-shadow--2dp" style="margin: 10px auto;">
                        <thead>
                        <tr>
                            <th class="mdl-data-table__cell--non-numeric">Field</th>
                            <th>Value</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="mdl-data-table__cell--non-numeric">Username</td>
                            <?php
                            echo "<td>{$accountObj->username}</td>";
                            ?>
                        </tr>
                        <tr>
                            <td class="mdl-data-table__cell--non-numeric">Buying Power</td>
                            <?php
                            echo "<td>$ {$accountObj->buyingPower}</td>";
                            ?>
                        </tr>
                        <tr>
                            <td class="mdl-data-table__cell--non-numeric">Realized Portfolio Value</td>
                            <?php
                            echo "<td>$ {$accountObj->realizedPortfolioValue}</td>";
                            ?>

                        </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>
        <!-- section to allow user to search for any stock they've owned at any point in the past and see some stats about how they did -->
        <section class="mdl-layout__tab-panel" id="fixed-tab-3">
            <div class="page-content">
                <div class="container fade-in-search" style="text-align: center;">
                    <h4 style="margin-bottom: 25px">Stock Trade History</h4>
                    <h6>Search for any stock you've traded in the past</h6>
                    <form action="stock_history_results.php" method="post">
                        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                            <input class="mdl-textfield__input" type="text" id="historySearch" name="historySearch">
                            <label class="mdl-textfield__label" for="historySearch">Search for a stock!</label>
                        </div>
                        <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent search">
                            Search
                        </button>
                    </form>
                </div>
            </div>
        </section>


    </main>
</div>

</body>
</html>
