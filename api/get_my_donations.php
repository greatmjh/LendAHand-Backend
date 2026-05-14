<?php
require_once(__DIR__."/../internal-src/constants.php");
require_once(__DIR__."/../internal-src/helper_functions.php");
require_once(__DIR__."/../internal-src/api_classes.php");

class DonationOffer{
    public $offerID;
    public $itemId;
    public $itemDesc;
    public $qty;
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

$my_donations_stmt = $dbh->prepare("SELECT item_code, item_class, item_name, qty FROM ITEMS_DONOR WHERE donor = :userID AND qty > 0");
$my_donations = $my_donations_stmt->execute(["userID" => $userID]);
$resulting_donations = $my_donations_stmt->fetchAll(PDO::FETCH_ASSOC); //check if fetchAll() works for no rows
if (!$resulting_donations){
    echo(json_encode(array()));
    exit();
}

//initialises the empty list
$list = array();

//Creates the DonationOffer objects and adds them to the list
foreach ($resulting_donations as $row){
    $donation = new DonationOffer();
    $donation->offerID = $row['item_code'];
    $donation->itemId = $row['item_class'];
    $donation->itemDesc = $row['item_name'];
    $donation->qty = $row['qty'];

    $list[] = $donation;
}

//Returns the list of items
echo (json_encode($list));
?>



