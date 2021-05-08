<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trade Confirmation</title>
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

$username = mysqli_real_escape_string($connection, $_SESSION['username']);

/*get user ID and how much money they have to spend before buying */
$queryGetCurrentAvailableFunds = "SELECT user_id, buying_power FROM user WHERE username='$username'";
$queryGetCurrentAvailableFundsRes = mysqli_query($connection, $queryGetCurrentAvailableFunds);
if(!$queryGetCurrentAvailableFundsRes)
    die('Sorry, total available funds could not be retrieved :(');
while($row = mysqli_fetch_row($queryGetCurrentAvailableFundsRes)) {
    $fk_user_id = intval($row[0]);
    $currentFundsAvailable = $row[1];
}

// get ticker and number of shares to be bought
$ticker = $_SESSION['tradeInfo']['tickerSymbol'];
$numShares = $_SESSION['tradeInfo']['numShares'];

$iex = new \GuzzleHttp\Client();
try {
    // get current price of stock
    $currentPriceRequest = $iex->request('GET', $IEX_CLOUD_API_BASE_URL . 'stable/stock/' . $ticker . '/quote/latestPrice?token=' . $IEX_CLOUD_API_KEY);
    $currentPriceRes = json_decode($currentPriceRequest->getBody()->getContents(), true);
} catch(\GuzzleHttp\Exception\GuzzleException $e) {
    header("Location:trade_buy.php?status=failedBuy");
}
// make sure total cost of purchase is not over what they have
$totalCost = $currentPriceRes * $numShares;
if($totalCost > $currentFundsAvailable) {
    echo "<script>alert('Sorry, this purchase would exceed your currently available funds. Try entering a valid number and placing another order.')</script>";
    header("Location:trade_buy.php");
}
else {
    // first check if stock is already in stock history (so they've traded it before)
    $queryCheckIfTickerInStockHistory = "SELECT * FROM stock_history WHERE symbol='$ticker' AND fk_user_id=fk_user_id";
    $queryCheckIfTickerInStockHistoryRes = mysqli_query($connection, $queryCheckIfTickerInStockHistory);
    if($queryCheckIfTickerInStockHistoryRes->num_rows == 0) {
        // if they never have, then add it and a new position (since they have never traded it there cannot possibly be an existing position
        $queryAddTickerToStockHistory = "INSERT INTO stock_history (symbol, shares_traded, profit_loss, total_investment, fk_user_id) VALUES 
                                            ('$ticker', $numShares, 0, $totalCost, $fk_user_id)";
        $queryAddTickerToStockHistoryRes = mysqli_query($connection, $queryAddTickerToStockHistory);
        if(!$queryAddTickerToStockHistoryRes)
            die('Sorry, the stock could not be added to your history :(');

        $queryGetStockHistoryID = "SELECT stock_history_id FROM stock_history WHERE symbol='$ticker'";
        $queryGetStockHistoryIDRes = mysqli_query($connection, $queryGetStockHistoryID);
        if($queryGetStockHistoryIDRes->num_rows == 0) {
            die('Sorry, something went wrong. Try again!');
        }
        while($row = mysqli_fetch_row($queryGetStockHistoryIDRes))
            $stockHistoryID = $row[0];

        $queryAddToPositions = "INSERT INTO position (symbol, shares, avg_share_price, total_cost, fk_user_id, fk_stock_history_id) VALUES 
                               ('$ticker', $numShares, $currentPriceRes, $totalCost, $fk_user_id, $stockHistoryID)";
        $queryAddToPositionsRes = mysqli_query($connection, $queryAddToPositions);
        if(!$queryAddToPositionsRes)
            die('Sorry, your new position was unable to be opened :(');

        // get the existing buying power they have and subtract how much they just spent from it
        $queryGetCurrentBuyingPower = "SELECT buying_power FROM user WHERE user_id=$fk_user_id";
        $queryGetCurrentBuyingPowerRes = mysqli_query($connection, $queryGetCurrentBuyingPower);
        if(!$queryGetCurrentBuyingPowerRes || $queryGetCurrentBuyingPowerRes->num_rows == 0)
            die('Sorry, the current buying power could not be retrieved :(');
        while($row = mysqli_fetch_row($queryGetCurrentBuyingPowerRes))
            $currentBuyingPower = $row[0];
        $updatedBuyingPower = $currentBuyingPower - $totalCost;

        $queryReduceBuyingPower = "UPDATE user SET buying_power=$updatedBuyingPower WHERE user_id=$fk_user_id";
        echo "<br>";
        $queryReduceBuyingPowerRes = mysqli_query($connection, $queryReduceBuyingPower);
        if(!$queryReduceBuyingPowerRes)
            die('Sorry, your buying power could not be reduced :(');
    }
    else {
        // if they have traded this stock before, get existing info
        while($row = mysqli_fetch_assoc($queryCheckIfTickerInStockHistoryRes)) {
            $stockHistoryID = $row['stock_history_id'];
            $sharesTraded = $row['shares_traded'];
            $profitLoss = $row['profit_loss'];
            $totalInvestment = $row['total_investment'];
        }
        // calculate new values to be updated in stock_history table
        $newSharesTraded = $sharesTraded + $numShares;
        $newTotalInvestment = $totalInvestment + $totalCost;
        $queryUpdateStockHistory = "UPDATE stock_history SET shares_traded=$newSharesTraded, total_investment=$newTotalInvestment
                                    WHERE stock_history_id=$stockHistoryID AND fk_user_id=$fk_user_id";
        $queryUpdateStockHistoryRes = mysqli_query($connection, $queryUpdateStockHistory);
        if(!$queryUpdateStockHistoryRes)
            die('Sorry, stock history could not be updated :(');

        // check if they already own this stock currently, and add new position if they don't
        $queryCheckIfCurrentPosition = "SELECT * FROM position WHERE symbol='$ticker'";
        $queryCheckIfCurrentPositionRes = mysqli_query($connection, $queryCheckIfCurrentPosition);
        if($queryCheckIfCurrentPositionRes->num_rows == 0) {
            $queryAddPosition = "INSERT INTO position (symbol, shares, avg_share_price, total_cost, fk_user_id, fk_stock_history_id) VALUES 
                                    ('$ticker', $numShares, $currentPriceRes, $totalCost, $fk_user_id, $stockHistoryID)";
            $queryAddPositionRes = mysqli_query($connection, $queryAddPosition);
            if(!$queryAddPositionRes)
                die('Sorry, your new position could not be added :(');
        }
        // if they own it right now, get existing position info and do the math to calculate how the share count, total cost, avg share price change and persist to DB
        else {
            $queryGetCurrentPosition = "SELECT * FROM position WHERE symbol='$ticker' AND fk_user_id=$fk_user_id";
            $queryGetCurrentPositionRes = mysqli_query($connection, $queryGetCurrentPosition);
            if($queryGetCurrentPositionRes->num_rows == 0)
                die('Sorry, something went wrong :(');
            while($row = mysqli_fetch_assoc($queryGetCurrentPositionRes)) {
                $currentShares = $row['shares'];
                $currentAvgSharePrice = $row['avg_share_price'];
                $currentTotalCost = $row['total_cost'];
            }
            $updatedShares = $currentShares + $numShares;
            $updatedTotalCost = $currentTotalCost + $totalCost;
            $updatedAverageSharePrice = ($updatedTotalCost) / ($updatedShares);
            $queryUpdatePosition = "UPDATE position SET shares = $updatedShares, avg_share_price = $updatedAverageSharePrice, total_cost = $updatedTotalCost WHERE symbol='$ticker' AND fk_user_id=fk_user_id";
            $queryUpdatePositionRes = mysqli_query($connection, $queryUpdatePosition);
            if(!$queryUpdatePositionRes)
                die('Sorry, positions could not be updated :(');
        }

        //update their buying power based on the successful purchase
        $queryGetCurrentBuyingPower = "SELECT buying_power FROM user WHERE user_id=$fk_user_id";
        $queryGetCurrentBuyingPowerRes = mysqli_query($connection, $queryGetCurrentBuyingPower);
        if(!$queryGetCurrentBuyingPowerRes || $queryGetCurrentBuyingPowerRes->num_rows == 0)
            die('Sorry, the current buying power could not be retrieved :(');
        while($row = mysqli_fetch_row($queryGetCurrentBuyingPowerRes))
            $currentBuyingPower = $row[0];
        $updatedBuyingPower = $currentBuyingPower - $totalCost;
        $queryReduceBuyingPower = "UPDATE user SET buying_power = $updatedBuyingPower WHERE user_id=$fk_user_id";
        $queryReduceBuyingPowerRes = mysqli_query($connection, $queryReduceBuyingPower);
        if(!$queryReduceBuyingPowerRes)
            die('Sorry, your buying power could not be reduced :(');
    }

    header("Location:trade_buy_confirmation_success.php");
}


?>


</body>
</html>