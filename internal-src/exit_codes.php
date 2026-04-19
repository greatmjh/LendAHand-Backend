<?php
/* 
This file contains functions you can call to send error codes or empty response codes
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
?>

