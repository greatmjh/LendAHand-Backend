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

if (is_null($json_received->{'sessionKey'}) || is_null($json_received->{'requestID'}) || is_null($json_received->{'accepted'})) {
    exit_bad_input("Missing fields in JSON");
}

//Get associated user ID
$userID = sessionKeyToUser($dbh, $json_received->{'sessionKey'});
if (is_null($userID)) {
    exit_bad_session_token();
}

if (!isValidUuid($json_received->{'requestID'})){
    exit_bad_input("Invalid uuid");
}

$status_of_request;
//determines if the request is 'accepted' or 'rejected' for the heading
if ($json_received->{'accepted'}){
    $status_of_request = "accepted";
}else{
    $status_of_request = "rejected";
}

//checks if the request_id exists in the table and then fetches all the required info for the notification
//and that the request is open
$check_request_stmt = $dbh->prepare("SELECT donee, items_donor, qty FROM REQUESTS WHERE request_id = :requestID AND req_state = 'open'");
$check_request = $check_request_stmt->execute(["requestID" => $json_received->{'requestID'}]);
$resulting_request = $check_request_stmt->fetch(PDO::FETCH_ASSOC);
if (!$resulting_request) {
    exit_bad_input("No such open request exists");
}

//links the items_donor uuid from the request with the name of the item, and the donor in the items_donor table
$find_item_info_stmt = $dbh->prepare("SELECT donor, item_name FROM ITEMS_DONOR WHERE item_code = :items_donor");
$find_item_info = $find_item_info_stmt->execute(["items_donor" => $resulting_request['items_donor']]);
if (is_null($find_item_info)) {
    exit_bad_input("No such donor item exists");
}
$item_info = $find_item_info_stmt->fetchAll()[0];

//finds the name of the donor
$find_name_donor_stmt = $dbh->prepare("SELECT full_name from USERS WHERE user_id = :donor");
$find_name_donor = $find_name_donor_stmt->execute(["donor" => $item_info['donor']]);
if (is_null($find_name_donor)) {
    exit_bad_input("No such donor exists");
}
$donor_name = $find_name_donor_stmt->fetchAll()[0][0];

//Drops qty of items_donor
if ($json_received->{'accepted'}) {
    $drop_qty_stmt = $dbh->prepare("UPDATE items_donor SET qty = qty - (SELECT qty FROM requests WHERE request_id = :requestID) WHERE item_code = (SELECT items_donor FROM requests WHERE request_id = :requestID)");
    $query_success = $drop_qty_stmt->execute(["requestID" => $json_received->{'requestID'}, "requestID" => $json_received->{'requestID'}]);
    if (!$query_success) {
        exit_internal_error("Updating quantity of items_donor failed.");
    }
}

//Alters the request's status of 'req_state' in the Requests table
$update_request_stmt = $dbh->prepare("UPDATE REQUESTS SET req_state = :req_state WHERE request_id = :requestID");
$query_success = $update_request_stmt->execute(["requestID" => $json_received->{'requestID'},
                                                "req_state" => $status_of_request]);
if (!$query_success) {
    exit_internal_error("Updating status of request SQL failed");
}


//Create notification for the donee
$create_notification_stmt = $dbh->prepare("INSERT INTO NOTIFICATIONS(user_id, content, on_click, heading)
                                            VALUES(:user_id, :content, :on_click, :heading)");
$notification = $create_notification_stmt->execute(["user_id" => $resulting_request['donee'],
                                                    "content" => $donor_name . " has " . $status_of_request . " your request for " . $resulting_request['qty'] . " " . $item_info['item_name'],
                                                    "on_click" => "outgoingRequests",
                                                    "heading" => "Request " . $status_of_request
                                                    ]);
if (!$notification){
    exit_internal_error("Creating notification for donee failed");
}


//returns HTTP 204 No Content code
echo(exit_no_content());
?>