<?php
require_once(__DIR__."/../internal-src/constants.php");
require_once(__DIR__."/../internal-src/helper_functions.php");
require_once(__DIR__."/../internal-src/api_classes.php");

//Class for the TopDonor object
class TopDonor {
    public $name;
    public $itemCount;
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

//Select user from the db ordered by total_donations
$top_donors_stmt = $dbh->prepare("SELECT full_name, total_donations FROM USERS ORDER BY total_donations DESC");
$top_donors_stmt->execute();

$resulting_top_donors = $top_donors_stmt->fetchAll()[0];

//initialises the empty list
$list = array();

//Creates the TopDonor objects and adds them to the list
foreach ($resulting_top_donors as $row){
    $top_donor = new TopDonor();
    $top_donor->name = $row['full_name'];
    $top_donor->itemCount = $row['total_donations'];

    $list[] = $top_donor;
}

//Returns the list of TopDonors
echo (json_encode($list));
?>