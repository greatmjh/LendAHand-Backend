<?php

/* 
This section contains functions you can call to send error codes or empty response codes
to the client, say a 400 Unauthorised code or a 204 No Content code.

NB: These functions all terminate execution as they are run
*/

function exit_no_content() {
    http_response_code(204);
    exit();
}

//optional: set a parameter to change the message
function exit_bad_input($message = "Bad input") {
    http_response_code(400);
    echo $message;
    exit();
}

function exit_bad_session_token() {
    http_response_code(401);
    echo "Bad session token";
    exit();
}

function exit_internal_error($message = "Internal server error") {
    http_response_code(500);
    echo $message;
    exit();
}

// Validation mathods (TODO: flesh them out):

function validatePhoneNumber($phoneNumber) {
    if (is_null($phoneNumber)) {
        return false;
    }

    if (strlen($phoneNumber) > 15) {
        return false;
    }

    //TODO: bring in validation to make sure this is an actual phone number

    return true;
}

// Sourced from: https://gist.github.com/joel-james/3a6201861f12a7acf4f2
function isValidUuid( $uuid ) {
    
    if (!is_string($uuid) || (preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $uuid) !== 1)) {
        return false;
    }

    return true;
}

// Given a session key, either returns the corresponding user uuid or NULL if its an invalid key
function sessionKeyToUser($dbh, $sessionKey) {
    if (!isValidUuid($sessionKey)) {
        return null;
    }
    $pstmt = $dbh->prepare("SELECT user_id FROM session_keys WHERE session_key = ?");
    $pstmt->execute([$sessionKey]);
    $results = $pstmt->fetchAll();

    if (count($results) != 1) {
        return null; //invalid session key
    } else {
        return $results[0][0];
    }
}

//Make a session key given a user ID
function makeSessionKey($dbh, $userID) {
    $get_sesskey_stmt = $dbh->prepare("INSERT INTO session_keys (user_id) VALUES (?) RETURNING session_key");
    $get_sesskey_stmt->execute([$userID]);

    return $get_sesskey_stmt->fetchAll()[0][0];
}
?>

