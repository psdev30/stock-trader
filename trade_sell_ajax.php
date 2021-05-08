<?php
require 'db_connect.php';
session_start();

$username = mysqli_real_escape_string($connection, $_SESSION['username']);

$queryGetCurrentAvailableFunds = "SELECT user_id FROM user WHERE username='$username'";
$queryGetCurrentAvailableFundsRes = mysqli_query($connection, $queryGetCurrentAvailableFunds);
if(!$queryGetCurrentAvailableFundsRes)
    die('Sorry, total available funds could not be retrieved :(');
while($row = mysqli_fetch_row($queryGetCurrentAvailableFundsRes)) {
    $userID = $row[0];
}

//gets info from ajax request
$ticker = $_REQUEST['ticker'];
$option = $_REQUEST['option'];
//assuming there is a valid stock and the checkbox is true, get the number of shares owned for the position and return it back to the trade_sell.php
if(isset($ticker) && isset($option) && $option == true) {
    $queryGetSharesOwned = "SELECT shares FROM position WHERE symbol = '$ticker' AND fk_user_id = $userID";
    $queryGetSharesOwnedRes = mysqli_query($connection, $queryGetSharesOwned);
    if(!$queryGetSharesOwnedRes || $queryGetSharesOwnedRes->num_rows == 0) {
        $response = json_encode(array("numShares" => false));
        echo $response;
    }
    else {
        while($row = mysqli_fetch_row($queryGetSharesOwnedRes))
            $sharesOwned = $row[0];

        $response = array("numShares" => $sharesOwned);
        echo json_encode($response);
    }


}

?>