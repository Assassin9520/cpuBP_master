<?php

namespace Core;
// use the following :  use \Core\Predictor;  - where u want to use this class

/*
	Class Predictor
	php ver 5.5.0
	This is where the entire cpuBP app is having Predictor/Predictors methods for building branch prediction
	*
	*
	RO:
	Aceasta clasa va tine: 
		-Predictorii de salturi (care vor fii implementati in JP conditionale : JNZ JPZ JNC JPZ JNN JPN)
		--without JNO JPO implementation - app mea nu lucreaza cu overflow flag
*/
class Predictor
{
	/***************************************************************************************************************
		Attributes of this class
	****************************************************************************************************************/

	/*
		Counter for good predictions
		--
		Since we can run just one predictor at a time , 
		we ll only have one global counter for all the predictors
		--
		Default value is 0
	*/ 
	public static $counter_good_predictions = 0;


	/*
		Counter for miss predictions
		--
		Since we can run just one predictor at a time , 
		we ll only have one global counter for all the predictors
		--
		Default value is 0
	*/ 
	public static $counter_miss_predictions = 0;




	/*
		the default(start state) of dynamic 1 bit predictor
		--
		we suppose that the dynamic 1 bit predictor was in state 1 at the beggining
	*/
	public static $dynamic_1_bit_start_state = 1; //starea 1 e starea care va prezice taken

	/*
		the array with program counter of caller JUMPS
		(for targeted local branch prediction) for dynamic 1 bit predictor
		--
		This is used to remember the state of a jump prediction at one defined moment
		(RO: folosit sa tina minte starea curenta prin care a trecut un Jump)
		--
		Default value is empty array
	*/
	public static $arr_1_bit = [];




	/*
		the default(start state) of dynamic 2 bit predictor
		--
		we suppose that the dynamic 2 bit predictor was in state 10(weakly taken) at the beggining
		(from math and tests done by others , this is the best state to start the automata)
		--
		This automata is on 2bit
		--
		I am giving value as a STRING("10") because if u pass 00 or 01 as an int, php will understand it as an 0 or 1 and it
		destroy the functionality of predictor(the switch statement inside predictor)
	*/
	public static $dynamic_2_bit_start_state = "10"; //starea 10 e starea care va prezice taken(weakly)

	/*
		the array with program counter of caller JUMPS
		(for targeted local branch prediction) for dynamic 2 bit predictor
		--
		This is used to remember the state of a jump prediction at one defined moment
		(RO: folosit sa tina minte starea curenta prin care a trecut un Jump)
		--
		Default value is empty array
	*/
	public static $arr_2_bit = [];





	/*
		variable used for displaying in table of order of execution  - semafor de good prediction
		---
		RO:
		variabila folosita pentru a fi afisata valoarea curenta de predictie corecta sau gresita 
		in tabelul de ordine executie instructiuni
		(EX: pentru un JNZ curent parcurs , daca jumpul a fost taken si predictia a prezis ca va fii taken,
		atunci aceasta variabila va deveni "1"[good prediction 1 ] , iar la urmatoarea trecere prin codul de predictori 
		resetez aceasta variabila , cat si variabila cealalta - miss_prediction)
		----
		ALLOWED VALUES:
		String "0" or String "1".

		Default Value:
		String "0"

		Alternate names:
		current stage good prediction
	*/
	public static $good_prediction = "0";


	/*
		variable used for displaying in table of order of execution - semafor de miss prediction
		---
		RO:
		variabila folosita pentru a fi afisata valoarea curenta de predictie corecta sau gresita 
		in tabelul de ordine executie instructiuni
		(EX: pentru un JNZ curent parcurs , daca jumpul a fost taken si predictia a prezis ca va fii taken,
		atunci aceasta variabila va deveni "1"[good prediction 1 ] , iar la urmatoarea trecere prin codul de predictori 
		resetez aceasta variabila , cat si variabila cealalta - miss_prediction)
		----
		ALLOWED VALUES:
		String "0" or String "1".

		Default Value:
		String "0"

		Alternate names:
		current stage miss prediction
	*/
	public static $miss_prediction = "0";



