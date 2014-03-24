<?php

// ========== Configuration ==========

//Temp Directory to work with files
DEFINE("TEMP_DIR", "C:/Users/speck_000/temp");

//Path to YUI Compressor
DEFINE("YUI_PATH", "C:/Users/speck_000/temp/yui.jar");

//Secret Token to protect your script
DEFINE("SECRET", "dsfdf");

// ========== End of Configuration ==========


header('Content-type: application/json');
error_reporting(0);

function output($status, $content){
	switch($status){
		case 200:
			header("HTTP/1.0 200 OK");
			echo json_encode($content);
			break;
		case 400:
			header("HTTP/1.0 400 BAD REQUEST");
			die(json_encode($content));
			break;
	}
}

function recurse_copy($src,$dst){ 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ($file = readdir($dir))){ 
        if (($file != '.' ) && ( $file != '..' )){ 
            if ( is_dir($src . '/' . $file) ) { 
                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else{ 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
} 

function delete_directory($path) {
	if (is_dir($path) === true){
        $files = array_diff(scandir($path), array('.', '..'));
        foreach ($files as $file){
            delete_directory(realpath($path) . '/' . $file);
        }
        return rmdir($path);
    }
    else if (is_file($path) === true){
        return unlink($path);
    }
    return false;
}

function recurse_compress($path){
	chdir(TEMP_DIR);
	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path),RecursiveIteratorIterator::SELF_FIRST);
	
	foreach($files as $file){
	    $fullPath = (string) $file; 
		if(is_file($file)){
			$file_info = pathinfo($fullPath);
			if(array_key_exists("extension", $file_info) and ($file_info["extension"]=="css" or $file_info["extension"]=="js")){
				$compress="java -jar ".YUI_PATH." ".$fullPath." -o ".$fullPath;
				shell_exec($compress);
			}
		}
		
	}
    

}

//Get POST Parameters
$repo = $_POST["repo"];
if(empty($repo)) output(400, "Repository not set.");
$dir = $_POST["dir"];
if(empty($dir)) output(400, "No valid directory set.");
$compress = false;
if($_POST["compress"]==1) $compress = true;

//Generate Temp Folder
$temp_dir_repo = md5(time()+rand(0,10));
$temp_dir_repo_abolute = TEMP_DIR."/".$temp_dir_repo;

//Create Temp Folder
mkdir($temp_dir_repo_abolute);

//Switch to Temp Folder
chdir($temp_dir_repo_abolute);

//Clone Repo
$statement = "git clone ".$repo." ".$temp_dir_repo_abolute;
shell_exec($statement);

if($compress) recurse_compress($temp_dir_repo);

//Copy Repo from Temp to Dir
recurse_copy($temp_dir_repo_abolute, $dir);

//Delete Temp Folder
delete_directory($temp_dir_repo_abolute);


//Ouput Success
output(200, "Deployment successful.");














?>