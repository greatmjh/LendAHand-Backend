<?php
require(__DIR__."/../internal-src/api_classes.php");

$logInResponse = new apiLogInResponse(true, "abc123", "success");

echo json_encode($logInResponse)
?>