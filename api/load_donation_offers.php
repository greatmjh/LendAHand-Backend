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

if (is_null($json_received->{'sessionKey'} || is_null($json_received->{'latitude'}) || is_null($json_received->{'longitude'}))) {
    exit_bad_input("Missing fields in JSON");
}

//Get associated user ID to check if it's a valid session token
$userID = sessionKeyToUser($dbh, $json_received->{'sessionKey'});
if (is_null($userID)) {
    exit_bad_session_token();
}

$lat = $json_received->{'latitude'};
$long = $json_received->{'longitude'};

$load_offers_stmt = $dbh->prepare("SELECT item_code AS offerID, item_class AS itemID, item_name AS itemName, 
                                    qty, users.full_name AS donorName, 
                                    get_distance(:lat, :long, users.latitude, users.longitude) AS distanceKm
                                    FROM items_donor INNER JOIN users ON items_donor.donor = users.user_id
                                    WHERE NOT donor = :currUserID
                                    ORDER BY get_distance(:lat, :long, users.latitude, users.longitude);");

$load_offers_stmt->execute(['lat' => $lat, 'long' => $long, 'currUserID' => $userID]);

$query_result = $load_offers_stmt->fetchAll();


$result = array();
foreach($query_result as $row) {
    $donationOffer = new DonationOffer();
    $donationOffer->offerID = $row['offerid'];
    $donationOffer->itemID = $row['itemid'];
    $donationOffer->itemName = $row['itemname'];
    $donationOffer->qty = $row['qty'];
    $donationOffer->donorName = $row['donorname'];
    $donationOffer->distanceKm = $row['distancekm'];
    $result[] = $donationOffer;
}
header('Content-Type: application/json; charset=utf-8');
echo(json_encode($result));
?>