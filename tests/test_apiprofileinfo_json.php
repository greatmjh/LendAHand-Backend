<?php

require_once(__DIR__."/../internal-src/api_classes.php");

$good_json = '{ "fullName": "Mel Higgs", "email": "mel@mel.com", "phoneNumber": "+27681386379", "bio": "hello", "homeLat": "27.27", "homeLong": "-27.12"}';
var_dump(apiProfileInfo::fromJson($good_json));

$json_syntax_error = '{. "fullName": "Mel Higgs", "email": "mel@mel.com", "phoneNumber": "+27681386379", "bio": "hello", "homeLat": "27.27", "homeLong": "-27.12"}';
var_dump(apiProfileInfo::fromJson($json_syntax_error));

$bad_coord = '{. "fullName": "Mel Higgs", "email": "mel@mel.com", "phoneNumber": "+27681386379", "bio": "hello", "homeLat": "27.27", "homeLong": "-27.1a2"}';
var_dump(apiProfileInfo::fromJson($bad_coord));