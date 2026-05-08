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
$get_items_stmt = $dbh->prepare("SELECT items_class, SUM(qty) AS qty, item_name
                                    FROM general_requests
                                    LEFT JOIN item_tree ON general_requests.items_class = item_tree.item_id
                                    GROUP BY items_class, item_name;");
$get_items_stmt->execute([]);

$resulting_items = $get_items_stmt->fetchAll();

//initialises the empty list
$list = array();

//Creates the Items objects and adds them to the list
foreach ($resulting_items as $row){
    $item = new apiItem($row['items_class'], $row['item_name'], $row['qty']);

    $list[] = $item;
}

//Returns the list of items
echo (json_encode($list));
?>