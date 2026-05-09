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

if (is_null($json_received->{'offerID'}) || is_null($json_received->{'qty'}) || is_null($json_received->{'sessionKey'})) {
    exit_bad_input("Missing fields in JSON");
}

if (!isValidUuid($json_received->{'offerID'})){
    exit_bad_input("Invalid uuid");
}

//Get associated user ID
$userID = sessionKeyToUser($dbh, $json_received->{'sessionKey'});
if (is_null($userID)) {
    exit_bad_session_token();
}

//Checks if the offerID (items_donor.item_code) exists and returns fields necessary for notification and request
$find_item_info_stmt = $dbh->prepare("SELECT donor, item_name FROM ITEMS_DONOR WHERE item_code = :offer_id");
$find_item = $find_item_info_stmt->execute(["offer_id" => $json_received->{'offerID'}]);
$resulting_item_info = $find_item_info_stmt->fetch(PDO::FETCH_ASSOC);
if (!$resulting_item_info) {
    exit_bad_input("No such donor item exists");
}



//Creates a request
$create_request_stmt = $dbh->prepare("INSERT INTO REQUESTS(donee, items_donor, qty) VALUES(:donee, :items_donor, :qty)");
$query_success = $create_request_stmt->execute(["donee" => $userID,
                                                "items_donor" => $json_received->{'offerID'},
                                                "qty" => $json_received->{'qty'}]);
if (!$query_success) {
    exit_internal_error("Creating request SQL failed");
}

//Finds the donee's name for the notification
$find_donee_name_stmt = $dbh->prepare("SELECT full_name FROM USERS WHERE user_id = :userID");
$donee_name = $find_donee_name_stmt->execute(["userID" => $userID]);
if (is_null($donee_name)) {
    exit_bad_input("No such donee exists");
}
$resulting_donee_name = $find_donee_name_stmt->fetchAll()[0][0];


//Create notification for the donor
$create_notification_stmt = $dbh->prepare("INSERT INTO NOTIFICATIONS(user_id, content, on_click, heading)
                                            VALUES(:user_id, :content, :on_click, :heading)");
$notification = $create_notification_stmt->execute(["user_id" => $resulting_item_info['donor'],
                                                    "content" => $resulting_donee_name . " would like " . $json_received->{'qty'} . " " . $resulting_item_info['item_name'],
                                                    "on_click" => "incomingRequests",
                                                    "heading" => "Request received"
                                                    ]);
if (!$notification){
    exit_internal_error("Creating notification for donor failed");
}


//returns HTTP 204 No Content code
echo(exit_no_content());
?>