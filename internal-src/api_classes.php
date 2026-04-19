<?php
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
?>