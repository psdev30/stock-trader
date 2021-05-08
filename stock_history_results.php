<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock History Results</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
</head>
<body>

<?php
require './vendor/autoload.php';
require 'environment.php';
require 'db_connect.php';
session_start();

// handles form submission from account page
if($_POST) {
    $ticker = mysqli_real_escape_string($connection, strtoupper($_POST['historySearch']));
    $username = mysqli_real_escape_string($connection, $_SESSION['username']);

    // get user ID for next query
    $queryGetUserID = "SELECT user_id FROM user WHERE username='$username'";
    $queryGetUserIDRes = mysqli_query($connection, $queryGetUserID);
    if(!$queryGetUserIDRes)
        die('Sorry, total available funds could not be retrieved :(');
    while($row = mysqli_fetch_row($queryGetUserIDRes)) {
        $userID = $row[0];
    }

    // retrieve stock history for that query, and redirect back to account page if there is no history for the inputted stock
    $queryGetStockHistoryForTicker = "SELECT * FROM stock_history WHERE symbol='$ticker' AND fk_user_id=$userID";
    $queryGetStockHistoryForTickerRes = mysqli_query($connection, $queryGetStockHistoryForTicker);
    if(!$queryGetStockHistoryForTickerRes || $queryGetStockHistoryForTickerRes->num_rows == 0) {
        header("Location:account.php?error=invalid stock");
    }
    else {
        // iterates through SQL response row and captures properties of selected stock history
        while($row = mysqli_fetch_assoc($queryGetStockHistoryForTickerRes)) {
            $sharesTraded = $row['shares_traded'];
            $profitLoss = $row['profit_loss'];
            $totalInvestment = $row['total_investment'];
        }
    }
}

?>

<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header
            mdl-layout--fixed-tabs">
    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <span class="mdl-layout-title">Stock Trader History Results</span>
            <div class="mdl-layout-spacer"></div>

            <nav class="mdl-navigation mdl-layout--large-screen-only">
                <nav class="mdl-navigation mdl-layout--large-screen-only">
                    <a class="mdl-navigation__link" href="home.php">Home</a>
                    <a class="mdl-navigation__link" href="watchlists.php">Watchlists</a>
                    <a class="mdl-navigation__link" href="trade_buy.php">Buy Stocks</a>
                    <a class="mdl-navigation__link" href="trade_sell.php">Sell Stocks</a>
                    <a class="mdl-navigation__link" href="login.php?loggedIn=FALSE">Logout</a>
                </nav>
            </nav>
        </div>
        <div class="mdl-layout__tab-bar mdl-js-ripple-effect">
            <a href="#fixed-tab-1" class="mdl-layout__tab is-active">Stock History for <b><?php echo $ticker; ?></b></a>
        </div>
    </header>
    <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">Stock Trader History Results</span>
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
                <div style="text-align: center; margin: auto">
                    <table class="mdl-data-table mdl-js-data-table mdl-data-table mdl-shadow--2dp" style="margin: 10px auto;">
                        <thead>
                        <tr>
                            <th class="mdl-data-table__cell--non-numeric">Ticker Symbol</th>
                            <th>Shares Traded</th>
                            <th>Realized Profit/Loss</th>
                            <th>Total Investment</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="mdl-data-table__cell--non-numeric"><?php echo $ticker; ?></td>
                            <td><?php echo $sharesTraded; ?></td>
                            <td><?php echo  '$' . $profitLoss; ?></td>
                            <td><?php echo '$' . $totalInvestment; ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>



    </main>
</div>



</body>

</html>
