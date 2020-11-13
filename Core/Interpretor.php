<?php

namespace Core;

/*including namespaces*/
/*included in main index.php file*/


/*using namespaces*/
use \Core\Config;
use \Core\Tokenizer;
use \Core\Parser;
use \Core\Memory;
use \Core\Predictor;
use \Core\HistoryTableOfChanges;

//using Helper/helper_functions.php



/*******************************
	Interpretor class
	php ver 5.5.0
	This is the Interpreter/Executor CPU of the cpuBP_app
	This is where all the logic and execution of instructions happens
*******************************/
class Interpretor
{
	/********************************************************************************************************************
		Attributes
	*********************************************************************************************************************/


	//Program counter register
	//default to 0 , for starting the execution at instruction 0;
	public static $PC = 0;

	//Stack pointer register
	//default to 0 , for dumping instruction executed in advance,(<-cred ca deaia, o sa vad)
	public static $SP = 0;

	//Instruction counter register
	//default value is 0, for counting total number of executed instructions
	//IC = instruction counter = Numar_total_instructiuni_executate
	//to be incremented in each statement instruction
	public static $IC = 0;


	//maybe i ll move data memory and program memory in Memory.php module
	//data memory is in Memory.php - not in here

	//Program Memory
	//Form: max 64x16 bits memory(64 fields of 16 bits each) - in conformity with Octissimo.pdf
	//in el incarc AST-ul (seamana ft mult cu AST-ul oricum)
	/*
	(sau poate il fac sub forma de 3 campuri:) 
	1.Adresa_instr_integer, 
	2.Adresa_instructiune_hexa, 
	3.Valoare(instructiunea curenta)(intregul nod din AST aici)
	*/
	public static $program_memory = null;

	//Data memory
	//Form: 64x8 (64 fields x 8 bits each) (in conformity with octissimo)
	//DEFINED AND WORKED WITH IN Memory.php
	public static $data_memory_NOT_USED_MOVED_IN_MEMORY_PHP = null;

	


	/******************************
		Other attributes
	*******************************/
	//the errors attribute
	public $errors = [];

	/*display images attribute for displaying images in frontend interface
	  used by foreach in main View template(index.php)
	*/
	//TO DELETE THIS - from previous app  
	public static $display_images = [];

	/*previously , we had declared the table_variables here . Create a new class to handle the table(SymbolTable)*/

	//the ast from parsing
	public static $AST;


	//this attr is used in display(index.php) to check if execution of all instructions is ready without error
	//this will be set to true inside of HALT instruction(because HALT comes last always in instruction flow)
	//called in HALT(and there will be called from callEndExecutionFunction)
	public static $app_execution_ready = false;


	/********************************************************************************************************************
		Methods
		All the methods of the Interpreter class
	********************************************************************************************************************/

	/*
	gets the errors of this instance(class)
	*/
	public function getErrors()
	{	
		return $this->errors;
	}





	/****************************************************************************************
		Interpretor main CORE methods
	*****************************************************************************************/

	/*
	analizes the ast given as param and dispatch(routes) to proper instructions for executing

	@param $ast the ast built by parser

	@return Boolean true if interpretations ends with success, false otherwise
	*/
	public static function Interpret($AST)
	{
		//1.assigning $ast param to class attribute
		static::$AST = $AST;

		//2.loading the program memory with the $AST
		//(in the future i can do like, dupa cum am lasat comentariu la initializarea attributului $program memory mai sus) :
		// static::$program_memory = Module::convertToProgramMemory($AST);
		// --aceasta functie propusa - Module::convertToProgramMemory($AST) doar va adauga o cheie in array-ul $AST cu numele hex_address_program_counter
		//   care va converti program counter int la hex(sa le tin in amandoua formatele) - fac asa ca sa nu stric functionarea aplicatiei 
		static::$program_memory = $AST;

		//display the program memory on screen (for testing)
		//static::displayProgramMemory();


		if(DEV_DATA_MEMORY_TEST_INSERT_ROWS_MEMORY == true)
		{
		//------------------------------------------------------------------FOR TESTING - codul dintre liniute nu are de a face cu codul din Interpret()
		//call dev function for adding 30 rand numbers in DATA memory
		//cheama functia de adaugare 30 numere in memoria de data - doar de testare -- a se comenta in PRODUCTIE
		//POT FACE ASA SA FIE MAI CLEAN:Config.php: Poti face o constanta enable_DEV si daca e true , apeleaza functia asta inainte de inceperea programului
		//start cod(urmatoarele 5 linii):
		Memory::addToDataMemoryFields_DEV();
		//Memory::displayDataMemory();
		//Memory::displayFieldDataMemory(30); //0x1E e 30 in hexa
		//Memory::displayFieldDataMemory(128); //0x0080 e 128 in hexa
		echo "dev memorie date activat in Interpretor->Interpret() <br>";
		//------------------------------------------------------------------
		}

		$callMethodResponse = static::callAppropiateMethodForExecution();


		//------------------------------------------------------------------
		//after callMethodResponse is finished , we can check the data memory , because execution of app is now complete
		//this is linked with addToDataMemoryFields_DEV() de mai sus - dinainte de variabila $callMethodResponse
		//next:
		//Memory::displayFieldDataMemory(128);
		//-----------------------------------------------------------------


		//start iterating over statements(instructions) and call appropiate method for execution via Program Counter(PC)
		if( $callMethodResponse == false){
			return false;
		} else {
			return true;
		}

	}/*end Interpret method*/




	/*
		display the ast (dump)
		@return Void
	*/
	public static function displayAST()
	{
		echo "<pre>";
		print_r(static::$AST);
		echo "</pre>";
	}	


	/*
		display the program memory (dump)
		@return Void
	*/
	public static function displayProgramMemory()
	{
		echo "<pre>";
		print_r(static::$program_memory);
		echo "</pre>";
	}	




	/*
		calls the Appropiate Method for Execution via the Program Counter
		
		@return boolean, true if completed running succesfully, false otherwise
	*/	
	public static function callAppropiateMethodForExecution()
	{
		//load attr $PC into local var PC
		$PC = static::$PC;
		$length_of_program_memory = count(static::$program_memory);

		//initialize dispatchResponse for returning outside of method true or false
		//suppose that we got a true response(means that function functioned correctly)
		$dispatchResponse = true;

		//set maximum number if executed instructions(la 100.000 de ex, ca sa nu mai moara in while-ul asta)
		$counter_max_runned_instructions = 0;
		$limit_runned_instructions = 50;


		//while Program Counter is in limits of program memory, and NOT outside, dispatch and call instruction where current PC indicates
		while (($PC>=0)  &&  ($PC <= $length_of_program_memory)) {

			//chemam functia pe care o avem de executat la cheia program_counter(cheia curenta, de ex 0)
			$dispatchResponse  =  static::dispatchStatement($PC);

			//update local var PC(donnot forget)
			$PC = static::$PC;

			//conditie de iesit din bucla obligatoriu daca s-a ajuns la 100.000 de rulari una dupa alta(sa nu mai inghete while-ul)
			//$counter_max_runned_instructions++;
			//if( $counter_max_runned_instructions >= $limit_runned_instructions){
			//	echo "S-a atins limita de nr max de instructiuni rulate in callAppropiateMethodForExecution,Interpretor";
			//	break;
			//}

			//temporary limit for computer to not FREEZE
			//if($PC >2){
			//	echo "S-a atins limita de apeluri in callAppropiateMethodForExecution in Interprter, linia 188";
			//	break;
			//}

			//return true or false and break
			if ($dispatchResponse == false) {
				//return false;//exit out of this method callAppropiateMethodForExecution
				break;
			} 
			//else {//VEZI CU ELSE ASTA CE FACI - ar trebui sters
			//	return true;
			//}
		}

		//for debugging of stopping execution:
		//echo $length_of_program_memory;
		//echo static::$PC;
		//echo $PC;



		//if $PC outside of program , give an error, something is wrong
		if ( ($PC < 0)  &&  ($PC > count($length_of_program_memory)) ) {
			//Error::raise('something is wrong with program counter');
			return false;
		}


		//return true or false and break
		if ($dispatchResponse == false) {
			return false;//exit out of this method callAppropiateMethodForExecution
		} else {
			return true;//continue running
		}


		//si in functia dispatch statement($atKey_PC)
		//voi avea un if else ca la dispatch statement care incarca numele de ex MOV_STMT si cheama el functia MOV_STMT
		//si in MOV_STMT, JMP_STMT, aceste functii vor schimba PC(program counterul) toate il vor updata
	}





	/*
		This check what type of instruction is it passed in param and call the apropiate execution for it
		
		@param $PC_key(Program Counter Key) - cheia dupa care trebuie preluat nodul din memoria de program si executat

		@return mixed false if there was an error in interpretation, true otherwise
	*/
	public static function dispatchStatement($PC_key)
	{
		//get the program memory row(cell) - (program memory Node) from address indicated at $PC_key
		$current_program_memory_row = static::$program_memory[$PC_key];
		//static::displayProgramMemory();
		//echo "<pre>";  print_r($current_program_memory_row);  echo "</pre>";

		//set to_return switching variable here to true, it will change value to false in the foreach
		$to_return = true;

		//get current instruction type
		$instruction = $current_program_memory_row;
		$instruction_type = $instruction['statement_type'];



		//In this if--elif block we route the current instruction to handle to corresponding instruction to execute
		if($instruction_type == "MOV_STMT") {//if detected instruction is - Move value to register
			$to_return = static::MOV_STMT_INSTRUCTION($instruction);
		}
		else if($instruction_type == "JMP_STMT"){//if detected instruction is JMP- JUMP to program memory row
			$to_return = static::JMP_STMT_INSTRUCTION($instruction);
		}
		else if($instruction_type == "LABEL_STMT"){//if detected instruction is LABEL- incremnt program counter by +1
			$to_return = static::LABEL_STMT_INSTRUCTION($instruction);
		}
		else if($instruction_type == "JNZ_STMT"){//if detected instruction is JNZ
			$to_return = static::JNZ_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "JPZ_STMT"){//if detected instruction is JPZ
			$to_return = static::JPZ_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "JNC_STMT"){//if detected instruction is JNC
			$to_return = static::JNC_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "JPC_STMT"){//if detected instruction is JPC
			$to_return = static::JPC_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "JNN_STMT"){//if detected instruction is JNN
			$to_return = static::JNN_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "JPN_STMT"){//if detected instruction is JPN
			$to_return = static::JPN_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "JNO_STMT"){//if detected instruction is JNO
			//NU FOLOSESC FLAG OVERFLOW -> nu mai continua cu implementarea Predictor si Javascript pe JP cu overflow
			//(OF(overflow flag) used in signed arithmetic - my app uses just unsigned arithmetic - vezi link la ADD STMT description)
			$to_return = static::JNO_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "JPO_STMT"){//if detected instruction is JPO
			//NU FOLOSESC FLAG OVERFLOW -> nu mai continua cu implementarea Predictor si Javascript pe JP cu overflow
			//(OF(overflow flag) used in signed arithmetic - my app uses just unsigned arithmetic - vezi link la ADD SMT description)
			$to_return = static::JPO_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "STR_STMT"){//if detected instruction is STR
			$to_return = static::STR_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "LDR_STMT"){//if detected instruction is LDR
			$to_return = static::LDR_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "ADD_STMT"){//if detected instruction is ADD
			$to_return = static::ADD_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "ADC_STMT"){//if detected instruction is ADC
			$to_return = static::ADC_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "SUB_STMT"){//if detected instruction is SUB
			$to_return = static::SUB_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "SBC_STMT"){//if detected instruction is SBC
			$to_return = static::SBC_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "AND_STMT"){//if detected instruction is AND
			$to_return = static::AND_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "ORR_STMT"){//if detected instruction is ORR
			$to_return = static::ORR_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "XOR_STMT"){//if detected instruction is XOR
			$to_return = static::XOR_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "CMP_STMT"){//if detected instruction is CMP
			$to_return = static::CMP_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "INV_STMT"){//if detected instruction is INV
			$to_return = static::INV_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "SHL_STMT"){//if detected instruction is SHL
			$to_return = static::SHL_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "SHR_STMT"){//if detected instruction is SHR
			$to_return = static::SHR_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "ROL_STMT"){//if detected instruction is ROL
			$to_return = static::ROL_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "ROR_STMT"){//if detected instruction is ROR
			$to_return = static::ROR_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "CLZ_STMT"){//if detected instruction is CLZ
			$to_return = static::CLZ_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "CLC_STMT"){//if detected instruction is CLC
			$to_return = static::CLC_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "CLN_STMT"){//if detected instruction is CLN
			$to_return = static::CLN_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "NOP_STMT"){//if detected instruction is NOP
			$to_return = static::NOP_STMT_INSTRUCTION($instruction);
		}	
		else if($instruction_type == "HALT_STMT"){//if detected instruction is HALT- stop execution of cpu
			$to_return = static::HALT_STMT_INSTRUCTION($instruction);
		}
		else{
			//inca nu am detectat instructiunea(pentru ca nu are inca functie de la parsare scrisa), oprim executia programului aici 
			//ca sa nu ruleze la infinit(while-ul din callAppropiateMethodForExecution)
			//pe viitor cheama functie care face fix asta : call static::NOT_DETECTED_INSTRUCTION();


			//PSH - PUSH R0 - nu a fost scrisa(nu e nevoie de ea) - scrierea acesteia in codul sursa va directiona la aceasta eroare
			//POP - POP  R0 - nu a fost scrisa(nu e nevoie de ea) - scrierea acesteia in codul sursa va directiona la aceasta eroare

			Error::raise("Eroare Interpretare/Asamblare : Instructiunea nu exista in limbajul cpuBP. se opreste functionarea cpuBP - eroare afisata in dispatchStatement,Interpreter.php(nu uita de HALT la sf instructiunilor)");
			$to_return = false;
		}



		//A TREBUIT PUS return true; TRE PUS LA FINAL IN TOATE FUNCTIILE DE EXECUTAT 
		//after iteration in the if--else if block after $current_program_memory_row , if all instructions returned true , return true in this method too
		if (isset($to_return) && $to_return === true) {
			return true;
		}

	}/*end dispatchStatement*/







	/*
		function that is called only by HALT statement at the end of execution of all statements
		-
		Used to do some final finishing touches(like display HTOC, transforming HTOC into JSON , setting some variables
		 to let know the cpuBP app that execution of all statements is ready , and so on)

		@return void, returns nothing
	*/
	public static function callEndExecutionFunction()
	{
		//echo "callend";


		//set app_execution_ready attr of class to true(because execution is finished successfully)
		static::$app_execution_ready = true;


		//display Data Memory for testing of interchimbare algo(select sort) and bubble sort
		//...->
		//echo "Memory data displayed in Interpretor->callEndExecutionFunction";
		//Memory::displayDataMemory();


		//1.
		//after all execution display the HTOC for dev purposes
		//...->
		//echo "<br>" . "HTOC afisat din HALT->callEndExecutionFunction in Interpretor.php" . "<br>";
		//HistoryTableOfChanges::displayHTOC();
	}




	/****************************************************************************************
		END Interpretor main CORE Methods
	*****************************************************************************************/

























	/****************************************************************************************
	****************************************************************************************
		Interpretor functions from parsing

		Here are defined methods for Interpreter for
		direct executing ( step by step execution)
		--------------------------------------------------------------------

		FOR ME,GEO,TO UNDERSTAND WHAT LIES UNDER THE HOOD OF PIPELINE: "FDEMW"
		The 5 stages of Pipeline and what they do:(MIPS Five Stages) - google it
		1.Fetch(IF(Instruction Fetch) of F(Fetch))
			-read next instruction from memory(program memory) , increment program counter

		2.Decode(ID or D)
			-decode the instruction
			-read register operands, compute branch target

		3.Execute(EX or X or E)
			-execute arithmetic/resolve branches  (/perform execution of instruction, pusa de mine aceasta paranteza)		

		4.Memory(MEM or M)
			-perform load/store accesses to memory, take branches

		5.Write back(WB or W)
			-write arithmetic results to register file		

		//FETCH
		//DECODE
		//EXECUTE
		//MEMORY
		//WRITEBACK

	****************************************************************************************
	*****************************************************************************************/

	/*
		This is a generic model of how an instruction execution should work
		------------
		Acesta este un model generic care arata cum ar trebui sa functioneze executia unei instructiuni
	*/
	public static function GENERIC_STMT_INSTR_MODEL()
	{
		//0.show execution message only if this constant set in Config.php is set to TRUE


		//BUILD EXECUTION :
		//1.define variables for local use from param 


		//START EXECUTING THIS INSTRUCTION
		//  2.Determine what type of  instruction is :    type1  or    type2
		//  All the execution of this instr happens in this IF/else statement
		//OR....
		//  2.Just execute this instruction


		//3.Add node to HistoryTableOfChanges for Javascript dynamic display


		//3.1 increment variable that count number of instructions executed until now


		//4.increment Program Counter and prepare cpuBP for next instruction		


		//5.this method executed successfully - put return true;
		//this return must be put at every instruction to be executed
	}







