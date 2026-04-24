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

if (is_null($json_received->{'sessionKey'}) || is_null($json_received->{'oldPassword'}) || is_null($json_received->{'newPassword'})) {
    exit_bad_input("Missing fields in JSON");
}

//Get associated user ID
$userID = sessionKeyToUser($dbh, $json_received->{'sessionKey'});
if (is_null($userID)) {
    exit_bad_session_token();
}

//Check if the old password matches
$pwd_check_stmt = $dbh->prepare("SELECT count(*) FROM USERS WHERE user_id = :userID AND password = crypt(:oldPassword, password);");
$pwd_check_stmt->execute(["userID" => $userID, "oldPassword" => $json_received->{'oldPassword'}]);
$old_password_was_correct = (bool)($pwd_check_stmt->fetchAll()[0][0]); //this will return 1 if it was successful or 0 if it wasn't

//Exit if the old password was incorrect
if (!$old_password_was_correct) {
    //Return a fail state
    $response = new apiLogInResponse(false, "", "Incorrect old password");
    echo (json_encode($response));
    exit();
}

//Update the password
$update_pwd_stmt = $dbh->prepare("  UPDATE users 
                                    SET password = crypt(:newPassword, gen_salt('bf'))
                                    WHERE user_id = :userID");
$update_pwd_stmt->execute(["userID" => $userID, "newPassword" => $json_received->{'newPassword'}]);

//Clear the session keys that were made under the old password
$delete_sesskeys_stmt = $dbh->prepare("DELETE FROM session_keys WHERE user_id = :userID");
$delete_sesskeys_stmt->execute(["userID" => $userID]);

//Make a new session key
$newSessionKey = makeSessionKey($dbh, $userID);

//Return the new session key
$response = new apiLogInResponse(true, $newSessionKey, "");
echo (json_encode($response));

?>