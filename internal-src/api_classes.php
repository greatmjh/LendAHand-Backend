<?php
//These are classes that are used more than once in the API spec. If they are also used as inputs, a JSON constructor
//is included which return null if the json is wrong.

require_once(__DIR__."/../internal-src/helper_functions.php");

class apiLogInResponse {
    public $success;
    public $sessionKey;
    public $errorMessage;

    function __construct($success, $sessionKey, $errorMessage) {
        $this->success = $success;
        $this->sessionKey = $sessionKey;
        $this->errorMessage = $errorMessage;
    }
}

class apiProfileInfo {
    public $fullName;
    public $email;
    public $phoneNumber;
    public $bio;
    public $homeLat;
    public $homeLong;

    public function __construct($fullName, $email, $phoneNumber, $bio, $homeLat, $homeLong) {
        $this->fullName = $fullName;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->bio = $bio;
        $this->homeLat = $homeLat;
        $this->homeLong = $homeLong;

    }

    public static function fromJsonData($data) {
        if ($data === null) {
            return null;
        }
        $fullName = $data->{'fullName'};
        $email = $data->{'email'};
        $phoneNumber = $data->{'phoneNumber'};
        $bio = $data->{'bio'};
        $homeLat_str = $data->{'homeLat'};
        $homeLong_str = $data->{'homeLong'};

        if (is_null($fullName) || is_null($phoneNumber) || is_null($email) || !validatePhoneNumber($phoneNumber) || is_null($bio)
            || !filter_var($homeLat_str, FILTER_VALIDATE_FLOAT) || !filter_var($homeLat_str, FILTER_VALIDATE_FLOAT)) {
            return null;
        }

        return new apiProfileInfo($fullName, $email, $phoneNumber, $bio, (float)$homeLat_str, (float)$homeLong_str);
    }
}

class apiItem {
    public $itemId;
    public $itemTitle;
    public $quantity;

    function __construct($itemId, $itemTitle, $quantity) {
        $this->itemId = $itemId;
        $this->itemTitle = $itemTitle;
        $this->quantity = $quantity;
    }
}
?>