	/*
		MOV instructiune de executat/interpretat cpu
		Forma(e):
		MOV R2,0x7D     - Pune valoarea 0x7D in reg R2
		MOV R111 , R2   - Pune valoarea continuta in R2 in R111(R111 de validat )

		//de facut:
		//DONE(in Memory.php) - valideaza R111(sa fie registru, nu balarie) cu functie externa in Modul Memory.php
		//DONE(in Memory.php) -valideaza valoarea hexa(sa fie maxim pe 8 biti / 0xff ) cu functie externa modul memory sau fctie interna helper aici , vad eu
		//DONE(in Memory.php) -fa metoda de incarcare registru cu valoare in modul Memory.php( setGeneralRegister, cum vrei tu sa o numesti).
		//DONE                -functia anterior facuta va merge si cand ai MOV R111 , R2 -> preiei valoare R2 si apelezi acceasi functie LoadRegisterWithValue
	*/
	public static function MOV_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare MOV  <br> ";
		}
		
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_first_register  =  $instruction['params']['name_of_first_register'];
		$second_value_register = (!empty($instruction['params']['second_value_register']))  ?  $instruction['params']['second_value_register']  :  null;
		$second_value_hexa = (!empty($instruction['params']['second_value_hexa']))  ?  ($instruction['params']['second_value_hexa'])  :  null;
		//var_dump($second_value_hexa);


		//START EXECUTING THIS INSTRUCTION

		//2.Determine what type of MOV instruction is :    MOV R2,0x7D   or    MOV R111 , R2 (MOV R1,R2)
		//All the execution of this instr happens in this IF/else statement
		if($second_value_register != null){ //MOV instr type is: MOV R1,R2

			//checking the first register name in interval R0-R14
			$checkFirstReg = Memory::helper_checkGeneralRegisterName($name_of_first_register);
			if ($checkFirstReg == false) {
				Error::raise("Eroare Interpretare/Executare (pe linia" . $instruction['line_no'] . "): " . $name_of_first_register . " nu e in intervalul  registrilor generali R0-R14");
				return false;
			}


			//getting the second register
			$got_second_general_register = Memory::getGeneralRegister($second_value_register);
			if ($got_second_general_register == false) {
				return false;
			}

			//now i got the second register full array
			$second_register_hexa_value = $got_second_general_register['hexa_value'];

			//Perform current MOV instr - setting the general register from first param wih given value FROM SECOND REGISTER
			$execute_set_register = Memory::setGeneralRegister($name_of_first_register, $second_register_hexa_value);
			if($execute_set_register == false){
				//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
				return false;
			}



		} else { //MOV instr type is: MOV R2,0x7D

			//perform current MOV instr - setting the general register from first param wih given value
			//setGeneralRegister method inside Memory class module performs all the Validations, daca ai nevoie de validari , le ai modulare acolo, doar le apelezi
			$execute_set_register = Memory::setGeneralRegister($name_of_first_register, $second_value_hexa);

			if($execute_set_register == false){
				//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
				return false;
			}

			//just for testing, i have return true jos de tot anyway
			//if(is_array($execute_set_register)){
			//	echo "setarea s-a facut cu succes";
			//}

		}



		//2.6 Build the HTOC node for this statement instruction(MOV)
		//Model full in MOV statement
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//no jump details for this statement
		//...
		//action for this statement:
		//...
		//for MOV statement, update register (json navigare friendly , daca ar fii sa faci)
		$HTOC_action = [
			'HTOC_update_general_register' => [
				$name_of_first_register => Memory::getGeneralRegister($name_of_first_register)
			]
		];
		//sau direct DOAR pentru tabel ordine executie : (afisare tabel ordine exec friendly , just show)
		/*
		$HTOC_action = [
				//camp  = valoare(e valabil cam la toate)
				$name_of_first_register => Memory::getGeneralRegister($name_of_first_register)['hexa_value']
		];
		*/
	

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, "" , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing , nu urmatorul , ci asta
		//HistoryTableOfChanges::displayHTOC();

		//end HTOC code inside of this instruction
		//donnot forget to put static::$IC++ like below in all instructions


		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed
		//just DOWN HERE i'll set return true, in restul corpului functiei voi seta doar return false-ul.
		return true;
	}//5 new lines after each definition of instruction





	/*
		JMP instructiune de executat/interpretat cpu
		Forma(e):
		JMP 0x1111      - JUMP absolut(jump neconditionat)(am doar jumpuri absolute, nu relative) la adresa hexa indicata in memoria de program
		JMP _to_label   - JUMP absolut la instructiunea label data in parametru

	*/
	public static function JMP_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare JMP  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";


		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$address_to_jump_to = (isset($instruction['params']['address_to_jump_to']))  ?  $instruction['params']['address_to_jump_to']  :  null;
		$label_to_jump_to = (isset($instruction['params']['label_to_jump_to']))  ?  ($instruction['params']['label_to_jump_to'])  :  null;



		//START EXECUTING THIS INSTRUCTION

		//set temporary program counter to jump to , this will be set in both next if/else statement
		$pc_for_jump = 0;

		//2.Determine what type of JMP instruction is :    JMP 0x1111   or    JMP _to_label
		//All the execution of this instr happens in this IF/else statement
		if ($address_to_jump_to != null) { //JMP 0x1111
			
			//convert hexa value into integer value
			$integer_value_to_jump_to = hexdec(substr($address_to_jump_to, 2)) ;

			//validating if integer number is in interval of program memory
			$check_program_memory_key_exists = static::helper_checkProgramMemoryKeyExists($integer_value_to_jump_to);

			if($check_program_memory_key_exists != true){ 
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Acest JUMP sare in afara memoriei de program.");
				return false;
			} 

			//assign $pc_for_jump 
			$pc_for_jump = $integer_value_to_jump_to;


		} else { //JMP _to_label

			//check and validate if label for jump exists in program memory
			$check_label_stmt_in_program_memory = static::helper_checkIfLabelStatementExistsInProgramMemory($label_to_jump_to);

			if ($check_label_stmt_in_program_memory == false) {
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Label-ul dat ca parametru la JMP nu a fost gasit in codul sursa pentru a efectua saltul.");
				return false;
			}

			//assign $pc_for_jump 
			$pc_for_jump = $check_label_stmt_in_program_memory;

		}



		//2.6 Build the HTOC node for this statement instruction(JMP)
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = ""; //no jump details for this statement
		//action for this statement:
		//...
		//for JMP statement, no action description
		$HTOC_action = "";
		/*$HTOC_action = [
			'HTOC_update_general_register' => [
				$name_of_first_register => Memory::getGeneralRegister($name_of_first_register)
			]
		]; */	

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, "" , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing , nu urmatorul , ci asta

		//end HTOC code inside of this instruction
		//donnot forget to put static::$IC++ like below in all instructions


		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		//static::$PC++;//fake pana una alta sa nu moara programul
		static::$PC = $pc_for_jump;

		//this method executed successfully
		//this return must be put at every instruction to be executed - always last(in rest , doar return false-uri)
		return true; 
	}





	/*
		LABEL instructiune de executat/interpretat cpu
		Forma(e):
		LABEL     - la aceste label-uri ajung jumpurile(ex label forma: _label_for_Jump_to_1:)
	*/
	public static function LABEL_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare LABEL  <br> ";
		}


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = ""; //no jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		$HTOC_action = "";
		/*$HTOC_action = [
			'HTOC_update_general_register' => [
				$name_of_first_register => Memory::getGeneralRegister($name_of_first_register)
			]
		]; */	

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, "" , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)


		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//increment Program Counter and prepare cpuBP for next instruction
		//label STMT simply increments the PC and moves on
		static::$PC++;

		//this method executed successfully
		//this return must be put at every instruction to be executed - always last(in rest , doar return false-uri)
		return true; 
	}







	/*
		JNZ instructiune de executat/interpretat cpu
		Explanation:
		JNZ -Jump if not zero (if Z=0 , if flag zero not set)
		Forma(e):
		JNZ 0x1111    - JUMP if not zero - JUMP conditionat(jump conditionat de flagZ) la adresa hexa indicata in memoria de program
		JNZ _to_label - JUMP if not zero - JUMP conditionat flagZ la instructiunea label data in parametru
	*/
	public static function JNZ_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare JNZ  <br> ";
		} 
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$address_to_jump_to = (isset($instruction['params']['address_to_jump_to']))  ?  $instruction['params']['address_to_jump_to']  :  null;
		$label_to_jump_to = (isset($instruction['params']['label_to_jump_to']))  ?  ($instruction['params']['label_to_jump_to'])  :  null;



		//START EXECUTING THIS INSTRUCTION

		//set temporary program counter to jump to , this will be set in both next if/else statement
		$pc_for_jump = 0;

		//2.Determine what type of JNZ instruction is :    JNZ 0x1111   or    JNZ _to_label
		//All the execution of this instr happens in this IF/else statement
		if ($address_to_jump_to != null) { //JNZ 0x1111
			
			//convert hexa value into integer value
			$integer_value_to_jump_to = hexdec(substr($address_to_jump_to, 2)) ;

			//validating if integer number is in interval of program memory
			$check_program_memory_key_exists = static::helper_checkProgramMemoryKeyExists($integer_value_to_jump_to);

			if($check_program_memory_key_exists != true){ 
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Acest JUMP(JNZ) sare in afara memoriei de program.");
				return false;
			} 

			//assign $pc_for_jump 
			$pc_for_jump = $integer_value_to_jump_to;


		} else { //JNZ _to_label

			//check and validate if label for jump exists in program memory
			$check_label_stmt_in_program_memory = static::helper_checkIfLabelStatementExistsInProgramMemory($label_to_jump_to);

			if ($check_label_stmt_in_program_memory == false) {
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Label-ul dat ca parametru la jump-ul JNZ nu a fost gasit in codul sursa pentru a efectua saltul.");
				return false;
			}

			//assign $pc_for_jump 
			$pc_for_jump = $check_label_stmt_in_program_memory;

		}


		//2.1 set a temp pc for this JP implementation
		$temp_pc = 0;

		//2.2 set a var branch_taken (branch_taken_jp or jump_taken) inside this JP for validating the prediction algorithm
		$jump_taken = "not_taken";

		//3.Temporary pc for jump is ready 
		//check if flagZ not set(Z=0)-JNZ and set/prepare the final program counter value according to octissimo
		if (Memory::getFlag('z') === 0) { //sau var_dump(Memory::$z_flag);
			//if Z=0 , , then jump to $pc_for_jump that was computed earlier
			$temp_pc = $pc_for_jump;

			//this means that this jump was taken(conditia acestui jump a fost indeplinita) 
			$jump_taken = "taken";
		} else {
			//otherwise, just increment main program counter and Jump there
			$inc_pc = static::$PC;
			$inc_pc++;
			$temp_pc = $inc_pc;

			//this means that this jump was NOT taken(conditia acestui jump NU a fost indeplinita) 
			$jump_taken = "not_taken";
		}


		
		//3.5 START PREDICTOR CODE inside this JP -- JNZ
		/*
			pot pune tot acest cod intr-o functie separata(ca sa nu mai ia spatiu in fiecare JNZ) : 
				a. pentru fiecare jump in parte : jnz_prediction($jump_taken, $pc)
				b. global pentru toate jumpurile(daca) codul seamana: global_jumps_prediction($jump_taken, $pc)
				VEZI TU CUM FACI(poti sa il lasi si aici , nu ma incurca)
		*/
		//do not forget to set variable $jump_taken in the other jumps when implementing predictor code
		//jump_taken variable comes from the jp stmt code : 2 possible values: "taken" or "not_taken"
		//store bp mode in a variable for easier reading
		$bp_mode = 	$_POST['branch_prediction_mode'];
		if ($bp_mode == 0) { 
			// mod predictie STATIC NOT-TAKEN(value 0)
			//code for STATIC NOT-TAKEN prediction
			if (SHOW_PREDICTOR_MESSAGES_DEV){ //constant to show messages in dev in Config.php
				echo "exec bp mode 0 - Static not taken <br>";
			}

			//template cod predictie
			//make the predictor predict if branch is taken or not
			$prediction = Predictor::predictStaticNotTaken(); //simply returns taken or not_taken - in this case returns no 
			if ($prediction == $jump_taken) {
				//prediction was good
				Predictor::predictStaticNotTaken_response("good_prediction");
			} else{
				//prediction was bad, missed
				Predictor::predictStaticNotTaken_response("miss_prediction");
			}

			if (SHOW_PREDICTOR_MESSAGES_DEV){ 
				echo Predictor::getCounterGoodPredictions() . " " ;
				echo Predictor::getCounterMissPredictions() . " " ;
			}
			//end template cod predictie

		} 
		else if ($bp_mode == 1) { 
			// mod predictie STATIC TAKEN(value 1)
			//code for STATIC TAKEN prediction - la fel ca not taken , doar ca asta raspunde cu DA
			if (SHOW_PREDICTOR_MESSAGES_DEV){ //constant to show messages in dev in Config.php
				echo "exec bp mode 1 - Static taken <br>";
			}

			//template cod predictie
			//make the predictor predict if branch is taken or not
			$prediction = Predictor::predictStaticTaken(); //simply returns taken or not_taken - in this case returns yes 
			if ($prediction == $jump_taken) {
				//prediction was good
				Predictor::predictStaticTaken_response("good_prediction");
			} else{
				//prediction was bad, missed
				Predictor::predictStaticTaken_response("miss_prediction");
			}

			if (SHOW_PREDICTOR_MESSAGES_DEV){ 
				echo Predictor::getCounterGoodPredictions() . " " ;
				echo Predictor::getCounterMissPredictions() . " " ;
			}
			//end template cod predictie 
		}
		else if ($bp_mode == 2) { 
			// mod predictie DYNAMIC 1BIT PREDICTOR(value 2)
			//code for DYNAMIC 1BIT PREDICTOR prediction
			if (SHOW_PREDICTOR_MESSAGES_DEV){ //constant to show messages in dev in Config.php
				echo "exec bp mode 2- Dynamic 1bit predictor <br>";
			}

			//template cod predictie
			//make the predictor predict if branch is taken or not
			$prediction = Predictor::predictDynamic1Bit(static::$PC , $jump_taken); //returns taken or not_taken - in this case is dynamic 1 bit 
			if ($prediction == $jump_taken) {
				//prediction was good
				Predictor::predictDynamic1bit_response("good_prediction");
			} else{
				//prediction was bad, missed
				Predictor::predictDynamic1bit_response("miss_prediction");
			}

			if (SHOW_PREDICTOR_MESSAGES_DEV){ 
				echo Predictor::getCounterGoodPredictions() . " " ;
				echo Predictor::getCounterMissPredictions() . " " ;
			}
			//end template cod predictie 

		}	
		else if ($bp_mode == 3) { 
			// mod predictie DYNAMIC 2BIT PREDICTOR(value 3)
			//code for DYNAMIC 2BIT PREDICTOR prediction
			if (SHOW_PREDICTOR_MESSAGES_DEV){ //constant to show messages in dev in Config.php
				echo "exec bp mode 3- Dynamic 2bit predictor <br>";
			}

			//template cod predictie
			//make the predictor predict if branch is taken or not
			$prediction = Predictor::predictDynamic2Bit(static::$PC , $jump_taken); //returns taken or not_taken - in this case is dynamic 2 bit 
			if ($prediction == $jump_taken) {
				//prediction was good
				Predictor::predictDynamic2bit_response("good_prediction") . " " ;
			} else{
				//prediction was bad, missed
				Predictor::predictDynamic2bit_response("miss_prediction") . " " ;
			}

			if (SHOW_PREDICTOR_MESSAGES_DEV){ 
				echo Predictor::getCounterGoodPredictions() . " " ;
				echo Predictor::getCounterMissPredictions() . " " ;
			}
			//end template cod predictie 

		}				
		else {
			//cover other cases ,  throw error and exit instruction execution
			Error::raise("Eroare Executare (din jumpul de pe linia".$instruction['line_no']."): Modul de predictie ". $bp_mode ." nu exista.");
			return false;
		}
		//END PREDICTOR CODE(inside Jumps)


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = [
			'HTOC_jump_details' => [
				"jump_taken"       => "jump " . $jump_taken , //jump taken  OR  jump not_taken
				"good_prediction"  =>  Predictor::$good_prediction, //GP(good prediction) information just about this Jump statement
				"miss_prediction"  =>  Predictor::$miss_prediction, //MP(miss prediction) information just about this Jump statement
				"good_predictions_total" => Predictor::getCounterGoodPredictions() ,
				"miss_predictions_total" => Predictor::getCounterMissPredictions() 
			]
		]; 

		//action for this statement:
		//...
		//for this statement, no action description
		$HTOC_action = "";
		/*
		$HTOC_action = [
			'HTOC_update' => [
 				$name_of_first_register => Memory::getGeneralRegister($name_of_first_register)
			]
		]; 
		*/

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//4.increment Program Counter and prepare cpuBP for next instruction
		static::$PC = $temp_pc;

		//this method executed successfully
		//this return must be put at every instruction to be executed - always last(in rest , doar return false-uri)
		return true; 
	}







	/*
		JPZ instructiune de executat/interpretat cpu
		Explanation:
		JPZ -Jump if zero (if Z=1 , if flag zero IS set)
		Forma(e):
		JPZ 0x1111    - JUMP if zero - JUMP conditionat(jump conditionat de flagZ) la adresa hexa indicata in memoria de program
		JPZ _to_label - JUMP if zero - JUMP conditionat flagZ la instructiunea label data in parametru
	*/
	public static function JPZ_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare JPZ  <br> ";
		} 
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$address_to_jump_to = (isset($instruction['params']['address_to_jump_to']))  ?  $instruction['params']['address_to_jump_to']  :  null;
		$label_to_jump_to = (isset($instruction['params']['label_to_jump_to']))  ?  ($instruction['params']['label_to_jump_to'])  :  null;



		//START EXECUTING THIS INSTRUCTION

		//set temporary program counter to jump to , this will be set in both next if/else statement
		$pc_for_jump = 0;

		//2.Determine what type of JPZ instruction is :    JPZ 0x1111   or    JPZ _to_label
		//All the execution of this instr happens in this IF/else statement
		if ($address_to_jump_to != null) { //JPZ 0x1111
			
			//convert hexa value into integer value
			$integer_value_to_jump_to = hexdec(substr($address_to_jump_to, 2)) ;

			//validating if integer number is in interval of program memory
			$check_program_memory_key_exists = static::helper_checkProgramMemoryKeyExists($integer_value_to_jump_to);

			if($check_program_memory_key_exists != true){ 
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Acest JUMP(JPZ) sare in afara memoriei de program.");
				return false;
			} 

			//assign $pc_for_jump 
			$pc_for_jump = $integer_value_to_jump_to;


		} else { //JPZ _to_label

			//check and validate if label for jump exists in program memory
			$check_label_stmt_in_program_memory = static::helper_checkIfLabelStatementExistsInProgramMemory($label_to_jump_to);

			if ($check_label_stmt_in_program_memory == false) {
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Label-ul dat ca parametru la jump-ul JPZ nu a fost gasit in codul sursa pentru a efectua saltul.");
				return false;
			}

			//assign $pc_for_jump 
			$pc_for_jump = $check_label_stmt_in_program_memory;

		}


		//2.1 set a temp pc for this JP implementation
		$temp_pc = 0;

		//2.2 set a var branch_taken (branch_taken_jp or jump_taken) inside this JP for validating the prediction algorithm
		$jump_taken = "not_taken";

		//3.Temporary pc for jump is ready 
		//check if flagZ IS set(Z=1)-JPZ and set/prepare the final program counter value according to octissimo
		if (Memory::getFlag('z') === 1) { //sau var_dump(Memory::$z_flag);
			//if Z=1 , , then jump to $pc_for_jump that was computed earlier
			$temp_pc = $pc_for_jump;

			//echo "JPZ sare daca z=1 la adresa <br>";

			//this means that this jump was taken(conditia acestui jump a fost indeplinita) 
			$jump_taken = "taken";
		} else {
			//otherwise, just increment main program counter and Jump there
			$inc_pc = static::$PC;
			$inc_pc++;
			$temp_pc = $inc_pc;

			//echo "JPZ sare la PC+1 <br>";

			//this means that this jump was NOT taken(conditia acestui jump NU a fost indeplinita) 
			$jump_taken = "not_taken";
		}
		//Memory::setFlag('z',1);


		//3.5 START COD PREDICTIE pentru JPZ
		//doar cu 1 singura functie in toate jump-urile(increased readability si nu umplu asa rau codul JUMP-urilor)
		//predictia e locala(pentru fiecare jump in parte de la un PC) , nu globala
		//daca ai nevoie ca predictia sa nu mai fie pe un singur JP(sa nu mai fie locala) de la un PC la un moment dat:
		// predictie globala -- dai "jpz"(sau in ce jp e acum)(dai un param generic) ca param in loc de static::$PC 
		// predictie locala  -- dai ca param static::$PC
		static::global_jumps_prediction(static::$PC, $jump_taken);


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = [
			'HTOC_jump_details' => [
				"jump_taken"       => "jump " . $jump_taken , //jump taken  OR  jump not_taken
				"good_prediction"  =>  Predictor::$good_prediction, //GP(good prediction) information just about this Jump statement
				"miss_prediction"  =>  Predictor::$miss_prediction, //MP(miss prediction) information just about this Jump statement
				"good_predictions_total" => Predictor::getCounterGoodPredictions() ,
				"miss_predictions_total" => Predictor::getCounterMissPredictions() 
			]
		]; 

		//action for this statement:
		//...
		//for this statement, no action description
		$HTOC_action = "";

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//4.increment Program Counter and prepare cpuBP for next instruction
		static::$PC = $temp_pc;

		//this method executed successfully
		//this return must be put at every instruction to be executed - always last(in rest , doar return false-uri)
		return true; 
	}







	/*
		JNC instructiune de executat/interpretat cpu
		Explanation:
		JNC -Jump if not carry (if C=0 , if flag carry not set)
		Forma(e):
		JNC 0x1111    - JUMP if not carry - JUMP conditionat(jump conditionat de flagC) la adresa hexa indicata in memoria de program
		JNC _to_label - JUMP if not carry - JUMP conditionat flagC la instructiunea label data in parametru
	*/
	public static function JNC_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare JNC  <br> ";
		} 
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$address_to_jump_to = (isset($instruction['params']['address_to_jump_to']))  ?  $instruction['params']['address_to_jump_to']  :  null;
		$label_to_jump_to = (isset($instruction['params']['label_to_jump_to']))  ?  ($instruction['params']['label_to_jump_to'])  :  null;



		//START EXECUTING THIS INSTRUCTION

		//set temporary program counter to jump to , this will be set in both next if/else statement
		$pc_for_jump = 0;

		//2.Determine what type of JNC instruction is :    JNC 0x1111   or    JNC _to_label
		//All the execution of this instr happens in this IF/else statement
		if ($address_to_jump_to != null) { //JNC 0x1111
			
			//convert hexa value into integer value
			$integer_value_to_jump_to = hexdec(substr($address_to_jump_to, 2)) ;

			//validating if integer number is in interval of program memory
			$check_program_memory_key_exists = static::helper_checkProgramMemoryKeyExists($integer_value_to_jump_to);

			if($check_program_memory_key_exists != true){ 
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Acest JUMP(JNC) sare in afara memoriei de program.");
				return false;
			} 

			//assign $pc_for_jump 
			$pc_for_jump = $integer_value_to_jump_to;


		} else { //JNC _to_label

			//check and validate if label for jump exists in program memory
			$check_label_stmt_in_program_memory = static::helper_checkIfLabelStatementExistsInProgramMemory($label_to_jump_to);

			if ($check_label_stmt_in_program_memory == false) {
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Label-ul dat ca parametru la jump-ul JNC nu a fost gasit in codul sursa pentru a efectua saltul.");
				return false;
			}

			//assign $pc_for_jump 
			$pc_for_jump = $check_label_stmt_in_program_memory;

		}


		//2.1 set a temp pc for this JP implementation
		$temp_pc = 0;

		//2.2 set a var branch_taken (branch_taken_jp or jump_taken) inside this JP for validating the prediction algorithm
		$jump_taken = "not_taken";

		//3.Temporary pc for jump is ready 
		//check if flagC not set(C=0)-JNC and set/prepare the final program counter value according to octissimo
		if (Memory::getFlag('c') === 0) {
			//if C=0 , , then jump to $pc_for_jump that was computed earlier
			$temp_pc = $pc_for_jump;

			//echo "JNC sare daca c=0 la adresa <br>"; //for testing

			//this means that this jump was taken(conditia acestui jump a fost indeplinita) 
			$jump_taken = "taken";
		} else {
			//otherwise, just increment main program counter and Jump there
			$inc_pc = static::$PC;
			$inc_pc++;
			$temp_pc = $inc_pc;

			//echo "JNC sare la PC+1 <br>";

			//this means that this jump was NOT taken(conditia acestui jump NU a fost indeplinita) 
			$jump_taken = "not_taken";
		}
		//Memory::setFlag('c',1);


		//3.5 START COD PREDICTIE pentru JNC
		//doar cu 1 singura functie in toate jump-urile(increased readability si nu umplu asa rau codul JUMP-urilor)
		//predictia e locala(pentru fiecare jump in parte de la un PC) , nu globala
		//daca ai nevoie ca predictia sa nu mai fie pe un singur JP(sa nu mai fie locala) de la un PC la un moment dat:
		// predictie globala -- dai "jnc"(sau in ce jp e acum)(dai un param generic) ca param in loc de static::$PC 
		// predictie locala  -- dai ca param static::$PC
		static::global_jumps_prediction(static::$PC, $jump_taken);


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = [
			'HTOC_jump_details' => [
				"jump_taken"       => "jump " . $jump_taken , //jump taken  OR  jump not_taken
				"good_prediction"  =>  Predictor::$good_prediction, //GP(good prediction) information just about this Jump statement
				"miss_prediction"  =>  Predictor::$miss_prediction, //MP(miss prediction) information just about this Jump statement
				"good_predictions_total" => Predictor::getCounterGoodPredictions() ,
				"miss_predictions_total" => Predictor::getCounterMissPredictions() 
			]
		]; 

		//action for this statement:
		//...
		//for this statement, no action description
		$HTOC_action = "";

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)


		//4.increment Program Counter and prepare cpuBP for next instruction
		static::$PC = $temp_pc;

		//this method executed successfully
		//this return must be put at every instruction to be executed - always last(in rest , doar return false-uri)
		return true; 
	}







	/*
		JPC instructiune de executat/interpretat cpu
		Explanation:
		JPC -Jump if carry (if C=1 , if flag carry IS set)
		Forma(e):
		JPC 0x1111    - JUMP if carry - JUMP conditionat(jump conditionat de flagC) la adresa hexa indicata in memoria de program
		JPC _to_label - JUMP if carry - JUMP conditionat flagC la instructiunea label data in parametru
	*/
	public static function JPC_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare JPC  <br> ";
		} 
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$address_to_jump_to = (isset($instruction['params']['address_to_jump_to']))  ?  $instruction['params']['address_to_jump_to']  :  null;
		$label_to_jump_to = (isset($instruction['params']['label_to_jump_to']))  ?  ($instruction['params']['label_to_jump_to'])  :  null;



		//START EXECUTING THIS INSTRUCTION

		//set temporary program counter to jump to , this will be set in both next if/else statement
		$pc_for_jump = 0;

		//2.Determine what type of JPC instruction is :    JPC 0x1111   or    JPC _to_label
		//All the execution of this instr happens in this IF/else statement
		if ($address_to_jump_to != null) { //JPC 0x1111
			
			//convert hexa value into integer value
			$integer_value_to_jump_to = hexdec(substr($address_to_jump_to, 2)) ;

			//validating if integer number is in interval of program memory
			$check_program_memory_key_exists = static::helper_checkProgramMemoryKeyExists($integer_value_to_jump_to);

			if($check_program_memory_key_exists != true){ 
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Acest JUMP(JPC) sare in afara memoriei de program.");
				return false;
			} 

			//assign $pc_for_jump 
			$pc_for_jump = $integer_value_to_jump_to;


		} else { //JPC _to_label

			//check and validate if label for jump exists in program memory
			$check_label_stmt_in_program_memory = static::helper_checkIfLabelStatementExistsInProgramMemory($label_to_jump_to);

			if ($check_label_stmt_in_program_memory == false) {
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Label-ul dat ca parametru la jump-ul JPC nu a fost gasit in codul sursa pentru a efectua saltul.");
				return false;
			}

			//assign $pc_for_jump 
			$pc_for_jump = $check_label_stmt_in_program_memory;

		}


		//2.1 set a temp pc for this JP implementation
		$temp_pc = 0;

		//2.2 set a var branch_taken (branch_taken_jp or jump_taken) inside this JP for validating the prediction algorithm
		$jump_taken = "not_taken";

		//3.Temporary pc for jump is ready 
		//check if flagC set(C=1)-JPC and set/prepare the final program counter value according to octissimo
		if (Memory::getFlag('c') === 1) {
			//if C=1 , , then jump to $pc_for_jump that was computed earlier
			$temp_pc = $pc_for_jump;

			//echo "JPC sare daca c=1 la adresa <br>"; //for testing

			//this means that this jump was taken(conditia acestui jump a fost indeplinita) 
			$jump_taken = "taken";
		} else {
			//otherwise, just increment main program counter and Jump there
			$inc_pc = static::$PC;
			$inc_pc++;
			$temp_pc = $inc_pc;

			//echo "JPC sare la PC+1 <br>";

			//this means that this jump was NOT taken(conditia acestui jump NU a fost indeplinita) 
			$jump_taken = "not_taken";
		}
		//Memory::setFlag('c',1);


		//3.5 START COD PREDICTIE pentru JPC
		//doar cu 1 singura functie in toate jump-urile(increased readability si nu umplu asa rau codul JUMP-urilor)
		//predictia e locala(pentru fiecare jump in parte de la un PC) , nu globala
		//daca ai nevoie ca predictia sa nu mai fie pe un singur JP(sa nu mai fie locala) de la un PC la un moment dat:
		// predictie globala -- dai "jpc"(sau in ce jp e acum)(dai un param generic) ca param in loc de static::$PC 
		// predictie locala  -- dai ca param static::$PC
		static::global_jumps_prediction(static::$PC, $jump_taken);


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = [
			'HTOC_jump_details' => [
				"jump_taken"       => "jump " . $jump_taken , //jump taken  OR  jump not_taken
				"good_prediction"  =>  Predictor::$good_prediction, //GP(good prediction) information just about this Jump statement
				"miss_prediction"  =>  Predictor::$miss_prediction, //MP(miss prediction) information just about this Jump statement
				"good_predictions_total" => Predictor::getCounterGoodPredictions() ,
				"miss_predictions_total" => Predictor::getCounterMissPredictions() 
			]
		]; 

		//action for this statement:
		//...
		//for this statement, no action description
		$HTOC_action = "";

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)


		//4.increment Program Counter and prepare cpuBP for next instruction
		static::$PC = $temp_pc;

		//this method executed successfully
		//this return must be put at every instruction to be executed - always last(in rest , doar return false-uri)
		return true; 
	}






	/*
		JNN instructiune de executat/interpretat cpu
		Explanation:
		JNN -Jump if not negative (if N=0 , if flag negative not set)
		Forma(e):
		JNN 0x1111    - JUMP if not negative - JUMP conditionat(jump conditionat de flagN) la adresa hexa indicata in memoria de program
		JNN _to_label - JUMP if not negative - JUMP conditionat flagN la instructiunea label data in parametru
	*/
	public static function JNN_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare JNN  <br> ";
		} 
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$address_to_jump_to = (isset($instruction['params']['address_to_jump_to']))  ?  $instruction['params']['address_to_jump_to']  :  null;
		$label_to_jump_to = (isset($instruction['params']['label_to_jump_to']))  ?  ($instruction['params']['label_to_jump_to'])  :  null;



		//START EXECUTING THIS INSTRUCTION

		//set temporary program counter to jump to , this will be set in both next if/else statement
		$pc_for_jump = 0;

		//2.Determine what type of JNN instruction is :    JNN 0x1111   or    JNN _to_label
		//All the execution of this instr happens in this IF/else statement
		if ($address_to_jump_to != null) { //JNN 0x1111
			
			//convert hexa value into integer value
			$integer_value_to_jump_to = hexdec(substr($address_to_jump_to, 2)) ;

			//validating if integer number is in interval of program memory
			$check_program_memory_key_exists = static::helper_checkProgramMemoryKeyExists($integer_value_to_jump_to);

			if($check_program_memory_key_exists != true){ 
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Acest JUMP(JNN) sare in afara memoriei de program.");
				return false;
			} 

			//assign $pc_for_jump 
			$pc_for_jump = $integer_value_to_jump_to;


		} else { //JNN _to_label

			//check and validate if label for jump exists in program memory
			$check_label_stmt_in_program_memory = static::helper_checkIfLabelStatementExistsInProgramMemory($label_to_jump_to);

			if ($check_label_stmt_in_program_memory == false) {
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Label-ul dat ca parametru la jump-ul JNN nu a fost gasit in codul sursa pentru a efectua saltul.");
				return false;
			}

			//assign $pc_for_jump 
			$pc_for_jump = $check_label_stmt_in_program_memory;

		}


		//2.1 set a temp pc for this JP implementation
		$temp_pc = 0;

		//2.2 set a var branch_taken (branch_taken_jp or jump_taken) inside this JP for validating the prediction algorithm
		$jump_taken = "not_taken";

		//3.Temporary pc for jump is ready 
		//check if flagN not set(N=0)-JNN and set/prepare the final program counter value according to octissimo
		if (Memory::getFlag('n') === 0) {
			//if C=1 , , then jump to $pc_for_jump that was computed earlier
			$temp_pc = $pc_for_jump;

			//echo "JNN sare daca n=0 la adresa <br>"; //for testing

			//this means that this jump was taken(conditia acestui jump a fost indeplinita) 
			$jump_taken = "taken";
		} else {
			//otherwise, just increment main program counter and Jump there
			$inc_pc = static::$PC;
			$inc_pc++;
			$temp_pc = $inc_pc;

			//echo "JNN sare la PC+1 <br>";

			//this means that this jump was NOT taken(conditia acestui jump NU a fost indeplinita) 
			$jump_taken = "not_taken";
		}
		//Memory::setFlag('n',1);


		//3.5 START COD PREDICTIE pentru JNN
		//doar cu 1 singura functie in toate jump-urile(increased readability si nu umplu asa rau codul JUMP-urilor)
		//predictia e locala(pentru fiecare jump in parte de la un PC) , nu globala
		//daca ai nevoie ca predictia sa nu mai fie pe un singur JP(sa nu mai fie locala) de la un PC la un moment dat:
		// predictie globala -- dai "jnn"(sau in ce jp e acum)(dai un param generic) ca param in loc de static::$PC 
		// predictie locala  -- dai ca param static::$PC
		static::global_jumps_prediction(static::$PC, $jump_taken);


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = [
			'HTOC_jump_details' => [
				"jump_taken"       => "jump " . $jump_taken , //jump taken  OR  jump not_taken
				"good_prediction"  =>  Predictor::$good_prediction, //GP(good prediction) information just about this Jump statement
				"miss_prediction"  =>  Predictor::$miss_prediction, //MP(miss prediction) information just about this Jump statement
				"good_predictions_total" => Predictor::getCounterGoodPredictions() ,
				"miss_predictions_total" => Predictor::getCounterMissPredictions() 
			]
		]; 

		//action for this statement:
		//...
		//for this statement, no action description
		$HTOC_action = "";

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)


		//4.increment Program Counter and prepare cpuBP for next instruction
		static::$PC = $temp_pc;

		//this method executed successfully
		//this return must be put at every instruction to be executed - always last(in rest , doar return false-uri)
		return true; 
	}







	/*
		JPN instructiune de executat/interpretat cpu
		Explanation:
		JPN -Jump if  negative (if N=1 , if flag negative is set)
		Forma(e):
		JPN 0x1111    - JUMP if  negative - JUMP conditionat(jump conditionat de flagN) la adresa hexa indicata in memoria de program
		JPN _to_label - JUMP if  negative - JUMP conditionat flagN la instructiunea label data in parametru
	*/
	public static function JPN_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare JPN  <br> ";
		} 
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$address_to_jump_to = (isset($instruction['params']['address_to_jump_to']))  ?  $instruction['params']['address_to_jump_to']  :  null;
		$label_to_jump_to = (isset($instruction['params']['label_to_jump_to']))  ?  ($instruction['params']['label_to_jump_to'])  :  null;



		//START EXECUTING THIS INSTRUCTION

		//set temporary program counter to jump to , this will be set in both next if/else statement
		$pc_for_jump = 0;

		//2.Determine what type of JPN instruction is :    JPN 0x1111   or    JPN _to_label
		//All the execution of this instr happens in this IF/else statement
		if ($address_to_jump_to != null) { //JPN 0x1111
			
			//convert hexa value into integer value
			$integer_value_to_jump_to = hexdec(substr($address_to_jump_to, 2)) ;

			//validating if integer number is in interval of program memory
			$check_program_memory_key_exists = static::helper_checkProgramMemoryKeyExists($integer_value_to_jump_to);

			if($check_program_memory_key_exists != true){ 
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Acest JUMP(JPN) sare in afara memoriei de program.");
				return false;
			} 

			//assign $pc_for_jump 
			$pc_for_jump = $integer_value_to_jump_to;


		} else { //JPN _to_label

			//check and validate if label for jump exists in program memory
			$check_label_stmt_in_program_memory = static::helper_checkIfLabelStatementExistsInProgramMemory($label_to_jump_to);

			if ($check_label_stmt_in_program_memory == false) {
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Label-ul dat ca parametru la jump-ul JPN nu a fost gasit in codul sursa pentru a efectua saltul.");
				return false;
			}

			//assign $pc_for_jump 
			$pc_for_jump = $check_label_stmt_in_program_memory;

		}


		//2.1 set a temp pc for this JP implementation
		$temp_pc = 0;

		//2.2 set a var branch_taken (branch_taken_jp or jump_taken) inside this JP for validating the prediction algorithm
		$jump_taken = "not_taken";

		//3.Temporary pc for jump is ready 
		//check if flagN IS set(N=1)-JPN and set/prepare the final program counter value according to octissimo
		if (Memory::getFlag('n') === 1) {
			//if C=1 , , then jump to $pc_for_jump that was computed earlier
			$temp_pc = $pc_for_jump;

			//echo "JPN sare daca n=1 la adresa <br>"; //for testing

			//this means that this jump was taken(conditia acestui jump a fost indeplinita) 
			$jump_taken = "taken";
		} else {
			//otherwise, just increment main program counter and Jump there
			$inc_pc = static::$PC;
			$inc_pc++;
			$temp_pc = $inc_pc;

			//echo "JPN sare la PC+1 <br>";

			//this means that this jump was NOT taken(conditia acestui jump NU a fost indeplinita) 
			$jump_taken = "not_taken";
		}
		//Memory::setFlag('n',1);


		//3.5 START COD PREDICTIE pentru JPN
		//doar cu 1 singura functie in toate jump-urile(increased readability si nu umplu asa rau codul JUMP-urilor)
		//predictia e locala(pentru fiecare jump in parte de la un PC) , nu globala
		//daca ai nevoie ca predictia sa nu mai fie pe un singur JP(sa nu mai fie locala) de la un PC la un moment dat:
		// predictie globala -- dai "jpn"(sau in ce jp e acum)(dai un param generic) ca param in loc de static::$PC 
		// predictie locala  -- dai ca param static::$PC
		static::global_jumps_prediction(static::$PC, $jump_taken);


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = [
			'HTOC_jump_details' => [
				"jump_taken"       => "jump " . $jump_taken , //jump taken  OR  jump not_taken
				"good_prediction"  =>  Predictor::$good_prediction, //GP(good prediction) information just about this Jump statement
				"miss_prediction"  =>  Predictor::$miss_prediction, //MP(miss prediction) information just about this Jump statement
				"good_predictions_total" => Predictor::getCounterGoodPredictions() ,
				"miss_predictions_total" => Predictor::getCounterMissPredictions() 
			]
		]; 

		//action for this statement:
		//...
		//for this statement, no action description
		$HTOC_action = "";

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)


		//4.increment Program Counter and prepare cpuBP for next instruction
		static::$PC = $temp_pc;

		//this method executed successfully
		//this return must be put at every instruction to be executed - always last(in rest , doar return false-uri)
		return true; 
	}








	/*
		O flag not used in app(nu folosesc unsigned arithmetic) => nici JNO nu va fi folosit
		-------
		JNO instructiune de executat/interpretat cpu
		Explanation:
		JNO -Jump if not overflow (if O=0 , if flag overflow not set)
		Forma(e):
		JNO 0x1111    - JUMP if  not overflow - JUMP conditionat(jump conditionat de flagO) la adresa hexa indicata in memoria de program
		JNO _to_label - JUMP if  not overflow - JUMP conditionat flagO la instructiunea label data in parametru
	*/
	public static function JNO_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare JNO  <br> ";
		} 
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$address_to_jump_to = (isset($instruction['params']['address_to_jump_to']))  ?  $instruction['params']['address_to_jump_to']  :  null;
		$label_to_jump_to = (isset($instruction['params']['label_to_jump_to']))  ?  ($instruction['params']['label_to_jump_to'])  :  null;



		//START EXECUTING THIS INSTRUCTION

		//set temporary program counter to jump to , this will be set in both next if/else statement
		$pc_for_jump = 0;

		//2.Determine what type of JNO instruction is :    JNO 0x1111   or    JNO _to_label
		//All the execution of this instr happens in this IF/else statement
		if ($address_to_jump_to != null) { //JNO 0x1111
			
			//convert hexa value into integer value
			$integer_value_to_jump_to = hexdec(substr($address_to_jump_to, 2)) ;

			//validating if integer number is in interval of program memory
			$check_program_memory_key_exists = static::helper_checkProgramMemoryKeyExists($integer_value_to_jump_to);

			if($check_program_memory_key_exists != true){ 
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Acest JUMP(JNO) sare in afara memoriei de program.");
				return false;
			} 

			//assign $pc_for_jump 
			$pc_for_jump = $integer_value_to_jump_to;


		} else { //JNO _to_label

			//check and validate if label for jump exists in program memory
			$check_label_stmt_in_program_memory = static::helper_checkIfLabelStatementExistsInProgramMemory($label_to_jump_to);

			if ($check_label_stmt_in_program_memory == false) {
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Label-ul dat ca parametru la jump-ul JNO nu a fost gasit in codul sursa pentru a efectua saltul.");
				return false;
			}

			//assign $pc_for_jump 
			$pc_for_jump = $check_label_stmt_in_program_memory;

		}


		//2.1 set a temp pc for this JP implementation
		$temp_pc = 0;

		//2.2 set a var branch_taken (branch_taken_jp or jump_taken) inside this JP for validating the prediction algorithm
		$jump_taken = "not_taken";

		//3.Temporary pc for jump is ready 
		//check if flagO not set(O=0)-JNO and set/prepare the final program counter value according to octissimo
		if (Memory::getFlag('o') === 0) {
			//if C=1 , , then jump to $pc_for_jump that was computed earlier
			$temp_pc = $pc_for_jump;

			//echo "JNO sare daca o=0 la adresa <br>"; //for testing

			//this means that this jump was taken(conditia acestui jump a fost indeplinita) 
			$jump_taken = "taken";
		} else {
			//otherwise, just increment main program counter and Jump there
			$inc_pc = static::$PC;
			$inc_pc++;
			$temp_pc = $inc_pc;

			//echo "JNO sare la PC+1 <br>";

			//this means that this jump was NOT taken(conditia acestui jump NU a fost indeplinita) 
			$jump_taken = "not_taken";
		}
		//Memory::setFlag('o',1);


		//3.5 START COD PREDICTIE pentru JNO
		//doar cu 1 singura functie in toate jump-urile(increased readability si nu umplu asa rau codul JUMP-urilor)
		//predictia e locala(pentru fiecare jump in parte de la un PC) , nu globala
		//daca ai nevoie ca predictia sa nu mai fie pe un singur JP(sa nu mai fie locala) de la un PC la un moment dat:
		// predictie globala -- dai "jno"(sau in ce jp e acum)(dai un param generic) ca param in loc de static::$PC 
		// predictie locala  -- dai ca param static::$PC
		static::global_jumps_prediction(static::$PC, $jump_taken);


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = [
			'HTOC_jump_details' => [
				"jump_taken"       => "jump " . $jump_taken , //jump taken  OR  jump not_taken
				"good_prediction"  =>  Predictor::$good_prediction, //GP(good prediction) information just about this Jump statement
				"miss_prediction"  =>  Predictor::$miss_prediction, //MP(miss prediction) information just about this Jump statement
				"good_predictions_total" => Predictor::getCounterGoodPredictions() ,
				"miss_predictions_total" => Predictor::getCounterMissPredictions() 
			]
		]; 

		//action for this statement:
		//...
		//for this statement, no action description
		$HTOC_action = "";

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)



		//4.increment Program Counter and prepare cpuBP for next instruction
		static::$PC = $temp_pc;

		//this method executed successfully
		//this return must be put at every instruction to be executed - always last(in rest , doar return false-uri)
		return true; 
	}







	/*
		JPO instructiune de executat/interpretat cpu
		Explanation:
		JPO -Jump if overflow (if O=1 , if flag overflow IS set)
		Forma(e):
		JPO 0x1111    - JUMP if  overflow - JUMP conditionat(jump conditionat de flagO) la adresa hexa indicata in memoria de program
		JPO _to_label - JUMP if  overflow - JUMP conditionat flagO la instructiunea label data in parametru
	*/
	public static function JPO_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare JPO  <br> ";
		} 
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$address_to_jump_to = (isset($instruction['params']['address_to_jump_to']))  ?  $instruction['params']['address_to_jump_to']  :  null;
		$label_to_jump_to = (isset($instruction['params']['label_to_jump_to']))  ?  ($instruction['params']['label_to_jump_to'])  :  null;



		//START EXECUTING THIS INSTRUCTION

		//set temporary program counter to jump to , this will be set in both next if/else statement
		$pc_for_jump = 0;

		//2.Determine what type of JPO instruction is :    JPO 0x1111   or    JPO _to_label
		//All the execution of this instr happens in this IF/else statement
		if ($address_to_jump_to != null) { //JPO 0x1111
			
			//convert hexa value into integer value
			$integer_value_to_jump_to = hexdec(substr($address_to_jump_to, 2)) ;

			//validating if integer number is in interval of program memory
			$check_program_memory_key_exists = static::helper_checkProgramMemoryKeyExists($integer_value_to_jump_to);

			if($check_program_memory_key_exists != true){ 
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Acest JUMP(JPO) sare in afara memoriei de program.");
				return false;
			} 

			//assign $pc_for_jump 
			$pc_for_jump = $integer_value_to_jump_to;


		} else { //JPO _to_label

			//check and validate if label for jump exists in program memory
			$check_label_stmt_in_program_memory = static::helper_checkIfLabelStatementExistsInProgramMemory($label_to_jump_to);

			if ($check_label_stmt_in_program_memory == false) {
				//raise error and exit instruction execution
				Error::raise("Eroare Interpretare/Executare (pe linia".$instruction['line_no']."): Label-ul dat ca parametru la jump-ul JPO nu a fost gasit in codul sursa pentru a efectua saltul.");
				return false;
			}

			//assign $pc_for_jump 
			$pc_for_jump = $check_label_stmt_in_program_memory;

		}


		//2.1 set a temp pc for this JP implementation
		$temp_pc = 0;

		//2.2 set a var branch_taken (branch_taken_jp or jump_taken) inside this JP for validating the prediction algorithm
		$jump_taken = "not_taken";

		//3.Temporary pc for jump is ready 
		//check if flagO IS set(O=1)-JPO and set/prepare the final program counter value according to octissimo
		if (Memory::getFlag('o') === 1) {
			//if C=1 , , then jump to $pc_for_jump that was computed earlier
			$temp_pc = $pc_for_jump;

			//echo "JPO sare daca o=1 la adresa <br>"; //for testing

			//this means that this jump was taken(conditia acestui jump a fost indeplinita) 
			$jump_taken = "taken";
		} else {
			//otherwise, just increment main program counter and Jump there
			$inc_pc = static::$PC;
			$inc_pc++;
			$temp_pc = $inc_pc;

			//echo "JPO sare la PC+1 <br>";

			//this means that this jump was NOT taken(conditia acestui jump NU a fost indeplinita) 
			$jump_taken = "not_taken";
		}
		//Memory::setFlag('o',1);


		//3.5 START COD PREDICTIE pentru JPO
		//doar cu 1 singura functie in toate jump-urile(increased readability si nu umplu asa rau codul JUMP-urilor)
		//predictia e locala(pentru fiecare jump in parte de la un PC) , nu globala
		//daca ai nevoie ca predictia sa nu mai fie pe un singur JP(sa nu mai fie locala) de la un PC la un moment dat:
		// predictie globala -- dai "jpo"(sau in ce jp e acum)(dai un param generic) ca param in loc de static::$PC 
		// predictie locala  -- dai ca param static::$PC
		static::global_jumps_prediction(static::$PC, $jump_taken);


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = [
			'HTOC_jump_details' => [
				"jump_taken"       => "jump " . $jump_taken , //jump taken  OR  jump not_taken
				"good_prediction"  =>  Predictor::$good_prediction, //GP(good prediction) information just about this Jump statement
				"miss_prediction"  =>  Predictor::$miss_prediction, //MP(miss prediction) information just about this Jump statement
				"good_predictions_total" => Predictor::getCounterGoodPredictions() ,
				"miss_predictions_total" => Predictor::getCounterMissPredictions() 
			]
		]; 

		//action for this statement:
		//...
		//for this statement, no action description
		$HTOC_action = "";

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)



		//4.increment Program Counter and prepare cpuBP for next instruction
		static::$PC = $temp_pc;

		//this method executed successfully
		//this return must be put at every instruction to be executed - always last(in rest , doar return false-uri)
		return true; 
	}







	/*
		STR instructiune de executat/interpretat cpu
		Forma(e):
		STR R3 , 0x017D  -  Stocheaza valoarea pe 8 biti din R3 la adresa de memorie date 0x017D(pe 16 biti) 
		STR R3, R4, R5   -  Stocheaza valoarea pe 8 biti din R3 la adresa de memorie date (R4R5)
		--
		Memoria de date se gaseste in Memory.php
	*/
	public static function STR_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare STR  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_first_register  =  $instruction['params']['name_of_first_register'];
		$register_to_store_from_1 = (!empty($instruction['params']['register_to_store_from_1']))  ?  $instruction['params']['register_to_store_from_1']  :  null;
		$register_to_store_from_2 = (!empty($instruction['params']['register_to_store_from_2']))  ?  ($instruction['params']['register_to_store_from_2'])  :  null;
		$immediate_hexa_value = (!empty($instruction['params']['immediate_hexa_value']))  ?  $instruction['params']['immediate_hexa_value']  :  null;
		//var_dump($immediate_hexa_value);


		//START EXECUTING THIS INSTRUCTION

		//2.Determine what type of STR instruction is :    STR R3, 0x017D   or    STR R3, R4,R5
		//All the execution of this instr happens in this IF/else statement
		if($register_to_store_from_1 != null){ //instr type is: STR R3, R4,R5

			//checking the first register name in interval R0-R14
			//if i call getGeneralRegister or setGeneralRegister, NU MAI TREBUIE SA FAC ACEASTA VERIFICARE, o face aceste functii
			$checkFirstReg = Memory::helper_checkGeneralRegisterNameAndThrowError($name_of_first_register, $instruction['line_no']);
			if ($checkFirstReg == false) {
				//error raised via helper_checkGeneralRegisterNameAndThrowError, just return false here
				return false;
			}

			//getting the first register to set value into memory
			$got_name_of_first_register = Memory::getGeneralRegister($name_of_first_register);
			if ($got_name_of_first_register == false) {
				return false;
			}

			//getting the first register to store from to build memory data ADDRESS
			$got_register_to_store_from_1 = Memory::getGeneralRegister($register_to_store_from_1);
			if ($got_register_to_store_from_1 == false) {
				return false;
			}

			//getting the second register to store from to build memory data ADDRESS
			$got_register_to_store_from_2 = Memory::getGeneralRegister($register_to_store_from_2);
			if ($got_register_to_store_from_2 == false) {
				return false;
			}
			//echo"<pre>";  print_r($got_register_to_store_from_2);  echo "</pre>";

			//now we know that registers are valid , we can proceed further
			//concatenate registers to create valid memory Data ADDRESS
			$regAddressCleanHex_High = substr($got_register_to_store_from_1['hexa_value'], 2);
			$regAddressCleanHex_Low  = substr($got_register_to_store_from_2['hexa_value'], 2);
			$memory_data_address =  $regAddressCleanHex_High .  $regAddressCleanHex_Low;

			//setting the memory DATA(memoria de date)  at $memory_data_address with 
			//and that's all with the execution for this instr
			//Just make sure that setDataMemory method validates everything and it's build as good as it can be
			$check_if_added = Memory::setDataMemory($memory_data_address , $got_name_of_first_register['hexa_value']);

			//for test DEV Method , see it's description
			//Memory::addToDataMemoryFields_DEV();
			//Memory::displayDataMemory();

			if ($check_if_added == false) {
				return false;
			}

		} else { //instr type is: STR R3, 0x017D

			//Perfom current STR instr - store value of R3 at specified direct address

			//get the first register to get the value from - PUTEA FI FACUT IN AFARA IF-ului pentru reducere cod
			//getting the first register to set value into memory
			$got_name_of_first_register = Memory::getGeneralRegister($name_of_first_register);
			if ($got_name_of_first_register == false) {
				return false;
			}

			//clean the address value(strip 0x from it)
			$cleanedAddress = substr($immediate_hexa_value, 2);
			
			//store the value of register into the data memory
			//validation inside setDataMemory method
			$check_if_added = Memory::setDataMemory($cleanedAddress , $got_name_of_first_register['hexa_value']);
			//Memory::displayDataMemory();

			//if something not valid, exit from execution
			if ($check_if_added == false) {
				return false;
			}
		}


		//2.1 BUILD THE ACTION OF THIS INSTRUCTION:
		//....
		//everything is inside if/else - do better with next instr


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$key1 = (isset($memory_data_address)) ? $memory_data_address  : $cleanedAddress ;
		$HTOC_action = [
			'HTOC_update_memory_data' => [
 				$key1 => $got_name_of_first_register
			]
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed
		//just DOWN HERE i'll set return true, in restul corpului functiei voi seta doar return false-ul.
		return true;
	}







	/*
		LDR instructiune de executat/interpretat cpu
		Forma(e):
		LDR R3, 0x0055   -  Pune ce gasesti la adresa 0x0055 din memoria de date in registrul R3(va fi o valoare pe 8 biti) 
		LDR R3, R4, R5   -  Pune ce gasesti la adresa (R4R5) din memoria de date in registrul R3(din nou va fi o valoare pe 8 biti)
								(R4R5) cei 3 registrii vor forma o adresa pe 4 bytes pe care DATA memory o intelege
		--
		Memoria de date se gaseste in Memory.php
	*/
	public static function LDR_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare LDR  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_first_register  =  $instruction['params']['name_of_first_register'];
		$register_to_load_from_1 = (!empty($instruction['params']['register_to_load_from_1']))  ?  $instruction['params']['register_to_load_from_1'] : null;
		$register_to_load_from_2 = (!empty($instruction['params']['register_to_load_from_2']))  ?  ($instruction['params']['register_to_load_from_2']) : null;
		$immediate_hexa_value_to_load_from = (!empty($instruction['params']['immediate_hexa_value_to_load_from']))  ?  $instruction['params']['immediate_hexa_value_to_load_from']  :  null;
		//var_dump($immediate_hexa_value_to_load_from);


		//START EXECUTING THIS INSTRUCTION

		//1.1 Validating the first register
		//checking the first register name in interval R0-R14
		//if i call getGeneralRegister or setGeneralRegister, NU MAI TREBUIE SA FAC ACEASTA VERIFICARE, o face aceste functii
		$checkFirstReg = Memory::helper_checkGeneralRegisterNameAndThrowError($name_of_first_register, $instruction['line_no']);
		if ($checkFirstReg == false) {
			//error raised via $checkFirstReg, just return false here
			return false;
		}


		//2.Determine what type of LDR instruction is :    LDR R3, 0x017D   or    LDR R3, R4,R5
		//All the execution of this instr happens in this IF/else statement
		if($register_to_load_from_1 != null){ //instr type is: LDR R3, R4,R5

			//getting the first register to set value into memory
			$got_name_of_first_register = Memory::getGeneralRegister($name_of_first_register);

			//getting the first register to store from to build memory data ADDRESS
			$got_register_to_load_from_1 = Memory::getGeneralRegister($register_to_load_from_1);
			if ($got_register_to_load_from_1 == false) {
				return false;
			}

			//getting the second register to store from to build memory data ADDRESS
			$got_register_to_load_from_2 = Memory::getGeneralRegister($register_to_load_from_2);
			if ($got_register_to_load_from_2 == false) {
				return false;
			}

			//now we know that registers are valid , we can proceed further
			//concatenate registers to create valid memory Data ADDRESS
			$regAddressCleanHex_High = substr($got_register_to_load_from_1['hexa_value'], 2);
			$regAddressCleanHex_Low  = substr($got_register_to_load_from_2['hexa_value'], 2);
			$memory_data_address =  $regAddressCleanHex_High .  $regAddressCleanHex_Low;

			//retrieving(getting) the memory DATA(memoria de date)  at $memory_data_address 
			//Just make sure that getDataMemory method validates everything and it's build as good as it can be
			$data_memory_row = Memory::getDataMemory($memory_data_address);
			//Memory::displayDataMemory();

			//something went wrong on getting the data from data memory
			if ($data_memory_row == false) {
				return false;
			}
			//print_r($data_memory_row);


			//reconstruct register hex value
			//because from DATA memory comes as FF, instead of 0xFF for example
			$hex_reg_data_memory_reconstruct = static::helper_reconstructHexNumber($data_memory_row['value_hex']);

			//set register with data from DATA memory			
			$execute_set_register = Memory::setGeneralRegister($name_of_first_register, $hex_reg_data_memory_reconstruct);
			if($execute_set_register == false){
				//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
				return false;
			}
			//echo $name_of_first_register . "=";
			//print_r(Memory::${$name_of_first_register});


		} else { //instr type is: LDR R3, 0x017D


			//Perfom current LDR instr - load value at address 0x017D into specified register

			//clean the address value(strip 0x from it)
			$cleanedAddress = substr($immediate_hexa_value_to_load_from, 2);

			//retrieving(getting) the memory DATA(memoria de date)  at specified address 
			$data_memory_row = Memory::getDataMemory($cleanedAddress);
			if ($data_memory_row == false) {
				return false;
			}

			//reconstruct  hex value
			//because from DATA memory comes as FF, instead of 0xFF for example
			$hex_reg_data_memory_reconstruct = static::helper_reconstructHexNumber($data_memory_row['value_hex']);

			//set register with data from DATA memory			
			$execute_set_register = Memory::setGeneralRegister($name_of_first_register, $hex_reg_data_memory_reconstruct);
			if($execute_set_register == false){
				//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
				return false;
			}

		}


		//2.1 BUILD THE ACTION OF THIS INSTRUCTION:
		//.... already done in if/else stmt for this


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			'HTOC_update_general_register' => [
 				$name_of_first_register => Memory::getGeneralRegister($name_of_first_register)
			]
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed
		//just DOWN HERE i'll set return true, in restul corpului functiei voi seta doar return false-ul.
		return true;
	}







	/*
		ADD instructiune de executat/interpretat cpu
		Forma(e):
		ADD R11, R12   -  (R11 <- R11 + R12)  Fa suma intre continutul lui R11 si R12 si adauga suma finala in R11 (ADD destination, source)  
		ADD R13, 0x0A  -  (R13 <- R13 + 0x0A) Fa suma intre continutul lui R13 si valoarea directa 0x0A si adauga suma finala in R13
		--
		SETS Carry(momentan)
		----------------
		Some stackoverflows about flags:
		https://stackoverflow.com/questions/19301498/carry-flag-auxiliary-flag-and-overflow-flag-in-assembly
		https://stackoverflow.com/questions/10945166/what-is-the-diffrence-between-zero-flag-and-carry-flag
		https://stackoverflow.com/questions/41732231/when-is-the-n-negative-flag-set-in-assembly
		https://www.slideshare.net/MuhammadUmarFarooq49/assembly-language-addition-and-subtraction
		http://teaching.idallen.com/dat2343/10f/notes/040_overflow.txt
		----------------
		ADD va seta/deseta urmatoarele flaguri:
		ZF(Zero Flag)
		CF(Carry Flag)
		OF(Overflow Flag) - pentru numere cu semn (0-127 pe 8 biti pt numere cu +) 
		                  - OF NU e folosit niciodata in app mea
		----------------
		APLICATIA MEA NU FOLOSESTE OF(overflow flag)
	*/
	public static function ADD_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare ADD  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_first_register  =  $instruction['params']['name_of_first_register'];
		$register_for_addition_2 = (!empty($instruction['params']['register_for_addition_2']))  ?  $instruction['params']['register_for_addition_2'] : null;
		$hexa_value_for_addition = (!empty($instruction['params']['hexa_value_for_addition']))  ?  $instruction['params']['hexa_value_for_addition']  :  null;
		//var_dump($hexa_value_for_addition);


		//START EXECUTING THIS INSTRUCTION

		//1.1 Validating the first register
		//checking the first register name in interval R0-R14
		//$checkFirstReg = Memory::helper_checkGeneralRegisterNameAndThrowError($name_of_first_register, $instruction['line_no']);
		$got_name_of_first_register = Memory::getGeneralRegister($name_of_first_register);
		if ($got_name_of_first_register == false) {
			return false;
		}

		//set a variable to hold the second operand (which can come from   ADD R11, R12   OR   ADD R3, 0x0A)
		$second_operand = 0;

		//2.Determine what type of ADD instruction is :    ADD R11, R12   or    ADD R3, 0x0A
		//All the execution of this instr happens in this IF/else statement
		if($register_for_addition_2 != null){ //instr type is: ADD R11, R12

			//getting the second register to make addition
			$got_register_for_addition_2 = Memory::getGeneralRegister($register_for_addition_2);
			if ($got_register_for_addition_2 == false) {
				return false;
			}
			//Memory::displayGeneralRegisters();

			//setting the second operand on this if
			$second_operand = $got_register_for_addition_2['hexa_value'];


		} else { //instr type is: ADD R3, 0x0A

			//Perfom current ADD instr 
			$second_operand = $hexa_value_for_addition;

		}


		//2.1 BUILD THE ACTION OF THIS INSTRUCTION:
		//now we have the second operand for making : ADDITION
		//setting up the first operand
		$first_operand = $got_name_of_first_register['hexa_value'];

		//converting first and second operand to integer
		$first_operand_int = hexdec( substr($first_operand, 2) );//also striping "0x" from it
		$second_operand_int = hexdec( substr($second_operand, 2) );//also striping "0x" from it

		//Make ADDITION
		$result_ADD = $first_operand_int + $second_operand_int;
		//Set flags
		if ($result_ADD > 255) { //255 is 0xFF in hex
			//set Carry Flag
			Memory::setFlag('c', 1);

			//in var result_ADD va ramane restul ce a depasit 255
			//ex: result_ADD = 260 => C=1 , new result_ADD = 260 - 255 = 5
			$result_ADD = $result_ADD - 255;

		}

		//convert the result back to hex
		$result_hex = "0x" . dechex($result_ADD);

		//setGeneralRegister back(the first one)
		$execute_set_register = Memory::setGeneralRegister($name_of_first_register, $result_hex);
		if($execute_set_register == false){
			//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
			return false;
		}



		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			'HTOC_update_general_register' => [
 				$name_of_first_register => Memory::getGeneralRegister($name_of_first_register)
			] ,
			"HTOC_update_flags" => [
				"c_flag" => Memory::getFlag('c')
			] //sau pot pune c_flag direct in interior cu update_general_register pentru afisare mai usoara
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed - just Down here set return true
		return true;
	}






	/*
		ADC instructiune de executat/interpretat cpu
		Forma(e):
		ADC R14, R0   -  (R14 <- R14 + R0 + C[Carry])  Fa suma intre continutul lui R14 si R0 si CF(Carry Flag) si adauga suma finala in R14 
						 -- ADC is the same as ADD but adds an extra 1 if processor's carry flag is set.
		----------------
		SETS: Carry(momentan) - seteaza la fel ca ADD
		----------------
		ADC va seta/deseta aceleasi flag-uri precum ADD
	*/
	public static function ADC_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare ADC  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_first_register  =  $instruction['params']['name_of_first_register'];
		$name_of_second_register = (!empty($instruction['params']['name_of_second_register']))  ?  $instruction['params']['name_of_second_register'] : null;
		//var_dump($name_of_second_register);


		//START EXECUTING THIS INSTRUCTION

		//1.1 Validating the first register
		//checking the first register name in interval R0-R14
		//$checkFirstReg = Memory::helper_checkGeneralRegisterNameAndThrowError($name_of_first_register, $instruction['line_no']);
		$got_name_of_first_register = Memory::getGeneralRegister($name_of_first_register);
		if ($got_name_of_first_register == false) {
			return false;
		}

		//getting the second register to make addition
		$got_name_of_second_register = Memory::getGeneralRegister($name_of_second_register);
		if ($got_name_of_second_register == false) {
			return false;
		}

		
		//2.1 BUILD THE ACTION OF THIS INSTRUCTION:
		//prepare the 2 operands for making : ADDITION with Carry
		//setting up the first operand
		$first_operand  = $got_name_of_first_register['hexa_value'];
		//set a variable to hold the second operand 
		$second_operand = $got_name_of_second_register['hexa_value'];

		//converting first and second operand to integer
		$first_operand_int = hexdec( substr($first_operand, 2) );//also striping "0x" from it
		$second_operand_int = hexdec( substr($second_operand, 2) );//also striping "0x" from it


		//Make ADDITION with carry
		$result_ADC = $first_operand_int + $second_operand_int;
		//add an extra 1 to $result_ADC if Carry flag isset
		if (Memory::getFlag('c') == 1) {
			$result_ADC += 1;
		}
		//Set flags
		if ($result_ADC > 255) { //255 is 0xFF in hex
			//set Carry Flag
			Memory::setFlag('c', 1);

			//in var result_ADD va ramane restul ce a depasit 255
			//ex: result_ADD = 260 => C=1 , new result_ADD = 260 - 255 = 5
			$result_ADC = $result_ADC - 255;

		}

		//convert the result back to hex
		$result_hex = "0x" . dechex($result_ADC);

		//setGeneralRegister back(the first one)
		$execute_set_register = Memory::setGeneralRegister($name_of_first_register, $result_hex);
		if($execute_set_register == false){
			//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
			return false;
		}


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			'HTOC_update_general_register' => [
 				$name_of_first_register => Memory::getGeneralRegister($name_of_first_register)
			] ,
			"HTOC_update_flags" => [
				"c_flag" => Memory::getFlag('c')
			] //sau pot pune c_flag direct in interior cu update_general_register pentru afisare mai usoara
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed - just Down here set return true
		return true;
	}







	/*
		SUB instructiune de executat/interpretat cpu
		Forma(e):
		SUB R1, R0   -  (R1 <- R1 - R0)  Fa scadere intre continutul lui R1 si R0 si adauga diferenta finala in R1 (SUB destination, source)  
		SUB R13, 0x0A  -  (R13 <- R13 - 0x0A) Fa scadere intre continutul lui R13 si valoarea directa 0x0A si adauga diferenta finala in R13
		---------------
		Daca rezultatul final e un nr negativ, forteaza nr final in 0
		---------------
		SETS ZeroFlag, NegativeFlag,(momentan)
		----------------
		Some stackoverflows about flags:
		https://stackoverflow.com/questions/19301498/carry-flag-auxiliary-flag-and-overflow-flag-in-assembly
		https://stackoverflow.com/questions/10945166/what-is-the-diffrence-between-zero-flag-and-carry-flag
		https://stackoverflow.com/questions/41732231/when-is-the-n-negative-flag-set-in-assembly
		https://www.slideshare.net/MuhammadUmarFarooq49/assembly-language-addition-and-subtraction
		----------------
		SUB va seta/deseta urmatoarele flaguri:
		ZF(Zero Flag)
		NF(Negative Flag)
	*/
	public static function SUB_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare SUB  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_first_register  =  $instruction['params']['name_of_first_register'];
		$register_for_substraction_2 = (!empty($instruction['params']['register_for_substraction_2']))  ?  $instruction['params']['register_for_substraction_2'] : null;
		$hexa_value_for_substract = (!empty($instruction['params']['hexa_value_for_substract']))  ?  $instruction['params']['hexa_value_for_substract']  :  null;
		//var_dump($hexa_value_for_substract);


		//START EXECUTING THIS INSTRUCTION

		//1.1 Validating the first register
		//checking the first register name in interval R0-R14
		//$checkFirstReg = Memory::helper_checkGeneralRegisterNameAndThrowError($name_of_first_register, $instruction['line_no']);
		$got_name_of_first_register = Memory::getGeneralRegister($name_of_first_register);
		if ($got_name_of_first_register == false) {
			return false;
		}

		//set a variable to hold the second operand (which can come from   SUB R11, R12   OR   SUB R3, 0x0A)
		$second_operand = 0;

		//2.Determine what type of SUB instruction is :    SUB R11, R12   or    SUB R3, 0x0A
		//All the execution of this instr happens in this IF/else statement
		if($register_for_substraction_2 != null){ //instr type is: SUB R11, R12

			//getting the second register to make addition
			$got_register_for_substraction_2 = Memory::getGeneralRegister($register_for_substraction_2);
			if ($got_register_for_substraction_2 == false) {
				return false;
			}
			//Memory::displayGeneralRegisters();

			//setting the second operand on this if
			$second_operand = $got_register_for_substraction_2['hexa_value'];


		} else { //instr type is: SUB R3, 0x0A

			//Perfom current SUB instr 
			$second_operand = $hexa_value_for_substract;

		}


		//2.1 BUILD THE ACTION OF THIS INSTRUCTION:
		//now we have the second operand for making : SUBSTRACTION
		//setting up the first operand
		$first_operand = $got_name_of_first_register['hexa_value'];

		//converting first and second operand to integer
		$first_operand_int = hexdec( substr($first_operand, 2) );//also striping "0x" from it
		$second_operand_int = hexdec( substr($second_operand, 2) );//also striping "0x" from it

		//Make SUBSTRACTION
		$result_SUB = ($first_operand_int) - ($second_operand_int);

		//Set flags
		if ($result_SUB == 0) { 
			//set Zero Flag
			Memory::setFlag('z', 1);			
		}
		if ($result_SUB < 0) {
			//set negative flag
			Memory::setFlag('n', 1);

			//force final result to 0 as cpuBP(this app) works just with positive numbers
			$result_SUB = 0;
		}
		//echo Memory::$z_flag;
		//echo Memory::$n_flag;

		//convert the result back to hex
		$result_hex = "0x" . dechex($result_SUB);//genereaza eroare din cauza la minusSS - pui subsctract sa nu se duca mai jos de ZERO

		//setGeneralRegister back(the first one)
		$execute_set_register = Memory::setGeneralRegister($name_of_first_register, $result_hex);
		if($execute_set_register == false){
			//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
			return false;
		}


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			'HTOC_update_general_register' => [
 				$name_of_first_register => Memory::getGeneralRegister($name_of_first_register)
			] ,
			"HTOC_update_flags" => [
				"z_flag" => Memory::getFlag('z'),
				"n_flag" => Memory::getFlag('n'),
			] //sau pot pune _flag direct in interior cu update_general_register pentru afisare mai usoara(deja e afisare usoara donnot worry)
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed - just Down here set return true
		return true;
	}







	/*
		SBC instructiune de executat/interpretat cpu
		Forma(e):
		SBC R14, R0   -  (R14 <- R14 - R0 - C[Carry])  Fa diferenta intre continutul lui R14 si R0 si CF(Carry Flag) si adauga diferenta finala in R14 
						 -- SBC is the same as SUB but substracts an extra 1 if processor's carry flag is set.
		----------------
		https://stackoverflow.com/questions/38166573/why-is-the-carry-flag-set-during-a-subtraction-when-zero-is-the-minuend
		----------------
		SETS: ZeroFlag,NegativeFlag - seteaza la fel ca SUB
		----------------
		SBC va seta/deseta aceleasi flag-uri precum SUB
	*/
	public static function SBC_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare SBC  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_first_register  =  $instruction['params']['name_of_first_register'];
		$name_of_second_register = (!empty($instruction['params']['name_of_second_register']))  ?  $instruction['params']['name_of_second_register'] : null;
		//var_dump($name_of_second_register);


		//START EXECUTING THIS INSTRUCTION

		//1.1 Validating the first register
		//checking the first register name in interval R0-R14
		//$checkFirstReg = Memory::helper_checkGeneralRegisterNameAndThrowError($name_of_first_register, $instruction['line_no']);
		$got_name_of_first_register = Memory::getGeneralRegister($name_of_first_register);
		if ($got_name_of_first_register == false) {
			return false;
		}

		//getting the second register to make addition
		$got_name_of_second_register = Memory::getGeneralRegister($name_of_second_register);
		if ($got_name_of_second_register == false) {
			return false;
		}

		
		//2.1 BUILD THE ACTION OF THIS INSTRUCTION:
		//prepare the 2 operands for making : SUBSTRACTION with Carry
		//setting up the first operand
		$first_operand  = $got_name_of_first_register['hexa_value'];
		//set a variable to hold the second operand 
		$second_operand = $got_name_of_second_register['hexa_value'];

		//converting first and second operand to integer
		$first_operand_int = hexdec( substr($first_operand, 2) );//also striping "0x" from it
		$second_operand_int = hexdec( substr($second_operand, 2) );//also striping "0x" from it


		//Make SUBSTRACTION with carry
		$result_SBC = ($first_operand_int) - ($second_operand_int);
		//substract an extra 1 from $result_SBC if Carry flag isset
		if (Memory::getFlag('c') == 1) {
			$result_SBC -= 1;
		}

		//Set flags
		if ($result_SBC == 0) { 
			//set Zero Flag
			Memory::setFlag('z', 1);			
		}
		if ($result_SBC < 0) {
			//set negative flag
			Memory::setFlag('n', 1);

			//force final result to 0 as cpuBP(this app) works just with positive numbers
			$result_SBC = 0;
		}


		//convert the result back to hex
		$result_hex = "0x" . dechex($result_SBC);

		//setGeneralRegister back(the first one)
		$execute_set_register = Memory::setGeneralRegister($name_of_first_register, $result_hex);
		if($execute_set_register == false){
			//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
			return false;
		}


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			'HTOC_update_general_register' => [
 				$name_of_first_register => Memory::getGeneralRegister($name_of_first_register)
			] ,
			"HTOC_update_flags" => [
				"z_flag" => Memory::getFlag('z'),
				"n_flag" => Memory::getFlag('n'),
			] //sau pot pune _flag direct in interior cu update_general_register pentru afisare mai usoara(deja e afisare usoara donnot worry)
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed - just Down here set return true
		return true;
	}






	/*
		AND instructiune de executat/interpretat cpu
		Forma(e):
		AND R0, R1   -  SI LOGIC intre continutul lui R0 si R1 si pune rezultatul final in R0(primul registru)
						AND face minimul pe biti(si_min) , e poarta de minim
		----------------
		Bitwise operators PHP -- https://www.php.net/manual/ro/language.operators.bitwise.php
	*/
	public static function AND_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare AND  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_first_register  =  $instruction['params']['name_of_first_register'];
		$name_of_second_register = (!empty($instruction['params']['name_of_second_register']))  ?  $instruction['params']['name_of_second_register'] : null;
		//var_dump($name_of_second_register);


		//START EXECUTING THIS INSTRUCTION

		//1.1 Validating the first register
		//checking the first register name in interval R0-R14
		//$checkFirstReg = Memory::helper_checkGeneralRegisterNameAndThrowError($name_of_first_register, $instruction['line_no']);
		$got_name_of_first_register = Memory::getGeneralRegister($name_of_first_register);
		if ($got_name_of_first_register == false) {
			return false;
		}

		//getting the second register to execute this instruction
		$got_name_of_second_register = Memory::getGeneralRegister($name_of_second_register);
		if ($got_name_of_second_register == false) {
			return false;
		}

		
		//2 BUILD THE ACTION OF THIS INSTRUCTION:
		//prepare the 2 operands for making : AND LOGIC
		//setting up the first operand
		$first_operand  = $got_name_of_first_register['hexa_value'];
		//set a variable to hold the second operand 
		$second_operand = $got_name_of_second_register['hexa_value'];

		//converting first and second operand to integer
		$first_operand_int = hexdec( substr($first_operand, 2) );//also striping "0x" from it
		$second_operand_int = hexdec( substr($second_operand, 2) );//also striping "0x" from it

		//2.1. Make AND 
		$result = ($first_operand_int) & ($second_operand_int);
		//echo $result;

		//convert the result back to hex
		$result_hex = "0x" . dechex($result);

		//setGeneralRegister back(the first one)
		$execute_set_register = Memory::setGeneralRegister($name_of_first_register, $result_hex);
		if($execute_set_register == false){
			//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
			return false;
		}



		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			'HTOC_update_general_register' => [
 				$name_of_first_register => Memory::getGeneralRegister($name_of_first_register)
			] 
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed - just Down here set return true
		return true;
	}







	/*
		ORR instructiune de executat/interpretat cpu
		Forma(e):
		ORR R0, R1   -  SAU LOGIC intre continutul lui R0 si R1 si pune rezultatul final in R0(primul registru)
						OR face maximul pe biti(sau_max) , e poarta de maxim
		----------------
		Bitwise operators PHP -- https://www.php.net/manual/ro/language.operators.bitwise.php
	*/
	public static function ORR_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare ORR  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_first_register  =  $instruction['params']['name_of_first_register'];
		$name_of_second_register = (!empty($instruction['params']['name_of_second_register']))  ?  $instruction['params']['name_of_second_register'] : null;
		//var_dump($name_of_second_register);


		//START EXECUTING THIS INSTRUCTION

		//1.1 Validating the first register
		//checking the first register name in interval R0-R14
		//$checkFirstReg = Memory::helper_checkGeneralRegisterNameAndThrowError($name_of_first_register, $instruction['line_no']);
		$got_name_of_first_register = Memory::getGeneralRegister($name_of_first_register);
		if ($got_name_of_first_register == false) {
			return false;
		}

		//getting the second register to execute this instruction
		$got_name_of_second_register = Memory::getGeneralRegister($name_of_second_register);
		if ($got_name_of_second_register == false) {
			return false;
		}

		
		//2 BUILD THE ACTION OF THIS INSTRUCTION:
		//prepare the 2 operands for making : OR LOGIC
		//setting up the first operand
		$first_operand  = $got_name_of_first_register['hexa_value'];
		//set a variable to hold the second operand 
		$second_operand = $got_name_of_second_register['hexa_value'];

		//converting first and second operand to integer
		$first_operand_int = hexdec( substr($first_operand, 2) );//also striping "0x" from it
		$second_operand_int = hexdec( substr($second_operand, 2) );//also striping "0x" from it

		//2.1. Make OR 
		$result = ($first_operand_int) | ($second_operand_int);
		//echo $result;

		//convert the result back to hex
		$result_hex = "0x" . dechex($result);

		//setGeneralRegister back(the first one)
		$execute_set_register = Memory::setGeneralRegister($name_of_first_register, $result_hex);
		if($execute_set_register == false){
			//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
			return false;
		}



		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			'HTOC_update_general_register' => [
 				$name_of_first_register => Memory::getGeneralRegister($name_of_first_register)
			] ,
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed - just Down here set return true
		return true;
	}







	/*
		XOR instructiune de executat/interpretat cpu
		Forma(e):
		XOR R0, R1   -  SAU EXCLUSIV LOGIC(la nivel de bit) intre continutul lui R0 si R1 si pune rezultatul final in R0(primul registru)
						EXCLUSIVE OR  A,B - A OR B , but not both(vezi XOR online)
		----------------
		Bitwise operators PHP -- https://www.php.net/manual/ro/language.operators.bitwise.php
	*/
	public static function XOR_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare XOR  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_first_register  =  $instruction['params']['name_of_first_register'];
		$name_of_second_register = (!empty($instruction['params']['name_of_second_register']))  ?  $instruction['params']['name_of_second_register'] : null;
		//var_dump($name_of_second_register);


		//START EXECUTING THIS INSTRUCTION

		//1.1 Validating the first register
		//checking the first register name in interval R0-R14
		//$checkFirstReg = Memory::helper_checkGeneralRegisterNameAndThrowError($name_of_first_register, $instruction['line_no']);
		$got_name_of_first_register = Memory::getGeneralRegister($name_of_first_register);
		if ($got_name_of_first_register == false) {
			return false;
		}

		//getting the second register to execute this instruction
		$got_name_of_second_register = Memory::getGeneralRegister($name_of_second_register);
		if ($got_name_of_second_register == false) {
			return false;
		}

		
		//2 BUILD THE ACTION OF THIS INSTRUCTION:
		//prepare the 2 operands for making : XOR LOGIC
		//setting up the first operand
		$first_operand  = $got_name_of_first_register['hexa_value'];
		//set a variable to hold the second operand 
		$second_operand = $got_name_of_second_register['hexa_value'];

		//converting first and second operand to integer
		$first_operand_int = hexdec( substr($first_operand, 2) );//also striping "0x" from it
		$second_operand_int = hexdec( substr($second_operand, 2) );//also striping "0x" from it

		//2.1. Make XOR 
		$result = ($first_operand_int) ^ ($second_operand_int);
		//echo $result;

		//convert the result back to hex
		$result_hex = "0x" . dechex($result);

		//setGeneralRegister back(the first one)
		$execute_set_register = Memory::setGeneralRegister($name_of_first_register, $result_hex);
		if($execute_set_register == false){
			//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
			return false;
		}


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			'HTOC_update_general_register' => [
 				$name_of_first_register => Memory::getGeneralRegister($name_of_first_register)
			] ,
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed - just Down here set return true
		return true;
	}







	/*
		CMP instructiune de executat/interpretat cpu
		Forma(e):
		CMP R10, R11   -  Compara registrii R10 si R11 si , daca sunt egali(au aceeasi valoare), seteaza flaguri(ca la SUB) 
		                  Fa diferenta dintre R10 si R11 si seteaza flaguri(flaguri ca la SUB). Nu modifica valorile registrilor din parametrii.
		----------------
		https://www.tutorialspoint.com/assembly_programming/assembly_conditions.htm
		https://stackoverflow.com/questions/45898438/understanding-cmp-instruction
		----------------
		CMP SETS: ZeroFlag,CarryFlag - seteaza la fel ca SUB(in afara de NegativeFlag, CMP va seta CarryFlag in loc de negative)
		----------------
		CMP va seta/deseta aceleasi flag-uri precum SUB(in afara de NegativeFlag , va seta in loc de negative => carry flag)
		CMP nu modifica valorile registrilor din parametrii
		----------------
		MOD FUNCTIONARE:
		(inspirat: https://reverseengineering.stackexchange.com/questions/20838/how-the-cmp-instruction-uses-condition-flags )
		(ZF = zero flag, CF = carry flag)
		CMP dst , src (CMP destination , source)
		1. dst = src => ZF=1, CF=0   ==> use JNZ for test against =
		2. dst < src => ZF=0, CF=1   ==> use JNC for test against <
		3. dst > src => ZF=0, CF=0   ==> use JPZ and JPC for test against > 
		   (pentru caz 3,fol si JPZ si JPC pentru mai mare -NEAPARAT - pe ramura de out_of_if - vezi algo interschimbare)
	*/
	public static function CMP_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare CMP  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_first_register  =  $instruction['params']['name_of_first_register'];
		$name_of_second_register = (!empty($instruction['params']['name_of_second_register']))  ?  $instruction['params']['name_of_second_register'] : null;
		//var_dump($name_of_second_register);


		//START EXECUTING THIS INSTRUCTION

		//1.1 Validating the first register
		//checking the first register name in interval R0-R14
		//$checkFirstReg = Memory::helper_checkGeneralRegisterNameAndThrowError($name_of_first_register, $instruction['line_no']);
		$got_name_of_first_register = Memory::getGeneralRegister($name_of_first_register);
		if ($got_name_of_first_register == false) {
			return false;
		}

		//getting the second register 
		$got_name_of_second_register = Memory::getGeneralRegister($name_of_second_register);
		if ($got_name_of_second_register == false) {
			return false;
		}

		
		//2 BUILD THE ACTION OF THIS INSTRUCTION:
		//prepare the 2 operands for making : CMP - COMPARE instruction
		//setting up the first operand
		$first_operand  = $got_name_of_first_register['hexa_value'];
		//set a variable to hold the second operand 
		$second_operand = $got_name_of_second_register['hexa_value'];

		//converting first and second operand to integer
		$first_operand_int = hexdec( substr($first_operand, 2) );//also striping "0x" from it
		$second_operand_int = hexdec( substr($second_operand, 2) );//also striping "0x" from it


		//2.1. Make CMP COMPARE 
		//first_operand = destination
		//second_operand = source
		$result_CMP = ($first_operand_int) - ($second_operand_int);
		//echo $result_CMP;
		
		//Just for testing/development in case another instruction setted our flags(reset flags):
		//Memory::setFlag('z', 0);	
		//Memory::setFlag('n', 0);	

		//Set flags
		if ($result_CMP == 0) { 
			//set Zero Flag
			Memory::setFlag('z', 1);			
		}
		if ($result_CMP < 0) {
			//set Negative flag
			//Memory::setFlag('n', 1);

			//according to this functionality of CMP assembly instruction :
			//https://reverseengineering.stackexchange.com/questions/20838/how-the-cmp-instruction-uses-condition-flags
			//i will set carry flag(instead of negative flag) if destination(first_operand) < source(second_operand)
			//so....:
			//set Carry flag 
			Memory::setFlag('c', 1);

			//force final result to 0 as cpuBP(this app) works just with positive numbers(works just with UNSIGNED ARITHMETIC)
			$result_CMP = 0;
		}
		//echo Memory::getFlag('z');
		//echo Memory::getFlag('n');


		//do not set any registers or memory data
		//CMP is used just for testing values, it not sets registers(folosit de obicei cu un Jump dupa el , pentru testare)


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			//'HTOC_update_general_register' => [
 			//	$name_of_first_register => Memory::getGeneralRegister($name_of_first_register)
			//] ,
			"HTOC_update_flags" => [
				"z_flag" => Memory::getFlag('z'),
				"c_flag" => Memory::getFlag('c'),
			] //sau pot pune _flag direct in interior cu update_general_register pentru afisare mai usoara(deja e afisare usoara donnot worry)
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed - just Down here set return true
		return true;
	}







	/*
		INV instructiune de executat/interpretat cpu
		Forma(e):
		INV R0   -  Inversam(Negam) toti bitii(0 devine 1 , 1 devine 0) lui R0 si punem rezultatul inapoi in R0
		---------
		https://www.php.net/manual/ro/language.operators.bitwise.php
	*/
	public static function INV_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare INV  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_register  =  $instruction['params']['name_of_register'];
		//var_dump($name_of_register);


		//START EXECUTING THIS INSTRUCTION

		//1. Validating the first register
		//checking the first register name in interval R0-R14
		//$checkFirstReg = Memory::helper_checkGeneralRegisterNameAndThrowError($name_of_first_register, $instruction['line_no']);
		$got_name_of_register = Memory::getGeneralRegister($name_of_register);
		if ($got_name_of_register == false) {
			return false;
		}

		
		//2. BUILD THE ACTION OF THIS INSTRUCTION:
		//prepare the operand for making this instruction :
		//setting up the first operand
		$first_operand  = $got_name_of_register['hexa_value'];

		//converting operand to integer
		$first_operand_int = hexdec( substr($first_operand, 2) );//also striping "0x" from it

		//2.1. Make INV
		//$result = static::helper_invertBits($first_operand_int); //this invert bits of an integer
		$result = static::helper_flipBin($first_operand_int);
		//echo $result;

		//convert the result back to hex
		$result_hex = "0x" . dechex($result);

		//setGeneralRegister back(the first one)
		$execute_set_register = Memory::setGeneralRegister($name_of_register, $result_hex);
		if($execute_set_register == false){
			//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
			return false;
		}



		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			'HTOC_update_general_register' => [
 				$name_of_register => Memory::getGeneralRegister($name_of_register)
			] ,
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed - just Down here set return true
		return true;
	}







	/*
		SHL instructiune de executat/interpretat cpu
		Forma(e):
		SHL R0   -  SHIFT LEFT(shiftare la stanga) bitii lui R0 si punem rezultatul inapoi in R0
					Testeaza Cazurile limita(peste 255 int)
		---------
		https://www.php.net/manual/ro/language.operators.bitwise.php
	*/
	public static function SHL_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare SHL  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_register  =  $instruction['params']['name_of_register'];
		//var_dump($name_of_register);


		//START EXECUTING THIS INSTRUCTION

		//1. Validating the first register
		//checking the first register name in interval R0-R14
		//$checkFirstReg = Memory::helper_checkGeneralRegisterNameAndThrowError($name_of_first_register, $instruction['line_no']);
		$got_name_of_register = Memory::getGeneralRegister($name_of_register);
		if ($got_name_of_register == false) {
			return false;
		}

		
		//2. BUILD THE ACTION OF THIS INSTRUCTION:
		//prepare the operand for making this instruction :
		//setting up the first operand
		$first_operand  = $got_name_of_register['hexa_value'];

		//converting operand to integer
		$first_operand_int = hexdec( substr($first_operand, 2) );//also striping "0x" from it

		//2.1. Make SHL INSTRUCTION
		$places = 1; //cu cate pozitii sa se shifteze
		$result_SHL = $first_operand_int << $places; //shiftare stanga cu o pozitie(zerouri umple partea din dreapta a numarului binar)
		//echo $result_SHL;

		//check if result if bigger than 255(0xFF), as our application registers if working on max 255 (0xFF)values
		if ($result_SHL > 255) {
			//acum avem 9 biti in variabila $result_SHL(asta se poate intampla la cazul limita)
			//stergem bitul din stanga(Most Significant Bit - MSB-ul) ca sa ramanem tot cu 8 biti

			//covert to binary value
			$convert_to_binary_value = base_convert($result_SHL, 10, 2);

			//stripe MSB from result_SHL
			$stripped_MSB = substr($convert_to_binary_value, 1);

			//put back this result in result_SHL
			$result_SHL = base_convert($stripped_MSB, 2, 10);
		}
		

		//convert the result back to hex
		$result_hex = "0x" . dechex($result_SHL);

		//setGeneralRegister back(the first one)
		$execute_set_register = Memory::setGeneralRegister($name_of_register, $result_hex);
		if($execute_set_register == false){
			//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
			return false;
		}


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			'HTOC_update_general_register' => [
 				$name_of_register => Memory::getGeneralRegister($name_of_register)
			] ,
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed - just Down here set return true
		return true;
	}






	/*
		SHR instructiune de executat/interpretat cpu
		Forma(e):
		SHR R0   -  SHIFT RIGHT(shiftare la dreapta) bitii lui R0 si punem rezultatul inapoi in R0
					Testeaza cazurile limita(valori sub 0 int)
		---------
		https://www.php.net/manual/ro/language.operators.bitwise.php
	*/
	public static function SHR_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare SHR  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_register  =  $instruction['params']['name_of_register'];
		//var_dump($name_of_register);


		//START EXECUTING THIS INSTRUCTION

		//1. Validating the first register
		//checking the first register name in interval R0-R14
		//$checkFirstReg = Memory::helper_checkGeneralRegisterNameAndThrowError($name_of_first_register, $instruction['line_no']);
		$got_name_of_register = Memory::getGeneralRegister($name_of_register);
		if ($got_name_of_register == false) {
			return false;
		}

		
		//2. BUILD THE ACTION OF THIS INSTRUCTION:
		//prepare the operand for making this instruction :
		//setting up the first operand
		$first_operand  = $got_name_of_register['hexa_value'];

		//converting operand to integer
		$first_operand_int = hexdec( substr($first_operand, 2) );//also striping "0x" from it

		//2.1. Make SHR INSTRUCTION
		$places = 1; //cu cate pozitii sa se shifteze
		$result_SHR = $first_operand_int >> $places; //shiftare dreapta cu o pozitie(zerouri umple partea din stanga a numarului binar)
		//echo $result_SHR;

		//check if result if smaller than 0(0x00), as our application registers if working just on pozitive numbers(from 0 up to 255)
		if ($result_SHR < 0) {
			//NO need to do nothing
			//NOTE: same result as before; can not shift beyond 0
			//asa functioneaza shiftarea la dreapta
		}
		

		//convert the result back to hex
		$result_hex = "0x" . dechex($result_SHR);

		//setGeneralRegister back(the first one)
		$execute_set_register = Memory::setGeneralRegister($name_of_register, $result_hex);
		if($execute_set_register == false){
			//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
			return false;
		}


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			'HTOC_update_general_register' => [
 				$name_of_register => Memory::getGeneralRegister($name_of_register)
			] ,
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed - just Down here set return true
		return true;
	}







	/*
		ROL instructiune de executat/interpretat cpu
		Forma(e):
		ROL R0   -  ROTATE LEFT(rotire la stanga) a bitilor lui R0 si punem rezultatul inapoi in R0
					Testeaza cazurile limita(valori sub 0 int)
					Scoate bitul de pe pozitia 7 si il baga pe pozitia 0 inapoi in R0
					(citim pozitiile de la dreapta[0]LSB la stanga[7]MSB) - total 8 pozitii
		---------
		https://stackoverflow.com/questions/2423802/rotate-a-string-n-times-in-php
	*/
	public static function ROL_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare ROL  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_register  =  $instruction['params']['name_of_register'];
		//var_dump($name_of_register);


		//START EXECUTING THIS INSTRUCTION

		//1. Validating the first register
		//checking the first register name in interval R0-R14
		//$checkFirstReg = Memory::helper_checkGeneralRegisterNameAndThrowError($name_of_first_register, $instruction['line_no']);
		$got_name_of_register = Memory::getGeneralRegister($name_of_register);
		if ($got_name_of_register == false) {
			return false;
		}

		
		//2. BUILD THE ACTION OF THIS INSTRUCTION:
		//prepare the operand for making this instruction :
		//setting up the first operand
		$first_operand  = $got_name_of_register['hexa_value'];

		//converting operand to integer
		$first_operand_int = hexdec( substr($first_operand, 2) );//also striping "0x" from it
		$first_operand_binary = $got_name_of_register['binary_value'];

		//2.1. Make ROL INSTRUCTION
		$n = 1;//de cate ori sa fie rotit la stanga numarul nostru binar
		$str = $first_operand_binary; //punem numarul nostru binar in aceasta variabila pentru o forma simpla a ec urmatoare
		$result_ROL = substr($str, $n) . substr($str, 0, $n); //fa rotire la stanga
		//echo $result_ROL . "<br>"; 

		//convert the result back to int
		$result_ROL = base_convert($result_ROL, 2, 10);
		//echo $result_ROL;	

		//convert the result back to hex
		//HEX is accept by the method of storing data into registers
		$result_hex = "0x" . dechex($result_ROL);

		//setGeneralRegister back(the first one)
		$execute_set_register = Memory::setGeneralRegister($name_of_register, $result_hex);
		if($execute_set_register == false){
			//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
			return false;
		}


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			'HTOC_update_general_register' => [
 				$name_of_register => Memory::getGeneralRegister($name_of_register)
			] ,
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed - just Down here set return true
		return true;
	}







	/*
		ROR instructiune de executat/interpretat cpu
		Forma(e):
		ROR R0   -  ROTATE RIGHT(rotire la dreapta) a bitilor lui R0 si punem rezultatul inapoi in R0
					Testeaza cazurile limita
					Scoate bitul de pe pozitia 0 si il baga pe pozitia 7 inapoi in R0
					(citim pozitiile de la dreapta[0]LSB la stanga[7]MSB) - total 8 pozitii
		---------
		https://stackoverflow.com/questions/2423802/rotate-a-string-n-times-in-php
	*/
	public static function ROR_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare ROR  <br> ";
		}
		//echo"<pre>";  print_r($instruction);  echo "</pre>";

		//BUILD EXECUTION :
		//1.define variables for local use from param 
		$name_of_register  =  $instruction['params']['name_of_register'];
		//var_dump($name_of_register);


		//START EXECUTING THIS INSTRUCTION

		//1. Validating the first register
		//checking the first register name in interval R0-R14
		//$checkFirstReg = Memory::helper_checkGeneralRegisterNameAndThrowError($name_of_first_register, $instruction['line_no']);
		$got_name_of_register = Memory::getGeneralRegister($name_of_register);
		if ($got_name_of_register == false) {
			return false;
		}

		
		//2. BUILD THE ACTION OF THIS INSTRUCTION:
		//prepare the operand for making this instruction :
		//setting up the first operand
		$first_operand  = $got_name_of_register['hexa_value'];

		//converting operand to integer
		$first_operand_int = hexdec( substr($first_operand, 2) );//also striping "0x" from it
		$first_operand_binary = $got_name_of_register['binary_value'];

		//2.1. Make ROR INSTRUCTION
		$n = 1;//de cate ori sa fie rotit la dreapta numarul nostru binar
		$str = $first_operand_binary; //punem numarul nostru binar in aceasta variabila pentru o forma simpla a ecuatiei urmatoare
		$result_ROR = substr($str, strlen($str)-1, $n) . substr($str, 0, strlen($str)-1)  ; //fa rotire la dreapta
		//echo $result_ROR . "<br>"; 

		//convert the result back to int
		$result_ROR = base_convert($result_ROR, 2, 10);
		//echo $result_ROR;	

		//convert the result back to hex
		//HEX is accept by the method of storing data into registers
		$result_hex = "0x" . dechex($result_ROR);

		//setGeneralRegister back(the first one)
		$execute_set_register = Memory::setGeneralRegister($name_of_register, $result_hex);
		if($execute_set_register == false){
			//if value given as parameter was not valid or something happened, return false and stop execution of Interpreter
			return false;
		}


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			'HTOC_update_general_register' => [
 				$name_of_register => Memory::getGeneralRegister($name_of_register)
			] ,
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//3.increment Program Counter and prepare cpuBP for next instruction
		static::$PC++;//poate faci metoda speciala pentru asta

		//4.this method executed successfully
		//this return must be put at every instruction to be executed - just Down here set return true
		return true;
	}







	/*
		CLZ instructiune de executat/interpretat cpu
		Forma(e):
		CLZ     - CLear Flag Z(Zero) - se va face clear pe flagul Z (seteaza Z = 0)
	*/
	public static function CLZ_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare CLZ  <br> ";
		}


		//clear the Flag Z(Zero)
		Memory::setFlag('z', 0);


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			"HTOC_update_flags" => [
				"z_flag" => Memory::getFlag('z'),
			] //sau pot pune _flag direct in interior cu update_general_register pentru afisare mai usoara(deja e afisare usoara donnot worry)
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//increment Program Counter and prepare cpuBP for next instruction 
		static::$PC++;

		//this method executed successfully
		//this return must be put at every instruction to be executed - always last(in rest , doar return false-uri)
		return true; 
	}







	/*
		CLC instructiune de executat/interpretat cpu
		Forma(e):
		CLC     - CLear Flag C(Carry) - se va face clear pe flagul C (seteaza C = 0)
	*/
	public static function CLC_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare CLC  <br> ";
		}


		//clear the Flag C(Carry)
		Memory::setFlag('c', 0);


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			"HTOC_update_flags" => [
				"c_flag" => Memory::getFlag('c'),
			] //sau pot pune _flag direct in interior cu update_general_register pentru afisare mai usoara(deja e afisare usoara donnot worry)
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//increment Program Counter and prepare cpuBP for next instruction 
		static::$PC++;

		//this method executed successfully
		//this return must be put at every instruction to be executed - always last(in rest , doar return false-uri)
		return true; 
	}







	/*
		CLN instructiune de executat/interpretat cpu
		Forma(e):
		CLN     - CLear Flag N(Negative) - se va face clear pe flagul N (seteaza N = 0)
	*/
	public static function CLN_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare CLN  <br> ";
		}


		//clear the Flag N(Negative)
		Memory::setFlag('n', 0);


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		//$HTOC_action = "";
		$HTOC_action = [
			"HTOC_update_flags" => [
				"n_flag" => Memory::getFlag('n'),
			] //sau pot pune _flag direct in interior cu update_general_register pentru afisare mai usoara(deja e afisare usoara donnot worry)
		]; 
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//increment Program Counter and prepare cpuBP for next instruction 
		static::$PC++;

		//this method executed successfully
		//this return must be put at every instruction to be executed - always last(in rest , doar return false-uri)
		return true; 
	}






    ///////////////////////////////////////////////////////////////////////////////////////////////////////
	//CLO - clear overflow flag nu a mai fost scrisa din moment ce aplicatia cpuBP nu foloseste acest flag
	//
	//PSH - push in stiva NU a fost scrisa -- din moment ce app cpuBP nu foloseste O structura de stiva
	//
	//POP - pop din stiva NU a fost scrisa -- din moment ce app cpuBP nu foloseste O structura de stiva
	///////////////////////////////////////////////////////////////////////////////////////////////////////







	/*
		NOP instructiune de executat/interpretat cpu
		Forma(e):
		NOP     - No OPeration - nici o instructiune nu se executa in acest ciclu, doar incrementare de Program Counter(PC)
	*/
	public static function NOP_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare NOP  <br> ";
		}


		//NO OPERATION do no operation
		//just increment Program Counter and go to next instruction



		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		$HTOC_action = "";
		

		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)
		

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)

		//increment Program Counter and prepare cpuBP for next instruction 
		static::$PC++;

		//this method executed successfully
		//this return must be put at every instruction to be executed - always last(in rest , doar return false-uri)
		return true; 
	}







	/*
		HALT instructiune de executat/interpretat cpu
		Forma(e):
		HALT     - opreste executarea instructiunilor in procesorul nostru
	*/
	public static function HALT_STMT_INSTRUCTION($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "Se executa linie cod asamblare HALT  <br> ";
		}


		//2.6 Build the HTOC node for this statement instruction()
		//get program counter for this statement
		$HTOC_PC = static::$PC;
		//get instruction counter(nr total instructiuni executate)
		$HTOC_IC = static::$IC; 
		//get type of instruction
		$HTOC_type_of_instr = substr($instruction['statement_type'], 0 , -5); //substract *_STMT from instruction statement type key
		//get mnemonica of instruction
		$HTOC_mnemonica_instr = $instruction['mnemonica_instruction'];
		//jump details for this statement(jump_taken or not_taken)
		$HTOC_jump = "";//nu jump details for this statement
		//action for this statement:
		//...
		//for this statement, no action description
		$HTOC_action = "";
		
		//add node to HTOC class
		HistoryTableOfChanges::addNode($HTOC_IC, $HTOC_PC, $HTOC_type_of_instr, $HTOC_mnemonica_instr, $HTOC_jump , $HTOC_action );
		//HistoryTableOfChanges::displayHTOCkey($HTOC_IC); //use this comment for testing 

		//end HTOC code inside of this instruction   
		//(donnot forget to put static::$IC++ like below in all instructions)

		//2.7 increment IC(numar total instructiuni executate) for table order of execution
		static::$IC++;//pune in fiecare statement(in afara codului HTOC)



		//increment Program Counter and prepare cpuBP for stopping execution 
		//(nu mai incrementeaza PC) -  asta ar trebui sa faca, defapt incrementam PC ca sa nu se blocheze programul
		//	    si setam $to_return =false in metoda dispatchStatement sa fim sigur ca executia e oprita
		//la instr HALT setez Program Counter la un numar ireal(ex. 10.000) aici si asa voi sti ca am iesit din executie datorita lui HALT instr.
		static::$PC = 10000;


		//at the end of the execution of all statements(because end of execution of statements finishes with HALT statement), call a function to do 
		//something for the end(prepare HTOC, display HTOC, transform HTOC into JSON if it's the case and so on)
		static::callEndExecutionFunction(); //should return void or true

		//set variable to tell cpuBP app that execution is successfull
		//... set it here

		//this method executed successfully
		//this return must be put at every instruction to be executed - always last(in rest , doar return false-uri)
		return true; 
	}








	/**************************************************************************************************************
		END Interpretor methods from parsing
	***************************************************************************************************************/







	/**********************************************************************************************
	 **********************************************************************************************
		Helper functions for Interpretor Execution methods
		(methods like (MOV_STATEMENT,JMP_STATEMENT,...))
		Modular functions for using them inside main Interpreter execution methods
	***********************************************************************************************
	***********************************************************************************************/
	
	// public static function addDisplayImageInFront($image)
	// {
	// }



	/*
		check the Program Memory if key exists based on given param $integer_value
		@param int $integer_value, the value to search for in $program_memory

		@return Boolean, true if key(entry) exists in  $program_memory, false otherwise

		USED in : JMP instruction, and JUMP instructions in general
	*/
	public static function helper_checkProgramMemoryKeyExists($integer_value)
	{
		//echo $integer_value;
		//static::displayProgramMemory();

		if( array_key_exists($integer_value, static::$program_memory) ){
			return true;
		}

		return false;
	}




	/*
		checks if Label Statement exists in Program Memory
		@param String $label_stmt, the label statement to search for

		@return Mixed, false if not Label statement was found with the given value, the program counter address otherwise(int)

		USED in JMP instruction , and JUMP instructions in general(to match name given as param)
			to a LABEL_STMT
	*/
	public static function helper_checkIfLabelStatementExistsInProgramMemory($label_stmt)
	{
		//static::displayProgramMemory();
		foreach (static::$program_memory as $key => $value) {
			if(!is_array($value)){
				echo "a murit php in Interpreter php , functia  helper_checkIfLabelStatementExistsInProgramMemory";
				die;
			}

			if ($value['statement_type'] == 'LABEL_STMT') {
				//trim : character from current Label STMT for comparing
				//aceasta este o instr de tipul _to_MY_LABEL: 
				$trimmed_label = trim( $value['params']['name_of_label_to_be_jumped_to'] , ':');

				//trim spaces from param $label_stmt(this space can occur if i have a comment in source code after instruction)
				$label_stmt_trimmed_space = trim($label_stmt);

				//compare param of method with trimmed label
				if ($label_stmt_trimmed_space == $trimmed_label) {
					//if so , return the Program counter address
					return $key;

				} 
			}
		}

		return false;
	}





	/*
		append 0x in front of param

		@param $hex_number_without_0x  , an hex value without the 0x appendix

		@return String , the full hex value (ex instead of FF, return 0xFF)
	*/
	public static function helper_reconstructHexNumber($hex_number_without_0x)
	{
		return "0x" . $hex_number_without_0x;
	}





  	/*
		Invert the bits of an integer number

		// PHP program to invert actual bits 
		// of a number. 

		Credit goes to 
		https://www.geeksforgeeks.org/invert-actual-bits-number/
  	*/
	public static function helper_invertBits( $num) 
	{ 
	      
	    // calculating number of bits 
	    // in the number 
	    $x = log($num) + 1; 
	  
	    // Inverting the bits one by one 
	    for($i = 0; $i < $x; $i++)  
	    $num = ($num ^ (1 << $i));  
	  
	    return $num; 
	} 




	/*
		invert the bits of an integer number (8 bits setted by me)

		Credit goes to 
		https://www.php.net/manual/ro/language.operators.bitwise.php
		(this method was in a comment)
	*/
	public static function helper_flipBin($number) {
        $bin = str_pad(base_convert($number, 10, 2), 8, 0, STR_PAD_LEFT);//pus de mine 8 in loc de 32 la parametrul 2
        for ($i = 0; $i < 8; $i++) {//pus de mine 8 in loc de 32 la pas i 
            switch ($bin{$i}) {
                case '0' :
                    $bin{$i} = '1';
                    break;
                case '1' :
                    $bin{$i} = '0';
                    break;
            }
        }
        return bindec($bin);
    }





    /*
		Function that is used inside the jump statements(inside all jump statements) 
		- pentru ca codul de predictori e la fel in toate functiile(that's why this function is called global_*)

		Builds the Prediction code inside Jump statements

		@param int $PC, the current program counter from caller Jump statement
		@param string $jump_taken , tells us if jump was taken or not_taken from the caller Jump statement

		IMPLEMENTED IN:
		JPZ, JNC, JPC, JNN, JPN
		fara:
		JNZ - pentru ca e codul scris direct acolo in functie
		JMP - nu e jump conditionat
		JNO, JPO - app mea nu foloseste unsigned arithmetic(am pus codul acolo , dar aceste jump-uri oricum nu vor fii folosite)

		CALL:
		to be called only from JUMP statements
    */
	public static function global_jumps_prediction($PC, $jump_taken)
	{

		// START PREDICTOR CODE inside this jumps
		/*
			pot pune tot acest cod intr-o functie separata(ca sa nu mai ia spatiu in fiecare JNZ) : 
				a. pentru fiecare jump in parte : jnz_prediction($jump_taken, $pc)
				b. global pentru toate jumpurile(daca) codul seamana: global_jumps_prediction($jump_taken, $pc)
				---
				am facut cu functie globala -- chiar aceasta  --  global_jumps_prediction($PC, $jump_taken)
		*/

		//do not forget to set variable $jump_taken in the other jumps when implementing predictor code
		//jump_taken variable comes from the jp stmt code : 2 possible values: "taken" or "not_taken"
		//store bp mode in a variable for easier reading
		$bp_mode = 	$_POST['branch_prediction_mode'];
		if ($bp_mode == 0) { 
			// mod predictie STATIC NOT-TAKEN(value 0)
			//code for STATIC NOT-TAKEN prediction
			if (SHOW_PREDICTOR_MESSAGES_DEV){ //constant to show messages in dev in Config.php
				echo "exec bp mode 0 - Static not taken <br>";
			}

			//template cod predictie
			//make the predictor predict if branch is taken or not
			$prediction = Predictor::predictStaticNotTaken(); //simply returns taken or not_taken - in this case returns no 
			if ($prediction == $jump_taken) {
				//prediction was good
				Predictor::predictStaticNotTaken_response("good_prediction");
			} else{
				//prediction was bad, missed
				Predictor::predictStaticNotTaken_response("miss_prediction");
			}

			if (SHOW_PREDICTOR_MESSAGES_DEV){ 
				echo Predictor::getCounterGoodPredictions() . " " ;
				echo Predictor::getCounterMissPredictions() . " ";
			}
			//end template cod predictie

		} 
		else if ($bp_mode == 1) { 
			// mod predictie STATIC TAKEN(value 1)
			//code for STATIC TAKEN prediction - la fel ca not taken , doar ca asta raspunde cu DA
			if (SHOW_PREDICTOR_MESSAGES_DEV){ //constant to show messages in dev in Config.php
				echo "exec bp mode 1 - Static taken <br>";
			}

			//template cod predictie
			//make the predictor predict if branch is taken or not
			$prediction = Predictor::predictStaticTaken(); //simply returns taken or not_taken - in this case returns yes 
			if ($prediction == $jump_taken) {
				//prediction was good
				Predictor::predictStaticTaken_response("good_prediction");
			} else{
				//prediction was bad, missed
				Predictor::predictStaticTaken_response("miss_prediction");
			}

			if (SHOW_PREDICTOR_MESSAGES_DEV){ 
				echo Predictor::getCounterGoodPredictions() . " " ;
				echo Predictor::getCounterMissPredictions() . " " ;
			}
			//end template cod predictie 
		}
		else if ($bp_mode == 2) { 
			// mod predictie DYNAMIC 1BIT PREDICTOR(value 2)
			//code for DYNAMIC 1BIT PREDICTOR prediction
			if (SHOW_PREDICTOR_MESSAGES_DEV){ //constant to show messages in dev in Config.php
				echo "exec bp mode 2- Dynamic 1bit predictor <br>";
			}

			//template cod predictie
			//make the predictor predict if branch is taken or not
			$prediction = Predictor::predictDynamic1Bit($PC , $jump_taken); //returns taken or not_taken - in this case is dynamic 1 bit 
			if ($prediction == $jump_taken) {
				//prediction was good
				Predictor::predictDynamic1bit_response("good_prediction");
			} else{
				//prediction was bad, missed
				Predictor::predictDynamic1bit_response("miss_prediction");
			}

			if (SHOW_PREDICTOR_MESSAGES_DEV){ 
				echo Predictor::getCounterGoodPredictions() . " " ;
				echo Predictor::getCounterMissPredictions() . " " ;
			}
			//end template cod predictie 

		}	
		else if ($bp_mode == 3) { 
			// mod predictie DYNAMIC 2BIT PREDICTOR(value 3)
			//code for DYNAMIC 2BIT PREDICTOR prediction
			if (SHOW_PREDICTOR_MESSAGES_DEV){ //constant to show messages in dev in Config.php
				echo "exec bp mode 3- Dynamic 2bit predictor <br>";
			}

			//template cod predictie
			//make the predictor predict if branch is taken or not
			$prediction = Predictor::predictDynamic2Bit($PC , $jump_taken); //returns taken or not_taken - in this case is dynamic 2 bit 
			if ($prediction == $jump_taken) {
				//prediction was good
				Predictor::predictDynamic2bit_response("good_prediction");
			} else{
				//prediction was bad, missed
				Predictor::predictDynamic2bit_response("miss_prediction");
			}

			if (SHOW_PREDICTOR_MESSAGES_DEV){ 
				echo Predictor::getCounterGoodPredictions() . " " ;
				echo Predictor::getCounterMissPredictions() . " " ;
			}
			//end template cod predictie 

		}				
		else {
			//cover other cases ,  throw error and exit instruction execution
			Error::raise("Eroare Executare (din jumpul de pe linia".$instruction['line_no']."): Modul de predictie ". $bp_mode ." nu exista.");
			return false;
		}
		//END PREDICTOR CODE(inside Jumps)

	}



	/***********************************************************************************************
	 ***********************************************************************************************
		END Helper functions for executing instructions Interpretor
		(helpers for MOV_STMT,JMP_STMT,etc....)
	************************************************************************************************
	************************************************************************************************/














	/**********************************************************************************************
	 **********************************************************************************************
		Prototyped functions for Core Interpretor methods
		(methods like Prototyping_PROGRAM_COUNTER_FUNCTIONARE) 
	***********************************************************************************************
	***********************************************************************************************/
	
	/*
		functie testare functionare program counter
		test function
	*/	
	public static function prototyping_PC_functionare()
	{
		$PC = 0;
		$length_of_program_memory = count(static::$program_memory);

		while (($PC>=0)  &&  ($PC <= $length_of_program_memory)) {
			//chemam functia pe care o avem de executat la cheia program_counter(cheia curenta, de ex 0)
			//callAppropiateMethodForExecution($atKey_PC); -- poate fi numele chiar al acestei functii
			//OR 
			//dispatchStatement($atKey_PC);
			echo "dispatchStatement(PC = ". $PC .") <br>";

			//incrementeaza counter aici doar de testare functionare functie
			$PC++;
		}

		//if $PC outside of program , give an error, something is wrong
		if ( ($PC < 0)  &&  ($PC > count($length_of_program_memory)) ) {
			//Error::raise('something is wrong with program counter');
			return false;
		}

		//si in functia callMethodForExecution($atKey_PC)
		//voi avea un if else ca la dispatch statement care incarca numele de ex MOV_STMT si cheama el functia
		//si in MOV_STMT, JMP_STMT, aceste functii vor schimba PC(program counterul)
	}



	/***********************************************************************************************
		END prototyped functions functions for Core instructions Interpretor
	************************************************************************************************/

}/*end class interpretor*/
