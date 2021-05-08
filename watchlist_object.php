<?php
// class to store the properties of every watchlist: an id, name, and array of the stocks contained within it
class watchlist_object {
    public int $watchlistID;
    public string $watchlistName;
    public $stockObjectArr;
}