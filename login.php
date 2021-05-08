<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Stock Trader Login</title>
    <meta name="author" content="Prateek Jukalkar">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
</head>

<body>

<?php
require 'db_connect.php';
session_start();

// if being redirected to logout from another page, update session variables
if ($_GET['loggedIn']) {
    $_SESSION['username'] = null;
    $_SESSION['password'] = null;
    $_SESSION['loggedIn'] = FALSE;
}

?>

<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <header class='mdl-layout__header'>
        <div class='mdl-layout__header-row'>
            <span class='mdl-layout-title'>Stock Trader</span>
            <div class='mdl-layout-spacer'></div>
            <nav class='mdl-navigation mdl-layout--large-screen-only'>
            </nav>
        </div>
    </header>
    <div class='mdl-layout__drawer'>
        <span class='mdl-layout-title'>Stock Trader</span>
        <nav class='mdl-navigation'>
        </nav>
    </div>

    <main class="mdl-layout__content">
        <div class="page-content">
            <div style='text-align: center; margin-top: 5%'>
                <h5>Please login</h5>
                <!-- sticky login form with username/password fields -->
                <form action="login.php" method="post">
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label"
                         style="margin-right: 15px">
                        <input class="mdl-textfield__input" type="text" id="username" name="username"
                            <?php if (isset($_POST['username'])) echo "value='" . htmlspecialchars($_POST['username'], ENT_QUOTES) . "'"; ?>>
                        <label class="mdl-textfield__label" for="username">Username</label>
                    </div>

                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label"
                         style="margin-right: 15px;">
                        <input class="mdl-textfield__input" type="password" id="password" name="password"
                            <?php if (isset($_POST['password'])) echo "value='" . htmlspecialchars($_POST['password'], ENT_QUOTES) . "'"; ?>>
                        <label class="mdl-textfield__label" for="password">Password</label>
                    </div>

                    <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent"
                            type="submit">
                        Login
                    </button>
                </form>
            </div>

            <?php
            // handle login form submission
            if ($_POST) {
                // extract username + password and sanitize
                $username = $_POST['username'];
                $usernameSanitized = mysqli_real_escape_string($connection, $_POST['username']);
                $password = mysqli_real_escape_string($connection, $_POST['password']);
                $success = TRUE;

                // check if login credentials are valid
                $queryVerifyLogin = "SELECT * FROM user WHERE username = '$usernameSanitized' AND pwd = '$password'";
                $queryVerifyLoginRes = mysqli_query($connection, $queryVerifyLogin);
                if (!$queryVerifyLoginRes)
                    die("Query to verify login information failed :(");


                // if credentials are invalid, update session variable + alert user
                if ($queryVerifyLoginRes->num_rows == 0) {
                    $success = FALSE;
                    $_SESSION['loggedIn'] = FALSE;
                    echo "<div style='text-align: center'><h6 style='color: #ff0000'>Please enter valid login credentials</h6></div>";
                }

                // if credentials are valid, update login session vars and redirect to forum home page
                if ($success) {
                    $_SESSION['username'] = $username;
                    $_SESSION['password'] = $password;
                    $_SESSION['loggedIn'] = TRUE;
                    header("Location:home.php");
                }
            }
            ?>

        </div>
    </main>
</div>

</body>

</html>



