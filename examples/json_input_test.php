<?php

//implementing a test version of login.php that sends back the email if the password is "mels_moodle_password123" (definitely my real moodle password)'

$raw_input = file_get_contents('php://input');

$input_data = json_decode($raw_input);

//input validataion
if ($input_data == null ||
    $input_data->{'email'} == null ||
    $input_data->{'password'} == null) {
        //this will be compartmentalised into a helper function later
        http_response_code(400);
        echo("Bad request");
        exit();
    }

if ($input_data->{'password'} == "mels_moodle_password123") {
    echo ("hi, ".$input_data->{'email'});
} else {
    http_response_code(400);
    echo("Error 400 -- Wrong password");
}


?>