<?php
// class that holds a single stock's properties within a watchlist
class watchlist_stock_object {
    public int $stockID;
    public string $stockName;
    public string $stockTicker;
    public float $currPrice;
    public float $change;
    public float $changePercent;
}