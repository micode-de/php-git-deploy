<?php
header('Content-type: application/json');


//Init Error
$error = FALSE;

//Get & Validate Parameters
$dir = $_POST["dir"];
if(!is_dir($dir)) $error = "Directory is not a valid directory.";
$repo = $_POST["repo"];
if(empty($repo)) $error = "Repository not set.";

if($error){
	header('HTTP/1.0 400 Bad Request', true, 400);
	die(json_encode($error));
}


$output = exec('ping philippspeck.com');
echo "<pre>$output</pre>";





?>