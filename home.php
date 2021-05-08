<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Trader Home</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
    <link rel="stylesheet" href="home.css" type="text/css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
</head>
<body>

<?php
require 'environment.php';
require './vendor/autoload.php';
require 'db_connect.php';
session_start();

if(!isset($_SESSION['username'])) {
    die('Sorry, you are not logged in and are thus unauthorized to view this page :(');
}

//when there is an error, page reloads and alerts user of mistake
if($_GET['error'] == 'invalid stock') {
    echo "<script>alert('Sorry, you entered an invalid ticker. Try again!')</script>";
}


$iex = new \GuzzleHttp\Client();

/*
 * when user submits form
 */
if ($_POST) {
    $inputTicker = mysqli_real_escape_string($connection, strtoupper($_POST['tickerSearch']));

    // first check if stock is already in DB (if yes, it must be a valid ticker)
    $queryCheckIfStockExists = "SELECT * FROM stock WHERE stock_ticker='$inputTicker'";
    $queryCheckIfStockExistsRes = mysqli_query($connection, $queryCheckIfStockExists);
    if($queryCheckIfStockExistsRes->num_rows == 0) {
        /*
         * if stock is not in DB, make API call
         * if API call fails, this means the ticker entered is invalid
         * if it succeeds, then add all info about stock so it will be in DB the next time it is called again
         */
        try {
            $quoteRequest = $iex->request('GET', $IEX_CLOUD_API_BASE_URL . '/stable/stock/' . $inputTicker . '/quote?token=' . $IEX_CLOUD_API_KEY);
            $quoteResp = json_decode($quoteRequest->getBody()->getContents(), true);
            $_SESSION['quote'] = $quoteResp;
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            header("Location:home.php?error=invalid stock");
        }

        // get data from company & keyStats IEX endpoints
        try {
            $companyRequest = $iex->request('GET', $IEX_CLOUD_API_BASE_URL . '/stable/stock/' . $inputTicker . '/company?token=' . $IEX_CLOUD_API_KEY);
            $companyResp = json_decode($companyRequest->getBody()->getContents(), true);
            $keyStatsRequest = $iex->request('GET', $IEX_CLOUD_API_BASE_URL . '/stable/stock/' . $inputTicker . '/stats?token=' . $IEX_CLOUD_API_KEY);
            $keyStatsResp = json_decode($keyStatsRequest->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            echo "<script>alert('Sorry, you entered an invalid ticker. Try again!')</script>";
        }

        //vars for stock info table
        $companyName = $companyResp['companyName'];
        $headquarters = $companyResp['address'] . ' ' . $companyResp['city'] . ', ' . $companyResp['state'];
        $website = $companyResp['website'];
        $ceo = $companyResp['CEO'];
        $industry = $companyResp['industry'];
        $tickerSymbol = $companyResp['symbol'];
        $marketCap = $keyStatsResp['marketcap'];
        $primaryExchange = $companyResp['exchange'];

        //vars for fundamental indicators table
        $peRatio = $keyStatsResp['peRatio'];
        $ttmEPS = $keyStatsResp['ttmEPS'];
        $ttmDividendRate = $keyStatsResp['ttmDividendRate'];
        $dividendYield = $keyStatsResp['dividendYield'];
        $beta = $keyStatsResp['beta'];

        // add stock to DB
        $queryAddStock = "INSERT INTO stock (stock_name, stock_ticker) VALUES ('$companyName', '$inputTicker')";
        $queryAddStockRes = mysqli_query($connection, $queryAddStock);
        if(!$queryAddStockRes)
            die('Sorry, stock could not be added :(');
            // get stock's ID for following queries
            $queryGetStockID = "SELECT stock_id FROM stock WHERE stock_ticker='$tickerSymbol'";
            $queryGetStockIDRes = mysqli_query($connection, $queryGetStockID);
            if(!$queryGetStockIDRes)
                die('Sorry, the stock ID could not be retrieved :(');
            while($row = mysqli_fetch_row($queryGetStockIDRes))
                $fk_stock_id = $row[0];

            // add data to stock_info table
            $queryAddStockInfo = "INSERT INTO stock_info (headquarters, website, ceo, industry, ticker_symbol, market_cap, primary_exchange, fk_stock_id)
                VALUES ('$headquarters', '$website', '$ceo', '$industry', '$tickerSymbol', '$marketCap', '$primaryExchange', $fk_stock_id)";
            $queryAddStockInfoRes = mysqli_query($connection, $queryAddStockInfo);
            if(!$queryAddStockInfoRes)
                die('Sorry, stock info could not be added :(');

            // add data to fundamentals table
            $queryAddFundamentalIndicators = "INSERT INTO fundamental (pe_ratio, ttm_eps, ttm_dividend_rate, dividend_yield, beta, fk_stock_id) VALUES
                ($peRatio, $ttmEPS, $ttmDividendRate, $dividendYield, $beta, $fk_stock_id)";
            $queryAddFundamentalIndicatorsRes = mysqli_query($connection, $queryAddFundamentalIndicators);
            if(!$queryAddFundamentalIndicatorsRes)
                die('Sorry, fundamental indicators could not be added :(');

        // if we reach this point, then everything succeeded and we can safely redirect to stock info page
        header("Location:stock_info.php?ticker=$tickerSymbol");
    }
    else {
        try {
            // if stock is in DB, just get quote with current price info
            $quoteRequest = $iex->request('GET', $IEX_CLOUD_API_BASE_URL . '/stable/stock/' . $inputTicker . '/quote?token=' . $IEX_CLOUD_API_KEY);
            $quoteResp = json_decode($quoteRequest->getBody()->getContents(), true);
            $_SESSION['quote'] = $quoteResp;
            header("Location:stock_info.php?ticker=$inputTicker");
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            echo "<script>alert('Sorry, you entered an invalid ticker. Try again!')</script>";
        }
    }
}

?>
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <!-- Title -->
            <span class="mdl-layout-title">Stock Trader Home</span>
            <div class="mdl-layout-spacer"></div>
            <nav class="mdl-navigation mdl-layout--large-screen-only">
                <a class="mdl-navigation__link" href="watchlists.php">Watchlists</a>
                <a class="mdl-navigation__link" href="trade_buy.php">Buy Stocks</a>
                <a class="mdl-navigation__link" href="trade_sell.php">Sell Stocks</a>
                <a class="mdl-navigation__link" href="account.php">Portfolio</a>
                <a class="mdl-navigation__link" href="login.php?loggedIn=FALSE">Logout</a>
            </nav>
        </div>
    </header>
    <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">Stock Trader</span>
        <nav class="mdl-navigation">
            <a class="mdl-navigation__link" href="watchlists.php">Watchlists</a>
            <a class="mdl-navigation__link" href="trade_buy.php">Buy Stocks</a>
            <a class="mdl-navigation__link" href="trade_sell.php">Sell Stocks</a>
            <a class="mdl-navigation__link" href="account.php">Portfolio</a>
            <a class="mdl-navigation__link" href="login.php?loggedIn=FALSE">Logout</a>
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