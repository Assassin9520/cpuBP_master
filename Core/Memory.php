<?php

namespace Core;

/*
	Class Memory 
	php ver 5.5.0
	This is where the entire cpuBP app is storing and managing, working with memory data
	*
	*
	RO:
	Aceasta clasa va tine: 
		-Flagurile(Z Flag, Overflow flag, Negative flag,Carry flag , etc),
		-Registrii de memorie(de uz general R0-R14) si Registrii speciali
		-Memoria de program
		-Memoria de date
		-orice legat de memorie
		-metode de lucru cu memoria si acces memorie
*/
class Memory
{
	/***************************************************************************************************************
		Attributes of this class
	****************************************************************************************************************/


	/*
		defining The flags of the cpu
		Flags are binary: either 0, either 1, so i ll set them as boolean variables
		Default of flags = false(0)
	*/
	//defining Zero(Z) flag
	public static $z_flag = 0;	

	//defining Overflow(O) flag
	//aplicatia mea lucreaza pe unsigned numbers - NU am nevoie de overflow flag- ar adauga prea mult efort pentru nimic
	public static $o_flag = 0; //NEVER used in my app -see stack overflow at ADD stmt Interpretor.php	

	//defining Carry(C) flag
	public static $c_flag = 0;	

	//defining Negative(N) flag
	public static $n_flag = 0;	



	/*
		defining the generale purpose(use) Registers
		15 general purpose registers.
		R0-R14
		R0-R14 a on byte(maximum 8 bits)
		array(Hexa_value, binary_value, integer_value)
		Defaults: 
			R0(null,null,null)
			SAU
			R0(0x00,00000000,0) - asa il fac

		If you need to modify these Structures, Modify the first $R0 register,
		and then copy and paste this register and just change name to R1,R2,...	
	*/
	//defining R0 general use Register
	public static $R0 =  array(
		'hexa_value' => "0x00",
		'binary_value' => "0000 0000",
		'integer_value' => 0,
		);


	//defining R1 general use Register
	public static $R1 =  array(
		'hexa_value' => "0x00",
		'binary_value' => "0000 0000",
		'integer_value' => 0,
		);

	//defining R2 general use Register
	public static $R2 =  array(
		'hexa_value' => "0x00",
		'binary_value' => "0000 0000",
		'integer_value' => 0,
		);

	//defining R3 general use Register
	public static $R3 =  array(
		'hexa_value' => "0x00",
		'binary_value' => "0000 0000",
		'integer_value' => 0,
		);

	//defining R4 general use Register
	public static $R4 =  array(
		'hexa_value' => "0x00",
		'binary_value' => "0000 0000",
		'integer_value' => 0,
		);

	//defining R5 general use Register
	public static $R5 =  array(
		'hexa_value' => "0x00",
		'binary_value' => "0000 0000",
		'integer_value' => 0,
		);

	//defining R6 general use Register
	public static $R6 =  array(
		'hexa_value' => "0x00",
		'binary_value' => "0000 0000",
		'integer_value' => 0,
		);

	//defining R7 general use Register
	public static $R7 =  array(
		'hexa_value' => "0x00",
		'binary_value' => "0000 0000",
		'integer_value' => 0,
		);

	//defining R8 general use Register
	public static $R8 =  array(
		'hexa_value' => "0x00",
		'binary_value' => "0000 0000",
		'integer_value' => 0,
		);

	//defining R9 general use Register
	public static $R9 =  array(
		'hexa_value' => "0x00",
		'binary_value' => "0000 0000",
		'integer_value' => 0,
		);

	//defining R10 general use Register
	public static $R10 =  array(
		'hexa_value' => "0x00",
		'binary_value' => "0000 0000",
		'integer_value' => 0,
		);

	//defining R11 general use Register
	public static $R11 =  array(
		'hexa_value' => "0x00",
		'binary_value' => "0000 0000",
		'integer_value' => 0,
		);

	//defining R12 general use Register
	public static $R12 =  array(
		'hexa_value' => "0x00",
		'binary_value' => "0000 0000",
		'integer_value' => 0,
		);

	//defining R13 general use Register
	public static $R13 =  array(
		'hexa_value' => "0x00",
		'binary_value' => "0000 0000",
		'integer_value' => 0,
		);

