<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Trader</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
    <link rel="stylesheet" href="watchlists.css" type="text/css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
</head>
<body>


<?php
require './vendor/autoload.php';
require 'environment.php';
require 'db_connect.php';
require 'watchlist_stock_object.php';
use watchlist_stock_object;
require 'watchlist_object.php';
use watchlist_object;
session_start();

$username = mysqli_real_escape_string($connection, $_SESSION['username']);

// gets how much money user has to spend currently
$queryGetCurrentAvailableFunds = "SELECT user_id FROM user WHERE username='$username'";
$queryGetCurrentAvailableFundsRes = mysqli_query($connection, $queryGetCurrentAvailableFunds);
if(!$queryGetCurrentAvailableFundsRes)
    die('Sorry, total available funds could not be retrieved :(');
while($row = mysqli_fetch_row($queryGetCurrentAvailableFundsRes)) {
    $userID = $row[0];
}

// watchlists.php is called also when user tries to delete either the entire watchlist or a single stock within it
if($_GET) {
    $watchlistIDToDelete = $_GET['watchlistID'];

    if(isset($_GET['deleteWatchlist']) && $_GET['deleteWatchlist'] == TRUE) {
        $queryDeleteFromStockWatchlistTable = "DELETE FROM stock_watchlist WHERE fk_watchlist_id=$watchlistIDToDelete";
        $queryDeleteFromStockWatchlistTableRes = mysqli_query($connection, $queryDeleteFromStockWatchlistTable);
        if(!$queryDeleteFromStockWatchlistTableRes)
            die('Sorry, junction table data could not be deleted :(');

        $queryDeleteWatchlist = "DELETE FROM watchlist WHERE watchlist_id=$watchlistIDToDelete";
        $queryDeleteWatchlistRes = mysqli_query($connection, $queryDeleteWatchlist);
        if(!$queryDeleteWatchlistRes)
            die('Sorry, watchlist could not be deleted :(');
    }
    else {
        $tickerToDelete = $_GET['deleteTicker'];
        $watchlistContainedID = $_GET['watchlistID'];
        $stockIDToDelete = $_GET['stockID'];

        $queryDeleteTickerFromWatchlist = "DELETE FROM stock_watchlist WHERE fk_stock_id = $stockIDToDelete AND fk_watchlist_id = $watchlistContainedID";
        $queryDeleteTickerFromWatchlistRes = mysqli_query($connection, $queryDeleteTickerFromWatchlist);
        if(!$queryDeleteTickerFromWatchlistRes)
            die('Sorry, stock could not be deleted from watchlist :(');
    }


}


$watchlistObjects = array();
$stockNames = array();

// gets properties of each watchlist and fills object
$queryGetWatchlists = "SELECT * FROM watchlist WHERE fk_user_id=$userID";
$queryGetWatchlistsRes = mysqli_query($connection, $queryGetWatchlists);
if(!$queryGetWatchlistsRes)
    die('Sorry, watchlists could not be retrieved :(');
while($row = mysqli_fetch_assoc($queryGetWatchlistsRes)) {
    $watchlistObject = new watchlist_object();
    $watchlistObject->watchlistID = $row['watchlist_id'];
    $watchlistObject->watchlistName = $row['watchlist_name'];
    $watchlistObject->stockObjectArr = array();
    array_push($watchlistObjects, $watchlistObject);
}

