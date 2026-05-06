<?php
require_once(__DIR__."/../internal-src/constants.php");
require_once(__DIR__."/../internal-src/helper_functions.php");
require_once(__DIR__."/../internal-src/api_classes.php");

// Connect to DB and make sure its available
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

//Get associated user ID
$userID = sessionKeyToUser($dbh, $json_received->{'sessionKey'});
if (is_null($userID)) {
    exit_bad_session_token();
}

//Select profile info from the table
$get_profile_stmt = $dbh->prepare("SELECT email, full_name, phone_no, bio, latitude, longitude FROM USERS WHERE user_id = :userID");
$get_profile_stmt->execute(["userID" => $userID]);

$resulting_profile = $get_profile_stmt->fetchAll()[0];

//Creates the ProfileInfo object
$profile = new apiProfileInfo($resulting_profile['full_name'], $resulting_profile['email'], $resulting_profile['phone_no'], $resulting_profile['bio'], $resulting_profile['latitude'], $resulting_profile['longitude']);

//Return the profile
echo (json_encode($profile));
?>