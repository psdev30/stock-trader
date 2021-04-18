<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <!--<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">-->
    <link rel="stylesheet" href="home.css" type="text/css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
</head>
<body>
<?php
session_start();
$searchPayload = json_decode($_SESSION['tickerInfo'], true);
print_r($searchPayload);
?>

<!-- Simple header with fixed tabs. -->
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header
            mdl-layout--fixed-tabs">
    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <!-- Title -->
            <span class="mdl-layout-title"><?php echo $searchPayload['companyName'] . ' ' . '(' . $searchPayload['symbol'] . ')'; ?></span>
        </div>
        <!-- Tabs -->
        <div class="mdl-layout__tab-bar mdl-js-ripple-effect">
            <a href="#companyInfo" class="mdl-layout__tab is-active">Company Info</a>
            <a href="#fundamentalIndicators" class="mdl-layout__tab">Fundamental Indicators</a>
            <a href="#technicalIndicators" class="mdl-layout__tab">Technical Indicators</a>
        </div>
    </header>
    <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">Title</span>
    </div>
    <main class="mdl-layout__content">
        <section class="mdl-layout__tab-panel is-active" id="companyInfo">
            <div class="page-content">zora</div>
        </section>
        <section class="mdl-layout__tab-panel" id="fundamentalIndicators">
            <div class="page-content">grace</div>
        </section>
        <section class="mdl-layout__tab-panel" id="technicalIndicators">
            <div class="page-content">mosley</div>
        </section>
    </main>
</div>

</body>
</html>