	/****************************************************************************************************************
		Methods of this class
	*****************************************************************************************************************/
	
	/**************************************************************
		Predictors methods
		The following methods below are for 
		working with predictors
	**************************************************************/

	/*****
		HOW PREDICTORS WORKS:
			GENERAL:
			----
			Jump-ul intreaba predictorul:
			prezice-mi si mie aici, iar predictorul ii da predictia(raspunsul), jump vede daca e bun raspunsul si ii trimite
			raspuns inapoi. Daca predictia nu a fost buna, incrementeaza un contor in clasa predictor de miss predictions, 
			altfel, incrementeaza un contor de good predictions.
			Si practic , dupa asta ma iau eu.Doar dupa acest contor vad cat de bun e predictorul meu. Ieeeee
			Ceata ridicata.

			Steps(in interiorul jumpurilor):
			1. preia variabila post care spune ce predictor se foloseste curent
				(setat default pe 0 ca sa am intotdeauna o predictie)
			2. seteaza o variabila branch_taken(jump_taken) pe fiecare JP in parte (langa var $temp_pc)
			3. fa template-ul de cod predictie (vezi cum l-am construit in JNZ , la static not-taken)


			A.STATIC NOT-TAKEN:
			----
			raspunde intotdeauna ca predictie "taken"("no"(nu)) (prezice ca ramura NU se va urma)


			B.STATIC TAKEN:
			----
			raspunde intotdeauna ca predictie "not_taken" ("yes"(da)) (prezice ca ramura se va urma)


			C.DYNAMIC 1BIT PREDICTOR
			----
			behaviour to be defined
	*****/





	/*****
		This is the first predictor: Static NOT Taken
		Equivalent to : without prediction

		Behaviour:
		This predictor will always predict that the branch will not be taken(responds "not_taken" ["no"])
	
		Params:
		No params for this method(for future methods ,like 1bit predictor there might be some params)

		@return String , "not_taken"("no") in a sense that this predicts that branch is not taken

		CALL:
		This method is called inside JNZ,JPZ, JNC,JPC, JNN,JPN only (fara JNO,JPO)
	*****/
	public static function predictStaticNotTaken()
	{
		//reset the good prediction and miss prediction semaphore variables
		static::$good_prediction = "0";
		static::$miss_prediction = "0";

		//now follows this predictor functionality:
		return "not_taken";
	}



	/*****
		This is the response from the Jump instruction
		for the fist predictor (STATIC NOT TAKEN) - i think it's the same for all the predictors

		@param string $prediction, the response from caller Jump that tells Predictor class 
			if this predictor had a good prediction or miss prediction

		@returns void NOTHING, just increment counter for specific case	

		CALL:
		This method is called inside JNZ,JPZ, JNC,JPC, JNN,JPN only (fara JNO,JPO)
	*****/
	public static function predictStaticNotTaken_response($prediction)
	{
		if ($prediction == "good_prediction") {
			//if it was good prediction , set current stage good prediction to "1"
			static::$good_prediction = "1";

			//if this was a good prediction , then inc counter_good_predictions
			static::$counter_good_predictions++ ;
		} 
		else if ($prediction == "miss_prediction") {
			//if it was miss prediction , set current stage miss prediction to "1"
			static::$miss_prediction = "1";

			//if this was a miss prediction , then inc counter_miss_predictions
			static::$counter_miss_predictions++ ;
		}
	}