// for each watchlist, get all the stocks currently in it and all of their properties
// when all the info for a stock is gathered, add it to its appropriate watchlist object
// at the end there will be as many watchlist objects as there are watchlists created by the user, and each watchlist object will have as many stock objects as the user added stocks to the watchlist
for($i = 0; $i < count($watchlistObjects); $i++) {
    $watchlistObject = $watchlistObjects[$i];
    $watchlistID = $watchlistObject->watchlistID;
    $queryGetStocksInCurrWatchlist = "SELECT * FROM stock_watchlist WHERE fk_watchlist_id=$watchlistID";
    $queryGetStocksInCurrWatchlistRes = mysqli_query($connection, $queryGetStocksInCurrWatchlist);
    if ($queryGetStocksInCurrWatchlistRes->num_rows == 0) {
        break;
    } else {
        $stockIDsInWatchlist = array();
        while ($row = mysqli_fetch_assoc($queryGetStocksInCurrWatchlistRes)) {
            array_push($stockIDsInWatchlist, $row['fk_stock_id']);
        }
        $stockNamesInWatchlist = array();
        $stockTickersInWatchlist = array();
        for ($j = 0; $j < count($stockIDsInWatchlist); $j++) {
            $currStockID = $stockIDsInWatchlist[$j];
            $queryGetStockNameFromID = "SELECT stock_name, stock_ticker FROM stock WHERE stock_id = $currStockID";
            $queryGetStockNameFromIDRes = mysqli_query($connection, $queryGetStockNameFromID);
            if (!$queryGetStockNameFromIDRes) {
                die('Sorry, stock names could not be retrieved from their IDs :(');
            } else {
                while ($row = mysqli_fetch_row($queryGetStockNameFromIDRes)) {
                    array_push($stockNamesInWatchlist, $row[0]);
                    array_push($stockTickersInWatchlist, $row[1]);
                }
            }
        }

        for ($z = 0; $z < count($stockNamesInWatchlist); $z++) {
            $stockObject = new watchlist_stock_object();
            $stockObject->stockID = $stockIDsInWatchlist[$z];
            $stockObject->stockName = $stockNamesInWatchlist[$z];
            $stockObject->stockTicker = strtoupper($stockTickersInWatchlist[$z]);

            $iex = new GuzzleHttp\Client();
            try {
                // use API to get current price, how much price has changed by, and the percent it has changed by today
                $stockPriceInfo = $iex->request('GET', $IEX_CLOUD_API_BASE_URL . 'stable/stock/' . $stockObject->stockTicker . '/quote?token=' . $IEX_CLOUD_API_KEY);
                $stockPriceInfoRes = json_decode($stockPriceInfo->getBody()->getContents(), true);
                $stockObject->currPrice = floatval(round($stockPriceInfoRes["latestPrice"], 2));
                $stockObject->change = floatval(round($stockPriceInfoRes["change"], 2));
                $stockObject->changePercent = floatval(round($stockPriceInfoRes["changePercent"] * 100, 2));
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
                echo "<script>alert('Sorry, the stock price info could not be loaded :(')</script>";
            }
            array_push($watchlistObject->stockObjectArr, $stockObject);
        }
    }
}
?>

