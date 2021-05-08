<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Info</title>
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
require 'db_connect.php';
session_start();

// get the stock info from the home page search
$quote = json_decode($_SESSION['tickerInfo'], true);
$companyName = $_SESSION['quote']['companyName'];
$ticker = strtoupper($_GET['ticker']);

//associative arrays that will hold the general info and fundamental data
$generalStockInfo = array("headquarters" => "", "website" => "", "CEO" => "", "industry" => "", "tickerSymbol" => "", "marketCap" => "", "primaryExchange" => "");
$fundamentals = array("pe_ratio" => 0, "ttm_eps" => 0, "ttm_dividend_rate" => 0, "dividend_yield" => 0, "beta" => 0);

// get stock ID for next query
$queryGetStockID = "SELECT stock_id FROM stock WHERE stock_ticker='$ticker'";
$queryGetStockIDRes = mysqli_query($connection, $queryGetStockID);
if(!$queryGetStockIDRes)
    die('Sorry, the stock ID could not be retrieved :(');
while($row = mysqli_fetch_row($queryGetStockIDRes)) {
    $fk_stock_id = $row[0];
}

// query to get general info for stock and store in above declared array
$queryGetGeneralInfo = "SELECT * FROM stock_info WHERE fk_stock_id=$fk_stock_id";
$queryGetGeneralInfoRes = mysqli_query($connection, $queryGetGeneralInfo);
if(!$queryGetGeneralInfoRes)
    die('Sorry, general stock info could not be retrieved :(');
while($row = mysqli_fetch_assoc($queryGetGeneralInfoRes)) {
    $generalStockInfo['headquarters'] = $row['headquarters'];
    $generalStockInfo['website'] = $row['website'];
    $generalStockInfo['CEO'] = $row['ceo'];
    $generalStockInfo['industry'] = $row['industry'];
    $generalStockInfo['tickerSymbol'] = $row['ticker_symbol'];
    $generalStockInfo['marketCap'] = $row['market_cap'];
    $generalStockInfo['primaryExchange'] = $row['primary_exchange'];
}

/* Get fundamentals from DB and store in above created array */
$queryGetFundamentals = "SELECT * FROM fundamental WHERE fk_stock_id=$fk_stock_id";
$queryGetFundamentalsRes = mysqli_query($connection, $queryGetFundamentals);
if(!$queryGetFundamentalsRes)
    die('Sorry, fundamental indicators could not be retrieved :(');
while($row = mysqli_fetch_assoc($queryGetFundamentalsRes)) {
    $fundamentals['pe_ratio'] = $row['pe_ratio'];
    $fundamentals['ttm_eps'] = $row['ttm_eps'];
    $fundamentals['ttm_dividend_rate'] = $row['ttm_dividend_rate'];
    $fundamentals['dividend_yield'] = $row['dividend_yield'];
    $fundamentals['beta'] = $row['beta'];
}

?>