	/*****
		This is the second predictor: Static Taken
		Equivalent to : always "yes" prediction

		Behaviour:
		This predictor will always predict that the branch will be taken(respond "taken" ["yes"])
	
		Params:
		No params for this method(for future methods ,like 1bit predictor there might be some params)

		@return String , "taken"["yes"] in a sense that this predicts that branch is taken

		CALL:
		This method is called inside JNZ,JPZ, JNC,JPC, JNN,JPN only (fara JNO,JPO)
	*****/
	public static function predictStaticTaken()
	{
		//reset the good prediction and miss prediction semaphore variables
		static::$good_prediction = "0";
		static::$miss_prediction = "0";

		//now follows this predictor functionality:
		return "taken";
	}



	/*****
		This is the response from the Jump instruction
		for the second predictor (STATIC TAKEN) - i think it's the same for all the predictors

		@param string $prediction, the response from caller Jump that tells Predictor class 
			if this predictor had a good prediction or miss prediction

		@returns void NOTHING, just increment counter for specific case	

		CALL:
		This method is called inside JNZ,JPZ, JNC,JPC, JNN,JPN only (fara JNO,JPO)
	*****/
	public static function predictStaticTaken_response($prediction)
	{
		if ($prediction == "good_prediction") {
			//if it was good prediction , set current stage good prediction to "1"
			static::$good_prediction = "1";

			//if this was a good prediction , then inc counter_good_predictions
			static::$counter_good_predictions++ ;
		} 
		else if ($prediction == "miss_prediction") {
			//if it was miss prediction , set current stage miss prediction to "1"
			static::$miss_prediction = "1";

			//if this was a miss prediction , then inc counter_miss_predictions
			static::$counter_miss_predictions++ ;
		}
	}










