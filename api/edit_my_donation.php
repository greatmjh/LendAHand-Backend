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

if (is_null($json_received->{'sessionKey'}) || is_null($json_received->{'itemId'}) || is_null($json_received->{'itemDesc'}) || is_null($json_received->{'newQty'})) {
    exit_bad_input("Missing fields in JSON");
}

//Get associated user ID
$userID = sessionKeyToUser($dbh, $json_received->{'sessionKey'});
if (is_null($userID)) {
    exit_bad_session_token();
}



//Checking if the input integer is negative
if ($json_received->{'newQty'} < 0){
    exit_bad_input("Invalid quantity");
}

//Checks if it's a new item or not
if (is_null($json_received->{'offerID'})){

    //checks if the itemId is a valid uuid
    if (!isValidUuid($json_received->{'itemId'})){
        exit_bad_input("Invalid item class uuid");
    }
    
    //New item
    //check if the item class exists (i.e. if itemId exists in the item_tree table)
    $check_item_class_stmt = $dbh->prepare("SELECT * FROM ITEM_TREE WHERE item_id = :itemId");
    $check_item_class = $check_item_class_stmt->execute(["itemId" => $json_received->{'itemId'}]);
    $resulting_check_item_class = $check_item_class_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$resulting_check_item_class){
        exit_bad_input("No such item class exists");
    }

    $create_item_stmt = $dbh->prepare("INSERT INTO ITEMS_DONOR(donor, item_class, item_name, qty) VALUES(:userID, :itemId, :itemDesc, :qty)");
    $create_item = $create_item_stmt->execute(["userID" => $userID,
                                                "itemId" => $json_received->{'itemId'},
                                                "itemDesc" => $json_received->{'itemDesc'},
                                                "qty" => $json_received->{"newQty"}]);
    if (!$create_item){
        exit_internal_error("Creation of donor's item failed");
    }                                           

}else{
    //checks if the offerID is a valid uuid
    if (!isValidUuid($json_received->{'offerID'})){
        exit_bad_input("Invalid item uuid");
    }

    //Existing item, therefore need to check if it exists in the table
    $check_item_exists_stmt = $dbh->prepare("SELECT * FROM ITEMS_DONOR WHERE item_code = :offerID");
    $check_item_exists = $check_item_exists_stmt->execute(["offerID" => $json_received->{'offerID'}]);
    $resulting_item = $check_item_exists_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$resulting_item) {
        exit_bad_input("No such donor item exists");
    }

    //Update the value of the qunatity
    $update_qty_stmt = $dbh->prepare("UPDATE ITEMS_DONOR SET qty = :qty WHERE item_code = :offerID");
    $update_qty = $update_qty_stmt->execute(["qty" => $json_received->{'newQty'},
                                            "offerID" => $json_received->{'offerID'}]);
    if (!$update_qty){
        exit_internal_error("Update of quantity failed");
    }
}

//returns HTTP 204 No Content code
echo(exit_no_content());
?>



