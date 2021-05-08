<!doctype html>

<html lang="en">

<head>
    <title>Add Stock to Watchlist</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
    <style>
        .demo-list-control {
            width: 300px;
        }
    </style>
</head>

<body>

<?php
require 'db_connect.php';
session_start();

$companyName = $_GET['companyName'];
$ticker = strtoupper($_GET['ticker']);
$watchlists = array();

// get list of all watchlists
$queryGetWatchlists = "SELECT * FROM watchlist";
$queryGetWatchlistsRes = mysqli_query($connection, $queryGetWatchlists);
if (!$queryGetWatchlistsRes)
    die('Sorry, the query to retrieve all watchlists failed :(');

while ($row = mysqli_fetch_assoc($queryGetWatchlistsRes)) {
    array_push($watchlists, $row['watchlist_name']);
}

// get ID of stock user wants to add
$queryGetStockID = "SELECT stock_id FROM stock WHERE stock_ticker='$ticker'";
$queryGetStockIDRes = mysqli_query($connection, $queryGetStockID);
if (!$queryGetStockIDRes)
    die('Sorry, stock ID could not be retrieved :(');
while ($row = mysqli_fetch_row($queryGetStockIDRes)) {
    $stockID = $row[0];
}

if ($_POST) {
    $finalSelectedWatchlistsToAddIDs = array();
    // get the ID for the stock user is trying to add to watchlists
    $stockTicker = $_GET['ticker'];
    $queryGetStockID = "SELECT stock_id FROM stock WHERE stock_ticker='$ticker'";
    $queryGetStockIDRes = mysqli_query($connection, $queryGetStockID);
    if (!$queryGetStockIDRes)
        die('Sorry, stock ID could not be retrieved :(');
    while ($row = mysqli_fetch_row($queryGetStockIDRes)) {
        $stockID = $row[0];
    }

    // if user doesn't select any watchlists to add stock to
    if(empty($_POST['selectedWatchlistNames'])) {
        echo "<script>alert('You did not select any watchlists to add to!')</script>";
    }

    // get IDs of all watchlists user wants to add stock to
    $selectedWatchlistIDs = array();
    for ($i = 0; $i < count($_POST['selectedWatchlistNames']); $i++) {
        $selectedWatchlistName = $_POST['selectedWatchlistNames'][$i];
        $queryGetSelectedWatchlistIDs = "SELECT * FROM watchlist WHERE watchlist_name='$selectedWatchlistName'";
        $queryGetSelectedWatchlistIDsRes = mysqli_query($connection, $queryGetSelectedWatchlistIDs);
        if (!$queryGetSelectedWatchlistIDsRes)
            die('Sorry, the watchlist ID could not be retrieved :(');

        while ($row = mysqli_fetch_row($queryGetSelectedWatchlistIDsRes)) {
            array_push($selectedWatchlistIDs, $row[0]);
        }
    }

    // check if stock has already been added to any of the watchlists the user tries adding to again and filter these out to avoid duplicate entry
    for ($i = 0; $i < count($selectedWatchlistIDs); $i++) {
        $currentSelectedWatchlistID = $selectedWatchlistIDs[$i];
        $queryCheckIfStockInWatchlist = "SELECT * FROM stock_watchlist WHERE fk_stock_id=$stockID AND fk_watchlist_id=$currentSelectedWatchlistID";
        $queryCheckIfStockInWatchlistRes = mysqli_query($connection, $queryCheckIfStockInWatchlist);
        if ($queryCheckIfStockInWatchlistRes->num_rows != 0) {
            while ($row = mysqli_fetch_assoc($queryCheckIfStockInWatchlistRes)) {
                if (in_array($row['fk_watchlist_id'], $selectedWatchlistIDs) == FALSE) {
                    array_push($finalSelectedWatchlistsToAddIDs, $row['fk_watchlist_id']);
                }
            }
        }
        else {
            array_push($finalSelectedWatchlistsToAddIDs, $currentSelectedWatchlistID);
        }
    }

    // if stock has already been added to all selected watchlists, notify user
    if(count($finalSelectedWatchlistsToAddIDs) == 0) {
        echo "<script>alert('This stock has already been added to all the watchlists you selected! You can see them in the Watchlists linked tab above')</script>";
    }
    else {
        // otherwise, add to DB
        for($i = 0; $i < count($finalSelectedWatchlistsToAddIDs); $i++) {
            $finalSelectedWatchlistsToAddID = $finalSelectedWatchlistsToAddIDs[$i];
            $queryAddStockToWatchlist = "INSERT INTO stock_watchlist (fk_stock_id, fk_watchlist_id) VALUES ($stockID, $finalSelectedWatchlistsToAddID)";
            $queryAddStockToWatchlistRes = mysqli_query($connection, $queryAddStockToWatchlist);
            if (!$queryAddStockToWatchlistRes)
                die('Sorry, the stock could not be added to the watchlist :(');
        }

        // redirect to success page for confirmation
        header("Location:add_stock_to_watchlist_success.php");
    }
}
?>

<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <span class="mdl-layout-title">Add <?php echo $ticker; ?> to Watchlist</span>
            <div class="mdl-layout-spacer"></div>
            <nav class="mdl-navigation mdl-layout--large-screen-only">
                <a class="mdl-navigation__link" href="home.php">Home</a>
                <a class="mdl-navigation__link" href="watchlists.php">Watchlists</a>
                <a class="mdl-navigation__link" href="trade_buy.php">Buy Stocks</a>
                <a class="mdl-navigation__link" href="trade_sell.php">Sell Stocks</a>
                <a class="mdl-navigation__link" href="account.php">Portfolio</a>
                <a class="mdl-navigation__link" href="login.php?loggedIn=FALSE">Logout</a>
            </nav>
        </div>
    </header>
    <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">Add <?php echo $ticker; ?> to Watchlist</span>
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
        <div class="page-content">
            <div style="text-align: center">
                <h5>Add Stock to Watchlists</h5>
                <form action="add_stock_to_watchlist.php?<?php echo 'ticker=' . $ticker; ?>" method="post">
                    <ul class="demo-list-control mdl-list" style="margin: auto">
                        <?php
                        // displays all existing watchlists user could stock to in list format inside an HTML form
                        for ($i = 0; $i < count($watchlists); $i++) {
                            echo "
                        <li class='mdl-list__item'>
                            <span class='mdl-list__item-primary-content'>
                                $watchlists[$i]
                            </span>
                            <span class='mdl-list__item-secondary-action'>
                              <label class='mdl-switch mdl-js-switch mdl-js-ripple-effect' for='list-switch-$i'>
                                <input type='checkbox' id='list-switch-$i' name='selectedWatchlistNames[]' value='$watchlists[$i]' class='mdl-switch__input'>
                              </label>
                            </span>
                        </li>
                        ";
                        }
                        ?>
                    </ul>
                    <input type=submit name="submit" value="Add to Selected Watchlists" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent"
                           style="margin-top: 10px;">
                </form>


            </div>
        </div>
    </main>
</div>


</body>

</html>