	/*****
		This is the third predictor: Dynamic 1 bit
		Equivalent to : this is dynamic prediction

		Type of Prediction:
		Branch Prediction(not global) - this means that for each jump will predict differently(prezice
		doar pentru un jump in parte , nu e afectat de deciziile care s-au facut la alte jumpuri)
		NOTA:
		-Daca nu e nevoie de branch local, furnizez ca parametru la apelare in loc de pc , un parametru generic (pentru jnz dau "jnz",
		 pentru jpz dau "jpz" si tot asa)
		-Daca e global dau acelasi parametru la toate apeluri din toate jumpurile(unul generic, de ex: "generic") 

		Behaviour:
		----
		Dynamic 1bit Predictor works like an 2-state automata(like an 1bit saturating counter, like an flip-flop):
		Definire Automat:
		Stari:
			State 0 : Not_Taken
			State 1 : Taken
		Tranzitii:(2 tipuri de tranzitii: T[Taken] si NT[Not_Taken])
			0 -> 0 : NT (va merge din starea 0 la starea 0 , cand e tranzitie NT)
			0 -> 1 : T  (din starea 0 pe tranzitie T, va merge in starea 1)
			1 -> 0 : NT
			1 -> 1 : T

		Links for understading behaviour:
		https://en.wikipedia.org/wiki/Branch_predictor#Dynamic_branch_prediction
		https://www.youtube.com/watch?v=malQIOtaAuU   -  Problems With 1 Bit Predictions - Georgia Tech - HPCA: Part 1  -  Udacity
		https://www.youtube.com/watch?v=5d2VW0yO-xE   -  Branch Prediction (1-bit and 2-bit predictors)  -  Ravish DeliWala
		D:\Php_xampp\htdocs\cpuBP_master\public\tests_and_resources\RESOURCES\dynamic_predictions_schema  - schema functionare predictorii mei

		Params:
		@param int $pc, the current program counter of caller JUMP (pentru a prezice doar pentru acest jump)
		@param String $jump_taken, vine din instr JUMP si imi spune daca jump-ul a fost luat sau nu

		@return String , "taken"["yes"] dupa modelul din video Ravish Deliwala
						 SAU
						 "not_taken"["no"]

		CALL:
		This method is called inside JNZ,JPZ, JNC,JPC, JNN,JPN only (fara JNO,JPO)
	*****/
	public static function predictDynamic1Bit($pc, $jump_taken)
	{
		//reset the good prediction and miss prediction semaphore variables(before entering functionality of predictor, do this)
		static::$good_prediction = "0";
		static::$miss_prediction = "0";

		//now follows this predictor functionality:


		//tine cont ca pc incepe de la 0

		//get predictor state to continue predicting
		//predictor must start in a state -- default start state is 1(predicts taken)
		$current_state_predictor = static::$dynamic_1_bit_start_state;

		//verifica daca acest jump(pc) a mai fost apelat cu predictie dinamica 
		//si pune starea curenta a predictorului 1 bit in starea acestui jump
		if (array_key_exists($pc, static::$arr_1_bit)) {
			//daca exista, schimba starea curenta a automatului nostru cu aceasta
			$current_state_predictor = static::$arr_1_bit[$pc];
			//testare 
			//echo "<br>" . $current_state_predictor . "<br>";
		} else {
			//daca nu exista creeaza intrare/cheie in array cu acest pc si starea default/de start
			static::$arr_1_bit[$pc] = static::$dynamic_1_bit_start_state ;
			//testare
			//echo "<pre>";  print_r(static::$arr_1_bit);  echo "</pre>";
		}

		//echo "<pre>";  print_r(static::$arr_1_bit);  echo "</pre>";

		//acum ca am obtinut starea din care sa incepem predictia($current_state_predictor) , construim automatul
		//faci switch aici si build automat
		switch ($current_state_predictor) {
			case 0:
				//fa tranzitii si updateaza array pentru urmatoare intrare in functie(dupa model yt si poza resources)
				//tranzitie NT(not_taken) pe starea 0
			    if ($jump_taken == "not_taken") { 
			    	static::$arr_1_bit[$pc] = 0; // la NT pe starea 0 vom merge din nou pe starea 0
			    }
			    //tranzitie T(taken) pe starea 0
			    if ($jump_taken == "taken") {
			    	static::$arr_1_bit[$pc] = 1;
			    }

				//return dynamic 1 bit response
				return "not_taken";
				break;
			

			case 1:
				//fa tranzitii si updateaza array pentru urmatoare intrare in functie(dupa model yt si poza resources)
				//tranzitie NT(not_taken) pe starea 1
			    if ($jump_taken == "not_taken") { 
			    	static::$arr_1_bit[$pc] = 0; // la NT pe starea 1 vom merge pe starea 0
			    }
			    //tranzitie T(taken) pe starea 1
			    if ($jump_taken == "taken") {
			    	static::$arr_1_bit[$pc] = 1;
			    }

			    //return dynamic 1 bit response
				return "taken";
				break;


			default:
				# code...
				break;
		}
	}



	/*****
		This is the response from the Jump instruction
		for the third predictor (DYNAMIC 1 BIT) - i think it's the same for all the predictors

		@param string $prediction, the response from caller Jump that tells Predictor class 
			if this predictor had a good prediction or miss prediction

		@returns void NOTHING, just increment counter for specific case	

		CALL:
		This method is called inside JNZ,JPZ, JNC,JPC, JNN,JPN only (fara JNO,JPO)
	*****/
	public static function predictDynamic1bit_response($prediction)
	{
		if ($prediction == "good_prediction") {
			//if it was good prediction , set current stage good prediction to "1"
			static::$good_prediction = "1";

			//if this was a good prediction , then inc counter_good_predictions
			static::$counter_good_predictions++ ;
		} 
		else if ($prediction == "miss_prediction") {
			//if it was miss prediction , set current stage miss prediction to "1"
			static::$miss_prediction = "1";

			//if this was a miss prediction , then inc counter_miss_predictions
			static::$counter_miss_predictions++ ;
		}
	}










