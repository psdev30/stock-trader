<!doctype html>

<html lang="en">

<head>
    <title>Trade Completed!</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
</head>

<body>

<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
        <header class='mdl-layout__header'>
            <div class='mdl-layout__header-row'>
                <span class='mdl-layout-title'>Stock Trader</span>
                <div class='mdl-layout-spacer'></div>
                <nav class='mdl-navigation mdl-layout--large-screen-only'>
                    <a class='mdl-navigation__link' href='home.php'>Home</a>
                    <a class='mdl-navigation__link' href='watchlists.php'>Watchlists</a>
                    <a class="mdl-navigation__link" href="trade_buy.php">Buy Stocks</a>
                    <a class="mdl-navigation__link" href="trade_sell.php">Sell Stocks</a>
                    <a class="mdl-navigation__link" href="account.php">Portfolio</a>
                    <a class='mdl-navigation__link' href='login.php?loggedIn=FALSE'>Logout</a>
                </nav>
            </div>
        </header>
        <div class='mdl-layout__drawer'>
            <span class='mdl-layout-title'>Stock Trader</span>
            <nav class='mdl-navigation'>
                <a class='mdl-navigation__link' href='home.php'>Home</a>
                <a class='mdl-navigation__link' href='watchlists.php'>Watchlists</a>
                <a class="mdl-navigation__link" href="trade_buy.php">Buy Stocks</a>
                <a class="mdl-navigation__link" href="trade_sell.php">Sell Stocks</a>
                <a class="mdl-navigation__link" href="account.php">Portfolio</a>
                <a class='mdl-navigation__link' href='login.php?loggedIn=FALSE'>Logout</a>
            </nav>
        </div>


        <main class="mdl-layout__content">
            <div class="page-content">
                <div style="text-align: center">
                    <h4>Your trade has been completed!</h4>
                    <h5>You can go back to the main page, watchlists, trade, view your account, or logout above</h5>
                </div>

            </div>
        </main>
    </div>

</body>

</html>
