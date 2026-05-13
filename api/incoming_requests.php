<?php
require_once(__DIR__."/../internal-src/constants.php");
require_once(__DIR__."/../internal-src/helper_functions.php");
require_once(__DIR__."/../internal-src/api_classes.php");

//Class for the IncomingRequest object
class IncomingRequest {
    public $requestID;
    public $requesterName;
    public $requesterBio;
    public $requesterPhoneNumber;
    public $requesterDistanceKm;
    public $itemQty;
    public $fulfilled;
    public $itemName;
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

//Selects all the requests belonging to one user (donor)
$get_requests_stmt = $dbh->prepare("SELECT 
        r.request_id, 
        u.full_name, 
        u.bio, 
        u.phone_no, 

        get_distance((SELECT users.latitude FROM USERS WHERE user_id = :userID),
                (SELECT users.longitude FROM USERS WHERE user_id = :userID),
                u.latitude,
                u.longitude) as distance,

        r.qty, 
        r.req_state, 
        i.item_name 
    FROM REQUESTS AS r
    LEFT JOIN ITEMS_DONOR AS i ON r.items_donor = i.item_code
    LEFT JOIN USERS AS u ON r.donee = u.user_id
    WHERE i.donor = :userID
    ORDER BY distance ASC");
$get_requests_stmt->execute(["userID" => $userID]);
$resulting_requests = $get_requests_stmt->fetchAll(PDO::FETCH_ASSOC);

//initialises the empty list
$list = array();

//Creates the IncomingRequest objects and adds them to the list
foreach ($resulting_requests as $row){
    $request = new IncomingRequest();
    $request->requestID = $row['request_id'];
    $request->requesterName = $row['full_name'];
    $request->requesterBio = $row['bio'];
    $request->requesterPhoneNumber = $row['phone_no'];
    $request->requesterDistanceKm = $row['distance'];
    $request->itemQty = $row['qty'];
    $request->fulfilled = $row['req_state'];
    $request->itemName = $row['item_name'];

    $list[] = $request;
}

//Returns the list of requests
echo (json_encode($list));
?>