	/*****
		This is the forth(AND THE LAST) predictor: Dynamic 2 bit
		Equivalent to : this is dynamic prediction on 2 bits saturating counter

		Type of Prediction:
		Branch Prediction(not global) - this means that for each jump will predict differently(prezice
		doar pentru un jump in parte , nu e afectat de deciziile care s-au facut la alte jumpuri)
		NOTA:
		-Daca nu e nevoie de branch local, furnizez ca parametru la apelare in loc de pc , un parametru generic (pentru jnz dau "jnz",
		 pentru jpz dau "jpz" si tot asa)
		-Daca e global dau acelasi parametru la toate apeluri din toate jumpurile(unul generic, de ex: "generic") 

		Behaviour:
		----
		Dynamic 2bit Predictor works like an 4-state automata(like an 2bit saturating counter, ):
		Definire Automat:
		Stari:
			State 00 : Not_Taken (Strongly not taken)
			State 01 : Not_Taken (Weakly not taken)
			State 10 : Taken     (Weakly taken)
			State 11 : Taken     (Strongly taken)
		Tranzitii:(2 tipuri de tranzitii: T[Taken] si NT[Not_Taken])
			00 -> 00 : NT (va merge din starea 00 la starea 00 , cand e tranzitie NT)
			00 -> 01 : T  (din starea 00 pe tranzitie T, va merge in starea 01)
			01 -> 00 : NT
			01 -> 10 : T
			10 -> 01 : NT
			10 -> 11 : T
			11 -> 10 : NT
			11 -> 11 : T

		Links for understading behaviour:
		https://en.wikipedia.org/wiki/Branch_predictor#Dynamic_branch_prediction
		https://www.youtube.com/watch?v=malQIOtaAuU   -  Problems With 1 Bit Predictions - Georgia Tech - HPCA: Part 1  -  Udacity
		https://www.youtube.com/watch?v=5d2VW0yO-xE   -  Branch Prediction (1-bit and 2-bit predictors)  -  Ravish DeliWala
		D:\Php_xampp\htdocs\cpuBP_master\public\tests_and_resources\RESOURCES\dynamic_predictions_schema  - schema functionare predictorii mei

		Params:
		@param int $pc, the current program counter of caller JUMP (pentru a prezice doar pentru acest jump[doar jump-ul care il apeleaza])
		@param String $jump_taken, vine din instr JUMP si imi spune daca jump-ul a fost luat sau nu

		@return String , "taken"["yes"] dupa modelul din video Ravish Deliwala
						 SAU
						 "not_taken"["no"]

		CALL:
		This method is called inside JNZ,JPZ, JNC,JPC, JNN,JPN only (fara JNO,JPO)
	*****/
	public static function predictDynamic2Bit($pc, $jump_taken)
	{
		//reset the good prediction and miss prediction semaphore variables(before entering functionality of predictor, do this)
		static::$good_prediction = "0";
		static::$miss_prediction = "0";

		//now follows this predictor functionality:


		//tine cont ca pc incepe de la 0

		//get predictor state to continue predicting
		//predictor must start in a state -- default start state is 1(predicts taken)
		$current_state_predictor = static::$dynamic_2_bit_start_state;

		//verifica daca acest jump(pc) a mai fost apelat cu predictie dinamica 
		//si pune starea curenta a predictorului 2 bit in starea acestui jump
		if (array_key_exists($pc, static::$arr_2_bit)) {
			//daca exista, schimba starea curenta a automatului nostru cu aceasta
			$current_state_predictor = static::$arr_2_bit[$pc];
			//testare 
			//echo "<br>" . $current_state_predictor . "<br>";
		} else {
			//daca nu exista creeaza intrare/cheie in array cu acest pc si starea default/de start
			static::$arr_2_bit[$pc] = static::$dynamic_2_bit_start_state ;
			//testare
			//echo "<pre>";  print_r(static::$arr_2_bit);  echo "</pre>";
		}

		//echo "<pre>";  print_r(static::$arr_2_bit);  echo "</pre>";

		//acum ca am obtinut starea din care sa incepem predictia($current_state_predictor) , construim automatul
		//faci switch aici si build automat
		switch ($current_state_predictor) {
			case "00":
				//STRONGLY NOT TAKEN
				//fa tranzitii si updateaza array pentru urmatoare intrare in functie
				//tranzitie NT(not_taken) pe starea 00
			    if ($jump_taken == "not_taken") { 
			    	static::$arr_2_bit[$pc] = "00"; // la NT pe starea 00 vom merge din nou pe starea 00
			    }
			    //tranzitie T(taken) pe starea 00
			    if ($jump_taken == "taken") {
			    	static::$arr_2_bit[$pc] = "01";
			    }

				//return dynamic 2 bit response
				return "not_taken";
				break;


			case "01":
				//WEAKLY NOT TAKEN
				//fa tranzitii si updateaza array pentru urmatoare intrare in functie
				//tranzitie NT(not_taken) pe starea 01
			    if ($jump_taken == "not_taken") { 
			    	static::$arr_2_bit[$pc] = "00"; // la NT pe starea 01 vom merge pe starea 00
			    }
			    //tranzitie T(taken) pe starea 01
			    if ($jump_taken == "taken") {
			    	static::$arr_2_bit[$pc] = "10";
			    }

				//return dynamic 2 bit response
				return "not_taken";
				break;


			case "10":
				//WEAKLY TAKEN
				//fa tranzitii si updateaza array pentru urmatoare intrare in functie
				//tranzitie NT(not_taken) pe starea 10
			    if ($jump_taken == "not_taken") { 
			    	static::$arr_2_bit[$pc] = "01"; // la NT pe starea 10 vom merge pe starea 01
			    }
			    //tranzitie T(taken) pe starea 10
			    if ($jump_taken == "taken") {
			    	static::$arr_2_bit[$pc] = "11";
			    }

				//return dynamic 2 bit response
				return "taken";
				break;


			case "11":
				//STRONGLY TAKEN
				//fa tranzitii si updateaza array pentru urmatoare intrare in functie
				//tranzitie NT(not_taken) pe starea 11
			    if ($jump_taken == "not_taken") { 
			    	static::$arr_2_bit[$pc] = "10"; // la NT pe starea 11 vom merge pe starea 10
			    }
			    //tranzitie T(taken) pe starea 11
			    if ($jump_taken == "taken") {
			    	static::$arr_2_bit[$pc] = "11";
			    }

				//return dynamic 2 bit response
				return "taken";
				break;


			default:
				# code...
				break;
		}
	}