	//defining R14 general use Register
	public static $R14 =  array(
		'hexa_value' => "0x00",
		'binary_value' => "0000 0000",
		'integer_value' => 0,
		);



	/*
		Defining the Data Memory
		Form: 64 x 8  (64 fields x 8 bits each) (in conformity with octissimo)
		  OR  64k x 8 (65535 fields x 8 bits each)(can be done whatever u wish) - JUST CHOOSE A MAX NUMBER
		Structure: array(4 fields)(with 4 keys each node):
			1.Address integer
			2.Address hexa (converted from int value)
			3.Content hexa
			4.Content integer (converted from int value)

		Default : empty array	
		---
		Chosen temp:(can be changed easily)
		Max fields:
			255 fields - 0xFF equivalent
	*/
	public static $data_memory = [];

	//related to $data_memory
	//set a fixed variable for max capacity of data memory(default is 255 ~ 0xFF in hex)
	//this can be set up to MAX 65535( ~ 0xFFFF in hex)
	public static $max_capacity_data_memory = 255;



	/****************************************************************************************************************
		Methods of this class
	*****************************************************************************************************************/
	
	/*
		Test method hello
	*/
	public static function sayHello()
	{
		//to be called from outside of class aka Memory::sayHello();
		echo "hello from memory module";
	}







	/************************************************************************************
		Methods for working with Flags
		Getters/Setters of flag z, c, o, n
		getFlag,setFlag,unsetFlag(unsetFlag e tot setFlag, dar setFlag('Z',0) )
	*************************************************************************************/

	/*
		Description:
		gets(returns) the value of an flag(which can be an int 1 or int 0)

		@flag_name , the flag to get

		@return int, the flag value given as param $flag_name
		if flag_name param not a valid flag attribute, die 
	*/
	public static function getFlag($flag_name)
	{
		if ( ($flag_name != 'z') && ($flag_name != 'c') && ($flag_name != 'o') && ($flag_name != 'n')  ) {
			die("param dat gresit in Memory.php , get flag method");
		}


		if ($flag_name == 'z') {
			return static::$z_flag;
		} elseif ($flag_name == 'c') {
			return static::$c_flag;
		} elseif ($flag_name == 'o') {
			return static::$o_flag;
		} elseif ($flag_name == 'n') {
			return static::$n_flag;
		}
	}





	/*
		Description:
		sets the value of an flag with the given param value_to_set(which can be an int 1 or int 0) 

		@flag_name , STRING,the flag to get
		@value_to_set , INT,  the value of an flag to be set(either 0 or 1)

		@return boolean, true 
		if flag_name param not a valid flag attribute, die 
		if value_to_set is not 0 or 1 , die

		@CALL:
		Memory::setFlag('z', 1);
	*/
	public static function setFlag($flag_name, $value_to_set)
	{
		//for setting flags:
		//Memory::setFlag('z', 1);
		//var_dump(Memory::$z_flag);

		//this main method code
		if ( ($flag_name != 'z') && ($flag_name != 'c') && ($flag_name != 'o') && ($flag_name != 'n')  ) {
			die("param dat gresit in Memory.php , set flag method");
		}

		if ( ($value_to_set != 0) && ($value_to_set != 1) ) {
			die("valoarea data la setFlag trebuie sa fi 0 SAU 1, Memory.php , set flag method");
		}


		if ($flag_name == 'z') {
			static::$z_flag = $value_to_set;
		} elseif ($flag_name == 'c') {
			static::$c_flag = $value_to_set;
		} elseif ($flag_name == 'o') {
			static::$o_flag = $value_to_set;
		} elseif ($flag_name == 'n') {
			static::$n_flag = $value_to_set;
		}

		return true;
	}










	/************************************************************************************
		Methods for working with General purpose Registers
		Getters/Setters of R0-R14
	*************************************************************************************/
	
	/** Description:
		gets the full array of a general purpose register

		Parameters,arguments:
		@register, the register to get

		Return:
		returns Mixed, the full array of current register given as param, false otherwise

		POATE BAGI SI LINIA DE COD CURENTA LA PARAMETRII, PENTRU AFISARE DE ERORI
	**/
	public static function getGeneralRegister($register)
	{
		//1.check if register param is actually an real general purpose register
		$checkReg = static::helper_checkGeneralRegisterName($register);

		if ($checkReg == false) {
			//registrul dat ca parametru nu exista(nu e in interval R0-R14)
			Error::raise("Eroare Interpretare/Executare :  " . $register . " nu e in intervalul  registrilor generali R0-R14");
			return false;
		}

		//get the register given as param
		return static::$$register;
	}