<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header
            mdl-layout--fixed-tabs">
    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <span class="mdl-layout-title"><?php echo $companyName . ' (' . $ticker . ')'; ?></span>
            <div class="mdl-layout-spacer"></div>
            <nav class="mdl-navigation mdl-layout--large-screen-only">
                <a class="mdl-navigation__link" href="home.php">Home</a>
                <a class="mdl-navigation__link" href="watchlists.php">Watchlists</a>
                <a class="mdl-navigation__link" href="trade_buy.php">Buy Stocks</a>
                <a class="mdl-navigation__link" href="trade_sell.php">Sell Stocks</a>
                <a class="mdl-navigation__link" href="account.php">Portfolio</a>
                <a class="mdl-navigation__link" href="login.php?loggedIn=FALSE">Logout</a>
            </nav>

            <a href="add_stock_to_watchlist.php?stock=<?php echo $companyName . '&ticker=' . $ticker?>">
                <button class="mdl-button mdl-js-button mdl-button--icon mdl-button--icon"
                        id="addWatchlist"
                        style="position: absolute; right: 0; margin-right: 10px; margin-bottom: 10px" >
                    <i class="material-icons">add</i>
                </button>
            </a>
            <div class="mdl-tooltip mdl-tooltip" for="addWatchlist">
                Add to Watchlist
            </div>

        </div>
        <div class="mdl-layout__tab-bar mdl-js-ripple-effect">
            <a href="#generalInfo" class="mdl-layout__tab is-active">General Info</a>
            <a href="#fundamentalIndicators" class="mdl-layout__tab">Fundamental Indicators</a>
        </div>
    </header>
    <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">Stock Info</span>
        <nav class="mdl-navigation">
            <a class="mdl-navigation__link" href="home.php">Home</a>
            <a class="mdl-navigation__link" href="watchlists.php">Watchlists</a>
            <a class="mdl-navigation__link" href="trade_buy.php">Buy Stocks</a>
            <a class="mdl-navigation__link" href="trade_sell.php">Sell Stocks</a>
            <a class="mdl-navigation__link" href="account.php">Portfolio</a>
            <a class="mdl-navigation__link" href="login.php?loggedIn=FALSE">Logout</a>
        </nav>
    </div>
    <main class="mdl-layout__content">
        <!-- General info table -->
        <section class="mdl-layout__tab-panel is-active" id="generalInfo">
            <div class="page-content">
                <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp" >
                    <thead>
                    <tr>
                        <th class="mdl-data-table__cell--non-numeric">Category</th>
                        <th class="mdl-data-table__cell--non-numeric">Value</th>
                    </tr>
                    </thead>

                    <tbody>
                    <tr>
                        <td class="mdl-data-table__cell--non-numeric"><b>Headquarters</b></td>
                        <td class="mdl-data-table__cell--non-numeric"><?php echo $generalStockInfo['headquarters']?></td>
                    </tr>
                    <tr>
                        <td class="mdl-data-table__cell--non-numeric"><b>Website</b></td>
                        <td class="mdl-data-table__cell--non-numeric"><?php echo $generalStockInfo['website']?></td>

                    </tr>
                    <tr>
                        <td class="mdl-data-table__cell--non-numeric"><b>CEO</b></td>
                        <td class="mdl-data-table__cell--non-numeric"><?php echo $generalStockInfo['CEO']?></td>

                    </tr>
                    <tr>
                        <td class="mdl-data-table__cell--non-numeric"><b>Industry</b></td>
                        <td class="mdl-data-table__cell--non-numeric"><?php echo $generalStockInfo['industry']?></td>

                    </tr>
                    <tr>
                        <td class="mdl-data-table__cell--non-numeric"><b>Ticker Symbol</b></td>
                        <td class="mdl-data-table__cell--non-numeric"><?php echo $generalStockInfo['tickerSymbol']?></td>
                    </tr>
                    <tr>
                        <td class="mdl-data-table__cell--non-numeric"><b>Market Cap</b></td>
                        <td class="mdl-data-table__cell--non-numeric"><?php echo '$' . $generalStockInfo['marketCap']?></td>

                    </tr>
                    <tr>
                        <td class="mdl-data-table__cell--non-numeric"><b>Primary Exchange</b></td>
                        <td class="mdl-data-table__cell--non-numeric"><?php echo $generalStockInfo['primaryExchange']?></td>

                    </tr>

                    </tbody>
                </table>
                <a href="add_stock_to_watchlist.php?stock=<?php echo $companyName; ?>&ticker=<?php echo $ticker; ?>">
                    <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent" style="display: block; margin: 10px auto auto;">
                        Add to Watchlist(s)
                    </button>
                </a>


            </div>
        </section>
        <!-- Fundamentals table -->
        <section class="mdl-layout__tab-panel" id="fundamentalIndicators">
            <div class="page-content">
                <div style="text-align: center">
                    <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp" >
                        <thead>
                        <tr>
                            <th class="mdl-data-table__cell--non-numeric">Indicator</th>
                            <th class="mdl-data-table__cell--non-numeric">Value</th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr>
                            <td class="mdl-data-table__cell--non-numeric"><b>PE Ratio</b></td>
                            <td class="mdl-data-table__cell--non-numeric"><?php echo $fundamentals['pe_ratio']?></td>
                        </tr>
                        <tr>
                            <td class="mdl-data-table__cell--non-numeric"><b>Trailing Twelve Month EPS</b></td>
                            <td class="mdl-data-table__cell--non-numeric"><?php echo '$' . $fundamentals['ttm_eps']?></td>

                        </tr>
                        <tr>
                            <td class="mdl-data-table__cell--non-numeric"><b>Trailing Twelve Month Dividend Rate</b></td>
                            <td class="mdl-data-table__cell--non-numeric"><?php echo $fundamentals['ttm_dividend_rate']?></td>

                        </tr>
                        <tr>
                            <td class="mdl-data-table__cell--non-numeric"><b>Dividend Yield</b></td>
                            <td class="mdl-data-table__cell--non-numeric"><?php echo $fundamentals['dividend_yield']?></td>

                        </tr>
                        <tr>
                            <td class="mdl-data-table__cell--non-numeric"><b>Beta</b></td>
                            <td class="mdl-data-table__cell--non-numeric"><?php echo $fundamentals['beta']?></td>
                        </tr>
                        </tbody>
                    </table>
                    <a href="add_stock_to_watchlist.php?stock=<?php echo $companyName; ?>&ticker=<?php echo $ticker; ?>">
                        <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent" style="display: block; margin: 10px auto auto;">
                            Add to Watchlist(s)
                        </button>
                    </a>
                </div>
            </div>
        </section>
    </main>
</div>

</body>
</html>




