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

$json_received = json_decode(file_get_contents('php://input'));

//Data validation
if (is_null($json_received)) {
    exit_bad_input("Failed to parse JSON");
}

if (is_null($json_received->{'profileInfo'}) || is_null($json_received->{'password'})) {
    exit_bad_input("Missing fields in JSON");
}

//Instantiate a profileinfo class from the data
$profileInfo = apiProfileInfo::fromJsonData($json_received->{'profileInfo'});

if (is_null($profileInfo)) {
    exit_bad_input("Missing fields in profile info");
}

//TODO: prevalidate that the email doesn't already exist so the user gets a nicer error

//Now we can actually create the user
$create_user_stmt = $dbh->prepare("INSERT INTO users (email, password, full_name, bio, phone_no, latitude, longitude) VALUES (:email, crypt(:password, gen_salt('bf')), :full_name, :bio, :phone_no, :latitude, :longitude) RETURNING user_id");

$query_success = $create_user_stmt->execute(['email' => $profileInfo->email, 
                            'password' => $json_received->{'password'},
                            'full_name' => $profileInfo->fullName,
                            'bio' => $profileInfo->bio,
                            'phone_no' => $profileInfo->phoneNumber,
                            'latitude' => $profileInfo->homeLat,
                            'longitude' => $profileInfo->homeLong]);

if (!$query_success) {
    exit_internal_error("User creation SQL failed");
}

$userID = $create_user_stmt->fetchAll()[0][0];

//Create a session key
$get_sesskey_stmt = $dbh->prepare("INSERT INTO session_keys (user_id) VALUES (?) RETURNING session_key");
$get_sesskey_stmt->execute([$userID]);

$new_session_key = $get_sesskey_stmt->fetchAll()[0][0];

$response = new apiLogInResponse(true, $new_session_key, "");

// return this in JSON to the user
echo(json_encode($response));
?>