	/** Description:
		gets the hexa value of a general purpose register

		Parameters,arguments:
		@register, the register to get

		Return:
		returns @hexa, the hexa value of the given register

		AM PUS-O aici ca sa stiu eu ca o pot face cumva si asa, la nevoie
	**/
	public static function getGeneralRegisterHexaVal($register)
	{
		//to be writtenn
		//....
	}




	/**
		sets the value of a general purpose register with specified hex_value given as param
		@register, the register to be set
		@hexa_value , the hexa value to be validated and converted and set into register
		returns Mixed, Rn(the current set register)(is array) if operation was finished successfully, false otherwise
	**/
	public static function setGeneralRegister($register, $hexa_value)
	{
		//1.check if register param is actually an real general purpose register
		if ( ($register != 'R0') && 
			 ($register != 'R1') && 
			 ($register != 'R2') && 
			 ($register != 'R3') && 
			 ($register != 'R4') && 
			 ($register != 'R5') && 
			 ($register != 'R6') && 
			 ($register != 'R7') && 
			 ($register != 'R8') && 
			 ($register != 'R9')  && 
			 ($register != 'R10') && 
			 ($register != 'R11') && 
			 ($register != 'R12') && 
			 ($register != 'R13') && 
			 ($register != 'R14')  ) 
		{
			//param register is none of our general registers($register nu e in intervalul R0-R14)
			//echo $register . " nu e in intervalul  registrilor generali R0-R14";
			Error::raise("Eroare Interpretare/Executare :  " . $register . " nu e in intervalul  registrilor generali R0-R14");
			return false;
		}

		
		//2.validate $hex_value to be max 1byte(ex: 0x1A, 0xFF) and not:(0x15FB, 0xJKML) via helper function
		//0xJKML - this case is handled by Lexer , donnot worry
		$validate_hex_value = static::helper_validateHexValue($hexa_value, 2);

		if (!$validate_hex_value) { //hex value NOT validated successfully
			Error::raise("Valoarea hexa" . $hexa_value .  " trebuie sa fie o valoare pe 8 biti sau mai mica de 8 biti(ex: 0x7D,0x1).");
			return false;
		}


		//3.if hex value is valid , set this register valuees finally
		//prepare values for setting current register
		$main_hex_val = substr($hexa_value, 2);
		//hexa value
		$for_set_hexa_value = $hexa_value;
		//integer value 
		$for_set_integer_value =  hexdec($main_hex_val);
		//binary value
		$convert_to_binary_value = base_convert($main_hex_val, 16, 2);
		//adauga zerori pana se fac 8 biti in variabila convert_to_binary_value
		$for_set_binary_value = static::helper_addLeadingZerosToBinaryNumber($convert_to_binary_value, 8);


		//4.open register and set values into it
		//using variable variables php builtin function
		//print_r(static::$$register); = WORKING WITH $$register

		//Looping variable variables, illegal string offset
		//https://stackoverflow.com/questions/24151713/looping-variable-variables-illegal-string-offset
		//eroare de precedenta.. vezi stackoverflow-ul de mai sus .. pune acolade la $register
		static::${$register}['hexa_value'] = $for_set_hexa_value;
		static::${$register}['binary_value'] = $for_set_binary_value;
		static::${$register}['integer_value'] = $for_set_integer_value;

		//print_r(static::${$register});

		return static::${$register};
	}





	/*
		Displays all the general registers for viewing
		And for testing

		Call this method where u need in app to see the registers
	*/
	public static function displayGeneralRegisters()
	{
		echo "<pre>";
		
		for ($i=0; $i < 14 ; $i++) { 
			$current_reg = 'R' . $i;
			echo $current_reg . "= ";
			print_r(static::${$current_reg});
		}

		echo "</pre>";
	}








	/************************************************************************************
		Methods for working with DATA memory(memoria de date)
		Getters/Setters, other methods of $data_memory
	*************************************************************************************/

