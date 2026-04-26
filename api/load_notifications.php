<?php
require_once(__DIR__."/../internal-src/constants.php");
require_once(__DIR__."/../internal-src/helper_functions.php");
require_once(__DIR__."/../internal-src/api_classes.php");

//Class for the Notification object
class Notification {
    public $heading;
    public $text;
    public $onClick;
    public $isRead;
    public $id;
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

//Selects all the notifications belonging to one user
$get_notifications_stmt = $dbh->prepare("SELECT heading, content, on_click, read, notification_id FROM NOTIFICATIONS where user_id = :userID");
$get_notifications_stmt->execute(["userID" => $userID]);

$resulting_notifications = $get_notifications_stmt->fetchAll();

//initialises the empty list
$list = array();

//Creates the Notifications objects and adds them to the list
foreach ($resulting_notifications as $row){
    $notification = new Notification();
    $notification->heading = $row['heading'];
    $notification->text = $row['content'];
    $notification->onClick = $row['on_click'];
    $notification->isRead = $row['read'];
    $notification->id = $row['notification_id'];

    $list[] = $notification;
}

//Returns the list of notifications
echo (json_encode($list));
?>