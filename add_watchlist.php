<!doctype html>

<html lang="en">

<head>
    <title>Add Topic</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
    <link rel="stylesheet" href="home.css" type="text/css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
</head>

<body>

<?php
require 'db_connect.php';
session_start();
$username = $_SESSION['username'];
$usernameSanitized = mysqli_real_escape_string($connection, $_SESSION['username']);

/*
 * Handle form submission for new watchlist
 */
if ($_POST) {
    // Sanitize user input
    $watchlistName = mysqli_real_escape_string($connection, $_POST['watchlistName']);

    $queryGetUsernameId = "SELECT user_id FROM user WHERE username = '$usernameSanitized'";
    $queryGetUsernameIdRes = mysqli_query($connection, $queryGetUsernameId);
    if (!$queryGetUsernameIdRes)
        die("Query to retrieve username id failed :(");

    while ($row = mysqli_fetch_row($queryGetUsernameIdRes))
        $usernameId = $row[0];

    // simply uses insert statement to add watchlist to DB
    $queryAddWatchlistToDB = "INSERT INTO watchlist (watchlist_name, fk_user_id) VALUES ('$watchlistName', $usernameId)";
    $queryAddWatchlistToDBRes = mysqli_query($connection, $queryAddWatchlistToDB);
    if(!$queryAddWatchlistToDBRes)
        die('Sorry, query to add watchlist to database failed :(');
    else
        header("Location:add_watchlist_success.php");

}
?>

<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <header class='mdl-layout__header'>
        <div class='mdl-layout__header-row'>
            <span class='mdl-layout-title'>Stock Trader</span>
            <div class='mdl-layout-spacer'></div>
            <nav class='mdl-navigation mdl-layout--large-screen-only'>
                <a class='mdl-navigation__link' href='home.php'>Home</a>
                <a class="mdl-navigation__link" href="account.php">Account</a>
                <a class='mdl-navigation__link' href='login.php?loggedIn=FALSE'>Logout</a>
            </nav>
        </div>
    </header>
    <div class='mdl-layout__drawer'>
        <span class='mdl-layout-title'>Stock Trader</span>
        <nav class='mdl-navigation'>
            <a class='mdl-navigation__link' href='home.php'>Home</a>
            <a class="mdl-navigation__link" href="account.php">Account</a>
            <a class='mdl-navigation__link' href='login.php?loggedIn=FALSE'>Logout</a>
        </nav>
    </div>

    <main class="mdl-layout__content">
        <div class="page-content">
            <div style='margin-left: 1%;'>

                <form action="add_watchlist.php" method="post">

                    <h5>Username: <?php echo $_SESSION['username'] ?></h5>

                    <h5>Watchlist Name: </h5>

                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="margin-right: 10px">
                        <input class="mdl-textfield__input" type="text" id="watchlistName" name="watchlistName">
                        <label class="mdl-textfield__label" for="watchlistName">Watchlist Name</label>
                    </div>

                    <button class='mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent'
                            type="submit" style='margin-top: -1%; margin-right: 1%;'>Add Watchlist
                    </button>
                    <a href='watchlists.php'>
                        <button class='mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored'
                                type="button" style='margin-top: -1%'>Cancel
                        </button>
                    </a>
                </form>



            </div>
        </div>
    </main>
</div>

</body>

</html>