	/**
		ADDS(sets) an field into the DataMemory
		Initially, data memory is an empty array

		@param String Hex $address, the address where to set the new field(on max 4 bytes. Ex: FFFF[0xFFFF])
		@param String Hex $value, the 8-bit(1 byte) value to be set in the current field

		@return Boolean , true if a new field was added and everything is valid, false otherwise(and also throw error)
		--------------
		A new field/node will look LIKE: array(4 fields)(with 4 keys each node):
		30 => (30 is the number of key in array -suppose address inserted is 30)
			1.Address integer(converted from hex value)
			2.Address hexa 
			3.Content integer (converted from hex value)
			4.Content hexa
		--------------
		IT IS OK if data is overwritten , this is how STR works
		IT IS OK if we have addresses on flexible no of bytes(ex 0x0,0x01,0x0123,) -> will get validated	
		--------------
		Daca nu a fost adaugat nimic in DATA memory, aceasta este pur si simplu goala(array gol)(nu e niciun rand pus in 0000000 sau in vreun default)
	**/
	public static function setDataMemory($address, $value)
	{
		//convert our params into Integers and have them as hex also
		$address_hex = $address; // from param , $address comes in format FFFF
		$address_int = hexdec($address);
		$value_hex = substr($value, 2); //from param , $values comes in format 0xFF
		$value_int = hexdec($value_hex);

		//check is address given as param is larger than max capacity of DATA MEMORY(tipically 255)
		//and implicitly this means that we can skip the check for $address param to be on 4 bytes(0xFFFF)
		if ($address_int > static::$max_capacity_data_memory) {
			Error::raise("Eroare de memorie date: S-a incercat adaugarea randului " . $address_hex . "(" . $address_int . " in integer) in memoria de date.  (Capacitatea maxima a memoriei de date este de " . static::$max_capacity_data_memory . "  noduri) ");
			return false;
		}

		//adding the node to DataMemory in conformity with description of $data_memory attribute
		// and in comformity with description of this method
		//ADD the node at key address_int(for being easy to get data from memory)
		static::$data_memory[$address_int] = 
			[	
				"address_hex" => $address_hex,
				"address_int" => $address_int,
				"value_hex"   => $value_hex,
				"value_int"   => $value_int
			];
		//static::displayDataMemory();

		//method executed succesfully 
		return true;	
	}





	/**
		Retrieves(GETS) a ROW/Field/Node(just one row)  from DATA memory specified at param $address

		@param String Hex $address, the address to retrieve from Data Memory

		@return Mixed, the Data Memory Field(node) if successful , false and error otherwise

		--------------
		USED IN: LDR instr statement
	**/
	public static function getDataMemory($address)
	{
		//convert our params into Integers and have them as hex also
		$address_hex = $address; // from param , $address comes in format FFFF
		$address_int = hexdec($address);


		//check if field exists in data memory
		if (!array_key_exists($address_int, static::$data_memory)) {
			Error::raise("Eroare de memorie date: S-a incercat extragerea(LDR) randului " . $address_hex . "(" . $address_int . " in integer) din memoria de date.  (Acest rand NU EXISTA/NU A FOST ADAUGAT in memoria de date) ");
			return false;
		}


		//check is address given as param is larger than max capacity of DATA MEMORY(tipically 255)
		//and implicitly this means that we can skip the check for $address param to be on 4 bytes(0xFFFF)
		if ($address_int > static::$max_capacity_data_memory) {
			Error::raise("Eroare de memorie date: S-a incercat extragerea(LDR) randului " . $address_hex . "(" . $address_int . " in integer) din memoria de date.  (Capacitatea maxima a memoriei de date este de " . static::$max_capacity_data_memory . "  noduri) ");
			return false;
		}


		//static::displayDataMemory();

		//validated all , perform this method work
		//get the data memory at $address and return it to caller
		//print_r(static::$data_memory[$address_int]);

		return static::$data_memory[$address_int];
	}




	/**
		just a display preview for the Data memory
	**/
	public static function displayDataMemory()
	{
		echo "<pre>";
		print_r(static::$data_memory);
		echo "</pre>";
	}





