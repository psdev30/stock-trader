<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trade Sell Order</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
</head>
<body>

<?php
require 'db_connect.php';
require 'environment.php';
require './vendor/autoload.php';
session_start();

$tradeInfoSell = $_SESSION['tradeInfoSell'];
$ticker = $tradeInfoSell['tickerSymbol'];
$numShares = $tradeInfoSell['numShares'];

$username = mysqli_real_escape_string($connection, $_SESSION['username']);

// get how much money they have to spend now
$queryGetCurrentAvailableFunds = "SELECT user_id, buying_power FROM user WHERE username='$username'";
$queryGetCurrentAvailableFundsRes = mysqli_query($connection, $queryGetCurrentAvailableFunds);
if(!$queryGetCurrentAvailableFundsRes)
    die('Sorry, total available funds could not be retrieved :(');
while($row = mysqli_fetch_row($queryGetCurrentAvailableFundsRes)) {
    $fk_user_id = intval($row[0]);
    $currentFundsAvailable = $row[1];
}

//get details of current position
$queryGetPositionDetails = "SELECT * FROM position WHERE symbol='$ticker' AND fk_user_id=$fk_user_id";
$queryGetPositionDetailsRes = mysqli_query($connection, $queryGetPositionDetails);
if(!$queryGetPositionDetailsRes || $queryGetPositionDetailsRes->num_rows == 0)
    die('Sorry, number of shares you own for this position could not be retrieved :(');
while($row = mysqli_fetch_assoc($queryGetPositionDetailsRes)) {
    $numSharesInPosition = $row['shares'];
    $avgSharePrice = $row['avg_share_price'];
    $totalCost = $row['total_cost'];
}


$iex = new \GuzzleHttp\Client();
try {
    //get current price of stock
    $currentPriceRequest = $iex->request('GET', $IEX_CLOUD_API_BASE_URL . 'stable/stock/' . $ticker . '/quote/latestPrice?token=' . $IEX_CLOUD_API_KEY);
    $currentPriceRes = json_decode($currentPriceRequest->getBody()->getContents(), true);
} catch(\GuzzleHttp\Exception\GuzzleException $e) {
    echo "<script>alert('Sorry, you entered an invalid ticker. Try entering a valid ticker symbol and placing another order.')</script>";
    header("Location:trade_sell.php");
}

// if they want to sell entire position, delete position entirely and update stock history with realized gain/loss since it was officially sold
if($numShares == $numSharesInPosition) {
    $netProceeds = floatval($currentPriceRes * $numShares);
    $costBasis = floatval($avgSharePrice * $numShares);
    $profitLoss = $netProceeds - $costBasis;

    $queryGetTickerFromStockHistory = "SELECT stock_history_id FROM stock_history WHERE symbol='$ticker' AND fk_user_id=fk_user_id";
    $queryGetTickerFromStockHistoryRes = mysqli_query($connection, $queryGetTickerFromStockHistory);
    if(!$queryGetTickerFromStockHistoryRes || $queryGetTickerFromStockHistoryRes->num_rows == 0)
        die('Sorry, information from stock history could not be retrieved :(');
    while($row = mysqli_fetch_row($queryGetTickerFromStockHistoryRes)) {
        $stockHistoryID = $row[0];
    }

    $queryUpdateStockHistory = "UPDATE stock_history SET profit_loss=$profitLoss WHERE stock_history_id=$stockHistoryID AND fk_user_id=$fk_user_id";
    $queryUpdateStockHistoryRes = mysqli_query($connection, $queryUpdateStockHistory);
    if(!queryUpdateStockHistoryRes)
        die('Sorry, stock history could not be updated :(');

    $queryDeletePosition = "DELETE FROM position WHERE symbol='$ticker'";
    $queryDeletePositionRes = mysqli_query($connection, $queryDeletePosition);
    if(!$queryDeletePositionRes)
        die('Sorry, position could not be deleted :(');

    // update realized portfolio value
    $queryGetCurrentRealizedPortfolioValue = "SELECT portfolio_value FROM user WHERE user_id=$fk_user_id";
    $queryGetCurrentRealizedPortfolioValueRes = mysqli_query($connection, $queryGetCurrentRealizedPortfolioValue);
    if(!$queryGetCurrentRealizedPortfolioValueRes || $queryGetCurrentRealizedPortfolioValueRes->num_rows == 0)
        die('Sorry, current realized portfolio value could not be retrieved :(');
    while($row = mysqli_fetch_row($queryGetCurrentRealizedPortfolioValueRes))
        $currRealizedPortfolioValue = $row[0];

    $newRealizedPortfolioValue = $currRealizedPortfolioValue + $profitLoss;
    $queryUpdateRealizedPortfolioValue = "UPDATE user SET portfolio_value=$newRealizedPortfolioValue WHERE user_id=$fk_user_id";
    $queryUpdateRealizedPortfolioValueRes = mysqli_query($connection, $queryUpdateRealizedPortfolioValue);
    if(!$queryUpdateRealizedPortfolioValueRes)
        die('Sorry, realized portfolio value could not be updated :(');

    header("Location:trade_sell_confirmation_success.php");
}

