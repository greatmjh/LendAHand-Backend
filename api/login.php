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

if (is_null($json_received->{'email'}) || is_null($json_received->{'password'})) {
    exit_bad_input("Missing fields in JSON");
}

//Get associated userID if email and password are correct
$auth_check_stmt = $dbh->prepare("SELECT user_id FROM USERS WHERE email = :email AND password = crypt(:password, password);");
$auth_check_stmt->execute(['email' => $json_received->{'email'}, 'password' => $json_received->{'password'}]);

$resulting_user_id = $auth_check_stmt->fetchAll()[0][0];

if (is_null($resulting_user_id)) {
    //login failed
    $response = new apiLogInResponse(false, "", "Invalid email or password");
    echo (json_encode($response));
} else {
    //login succeeded
    $new_session_key = makeSessionKey($dbh, $resulting_user_id);
    $response = new apiLogInResponse(true, $new_session_key, "");
    echo (json_encode($response));
}
?>