	/**
		just a display preview for a Data memory Field(doar un camp anume)
		
		@param int $field , the field(a number in integer , ex: '30') that needs to be displayed

		@return mixed , the array node if field exists, displayed message otherwise

		Apelare RECOMANDATA:
		(ca la addToDataMemoryFields_DEV() ):
		Din Interpretor.php , metoda Interpret() -  ai deja codul pentru asta acolo , 
		comenteaza-l si decomenteaza-l cand ai nevoie

	**/
	public static function displayFieldDataMemory($field)
	{
		if (isset(static::$data_memory[$field]) && !empty(static::$data_memory[$field])) {
			echo "<pre>";
			print_r(static::$data_memory[$field]);
			echo "</pre>";
		} else {
			echo "Campul cerut pentru afisare nu exista(mesaj generat din Memory.php->displayFieldDataMemory)" . "<br>";
		}

	}





	/*
		ACEASTA FUNCTIE E DE DEV(DEVELOPER)
		Aceasta functie va adauga 30 de campuri(fields date de mine la tastatura) in memoria de date

		Poate fi folosita pentru diversi algoritmi ca sa nu stai sa scrii o groaza de instructiuni STORE (STR)
		O poti folosi prin statementurile instructiunilor de asamblare
		SAU
		Cu Config.php: Poti face o constanta enable_DEV si daca e true , apeleaza functia asta inainte de inceperea programului

		Apelare:
		oriunde, cum e specificat si mai sus, dar nu apelat inauntrul metodei static::setDataMemory;

		Pot face mai multe functii de genul asta ,adica:
		addToDataMemoryFields2_DEV - dar cu alte numere(poate mai mici)

		Apelare RECOMANDATA:
		Din Interpretor.php , metoda Interpret() -  ai deja codul pentru asta acolo , 
		comenteaza-l si decomenteaza-l cand ai nevoie
	*/
	public static function addToDataMemoryFields_DEV()
	{
		static::setDataMemory('0000','0x07'); //1 - notare start de la 0
		static::setDataMemory('0001','0x08'); //2
		static::setDataMemory('0002','0x03'); //3
		static::setDataMemory('0003','0x01'); //4
		static::setDataMemory('0004','0x1D'); //..
		static::setDataMemory('0005','0x06');
		static::setDataMemory('0006','0x02');
		static::setDataMemory('0007','0x0D');
		static::setDataMemory('0008','0x0E');
		static::setDataMemory('0009','0x0F');
		static::setDataMemory('000A','0x10'); //10
		static::setDataMemory('000B','0x19'); //11
		static::setDataMemory('000C','0x07'); //12
		static::setDataMemory('000D','0x20'); //13
		static::setDataMemory('000E','0x0A'); //14
		static::setDataMemory('000F','0x00'); //15
		static::setDataMemory('0010','0x01'); //16
		static::setDataMemory('0011','0x0B'); //17
		static::setDataMemory('0012','0x13'); //18
		static::setDataMemory('0013','0x14'); //19
		static::setDataMemory('0014','0x0C'); //20
		static::setDataMemory('0015','0x0D'); //21
		static::setDataMemory('0016','0x01'); //22
		static::setDataMemory('0017','0x1F'); //23
		static::setDataMemory('0018','0x11'); //24
		static::setDataMemory('0019','0x01'); //25
		static::setDataMemory('001A','0x02'); //26
		static::setDataMemory('001B','0x04'); //27
		static::setDataMemory('001C','0x05'); //28
		static::setDataMemory('001D','0x0B'); //29
		static::setDataMemory('001E','0x0E'); //30

		//can be commented->
		//MUST be commented:
		//static::displayDataMemory();
	}





	/**********************************************************************************
		Helpers for methods of memory class
		Helpers for:
			-general purpose register validations
			-helpers for DATA memory validations and stuff
			-for everything that is inside memory class
			-...
	***********************************************************************************/

