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

if (is_null($json_received->{'sessionKey'}) || is_null($json_received->{'profileInfo'})) {
    exit_bad_input("Missing fields in JSON");
}

//Get associated user ID to check if it's a valid session token
$userID = sessionKeyToUser($dbh, $json_received->{'sessionKey'});
if (is_null($userID)) {
    exit_bad_session_token();
}

//Instantiate a profileinfo class from the data
$new_profileInfo = apiProfileInfo::fromJsonData($json_received->{'profileInfo'});

if (is_null($new_profileInfo)) {
    exit_bad_input("Missing fields in profile info");
}

//Updating all the attributes for the user
$update_info_stmt = $dbh->prepare("UPDATE users SET 
                            email = :email, 
                            full_name = :full_name, 
                            bio = :bio, 
                            phone_no = :phone_no, 
                            latitude = :latitude, 
                            longitude = :longitude
                            WHERE user_id = :userID");

$query_success = $update_info_stmt->execute([
                            'email' => $new_profileInfo->email, 
                            'full_name' => $new_profileInfo->fullName,
                            'bio' => $new_profileInfo->bio,
                            'phone_no' => $new_profileInfo->phoneNumber,
                            'latitude' => $new_profileInfo->homeLat,
                            'longitude' => $new_profileInfo->homeLong,
                            'userID' => $userID]);

if (!$query_success) {
    exit_internal_error("Profile info update SQL failed");
}

//returns HTTP 204 No Content code
echo(exit_no_content());
?>
