<?php
// Currently all of this is in a TODO state

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

?>