	/**
		validate an hex value to be not greater than max_number_of_hex_letters param
		ex: if max_number_of_hex_letters = 2 , accepted hex values will be like: 0x1,0x0A,0x7D,0xFF.
											   not accepted will be : 0x0001,0x1FBC, 0x1234FDD, and so on
		@param hex_value, the hex_value to be validated
		@param $max_number_of_hex_letters, the maximum number of hex letters for hex value param to contain
		@return boolean, true in validation had passed, false otherwise							   

		This can be used inside setGeneralRegister method, but also called everywhere in the program.

		De ex, pot valida ca valoarea hexa data sa fie pe maximum 8 biti(sau 2 caractere hexa)
		A FOST GANDITA MODULARA PENTRU UZ GENERAL SA VALIDEZ ORIUNDE AM NEVOIE
	**/
	public static function helper_validateHexValue($hex_value, $max_number_of_hex_letters)
	{
		//the main value of this hex(without 0x at start)
		//remove 0x hex format at start of param
		$main_hex_val = substr($hex_value, 2);
		//echo $main_hex_val;

		if(strlen($main_hex_val) <= $max_number_of_hex_letters){
			return true;
		} else{
			return false;
		}
	}




	/**
		addLeadingZerosToBinaryNumber
		PHP - Add leading zeros to number but keep maximum length
		https://stackoverflow.com/questions/44986395/php-add-leading-zeros-to-number-but-keep-maximum-length

		@return the new padded string 
	**/
	public static function helper_addLeadingZerosToBinaryNumber($binary_string, $nr_max_pana_unde_se_adauga_zerouri)
	{
		//get the length of param binary string
		$length_bin = strlen($binary_string);

		//if($length_bin <= $nr_max_pana_unde_se_adauga_zerouri){
		//	$new_bin = str_pad($binary_string, 8, "0", STR_PAD_LEFT);
		//	echo $new_bin;
		//}


		$new_bin = str_pad($binary_string, $nr_max_pana_unde_se_adauga_zerouri, "0", STR_PAD_LEFT);
		return $new_bin;
	}




	/**
		verifica registrii de uz general sa fie in intervalul R0-R14
		@param String $register_name, numele registrului de verificat daca e in intervalul R0-R14

		@return Boolean, true daca registrul dat ca parametru se afla in intervalul R0-R14, fals altfel
	**/
	public static function helper_checkGeneralRegisterName($register_name)
	{
		$register = $register_name;

		if ( ($register != 'R0') && 
			 ($register != 'R1') && 
			 ($register != 'R2') && 
			 ($register != 'R3') && 
			 ($register != 'R4') && 
			 ($register != 'R5') && 
			 ($register != 'R6') && 
			 ($register != 'R7') && 
			 ($register != 'R8') && 
			 ($register != 'R9')  && 
			 ($register != 'R10') && 
			 ($register != 'R11') && 
			 ($register != 'R12') && 
			 ($register != 'R13') && 
			 ($register != 'R14')  ) 
		{
			//param $register_name NU e in interval R0-R14
			return false;
		}  else {
			//$register_name E in interval
			return true;
		}
	}





	/**
		verifica registrii de uz general sa fie in intervalul R0-R14
		SI face ea Error::raise daca registrul general nu exista

		@param String $register_name, numele registrului de verificat daca e in intervalul R0-R14
		@param int $instruction_line, linia instructiunii de unde s-a dat eroarea

		@return Boolean, true daca registrul dat ca parametru se afla in intervalul R0-R14, fals altfel

		@USED_IN:
		Folosita de ex in Interpretor.php , metoda STR_STMT_INSTRUCTION, la verificare registrii
	**/
	public static function helper_checkGeneralRegisterNameAndThrowError($register_name, $instruction_line)
	{
		$register = $register_name;

		if ( ($register != 'R0') && 
			 ($register != 'R1') && 
			 ($register != 'R2') && 
			 ($register != 'R3') && 
			 ($register != 'R4') && 
			 ($register != 'R5') && 
			 ($register != 'R6') && 
			 ($register != 'R7') && 
			 ($register != 'R8') && 
			 ($register != 'R9')  && 
			 ($register != 'R10') && 
			 ($register != 'R11') && 
			 ($register != 'R12') && 
			 ($register != 'R13') && 
			 ($register != 'R14')  ) 
		{
			//param $register_name NU e in interval R0-R14
			Error::raise("Eroare Interpretare/Executare (pe linia " . $instruction_line . "): " . $register_name . " nu e in intervalul  registrilor generali R0-R14(eroare din modulul memory)");

			return false;
		}  else {
			//$register_name E in interval
			return true;
		}
	}











}/*end of class Memory*/

















?>