// if they only want to sell part of their position, update position instead of deleting it and also update stock history after doing the basic math to reflect realized gain/loss
else {
    $newNumShares = floatval($numSharesInPosition - $numShares);
    $newTotalCost = floatval($totalCost - ($currentPriceRes * $numShares));
    $newAvgSharePrice = $newTotalCost / $newNumShares;
    $netProceed = floatval($currentPriceRes * $numShares);
    $newProfitLoss = floatval(($currentPriceRes * $numShares) - ($avgSharePrice * $numShares));

    $queryUpdatePosition = "UPDATE position SET shares=$newNumShares, total_cost=$newTotalCost, avg_share_price=$newAvgSharePrice WHERE symbol='$ticker' AND fk_user_id=$fk_user_id";
    $queryUpdatePositionRes = mysqli_query($connection, $queryUpdatePosition);
    if(!$queryUpdatePositionRes)
        die('Sorry, position could not be updated :(');

    $queryUpdateStockHistoryPL = "UPDATE stock_history SET profit_loss=$newProfitLoss";
    $queryUpdateStockHistoryPLRes = mysqli_query($connection, $queryUpdateStockHistoryPL);
    if(!$queryUpdateStockHistoryPLRes)
        die('Sorry, stock history could not be updated :(');

    // update realized portfolio value
    $queryGetCurrRealizedPortfolioValue = "SELECT portfolio_value, buying_power FROM user WHERE user_id=$fk_user_id";
    $queryGetCurrRealizedPortfolioValueRes = mysqli_query($connection, $queryGetCurrRealizedPortfolioValue);
    if(!$queryGetCurrRealizedPortfolioValueRes || $queryGetCurrRealizedPortfolioValueRes->num_rows == 0)
        die('Sorry, current realized portfolio value could not be retrieved :(');
    while($row = mysqli_fetch_row($queryGetCurrRealizedPortfolioValueRes)) {
        $currRealizedPortfolioValue = $row[0];
        $currBuyingPower = $row[1];
    }


    $newRealizedPortfolioValue = $currRealizedPortfolioValue + $newProfitLoss;
    $newBuyingPower = $currBuyingPower + $netProceed;
    $queryUpdateUserTable = "UPDATE user SET portfolio_value=$newRealizedPortfolioValue, buying_power=$newBuyingPower WHERE user_id=$fk_user_id";
    $queryUpdateUserTableRes = mysqli_query($connection, $queryUpdateUserTable);
    if(!$queryUpdateUserTableRes)
        die('Sorry, realized portfolio value could not be updated :(');

    header("Location:trade_sell_confirmation_success.php");

}










?>


</body>
</html>