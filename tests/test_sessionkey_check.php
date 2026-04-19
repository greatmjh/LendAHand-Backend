<?php
require_once(__DIR__."/../internal-src/helper_functions.php");
require_once(__DIR__."/../internal-src/constants.php");

$dbh = new PDO(DB_INFO);


//obviously change this if your running the test yourself
$goodSessionKey = "482900ff-a299-4052-9d62-3c081c2b7f92";

$badSessionKey1 = "abcd";

$badSessionKey2 = "482900ff-a299-4052-9d62-3c081c2b7f93";

var_dump(sessionKeyToUser($dbh, $goodSessionKey));


?>