	/*****
		This is the response from the Jump instruction
		for the FORTH predictor (DYNAMIC 2 BIT BRANCH PREDICTOR) -it's the same for all the predictors

		@param string $prediction, the response from caller Jump that tells Predictor class 
			if this predictor had a good prediction or miss prediction

		@returns void NOTHING, just increment counter for specific case	

		CALL:
		This method is called inside JNZ,JPZ, JNC,JPC, JNN,JPN only (fara JNO,JPO)
	*****/
	public static function predictDynamic2bit_response($prediction)
	{
		if ($prediction == "good_prediction") {
			//if it was good prediction , set current stage good prediction to "1"
			static::$good_prediction = "1";

			//if this was a good prediction , then inc counter_good_predictions
			static::$counter_good_predictions++ ;
		} 
		else if ($prediction == "miss_prediction") {
			//if it was miss prediction , set current stage miss prediction to "1"
			static::$miss_prediction = "1";

			//if this was a miss prediction , then inc counter_miss_predictions
			static::$counter_miss_predictions++ ;
		}
	}


	/**************************************************************
		Getters for attributes methods
	**************************************************************/

	/*
		get value of counter good predictions
	*/
	public static function getCounterGoodPredictions()
	{
		return static::$counter_good_predictions;
	}	



	/*
		get value of counter miss predictions
	*/
	public static function getCounterMissPredictions()
	{
		return static::$counter_miss_predictions;
	}	












} //end class Predictor


?>