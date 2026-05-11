<?php
require_once(__DIR__."/../internal-src/constants.php");
require_once(__DIR__."/../internal-src/helper_functions.php");
require_once(__DIR__."/../internal-src/api_classes.php");

//Class for DonationOffer
class DonationOffer {
    public $offerID;
    public $itemID;
    public $itemName;
    public $qty;
    public $donorName;
    public $distanceKm;
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

if (is_null($json_received->{'sessionKey'} || is_null($json_received->{'requestID'}))) {
    exit_bad_input("Missing fields in JSON");
}

//Get associated user ID to check if it's a valid session token
$userID = sessionKeyToUser($dbh, $json_received->{'sessionKey'});
if (is_null($userID)) {
    exit_bad_session_token();
}

$cancel_req_stmt = $dbh->prepare("DELETE FROM requests WHERE request_id = :requestID AND donee = :userID");
$cancel_req_stmt->execute(['requestID' => $json_received->{'requestID'}, 'userID' => $userID]);
exit_no_content();
?>