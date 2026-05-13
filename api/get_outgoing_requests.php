<?php
require_once(__DIR__."/../internal-src/constants.php");
require_once(__DIR__."/../internal-src/helper_functions.php");
require_once(__DIR__."/../internal-src/api_classes.php");

class OutgoingRequest{
    public $requestID;
    public $donorName;
    public $donorPhoneNumber;
    public $itemName;
    public $itemQty;
    public $state;
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

if (is_null($json_received->{'sessionKey'})){
    exit_bad_input("Missing fields");
}

//Get associated user ID
$userID = sessionKeyToUser($dbh, $json_received->{'sessionKey'});
if (is_null($userID)) {
    exit_bad_session_token();
}

//Joins the requests, items_donor and users tables 
$my_requests_stmt = $dbh->prepare("SELECT r.request_id, u.full_name, i.item_name, r.qty, r.req_state,
                                        CASE
                                            WHEN req_state = 'accepted' THEN u.phone_no
                                            ELSE NULL
                                        END AS phone_no
                                    FROM REQUESTS as r
                                    LEFT JOIN ITEMS_DONOR as i ON r.items_donor = i.item_code
                                    LEFT JOIN USERS AS u ON i.donor = u.user_id

                                    WHERE r.donee = :userID");
$my_requests = $my_requests_stmt->execute(["userID" => $userID]);
$resulting_requests = $my_requests_stmt->fetchAll(PDO::FETCH_ASSOC); //check if fetchAll() works for no rows
if (!$resulting_requests){
    exit_bad_input("The donee has made no requests");
}

//initialises the empty list
$list = array();

//Creates the OutgoingRequests objects and adds them to the list
foreach ($resulting_requests as $row){
    $request = new OutgoingRequest();
    $request->requestID = $row['request_id'];
    $request->donorName = $row['full_name'];
    if (!is_null($row['phone_no'])){
        $request->donorPhoneNumber = $row['phone_no'];
    } else {
        $request->donorPhoneNumber; //as to not break the parsing
    }
    $request->itemName = $row['item_name'];
    $request->itemQty = $row['qty'];
    $request->state = $row['req_state'];

    $list[] = $request;
}

//reverses the list to return requests from newest to oldest
$reversed_list = array_reverse($list);

//Returns the list of items
echo (json_encode($list));
?>





