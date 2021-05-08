<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Trader</title>
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

// this handles the redirect when an invalid sell attempt is made
if($_GET['error'] == 'invalid stock') {
    echo "<script>alert('Sorry, number of shares owned could not be verified because do not have an open position in this stock :(')</script>";
}

// when trying to sell from watchlist stock, capture the incoming ticker
if($_GET && !isset($_GET['error'])) {
    $ticker = $_GET['ticker'];
}

$userID = 0;

$username = mysqli_real_escape_string($connection, $_SESSION['username']);

// query to get user ID and current buying power to be changed
$queryGetCurrentAvailableFunds = "SELECT user_id, buying_power FROM user WHERE username='$username'";
$queryGetCurrentAvailableFundsRes = mysqli_query($connection, $queryGetCurrentAvailableFunds);
if(!$queryGetCurrentAvailableFundsRes)
    die('Sorry, total available funds could not be retrieved :(');
while($row = mysqli_fetch_row($queryGetCurrentAvailableFundsRes)) {
    $userID = $row[0];
    $currentFundsAvailable = $row[1];
}

// handles form submission for selling
if($_POST) {
    // capture ticker symbol and number of shares user wants to sell
    $tickerSymbol = mysqli_real_escape_string($connection, strtoupper($_POST['tickerSymbol']));
    $sharesEntered = mysqli_real_escape_string($connection, $_POST['numShares']);
    // get the number of shares currently owned, and redirect back to sell page with error if they don't own any shares
    $queryGetSharesInPosition = "SELECT shares FROM position WHERE symbol='$tickerSymbol' AND fk_user_id=$userID";
    $queryGetSharesInPositionRes = mysqli_query($connection, $queryGetSharesInPosition);
    if(!$queryGetSharesInPositionRes || $queryGetSharesInPositionRes->num_rows == 0) {
        header("Location:trade_sell.php?error=invalid stock");
    }
    // assuming request is valid, validate that share number is not over # owned or negative
    // if no # of shares were entered, assume it is 1
    // redirect to confirmation page for transaction to take place
    else {
        while($row = mysqli_fetch_row($queryGetSharesInPositionRes))
            $sharesInPosition = $row[0];

        if($sharesEntered  < 0 || $sharesEntered > $sharesInPosition) {
            echo "<script>alert('You entered an invalid number of shares (either negative or greater than the number you own. Check the portfolio tab to see how many you currently own.')</script>";
        }
        else {
            if(($_POST['numShares'] == 0))
                $_POST['numShares'] = 1;
            $_SESSION['tradeInfoSell'] = $_POST;

            header("Location:trade_sell_confirmation.php");
        }
    }
}


?>

<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <span class="mdl-layout-title">Stock Trader Sell Screen</span>
            <div class="mdl-layout-spacer"></div>
            <nav class="mdl-navigation mdl-layout--large-screen-only">
                <a class="mdl-navigation__link" href="home.php">Home</a>
                <a class="mdl-navigation__link" href="watchlists.php">Watchlists</a>
                <a class="mdl-navigation__link" href="trade_buy.php">Buy Stocks</a>
                <a class="mdl-navigation__link" href="account.php">Portfolio</a>
                <a class="mdl-navigation__link" href="login.php?loggedIn=FALSE">Logout</a>
            </nav>
        </div>
    </header>
    <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">Stock Trader Sell Screen</span>
        <nav class="mdl-navigation">
            <a class="mdl-navigation__link" href="home.php">Home</a>
            <a class="mdl-navigation__link" href="watchlists.php">Watchlists</a>
            <a class="mdl-navigation__link" href="trade_buy.php">Buy Stocks</a>
            <a class="mdl-navigation__link" href="account.php">Portfolio</a>
            <a class="mdl-navigation__link" href="login.php?loggedIn=FALSE">Logout</a>
        </nav>
    </div>
    <main class="mdl-layout__content">
        <div class="page-content">
            <h4 style="text-align: center">Sell Order <?php if(isset($ticker)) echo '(' . $ticker . ')' ?></h4>
            <div style="text-align: center">
                <form action="trade_sell.php" method="post">
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="margin-bottom: 10px;">
                        <label class="mdl-textfield__label" style="color: black;">Current Funds Available: <?php echo '$' . $currentFundsAvailable; ?> </label>
                    </div>
                    <br>
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <input class="mdl-textfield__input" type="text" id="tickerSymbol" name="tickerSymbol" value="<?php if(isset($ticker)) echo $ticker; ?>" required>
                        <label class="mdl-textfield__label" for="tickerSymbol">Ticker Symbol</label>
                    </div>

                    <br>

                    <div>
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 225px;">
                        <label class="mdl-textfield__label" style="color: black;">Do you want to sell all stocks?</label>
                    </div>

                    <input type="checkbox" id="yes" name="sellAll" onclick="getSharesOwned(this.checked)">
                    <label for="sellAll">Yes</label>
                    </div>
                    <br>
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <input class="mdl-textfield__input" type="text" id="numShares" name="numShares">
                        <label class="mdl-textfield__label" for="numShares" id="numSharesLabel">Number of Shares (default is 1)</label>
                        <span class="mdl-textfield__error" id="numSharesError">You must enter a number</span>
                    </div>
                    <br>
                    <input type=submit value="Submit Order" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent" style="margin-top: 10px;">
                </form>
            </form>
        </div>
    </main>
</div>

<script>
    // async AJAX function to get total # of shares owned from server without a page reload when user indicates that they want to sell all owned shares
    async function getSharesOwned(checked) {
        let ticker = document.getElementById("tickerSymbol").value;
        let xmlHttpRequest = new XMLHttpRequest();
        xmlHttpRequest.onreadystatechange = function () {
            if(this.readyState == 4 && this.status == 200) {
                let response = JSON.parse(this.responseText);
                if(response['numShares'] != false) {
                    document.getElementById("numSharesLabel").click();
                    document.getElementById("numShares").value = response['numShares'];
                }
            }
        }
        xmlHttpRequest.open('GET', 'trade_sell_ajax.php?ticker=' + ticker + '&option=' + checked, true);
        xmlHttpRequest.send();
    }
</script>

</body>

</html>