<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <span class="mdl-layout-title">Stock Trader Watchlists</span>

            <div class="mdl-layout-spacer"></div>
            <nav class="mdl-navigation mdl-layout--large-screen-only">
                <a class="mdl-navigation__link" href="home.php">Home</a>
                <a class="mdl-navigation__link" href="trade_buy.php">Buy Stocks</a>
                <a class="mdl-navigation__link" href="trade_sell.php">Sell Stocks</a>
                <a class="mdl-navigation__link" href="account.php">Portfolio</a>
                <a class="mdl-navigation__link" href="login.php?loggedIn=FALSE">Logout</a>
            </nav>

            <a href="add_watchlist.php">
                <button class="mdl-button mdl-js-button mdl-button--icon mdl-button--icon" id="addWatchlist" style="position: absolute; right: 0; margin-right: 10px; margin-bottom: 10px" >
                    <i class="material-icons">add</i>
                </button>
            </a>
            <div class="mdl-tooltip mdl-tooltip" for="addWatchlist">
                Create New Watchlist
            </div>

        </div>

        <?php
        echo "<div class='mdl-layout__tab-bar mdl-js-ripple-effect'>";
        for($i = 0; $i < count($watchlistObjects); $i++) {
            if($i == 0) {
                echo "<a href='#scroll-tab-$i' class='mdl-layout__tab is-active'>{$watchlistObjects[$i]->watchlistName}</a>";
            }
            else {
                echo "<a href='#scroll-tab-$i' class='mdl-layout__tab'>{$watchlistObjects[$i]->watchlistName}</a>";
            }
        }
        echo "</div>"
        ?>

    </header>
    <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">Stock Trader Watchlists</span>
        <nav class="mdl-navigation">
            <a class="mdl-navigation__link" href="home.php">Home</a>
            <a class="mdl-navigation__link" href="trade_buy.php">Buy Stocks</a>
            <a class="mdl-navigation__link" href="trade_sell.php">Sell Stocks</a>
            <a class="mdl-navigation__link" href="account.php">Portfolio</a>
            <a class="mdl-navigation__link" href="login.php?loggedIn=FALSE">Logout</a>
        </nav>
    </div>


    <main class="mdl-layout__content">
        <?php
        for($i = 0; $i < count($watchlistObjects); $i++) {
            $watchlistObject = $watchlistObjects[$i];
            if($i == 0) {
                echo "<section class='mdl-layout__tab-panel is-active' id='scroll-tab-$i'>";
            } else {
                echo "<section class='mdl-layout__tab-panel' id='scroll-tab-$i'>";
            }
            echo "<div class='page-content'>
                    <div class='container-style fade-in'>
                        <div class='header fade-in-headers'>
                            <h4 class='headings fade-in-headers'>
                                {$watchlistObjects[$i]->watchlistName}
                            </h4>
                        </div>
                        <div class='container button-suite' style='margin-bottom: 5%; text-align: center'>       
                            <a href='watchlists.php'>
                                <button class='mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-button--colored fade-in-refresh'>
                                    <i id='refreshWatchlist' class='material-icons'>refresh</i>
                                </button>
                            </a>
                            <div class='mdl-tooltip' data-mdl-for='refreshWatchlist'>
                                <strong>Refresh prices</strong>
                            </div>
                            <a href='watchlists.php?deleteWatchlist=TRUE&watchlistID={$watchlistObject->watchlistID}'>
                            <button class='mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-button--colored fade-in-refresh'>
                                <i id='deleteWatchlist' class='material-icons'>delete_sweep</i>
                            </button>
                            <div class='mdl-tooltip' data-mdl-for='deleteWatchlist'>
                                <strong>Delete watchlist</strong>
                            </div>
                            </a> 
                        </div>
            ";

            for($z = 0; $z < count($watchlistObject->stockObjectArr); $z++) {
                echo "
                        <div class='demo-card-wide mdl-card mdl-shadow--2dp fade-in' style='padding: 0; margin: auto auto 5%;'>
                            <div style='display: flex; flex-direction: row; justify-content: center;'>
                                <div class='mdl-card__title
                            
                      ";
                                if($watchlistObject->stockObjectArr[$z]->change > 0)
                                    echo "card-color-green";
                                else if($watchlistObject->stockObjectArr[$z]->change < 0)
                                    echo "card-color-red";
                                else
                                    echo "card-color-gray";
                echo "                   
                                'style='padding-top: 2%;
                                    padding-bottom: 2%; width: 100%; color: white; font-size: x-large; justify-content: center;'>
                                    <h2 class='mdl-card__title-text'>{$watchlistObject->stockObjectArr[$z]->stockTicker}</h2>
                                    <i class='material-icons' style='margin-left: 10px'>
                                    ";
                                    if($watchlistObject->stockObjectArr[$z]->change > 0.05)
                                        echo "trending_up";
                                    else if($watchlistObject->stockObjectArr[$z]->change > 0 && $watchlistObject->stockObjectArr[$z]->change < 0.05)
                                        echo "trending_flat";
                                    else
                                        echo "trending_down";
                echo "
                                    </i>
                                </div>
                            </div>
                            <div class='mdl-card__supporting-text' style='margin-top: 2%; margin-right: 20px;'>
                                <div style='display: flex; flex-direction: row; justify-content: space-around;'>
                                    <p style='font-size: large;'>
                                        $ {$watchlistObject->stockObjectArr[$z]->currPrice}</p>
                                    <p style='font-size: large;'>
                                        $ {$watchlistObject->stockObjectArr[$z]->change}</p>
                                    <p style='font-size: large;'>
                                        {$watchlistObject->stockObjectArr[$z]->changePercent}%
                                    </p>
                                    <a href='watchlists.php?deleteTicker={$watchlistObject->stockObjectArr[$z]->stockTicker}&stockID={$watchlistObject->stockObjectArr[$z]->stockID}&watchlistID={$watchlistObject->watchlistID}'>
                                        <i class='material-icons' style='justify-content: flex-end; float: right;'>remove_circle_outline
                                        </i>
                                    </a>
                                </div>
                            </div>
                            <div class='mdl-card__actions mdl-card--border'>
                                <a href='trade_buy.php?ticker={$watchlistObject->stockObjectArr[$z]->stockTicker}'>
                                    <button class='mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored'
                                            style='float: left; margin-left: 10%'>
                                        Buy
                                    </button>
                                </a>
                                <a href='trade_sell.php?ticker={$watchlistObject->stockObjectArr[$z]->stockTicker}'>
                                    <button class='mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent'
                                           style='float: right; margin-right: 10%'>
                                        Sell
                                    </button>
                                </a>
                            </div>
                        </div>
                ";
            }

            echo "</div>      
                  </div>
                  </section>";
        }
        ?>



    </main>
</div>


</body>
</html>
