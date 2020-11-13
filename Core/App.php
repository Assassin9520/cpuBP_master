<?php

namespace Core;

/*
	Class core app
	php ver 5.5.0
	This is where the entire app is being initialized and runned
*/
class App 
{



	/*
		Run the application(image interpreter) 

		@param String $source_code , the source code to interpret

		@return boolean false if there was an error in the entire process, true Otherwise
	*/
	public function run($source_code)
	{
		//if there was no source_code typed in..
		if (empty($source_code)) {
			Error::raise('Eroare : Nu a fost introdus cod sursa..');
			return false;
		}

		//creating a new file that will store the source code to be available for students
		//inserting text into file
		$this->createFile($source_code);	

		//go to processing(interpreting) the source code and away we go 
		$interpret_result = $this->process($_POST['text']);

		return $interpret_result;
		//OR:
		/*
		if(empty($interpret_result)){
			return true;
		}
		return false;
		*/
	}



	/*
	Create the file (as a backup of written code)

	@param $text the text to be inserted into the file(from textarea)

	@return Void. Just Creates the file and fill it with the text
	*/
	public function createFile($text)
	{
		$name_of_file = dirname(__DIR__) . DS . "public" . DS . "file" . DS . "text.txt" ;
		
		//set $file variable of this class ;
		$this->file = $name_of_file;

		if( ! file_exists( $name_of_file )){//check if file exists	
			
			//create file
			$content = "";
			$fp = fopen($name_of_file ,"wb");
			fwrite($fp,$content);
			fclose($fp);
		} 
		
		//add text to file
		$content = $text;
		$fp = fopen($name_of_file ,"wb");
		fwrite($fp,$content);
		fclose($fp);

	}



	/*
	Interprets the entire text(source_code)

	@param $text the source code that will be interpreted(tokenized and parsed)

	@return mixed nothing if the entire process is successful , false otherwise (if we got an error somewhere)
	*/
	public function process($text)
	{
		/*
			The process for creating a new instruction is:
				a. add new token(s) in the token list(in Lexer.php)
				b. add constants for new tokens in Config.php
				c. add a new comment for parsing grammar (VERSION) in Parser that contains the new instruction
				d. add the new instruction in Instructiune method of Parser to create AST Node from it
				e. create the method of Parser that Parser this new instruction and creates corresponding AST Node
				f. add the checking for instruction type from AST into Interpretor::dispatchStatement()
				g. write the method in Interpretor to execute 
				h. (optionally) write any helper methods for Interpretor method to execute
			Finish	
		*/

		//1.
		//process the source code entirely (no line by line)
		/*The Whole Process:
			Lexer(Tokenizer or Scanner)
			Parser
			Interpreter
		*/


		/************************************************************************
			Creating the Stream of Tokens from source code
			1.tokenize the input
		************************************************************************/
		$tokens = Lexer::tokenize_input($text);
		if( !is_array($tokens) ){//we have an error if is NOT array
			//lexer error
			Error::raise($tokens);
			return false;	
		}
		//show the app big steps in order for me to debug easier(find this constant in Config.php)
		if (SHOW_APP_BIG_STEPS_MESSAGES == true) {
			echo "LEXER: S-a executat cu succes tokenizarea termenilor(lista tokeni generata) <br/>";
		}
		//echo "<pre>";
		//print_r($tokens);
		//echo "</pre>";
		


		/***********************************************************************
			Creating the AST produced by parsing
			2.parse the $tokens or display an error
		************************************************************************/
		$AST = Parser::parse($tokens);
		if(isset($AST) && $AST === false){
			//raise error...
			Error::raise("Cannot generate AST . An error occured in parsing");
			return false;
		}
		//show the app big steps in order for me to debug easier
		if (SHOW_APP_BIG_STEPS_MESSAGES == true) {
			echo "PARSER: S-a executat cu succes detectarea tipurilor de instructiuni(AST generat) <br/>";
		}
		//echo "<pre>";
		//print_r($AST);
		//echo "</pre>";



		/************************************************************************
			Interpreting(our case , directly execute the statements(instructions) generated in AST)
			3.interpret the AST from parser
		************************************************************************/		
		$interpret = Interpretor::Interpret($AST);
		//SymbolTable::dumpTable("dump");	
		//print_r($interpret);
		
		//show the app big steps in order for me to debug easier
		if ((SHOW_APP_BIG_STEPS_MESSAGES == true) && ($interpret==true)) {
			echo "INTERPRETER: S-a executat cu succes executarea instructiunilor(Ordinea instructiunilor si HistoryTableJSON generat) <br/>";
			//HistoryTableOfChanges JSON o sa il verific intr-un alt if , nu tot aici
		}

		//intepret process check
		if($interpret === false) {
			//got error
			return false;
		} else {

			//this return mean that entire app worked correctly, exiting from execution
			//Daca inserez alte module, mut acest return true mai in fata
			return true;
		}




		/************************************************************************
			HTOC generated - fill display info in site
			4.Obtained the HTOC - app now can run -- poate mai pun un pas intermediar aici
		************************************************************************/		
		//poate pun pas intermediar de return true aici - daca HTOC a fost obtinut 






		//NEVER USE FROM NOW ON
		//2.
		//OR process the source code LINE BY LINE
		//$this->processByLine($text);
	}



	/*
	Interprets the entire text(source_code)

	@param $text the source code that will be interpreted(tokenized and parsed)

	@return boolean true if the entire process is successful , false otherwise (if we got an error somewhere)

	Used in submit-check-code part of application(button) that just checks for lexer errors and parser errors(doesn't execute anything here)
	*/
	public function checkCode($text)
	{
		/*check if $text param is empty*/
		if (empty($text)) {
			Error::raise("Nu a fost introdus cod pentru verificare.");
			return false;
		}



		/*
			Creating the Stream of Tokens from source code
			1.tokenize the input
		*/
		$tokens = Lexer::tokenize_input($text);
		if( !is_array($tokens) ){//we have an error if is NOT array
			//lexer error
			Error::raise($tokens);
			return false;	
		}


		/*
			Creating the AST produced by parsing
			2.parse the $tokens or display an error
		*/
		$AST = Parser::parse($tokens);
		if(isset($AST) && $AST === false){
			//raise error...
			Error::raise("Cannot generate AST . An error occured in parsing");
			return false;
		}


		//return true if anything was ok.
		return true;
	}



	/*
	Interprets the current Line From file
	Used in Process() function in this class

	@param $source_text String , The source text string that will processed line by line

	@return Void
	*/
	public function processByLine($source_text)
	{
		//process the source code LineByLine

		$text = trim($source_text);//trim any whitespace from source text , check trim() in php manual
		$textAr = explode("\n", $text);//explode into an array $text variable by \n char
		$textAr = array_filter($textAr, 'trim'); // remove any extra \r characters left behind

		foreach ($textAr as $line) {
		    // processing by line. 
		    //echo $line; echo "<br>";

			//tokenize the input
		    $tokens = Tokenizer::tokenize_input($line);
		    //show the $tokens 
			echo "<pre>";
			print_r($tokens);
			echo "</pre>";
		} 
	}



	/**************
		HELPER FUNCTIONS
	***************/
	//no helpers for now
	//...



}

















?>

