<?php
header('Content-type: application/json');


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



//Constants
DEFINE("TEMP_DIR", "C:/Users/speck_000/temp");
DEFINE("YUI_PATH", "C:/Users/speck_000/temp/yui.jar");
DEFINE("SECRET", "dsfdf");

//Get Variables
$repo = $_POST["repo"];
$dir = $_POST["dir"];
$compress = false;
if($_POST["compress"]==1) $compress = true;

//Generate and Create Temp Folder
$temp_dir_repo = md5(time());
$temp_dir_repo_abolute = TEMP_DIR."/".$temp_dir_repo;
mkdir($temp_dir_repo_abolute);

//Switch to Temp Folder
chdir($temp_dir_repo_abolute);

//Clone Repo
$cmd = "git clone ".$repo." ".$temp_dir_repo_abolute;
shell_exec($cmd);

if($compress) recurse_compress($temp_dir_repo);

//Copy Repo from Temp to Dir
recurse_copy($temp_dir_repo_abolute, $dir);

//Delete Temp Folder
delete_directory($temp_dir_repo_abolute);

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














?>