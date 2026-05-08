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

if (is_null($json_received->{'sessionKey'}) || is_null($json_received->{'itemId'}) || is_null($json_received->{'qty'})) {
    exit_bad_input("Missing fields in JSON");
}

//Get associated user ID to check if it's a valid session token
$userID = sessionKeyToUser($dbh, $json_received->{'sessionKey'});
if (is_null($userID)) {
    exit_bad_session_token();
}

$itemId = $json_received->{'itemId'};
$qty = $json_received->{'qty'};


//Change the quantity in the table to what the user asked for
$update_unireq_stmt = $dbh->prepare("INSERT INTO general_requests (donee, items_class, qty) 
                                    VALUES (:userID, :itemID, :qty) 
                                    ON CONFLICT (donee, items_class) 
                                    DO UPDATE SET qty = EXCLUDED.qty");
$update_unireq_stmt->execute(["userID" => $userID, "itemID" => $itemId, "qty" => $qty]);

exit_no_content();

?>