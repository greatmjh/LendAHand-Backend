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

if (is_null($json_received->{'sessionKey'}) || is_null($json_received->{'notificationId'})) {
    exit_bad_input("Missing fields in JSON");
}

//Get associated user ID to check if it's a valid session token
$userID = sessionKeyToUser($dbh, $json_received->{'sessionKey'});
if (is_null($userID)) {
    exit_bad_session_token();
}

if ($json_received->{'notificationId'} == "all") {
    //Mark all notifications as read
    $mark_all_stmt = $dbh->prepare("UPDATE notifications SET read = true WHERE user_id = :userID");
    $mark_all_stmt->execute(["userID" => $userID]);
} else {
    //Mark specific notification as read
    $mark_specific_stmt = $dbh->prepare("UPDATE notifications SET read = true WHERE user_id = :userID AND notification_id = :notificationId");
    $mark_specific_stmt->execute(["userID" => $userID, "notificationId" => $json_received->{'notificationId'}]);
}
exit_no_content();

?>