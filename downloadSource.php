<?php

define("DS", DIRECTORY_SEPARATOR);

//$source_code = ( isset($_GET['code']) ? $_GET['code'] : null);

if(isset($_GET['code'])){
	$source_code = $_GET['code'];
} else {
	$source_code = null;
}

//echo "<pre>";
//echo urldecode($source_code);
//echo "</pre>";

//decode the source code for seeing the carriage return and new line feed
//$decodedCode = urldecode($source_code);
$decodedCode = rawurldecode($source_code);

//save source code into file
$name_of_file = __DIR__ . DS . "public" . DS . "file" . DS . "text.txt" ;

if( !file_exists( $name_of_file )){//check if file exists	
	
	//create file
	$content = "";
	$fp = fopen($name_of_file ,"wb");
	fwrite($fp,$content);
	fclose($fp);
} else{
	//just add text to file
	$content = $decodedCode;
	$fp = fopen($name_of_file ,"wb");
	fwrite($fp,$content);
	fclose($fp);
}
//end save source code

//get current date to insert it into filename
$current_date = date("d m Y His");

//check if exists any new line feed into our source Code
//if (strpos($decodedCode, "\n") !== false) {
//    echo 'true';
//}


/*
set headers and force download of file instead of rendering into browser
*/

function forceDownload($filename, $type = "application/octet-stream") {
    header('Content-Type: '.$type.'; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
}

forceDownload("cod sursa " . $current_date . ".html", "text/html");
//echo file_get_contents($name_of_file);

echo "<pre>";
echo $decodedCode;
echo "</pre>";


?>