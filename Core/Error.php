<?php

namespace Core;


/*
	Class Errors Handling
	php ver 5.5.0
*/
class Error
{
	private static $errors = [];



	/*
	get all the errors
	*/
	public static function getErrors()
	{
		return static::$errors;
	}



	/*
	raise(add) a new error
	*/
	public static function raise($error)
	{
		array_push( static::$errors, $error);

		//pana una alta
		//die("<br>" . $error);

		//1. cu return false-uri prin metode
		return false;		

		//2.redirect cu sesiuni la localhost/imageinterpreter
		// $_SESSION['have_error'] = true;
		// $_SESSION['source_code'] = $_POST['text'];
		// $_SESSION['errors'] = self::getErrors();
		// header("Location: http://localhost/imageinterpreter");
	}



	/*
		Checks if exists any error set

		@return boolean , true if errors exist , false otherwise
	*/
	public static function occur()
	{
		if(!empty(self::getErrors()) ){
			return true;
		}
		return false;
	}



	/*
		wipes the errors

		@return Void
	*/
	public static function wipeErrors()
	{
		self::$errors = [];
	}	


	/*
		print the errors on screen
	*/
	public static function dumpErrors()
	{
		echo "<pre>";
		print_r(static::$errors);
		echo "</pre>";
	}

}/*end of Error class*/













?>