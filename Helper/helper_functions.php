<?php

/*
	HELPER functions to use in main functionality(lexer, parser,interpreter)
	Ajutor:
		numele functiilor de aici se vor scrie incepand cu helper_*;
		eg. helper_generate_unique_name()
		 	helper_test()
		 	etc.
*/



/*
	generate a new unique name

	@return String , a new unique 32 characters identifier
*/
function helper_generate_unique_name()
{
	$uniq_name = md5(microtime());
	usleep(1);

	return $uniq_name; 
}


/*
	deletes all images in the images folder , before rerun of the interpreting process
	(useful for not overloading over time that folder)
*/
function helper_clean_images_folder()
{
	$files = glob(IMAGES_DIRECTORY . '/*'); // get all file names
    foreach($files as $file){ // iterate files 
      if(is_file($file))
        unlink($file); // delete file
    }
}



/*
	Perform a check on a multidimensional(including nested arrays within it) array for a value
	Recursive_array_search

	Used in parser for throwing errors

	@param Mixed $needle , the value to search for

	@param Mixed $haystack the parent array to search the value in

	@return Mixed Integer $current_key if $needle found , false otherwise

	As seen on:
	http://php.net/manual/ro/function.array-search.php
*/
function helper_recursive_array_search($needle,$haystack) 
{
    foreach($haystack as $key=>$value) {
        $current_key=$key;
        if($needle===$value OR (is_array($value) && helper_recursive_array_search($needle,$value) !== false)) {
            return $current_key;
        }
    }
    return false;
}


/*
	check if a matrix (nested arrays) si square(patratica) and Odd(impara)

	@param array $matrix the matrix to be checked

	@return true if matrix is square and odd at the same time , false otherwise
*/
function helper_checkIfMatrixSquareAndOdd(array $matrix)
{

	$median=0;
	$counter = 0;
	foreach ($matrix as $line) {
		$median += count($line);
		$counter++;
	}
	$median_arithmetic = $median / $counter;

	// $count_line = count($arr2[0]);//vino cu un cod mai bun aici
	$count_line = $median_arithmetic;
	$count_column = count($matrix);

	if(is_int($count_line) ){
		//echo "linia e un numar intreg = ".$count_line;

		if ($count_line == $count_column) {
			//matricea e patratica
			//echo "matrice patratica ";

			if ( ($count_line % 2 == 1) && ($count_column % 2 ==1) ) {
				//echo "matricea este si patratic-impara";
				return true;
			}
		} else {
			//matrix not square
			return false;
		}
	} else {
		//echo "MATRICEA NU SE IMPARTE EXACT, nu e patratica";
		return false;
	}

}



/*
	Sanitizes(clean ) the string (removes "" from start and end of a string)
	Used for now in Interpretor

	@param $string the string to be sanitized

	@return String the sanitized string
*/
function helper_sanitizeString($string)
{
	return trim($string,'"');
}	



/*
	checks if an target array contais another values than some values specified in source_values

	USED in erosion and dilate in interpreter.php for now(works with array from DEFMAT)

	@param Array $source_values , the values to look into target for and check if contains another values
	@param Array $target_array the array to search in for values

	@return boolean True if target array contains another values than source values , False otherwise
*/
function helper_arrayContainsOtherValuesThan(array $source_values, $target_array)
{
	//merge all nested arrays below $target_array into one unique values array
	$merged_target_array = array_unique(call_user_func_array('array_merge', $target_array));

	foreach ($merged_target_array as $current_value) {//iteration over $target_array
		//this code not good
		// foreach ($source_values as $source_value) {
		// 	if ( $current_value != $source_value ) {
		// 		//2nd array contains another values than the ones specified
		// 		return true;
		// 	}
		// }

		if (!in_array($current_value, $source_values)) {//if not in source values , return true(array contains another values than the ones specified)
			return true;
		}
	}

	return false;
}



?>