<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buy Stocks</title>
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

// when user attempts to buy specific stock from watchlists page
if($_GET && !isset($_GET['status'])) {
    $ticker = mysqli_real_escape_string($connection, $_GET['ticker']);
}

//handles redirect when invalid buy attempt is made and displays alert of mistake to user
if($_GET['status'] == 'failedBuy') {
    echo "<script>alert('Sorry, you entered an invalid ticker. Try entering a valid ticker symbol and placing another order.')</script>";
}

$username = mysqli_real_escape_string($connection, $_SESSION['username']);

//get the user ID and how much money they have available to spend before buying
$queryGetCurrentAvailableFunds = "SELECT user_id, buying_power FROM user WHERE username='$username'";
$queryGetCurrentAvailableFundsRes = mysqli_query($connection, $queryGetCurrentAvailableFunds);
if(!$queryGetCurrentAvailableFundsRes)
    die('Sorry, total available funds could not be retrieved :(');
while($row = mysqli_fetch_row($queryGetCurrentAvailableFundsRes)) {
    $userID = $row[0];
    $currentFundsAvailable = $row[1];
}



if($_POST) {
    $sharesEntered = mysqli_real_escape_string($connection, $_POST['numShares']);

    // assuming shares aren't negative, set to 1 if nothing was entered or push to session to be used in confirmation page
    if($sharesEntered  < 0) {
        echo "<script>alert('You entered a negative number of shares, try again.')</script>";
    }
    else {
        if(($sharesEntered == 0))
            $_POST['numShares'] = 1;
        $_SESSION['tradeInfo'] = $_POST;

        header("Location:trade_buy_confirmation.php");
    }
}


?>

<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <span class="mdl-layout-title">Stock Trader Buy Screen</span>
            <div class="mdl-layout-spacer"></div>
            <nav class="mdl-navigation mdl-layout--large-screen-only">
                <a class="mdl-navigation__link" href="home.php">Home</a>
                <a class="mdl-navigation__link" href="watchlists.php">Watchlists</a>
                <a class="mdl-navigation__link" href="trade_sell.php">Sell Stocks</a>
                <a class="mdl-navigation__link" href="account.php">Portfolio</a>
                <a class="mdl-navigation__link" href="login.php?loggedIn=FALSE">Logout</a>
            </nav>
        </div>
    </header>
    <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">Stock Trader Buy Screen</span>
        <nav class="mdl-navigation">
            <a class="mdl-navigation__link" href="home.php">Home</a>
            <a class="mdl-navigation__link" href="watchlists.php">Watchlists</a>
            <a class="mdl-navigation__link" href="trade_sell.php">Sell Stocks</a>
            <a class="mdl-navigation__link" href="account.php">Portfolio</a>
            <a class="mdl-navigation__link" href="login.php?loggedIn=FALSE">Logout</a>
        </nav>
    </div>
    <main class="mdl-layout__content">
        <div class="page-content">
            <h4 style="text-align: center">Buy Order <?php if(isset($ticker)) echo '(' . $ticker . ')' ?></h4>
            <div style="text-align: center">
                <form action="trade_buy.php" method="post">
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <label class="mdl-textfield__label" style="color: black;">Current Funds Available: <?php echo '$' . $currentFundsAvailable; ?> </label>
                    </div>
                    <br>
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <input class="mdl-textfield__input" type="text" id="tickerSymbol" name="tickerSymbol" value="<?php if(isset($ticker)) echo $ticker; ?>" required>
                        <label class="mdl-textfield__label" for="tickerSymbol">Ticker Symbol</label>
                    </div>
                    <br>
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <input class="mdl-textfield__input" type="text" id="numShares" name="numShares"">
                        <label class="mdl-textfield__label" for="numShares">Number of Shares (default is 1)</label>
                        <span class="mdl-textfield__error">Input is not a number!</span>
                    </div>
                    <br>
                    <input type=submit value="Submit Order" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">
                </form>
            </div>
        </div>
    </main>
</div>

</body>
</html>
