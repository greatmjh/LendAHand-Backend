<?php
require_once(__DIR__."/../internal-src/constants.php");
require_once(__DIR__."/../internal-src/helper_functions.php");
require_once(__DIR__."/../internal-src/api_classes.php");

//Class for the ItemTreeItem object
class ItemTreeItem {
    public $itemID;
    public $itemName;
    public $itemChildren;
    function __construct($itemID, $itemName) {
        $this->itemID = $itemID;
        $this->itemName = $itemName;
        $this->itemChildren = array();
    }
    function findChildren($flatItemList) {
        foreach($flatItemList as $flatItem) {
            if ($flatItem->parentID == $this->itemID) {
                $newItem = new ItemTreeItem($flatItem->itemID, $flatItem->itemName);
                $newItem->findChildren($flatItemList);
                array_push($this->itemChildren, $newItem);
            }
        }
    }
}

//Class for the flat item object straight out of DB
class FlatItem {
    public $itemID;
    public $itemName;
    public $parentID;
    function __construct($itemID, $itemName, $parentID) {
        $this->itemID = $itemID;
        $this->itemName = $itemName;
        $this->parentID = $parentID;
    }
}

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

//Query to get a flat list of items
$queryResult = $dbh->query("SELECT item_id, item_name, parent_id FROM item_tree");
$flatItemList = array();
foreach($queryResult as $row) {
    array_push($flatItemList, new FlatItem($row['item_id'], $row['item_name'], $row['parent_id']));
}

//Construct the tree
$itemTree = array();
//Find root nodes (with no parent)
foreach($flatItemList as $flatItem) {
    if ($flatItem->parentID  == "") {
        $newItem = new ItemTreeItem($flatItem->itemID, $flatItem->itemName);
        $newItem->findChildren($flatItemList);
        array_push($itemTree, $newItem);
    }
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode($itemTree)

?>