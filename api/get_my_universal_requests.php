<?php
require_once(__DIR__."/../internal-src/constants.php");
require_once(__DIR__."/../internal-src/helper_functions.php");
require_once(__DIR__."/../internal-src/api_classes.php");

//Connect to DB and make sure its available
$dbh = new PDO(DB_INFO);

if (is_null($dbh)) {
    exit_internal_error("DB failed.");
}

$json_received = json_decode(file_get_contents('php://input'));

//Data validation
if (is_null($json_received)) {
    exit_bad_input("Failed to parse JSON");
}

if (is_null($json_received->{'sessionKey'})) {
    exit_bad_input("Missing fields in JSON");
}

//Get associated user ID to check if it's a valid session token
$userID = sessionKeyToUser($dbh, $json_received->{'sessionKey'});
if (is_null($userID)) {
    exit_bad_session_token();
}

//Selects all the universal requests belonging to one user (donee)
$get_items_stmt = $dbh->prepare("SELECT g.items_class, g.qty, i.item_name FROM GENERAL_REQUESTS as g
LEFT JOIN ITEM_TREE as i ON g.items_class = i.item_id WHERE g.donee = :userID");
$get_items_stmt->execute(["userID" => $userID]);

$resulting_items = $get_items_stmt->fetchAll();

//initialises the empty list
$list = array();

//Creates the Items objects and adds them to the list
foreach ($resulting_items as $row){
    $item = new Item();
    $item->itemId = $row['items_class'];
    $item->itemTitle = $row['item_name'];
    $item->quantity = $row['qty'];

    $list[] = $item;
}

//Returns the list of items
echo (json_encode($list));
?>