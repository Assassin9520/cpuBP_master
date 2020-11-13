<?php

namespace Core;

//no need to include config.php here , it was included in interpretor.php , which already includes this file(parser.php)
//include("Config.php");




/*
	The Parser class
	This accepts a token as param in __construct
	Analizes Tokens and executes code based on some arrangements

	REFERENCE: REFER TO Parser_OLD.php

	php ver 5.5.0
*/
class Parser
{
	/**************
	class Attributes
	***************/

	/*token attr - will host the tokens from the tokenizer*/
	public static $tokens;

	/*current index in the tokens array(current_tokens_index)*/
	public static $ti = 0;//$ti = token_index


	/*================*/
	/*   Class Methods
	/*================*/


	/*
	parse method , will resolve the main parsing
	Will Build the ABSTRACT SYNTAX TREE ( AST ) , which , therefore 
	, will be given to the interpretor to interpret

	@param Array $tokens, the $tokens array from the Tokenizer

	@return the AST(abstract syntax tree) for further interpreting
	*/
	public static function parse($tokens)
	{

		//set tokens var of this class
		static::$tokens = $tokens;

		//call main method of Our Context-Free Grammar and away we go with parsing
		return static::ExpresiePrincipala();

	}




	/*************************************************
		PASI CONSTRUIRE INSTRUCTIUNE NOUA:
		1. pui instructiunea in lista asta de comentarii la Structura Gramaticii
		2. creezi recunoastere instructiune in metoda Instructiune() in switch
		3. creezi metoda care va parsa instructiunea adaugata de tine(metode precum MOV,JMP,CMP,etc...)


		THE GRAMMAR
		You also have example of this in imageInterpreter app
		REFERENCE: REFER TO Parser_OLD.php in this folder

		VER 1.0 :
		Structura gramaticii(until now) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> MOV | JNZ | JPZ | LABEL_ETICHETA | JMP | JNC | JPC | JNN | JPN | JNO | JPO | STR | LDR | ADD | ADC | SUB | 
									 | SBC | AND | ORR | XOR | CMP | INV | SHL | SHR | ROL | ROR | PSH | POP | NOP | HALT 
										//(operatori neterminali)
			MOV            -> <MOV> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL>| <HEXA_NUMBER>)    (operatori terminali [Tokens])



		VER 2.0 :
		Structura gramaticii(until now) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> MOV | JNZ | JPZ | LABEL_ETICHETA | JMP | JNC | JPC | JNN | JPN | JNO | JPO | STR | LDR | ADD | ADC | SUB | 
									 | SBC | AND | ORR | XOR | CMP | INV | SHL | SHR | ROL | ROR | PSH | POP | NOP | HALT 
										//(operatori neterminali)
			MOV            -> <MOV> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL>| <HEXA_NUMBER>)    (operatori terminali [Tokens])
			---------------
			JNZ            -> <JNZ> <HEXA_NUMBER>	
			JPZ            -> <JPZ> <HEXA_NUMBER>
			LABEL_ETICHETA -> <LABEL_ETICHETA>
			JMP            -> <JMP> <HEXA_NUMBER>
			JNC            -> <JNC> <HEXA_NUMBER>
			JPC            -> <JPC> <HEXA_NUMBER>
			JNN            -> <JNN> <HEXA_NUMBER>
			JPN            -> <JPN> <HEXA_NUMBER>
			JNO            -> <JNO> <HEXA_NUMBER>
			JPO            -> <JPO> <HEXA_NUMBER>



		VER 3.0 :
		Structura gramaticii(until now) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> MOV | JNZ | JPZ | LABEL_ETICHETA | JMP | JNC | JPC | JNN | JPN | JNO | JPO | STR | LDR | ADD | ADC | SUB | 
									 | SBC | AND | ORR | XOR | CMP | INV | SHL | SHR | ROL | ROR | PSH | POP | NOP | HALT 
										//(operatori neterminali)
			MOV            -> <MOV> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL>| <HEXA_NUMBER>)    (operatori terminali [Tokens])
			JNZ            -> <JNZ> <HEXA_NUMBER>	
			JPZ            -> <JPZ> <HEXA_NUMBER>
			LABEL_ETICHETA -> <LABEL_ETICHETA>
			JMP            -> <JMP> <HEXA_NUMBER>
			JNC            -> <JNC> <HEXA_NUMBER>
			JPC            -> <JPC> <HEXA_NUMBER>
			JNN            -> <JNN> <HEXA_NUMBER>
			JPN            -> <JPN> <HEXA_NUMBER>
			JNO            -> <JNO> <HEXA_NUMBER>
			JPO            -> <JPO> <HEXA_NUMBER>
            ---------------
            STR            -> <STR> <REGISTER_GENERAL> <COMMA> ( (<REGISTER_GENERAL><COMMA><REGISTER_GENERAL>) | <HEXA_NUMBER> )
            LDR            -> <LDR> <REGISTER_GENERAL> <COMMA> ( (<REGISTER_GENERAL><COMMA><REGISTER_GENERAL>) | <HEXA_NUMBER> )
			ADD            -> <ADD> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL> | <HEXA_NUMBER>)
			ADC            -> <ADC> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			SUB            -> <SUB> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL> | <HEXA_NUMBER>)
			SBC            -> <SBC> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 



		VER 4.0 :
		Structura gramaticii(until now) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> MOV | JNZ | JPZ | LABEL_ETICHETA | JMP | JNC | JPC | JNN | JPN | JNO | JPO | STR | LDR | ADD | ADC | SUB | 
									 | SBC | AND | ORR | XOR | CMP | INV | SHL | SHR | ROL | ROR | PSH | POP | NOP | HALT 
										//(operatori neterminali)
			MOV            -> <MOV> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL>| <HEXA_NUMBER>)    (operatori terminali [Tokens])
			JNZ            -> <JNZ> <HEXA_NUMBER>	
			JPZ            -> <JPZ> <HEXA_NUMBER>
			LABEL_ETICHETA -> <LABEL_ETICHETA>
			JMP            -> <JMP> <HEXA_NUMBER>
			JNC            -> <JNC> <HEXA_NUMBER>
			JPC            -> <JPC> <HEXA_NUMBER>
			JNN            -> <JNN> <HEXA_NUMBER>
			JPN            -> <JPN> <HEXA_NUMBER>
			JNO            -> <JNO> <HEXA_NUMBER>
			JPO            -> <JPO> <HEXA_NUMBER>
            ---------------
            STR            -> <STR> <REGISTER_GENERAL> <COMMA> ( (<REGISTER_GENERAL><COMMA><REGISTER_GENERAL>) | <HEXA_NUMBER> )
            LDR            -> <LDR> <REGISTER_GENERAL> <COMMA> ( (<REGISTER_GENERAL><COMMA><REGISTER_GENERAL>) | <HEXA_NUMBER> )
			ADD            -> <ADD> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL> | <HEXA_NUMBER>)
			ADC            -> <ADC> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			SUB            -> <SUB> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL> | <HEXA_NUMBER>)
			SBC            -> <SBC> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			---------------
			AND            -> <AND> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			ORR            -> <ORR> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			XOR            -> <XOR> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			CMP            -> <CMP> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 



		VER 5.0 :
		Structura gramaticii(until now) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> MOV | JNZ | JPZ | LABEL_ETICHETA | JMP | JNC | JPC | JNN | JPN | JNO | JPO | STR | LDR | ADD | ADC | SUB | 
									 | SBC | AND | ORR | XOR | CMP | INV | SHL | SHR | ROL | ROR | PSH | POP | NOP | HALT 
										//(operatori neterminali)
			MOV            -> <MOV> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL>| <HEXA_NUMBER>)    (operatori terminali [Tokens])
			JNZ            -> <JNZ> <HEXA_NUMBER>	
			JPZ            -> <JPZ> <HEXA_NUMBER>
			LABEL_ETICHETA -> <LABEL_ETICHETA>
			JMP            -> <JMP> <HEXA_NUMBER>
			JNC            -> <JNC> <HEXA_NUMBER>
			JPC            -> <JPC> <HEXA_NUMBER>
			JNN            -> <JNN> <HEXA_NUMBER>
			JPN            -> <JPN> <HEXA_NUMBER>
			JNO            -> <JNO> <HEXA_NUMBER>
			JPO            -> <JPO> <HEXA_NUMBER>
            ---------------
            STR            -> <STR> <REGISTER_GENERAL> <COMMA> ( (<REGISTER_GENERAL><COMMA><REGISTER_GENERAL>) | <HEXA_NUMBER> )
            LDR            -> <LDR> <REGISTER_GENERAL> <COMMA> ( (<REGISTER_GENERAL><COMMA><REGISTER_GENERAL>) | <HEXA_NUMBER> )
			ADD            -> <ADD> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL> | <HEXA_NUMBER>)
			ADC            -> <ADC> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			SUB            -> <SUB> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL> | <HEXA_NUMBER>)
			SBC            -> <SBC> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			---------------
			AND            -> <AND> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			ORR            -> <ORR> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			XOR            -> <XOR> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			CMP            -> <CMP> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			---------------
			INV            -> <INV> <REGISTER_GENERAL>
			SHL            -> <SHL> <REGISTER_GENERAL>
			SHR            -> <SHR> <REGISTER_GENERAL>
			ROL            -> <ROL> <REGISTER_GENERAL>
			ROR            -> <ROR> <REGISTER_GENERAL>
			PSH            -> <PSH> <REGISTER_GENERAL>
			POP            -> <POP> <REGISTER_GENERAL>
			NOP            -> <NOP>
			HALT           -> <HALT>



		VER 6.0 : (adaugate recunoastere Label-uri inauntru jump-uri)
		Structura gramaticii(until now) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> MOV | JNZ | JPZ | LABEL_ETICHETA | JMP | JNC | JPC | JNN | JPN | JNO | JPO | STR | LDR | ADD | ADC | SUB | 
									 | SBC | AND | ORR | XOR | CMP | INV | SHL | SHR | ROL | ROR | PSH | POP | NOP | HALT 
										//(operatori neterminali)
			MOV            -> <MOV> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL>| <HEXA_NUMBER>)    (operatori terminali [Tokens])
			JNZ            -> <JNZ> <HEXA_NUMBER>	
			JPZ            -> <JPZ> <HEXA_NUMBER>
			LABEL_ETICHETA -> <LABEL_ETICHETA>
			JMP            -> <JMP> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			JNC            -> <JNC> <HEXA_NUMBER>
			JPC            -> <JPC> <HEXA_NUMBER>
			JNN            -> <JNN> <HEXA_NUMBER>
			JPN            -> <JPN> <HEXA_NUMBER>
			JNO            -> <JNO> <HEXA_NUMBER>
			JPO            -> <JPO> <HEXA_NUMBER>
            ---------------
            STR            -> <STR> <REGISTER_GENERAL> <COMMA> ( (<REGISTER_GENERAL><COMMA><REGISTER_GENERAL>) | <HEXA_NUMBER> )
            LDR            -> <LDR> <REGISTER_GENERAL> <COMMA> ( (<REGISTER_GENERAL><COMMA><REGISTER_GENERAL>) | <HEXA_NUMBER> )
			ADD            -> <ADD> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL> | <HEXA_NUMBER>)
			ADC            -> <ADC> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			SUB            -> <SUB> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL> | <HEXA_NUMBER>)
			SBC            -> <SBC> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			---------------
			AND            -> <AND> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			ORR            -> <ORR> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			XOR            -> <XOR> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			CMP            -> <CMP> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			---------------
			INV            -> <INV> <REGISTER_GENERAL>
			SHL            -> <SHL> <REGISTER_GENERAL>
			SHR            -> <SHR> <REGISTER_GENERAL>
			ROL            -> <ROL> <REGISTER_GENERAL>
			ROR            -> <ROR> <REGISTER_GENERAL>
			PSH            -> <PSH> <REGISTER_GENERAL>
			POP            -> <POP> <REGISTER_GENERAL>
			NOP            -> <NOP>
			HALT           -> <HALT>
			---------------



		VER 7.0 : (adaugate recunoastere Label-uri inauntru jump-uri)
		Structura gramaticii(until now) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> MOV | JNZ | JPZ | LABEL_ETICHETA | JMP | JNC | JPC | JNN | JPN | JNO | JPO | STR | LDR | ADD | ADC | SUB | 
									 | SBC | AND | ORR | XOR | CMP | INV | SHL | SHR | ROL | ROR | PSH | POP | NOP | HALT 
										//(operatori neterminali)
			MOV            -> <MOV> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL>| <HEXA_NUMBER>)    (operatori terminali [Tokens])
			JNZ            -> <JNZ> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			JPZ            -> <JPZ> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			LABEL_ETICHETA -> <LABEL_ETICHETA>
			JMP            -> <JMP> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			JNC            -> <JNC> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			JPC            -> <JPC> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			JNN            -> <JNN> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			JPN            -> <JPN> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			JNO            -> <JNO> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			JPO            -> <JPO> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
            ---------------
            STR            -> <STR> <REGISTER_GENERAL> <COMMA> ( (<REGISTER_GENERAL><COMMA><REGISTER_GENERAL>) | <HEXA_NUMBER> )
            LDR            -> <LDR> <REGISTER_GENERAL> <COMMA> ( (<REGISTER_GENERAL><COMMA><REGISTER_GENERAL>) | <HEXA_NUMBER> )
			ADD            -> <ADD> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL> | <HEXA_NUMBER>)
			ADC            -> <ADC> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			SUB            -> <SUB> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL> | <HEXA_NUMBER>)
			SBC            -> <SBC> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			---------------
			AND            -> <AND> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			ORR            -> <ORR> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			XOR            -> <XOR> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			CMP            -> <CMP> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			---------------
			INV            -> <INV> <REGISTER_GENERAL>
			SHL            -> <SHL> <REGISTER_GENERAL>
			SHR            -> <SHR> <REGISTER_GENERAL>
			ROL            -> <ROL> <REGISTER_GENERAL>
			ROR            -> <ROR> <REGISTER_GENERAL>
			PSH            -> <PSH> <REGISTER_GENERAL>
			POP            -> <POP> <REGISTER_GENERAL>
			NOP            -> <NOP>
			HALT           -> <HALT>
			---------------



		VER 8.0 : (adaugate instr Clear flag-uri CLZ, CLC, CLN - fara clear overflow flag , nu e implementat nicaieri)
		Structura gramaticii(until now) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> MOV | JNZ | JPZ | LABEL_ETICHETA | JMP | JNC | JPC | JNN | JPN | JNO | JPO | STR | LDR | ADD | ADC | SUB | 
									 | SBC | AND | ORR | XOR | CMP | INV | SHL | SHR | ROL | ROR | PSH | POP | NOP | HALT 
										//(operatori neterminali)
			MOV            -> <MOV> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL>| <HEXA_NUMBER>)    (operatori terminali [Tokens])
			JNZ            -> <JNZ> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			JPZ            -> <JPZ> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			LABEL_ETICHETA -> <LABEL_ETICHETA>
			JMP            -> <JMP> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			JNC            -> <JNC> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			JPC            -> <JPC> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			JNN            -> <JNN> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			JPN            -> <JPN> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			JNO            -> <JNO> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
			JPO            -> <JPO> (<HEXA_NUMBER> | <LABEL_ETICHETA_IN_JUMP> )
            ---------------
            STR            -> <STR> <REGISTER_GENERAL> <COMMA> ( (<REGISTER_GENERAL><COMMA><REGISTER_GENERAL>) | <HEXA_NUMBER> )
            LDR            -> <LDR> <REGISTER_GENERAL> <COMMA> ( (<REGISTER_GENERAL><COMMA><REGISTER_GENERAL>) | <HEXA_NUMBER> )
			ADD            -> <ADD> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL> | <HEXA_NUMBER>)
			ADC            -> <ADC> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			SUB            -> <SUB> <REGISTER_GENERAL> <COMMA> (<REGISTER_GENERAL> | <HEXA_NUMBER>)
			SBC            -> <SBC> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			---------------
			AND            -> <AND> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			ORR            -> <ORR> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			XOR            -> <XOR> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			CMP            -> <CMP> <REGISTER_GENERAL> <COMMA> <REGISTER_GENERAL> 
			---------------
			INV            -> <INV> <REGISTER_GENERAL>
			SHL            -> <SHL> <REGISTER_GENERAL>
			SHR            -> <SHR> <REGISTER_GENERAL>
			ROL            -> <ROL> <REGISTER_GENERAL>
			ROR            -> <ROR> <REGISTER_GENERAL>
			PSH            -> <PSH> <REGISTER_GENERAL>
			POP            -> <POP> <REGISTER_GENERAL>
			NOP            -> <NOP>
			HALT           -> <HALT>
			---------------
			CLZ            -> <CLZ>
			CLC            -> <CLC>
			CLN            -> <CLN>
			---------------

	**************************************************/



	/*
	main program grammar
	*/
	public static function ExpresiePrincipala()
	{
		$ast = [];

		while ( ! static::endOfTokens() ) { //cat timp nu s-au parcurs toti tokenii din Tokens
			//Ruleaza metoda Instructiune();
			$ast[] = static::Instructiune();

			//if we have any false key in array , return false(this is FOR ERRORS-- INCEARCA PE VIITOR ALTA METODA DE AFISARE A ERORILOR)
			//Testul asta apare si in functia gramaticii Repeta , si in oricare alta instructiune nested
			// if ( is_int(helper_recursive_array_search(false, $ast)) ) {
			// 	return false;
			// }

			/*OR return false with the following test:*/

			if(Error::occur()){//if any error occured from parsing
			    return false;
			}

		}

		if (static::endOfTokens()) {//daca s-a ajuns la sfarsitul tokens-ului si os sa mai adaug && Errors::getErrors() is empty
			//echo "Parsing finished. Everything ok with your code.  <br>";
		}

		return $ast;
	}/*end of method ExpresiePrincipala*/




	/*Instructiune method of Grammar*/
	public static function Instructiune()
	{
		/*Some Testing*/
		// static::displayTokens();
		// die();


		//switch after next token
		switch (static::TokenUrmator()) {

			case 'MOV_IC':
				//MOV assembly AST parsing node build
				return static::MOV();
				break;//always break from case

			case 'SINGLELINE_COMMENT':
				static::EatToken('SINGLELINE_COMMENT');//if u meet an single line comment , jump over it
				break;	

			case T_STMT_JNZ:
				return static::JNZ();	
				break;

			case T_STMT_JPZ:
				return static::JPZ();	
				break;

			case T_LABEL:
				return static::LABEL_ETICHETA();
				break;	

			case T_STMT_JMP:
				return static::JMP();	
				break;

			case T_STMT_JNC:
				return static::JNC();	
				break;

			case T_STMT_JPC:
				return static::JPC();	
				break;

			case T_STMT_JNN:
				return static::JNN();	
				break;

			case T_STMT_JPN:
				return static::JPN();	
				break;

			case T_STMT_JNO:
				return static::JNO();	
				break;

			case T_STMT_JPO:
				return static::JPO();	
				break;

			case T_STMT_STR:
				return static::STR();	
				break;

			case T_STMT_LDR:
				return static::LDR();	
				break;

			case T_STMT_ADD:
				return static::ADD();	
				break;

			case T_STMT_ADC:
				return static::ADC();	
				break;

			case T_STMT_SUB:
				return static::SUB();	
				break;

			case T_STMT_SBC:
				return static::SBC();	
				break;

			case T_STMT_AND:
				return static::AND_Parsing();//seems that AND method/function is php reserved(T_LOGICAL_AND error)	
				break;

			case T_STMT_ORR:
				return static::ORR();	
				break;

			case T_STMT_XOR:
				return static::XOR_Parsing();//seems that XOR method/function is php reserved(T_LOGICAL_XOR error)	
				break;

			case T_STMT_CMP:
				return static::CMP();
				break;

			case T_STMT_INV:
				return static::INV();
				break;

			case T_STMT_SHL:
				return static::SHL();
				break;

			case T_STMT_SHR:
				return static::SHR();
				break;

			case T_STMT_ROL:
				return static::ROL();
				break;

			case T_STMT_ROR:
				return static::ROR();
				break;

			case T_STMT_PSH:
				return static::PSH();
				break;

			case T_STMT_POP:
				return static::POP();
				break;

			case T_STMT_CLZ:
				return static::CLZ();
				break;

			case T_STMT_CLC:
				return static::CLC();
				break;

			case T_STMT_CLN:
				return static::CLN();
				break;

			case T_STMT_NOP:
				return static::NOP();
				break;

			case T_STMT_HALT:
				return static::HALT();
				break;
	

							
			default:
				//get current line from tokens variable to set error line no.
				$current_line = static::$tokens[static::$ti]['linie'];
				//set an error 
				return Error::raise('Parse Error (linia ' . $current_line . '): eroare de sintaxa 1. Tip necunoscut de instructiune.');
				//die("Eroare de sintaxa 1 ");
				//return false;
				break;
		}/*end main switch*/

	}/*end of method instructiune*/





	/************************************************************************************************
		Building the nodes of the AST(Abstract-syntax Tree) 
		by calling each method from Instructiune method

		AM o functie BuildNode care sa construiasca el un array cum poti
		vedea in gramatica MOV la final(reutilizarea codului) - si aceasta functie va returna
		ea un array
	************************************************************************************************/	

	/*
		MOV method of Grammar (MOVE REGISTER OR DATA)
		MOV R1, R2 - move data from R2 to R1
		MOV R1, HEXA_VALUE - move hexa_value to R1
	*/
	public static function MOV()
	{
		$statement_type = 'MOV_STMT';

		//get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//consume the tokens and assign variables for procedding next to Interpret the command
		$consumeToken1 = static::EatToken('MOV_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');
		$consumeToken3 = static::EatToken('COMMA');

		//prepare params array to build node ast
		$params = [];
		$params['name_of_first_register']  =  $consumeToken2['token'] ;

		//read current token
		$current_token = static::currentToken();
		//print_r($current_token);

		//current token now can be REGISTER_GENERAL or NUMBER_HEXA
		if ($current_token['name'] == 'REGISTER_GENERAL') {
			$params['second_value_register'] = $current_token['token']; //setam this param
			$consumaToken4 = static::EatToken('REGISTER_GENERAL');//consumam un token de tip registru general

		} else if ($current_token['name'] == 'NUMBER_HEXA') {
			$params['second_value_hexa'] = $current_token['token']; 
			$consumaToken4 = static::EatToken('NUMBER_HEXA');
		} 

		//build mnemonica_instr node for this statement(eg) : MOV R1, R2  sau  MOV R1, 0x01
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token'] . $consumeToken3['token'] . " " . $consumaToken4['token'] ;
		//echo $mnemonica_instruction;
		//echo "<pre>";
		//print_r($consumeToken1['token']);
		//echo "</pre>";


		//build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params, $mnemonica_instruction);
		//echo "<pre>";
		//print_r($node);
		//echo "</pre>";

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser MOV <br>";
			//die("Se executa functie parser MOV/curenta");
		}

		//finally , return this node
		return $node;
	}




	/*
		JNZ method of Grammar (JUMP IF NOT ZERO)
		JNZ 0xE300  -  Jump if not zero(flagZ) to 0xE300
		JNZ to_label - Jump if not zero(flagZ) - to a label in asm source code(conditioned jump)
	*/
	public static function JNZ()
	{
		$statement_type = 'JNZ_STMT';

		//1. get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables for procedding next to Interpret the command
		$consumeToken1 = static::EatToken('JNZ_IC'); 
		//$consumeToken2 = static::EatToken('NUMBER_HEXA');

		//3.prepare params array to build node ast
		$params = [];
		//$params['address_to_jump_to']  =  $consumeToken2['token'] ;


		//4.Figuring out what is the form of JNZ instr : HEXA_VALUE or LABEL_IN_JUMP
		//read current token
		$current_token = static::currentToken();
		//current token now can be HEXA_VALUE or LABEL_IN_JUMP
		if ($current_token['name'] == 'NUMBER_HEXA') {
			$consumaToken2 = static::EatToken('NUMBER_HEXA');//first consume a token and assign it to a variable
			$params['address_to_jump_to'] = $consumaToken2['token']; //then insert values to param

		} else if ($current_token['name'] == 'LABEL_IN_JUMP') {
			$consumaToken2 = static::EatToken('LABEL_IN_JUMP');
			$params['label_to_jump_to'] = $consumaToken2['token']; 
		} 

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumaToken2['token']  ;
		//echo $mnemonica_instruction;


		//build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params, $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser JNZ <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}




	/*
		JPZ method of Grammar (JUMP IF ZERO)
		JPZ 0x0001  -  Jump if zero(flagZ) to 0x0001
		JPZ to_label - Jump if zero(flagZ) - to a label in asm source code(conditioned jump)
	*/
	public static function JPZ()
	{
		$statement_type = 'JPZ_STMT';

		//1. get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('JPZ_IC'); 
		//$consumeToken2 = static::EatToken('NUMBER_HEXA');

		//3.prepare params array to build node ast
		$params = [];
		//$params['address_to_jump_to']  =  $consumeToken2['token'] ;


		//4.Figuring out what is the form of JPZ(this JP) instr : HEXA_VALUE or LABEL_IN_JUMP
		//read current token
		$current_token = static::currentToken();
		//current token now can be HEXA_VALUE or LABEL_IN_JUMP
		if ($current_token['name'] == 'NUMBER_HEXA') {
			$consumaToken2 = static::EatToken('NUMBER_HEXA');//first consume a token and assign it to a variable
			$params['address_to_jump_to'] = $consumaToken2['token']; //then insert values to param

		} else if ($current_token['name'] == 'LABEL_IN_JUMP') {
			$consumaToken2 = static::EatToken('LABEL_IN_JUMP');
			$params['label_to_jump_to'] = $consumaToken2['token']; 
		} 

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumaToken2['token']  ;
		//echo $mnemonica_instruction;


		//build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser JPZ <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}




	/*
		LABEL_ETICHETA method of Grammar (LABEL - loop: for eg.)
		eticheta_label_1: -  for labelling assembly
	*/
	public static function LABEL_ETICHETA()
	{
		$statement_type = 'LABEL_STMT';

		//1. get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('LABEL'); 

		//3.prepare params array to build node ast
		//no params for this instr yet.. __OLD_COMMENT - (now we have param)
		$params =  [];
		$params['name_of_label_to_be_jumped_to'] = $consumeToken1['token'];


		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token']  ;
		//echo $mnemonica_instruction;

		//build Node in AST
		//$node = static::buildNodeAST($statement_type, $current_line , null);
		$node = static::buildNodeAST($statement_type, $current_line , $params, $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser LABEL_ETICHETA <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}




	/*
		JMP method of Grammar (JUMP TO)
		JMP 0x0001 -  Jump to 0x0001(absolute jump)
		OR
		JMP to_label - Jump to a label in asm source code(absolute jump)
	*/
	public static function JMP()
	{
		$statement_type = 'JMP_STMT';

		//1. get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('JMP_IC'); 
		//$consumeToken2 = static::EatToken('NUMBER_HEXA');

		//3.prepare params array to build node ast
		$params = [];
		//$params['address_to_jump_to']  =  $consumeToken2['token'] ;


		//4.Figuring out what is the form of JMP instr : HEXA_VALUE or LABEL_IN_JUMP
		//read current token
		$current_token = static::currentToken();
		//current token now can be HEXA_VALUE or LABEL_IN_JUMP
		if ($current_token['name'] == 'NUMBER_HEXA') {
			$consumaToken2 = static::EatToken('NUMBER_HEXA');//first consume a token and assign it to a variable
			$params['address_to_jump_to'] = $consumaToken2['token']; //then insert values to param

		} else if ($current_token['name'] == 'LABEL_IN_JUMP') {
			$consumaToken2 = static::EatToken('LABEL_IN_JUMP');
			$params['label_to_jump_to'] = $consumaToken2['token']; 
		} 

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumaToken2['token']  ;
		//echo $mnemonica_instruction;


		//build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser JMP <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}




	/*
		JNC method of Grammar (JUMP IF NOT CARRY)
		JNC 0x0001  -  Jump if not carry to 0x0001
		JNC to_label - Jump if not carry - to a label in asm source code(conditioned jump)
	*/
	public static function JNC()
	{
		$statement_type = 'JNC_STMT';

		//1. get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('JNC_IC'); 
		//$consumeToken2 = static::EatToken('NUMBER_HEXA');

		//3.prepare params array to build node ast
		$params = [];
		//$params['address_to_jump_to']  =  $consumeToken2['token'] ;


		//4.Figuring out what is the form of this JP instr : HEXA_VALUE or LABEL_IN_JUMP
		//read current token
		$current_token = static::currentToken();
		//current token now can be HEXA_VALUE or LABEL_IN_JUMP
		if ($current_token['name'] == 'NUMBER_HEXA') {
			$consumaToken2 = static::EatToken('NUMBER_HEXA');//first consume a token and assign it to a variable
			$params['address_to_jump_to'] = $consumaToken2['token']; //then insert values to param

		} else if ($current_token['name'] == 'LABEL_IN_JUMP') {
			$consumaToken2 = static::EatToken('LABEL_IN_JUMP');
			$params['label_to_jump_to'] = $consumaToken2['token']; 
		} 

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumaToken2['token']  ;
		//echo $mnemonica_instruction;


		//build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser JNC <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}




	/*
		JPC method of Grammar (JUMP IF CARRY)
		JPC 0x0001  -  Jump if  carry to 0x0001
		JPC to_label - Jump if  carry - to a label in asm source code(conditioned jump)
	*/
	public static function JPC()
	{
		$statement_type = 'JPC_STMT';

		//1. get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('JPC_IC'); 
		//$consumeToken2 = static::EatToken('NUMBER_HEXA');

		//3.prepare params array to build node ast
		$params = [];
		//$params['address_to_jump_to']  =  $consumeToken2['token'] ;


		//4.Figuring out what is the form of this JP instr : HEXA_VALUE or LABEL_IN_JUMP
		//read current token
		$current_token = static::currentToken();
		//current token now can be HEXA_VALUE or LABEL_IN_JUMP
		if ($current_token['name'] == 'NUMBER_HEXA') {
			$consumaToken2 = static::EatToken('NUMBER_HEXA');//first consume a token and assign it to a variable
			$params['address_to_jump_to'] = $consumaToken2['token']; //then insert values to param

		} else if ($current_token['name'] == 'LABEL_IN_JUMP') {
			$consumaToken2 = static::EatToken('LABEL_IN_JUMP');
			$params['label_to_jump_to'] = $consumaToken2['token']; 
		} 

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumaToken2['token']  ;
		//echo $mnemonica_instruction;


		//build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser JPC <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}




	/*
		JNN method of Grammar (JUMP IF NOT NEGATIVE FLAG)
		JNN 0x0001  -  Jump if not negative to 0x0001
		JNN to_label - Jump if not negative - to a label in asm source code(conditioned jump)
	*/
	public static function JNN()
	{
		$statement_type = 'JNN_STMT';

		//1. get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('JNN_IC'); 
		//$consumeToken2 = static::EatToken('NUMBER_HEXA');

		//3.prepare params array to build node ast
		$params = [];
		//$params['address_to_jump_to']  =  $consumeToken2['token'] ;


		//4.Figuring out what is the form of this JP instr : HEXA_VALUE or LABEL_IN_JUMP
		//read current token
		$current_token = static::currentToken();
		//current token now can be HEXA_VALUE or LABEL_IN_JUMP
		if ($current_token['name'] == 'NUMBER_HEXA') {
			$consumaToken2 = static::EatToken('NUMBER_HEXA');//first consume a token and assign it to a variable
			$params['address_to_jump_to'] = $consumaToken2['token']; //then insert values to param

		} else if ($current_token['name'] == 'LABEL_IN_JUMP') {
			$consumaToken2 = static::EatToken('LABEL_IN_JUMP');
			$params['label_to_jump_to'] = $consumaToken2['token']; 
		} 

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumaToken2['token']  ;
		//echo $mnemonica_instruction;


		//build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser JNN <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}




	/*
		JPN method of Grammar (JUMP IF NEGATIVE FLAG)(JP N = Jump Negative)
		JPN 0x0001  -  Jump if negative to 0x0001
		JPN to_label - Jump if negative - to a label in asm source code(conditioned jump)
	*/
	public static function JPN()
	{
		$statement_type = 'JPN_STMT';

		//1. get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('JPN_IC'); 
		//$consumeToken2 = static::EatToken('NUMBER_HEXA');

		//3.prepare params array to build node ast
		$params = [];
		//$params['address_to_jump_to']  =  $consumeToken2['token'] ;


		//4.Figuring out what is the form of this JP instr : HEXA_VALUE or LABEL_IN_JUMP
		//read current token
		$current_token = static::currentToken();
		//current token now can be HEXA_VALUE or LABEL_IN_JUMP
		if ($current_token['name'] == 'NUMBER_HEXA') {
			$consumaToken2 = static::EatToken('NUMBER_HEXA');//first consume a token and assign it to a variable
			$params['address_to_jump_to'] = $consumaToken2['token']; //then insert values to param

		} else if ($current_token['name'] == 'LABEL_IN_JUMP') {
			$consumaToken2 = static::EatToken('LABEL_IN_JUMP');
			$params['label_to_jump_to'] = $consumaToken2['token']; 
		} 

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumaToken2['token']  ;
		//echo $mnemonica_instruction;


		//build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser JPN <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}




	/*
		JNO method of Grammar (JUMP IF NOT OVERFLOW FLAG)
		JNO 0x0001  -  Jump if not overflow to 0x0001
		JNO to_label - Jump if not overflow - to a label in asm source code(conditioned jump)
	*/
	public static function JNO()
	{
		$statement_type = 'JNO_STMT';

		//1. get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('JNO_IC'); 
		//$consumeToken2 = static::EatToken('NUMBER_HEXA');

		//3.prepare params array to build node ast
		$params = [];
		//$params['address_to_jump_to']  =  $consumeToken2['token'] ;


		//4.Figuring out what is the form of this JP instr : HEXA_VALUE or LABEL_IN_JUMP
		//read current token
		$current_token = static::currentToken();
		//current token now can be HEXA_VALUE or LABEL_IN_JUMP
		if ($current_token['name'] == 'NUMBER_HEXA') {
			$consumaToken2 = static::EatToken('NUMBER_HEXA');//first consume a token and assign it to a variable
			$params['address_to_jump_to'] = $consumaToken2['token']; //then insert values to param

		} else if ($current_token['name'] == 'LABEL_IN_JUMP') {
			$consumaToken2 = static::EatToken('LABEL_IN_JUMP');
			$params['label_to_jump_to'] = $consumaToken2['token']; 
		} 

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumaToken2['token']  ;
		//echo $mnemonica_instruction;


		//build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser JNO <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}




	/*
		JPO method of Grammar (JUMP IF OVERFLOW FLAG)
		JPO 0x0001  -  Jump if overflow to 0x0001
		JPO to_label - Jump if overflow - to a label in asm source code(conditioned jump)		
	*/
	public static function JPO()
	{
		$statement_type = 'JPO_STMT';

		//1. get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('JPO_IC'); 
		//$consumeToken2 = static::EatToken('NUMBER_HEXA');

		//3.prepare params array to build node ast
		$params = [];
		//$params['address_to_jump_to']  =  $consumeToken2['token'] ;


		//4.Figuring out what is the form of this JP instr : HEXA_VALUE or LABEL_IN_JUMP
		//read current token
		$current_token = static::currentToken();
		//current token now can be HEXA_VALUE or LABEL_IN_JUMP
		if ($current_token['name'] == 'NUMBER_HEXA') {
			$consumaToken2 = static::EatToken('NUMBER_HEXA');//first consume a token and assign it to a variable
			$params['address_to_jump_to'] = $consumaToken2['token']; //then insert values to param

		} else if ($current_token['name'] == 'LABEL_IN_JUMP') {
			$consumaToken2 = static::EatToken('LABEL_IN_JUMP');
			$params['label_to_jump_to'] = $consumaToken2['token']; 
		} 

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumaToken2['token']  ;
		//echo $mnemonica_instruction;


		//build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser JPO <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}




	/*
		STR method of Grammar (STORE REGISTER OR DATA)
		STR R1, R2 , R3 - concateneaza registrii R2 si R3 si stores the value found in R2R3 to the memory address found in R1.
						  Poate fac instr mai simpla = doar STR R1, R4(cu 2 registrii, nu cu 3)
		STR R1, HEXA_VALUE(0x01) - stores the immediate value 0x01  to the memory address found in R1.
	*/
	public static function STR()
	{
		$statement_type = 'STR_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('STR_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');
		$consumeToken3 = static::EatToken('COMMA');

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_first_register']  =  $consumeToken2['token'] ;

		//4.Figuring out what is the form of STR instr : R2, R2   or HEXA_VALUE
		//read current token
		$current_token = static::currentToken();
		//current token now can be REGISTER_GENERAL or NUMBER_HEXA
		if ($current_token['name'] == 'REGISTER_GENERAL') {
			$consumaToken4 = static::EatToken('REGISTER_GENERAL');//consumam un token de tip registru general
			$params['register_to_store_from_1'] = $consumaToken4['token']; //setam this param
			$consumaToken5 = static::EatToken('COMMA');//consumam/mancam o virgula
			$consumaToken6 = static::EatToken('REGISTER_GENERAL');//consumam un token de tip registru general
			$params['register_to_store_from_2'] = $consumaToken6['token']; //setam this param

		} else if ($current_token['name'] == 'NUMBER_HEXA') {
			$consumaToken4 = static::EatToken('NUMBER_HEXA');//first consume a token and assign it to a variable
			$params['immediate_hexa_value'] = $consumaToken4['token']; //then insert values to param
		} 

		//build mnemonica_instr node for this statement
		//for str : pune tokenii consumati pana la 4 si apoi verifica daca exista token 5 si pune si restul daca da -- frumos , pe o singura linie
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token'] . $consumeToken3['token'] . " " . $consumaToken4['token'] . 
			(isset($consumaToken5) ?  ($consumaToken5['token'] . " " . $consumaToken6['token'])   : '' ) ;
		//echo $mnemonica_instruction;


		//build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction );

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser STR <br>";
			//die("Se executa functie parser X/curenta");
		}

		//finally , return this node
		return $node;
	}




	/*
		LDR method of Grammar (LOAD REGISTER OR DATA)(LD R - LOAD Register )
		LDR R1, R2 , R3 - concateneaza registrii R2 si R3 si apoi: LDR operation: loads the value at the address found in R2R3 to the destination register R1.
						  Poate fac instr mai simpla = doar LDR R1, R4(cu 2 registrii, nu cu 3)
		LDR R1, HEXA_VALUE(0x01) - LDR operation: loads the imeddiate value at the address 0x01(-for example) to the destination register R1.
	*/
	public static function LDR()
	{
		$statement_type = 'LDR_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('LDR_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');
		$consumeToken3 = static::EatToken('COMMA');

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_first_register']  =  $consumeToken2['token'] ;

		//4.Figuring out what is the form of LDR instr : R2, R2   or HEXA_VALUE
		//read current token
		$current_token = static::currentToken();
		//current token now can be REGISTER_GENERAL or NUMBER_HEXA
		if ($current_token['name'] == 'REGISTER_GENERAL') {
			$consumaToken4 = static::EatToken('REGISTER_GENERAL');//consumam un token de tip registru general
			$params['register_to_load_from_1'] = $consumaToken4['token']; //setam this param
			$consumaToken5 = static::EatToken('COMMA');//consumam/mancam o virgula
			$consumaToken6 = static::EatToken('REGISTER_GENERAL');//consumam un token de tip registru general
			$params['register_to_load_from_2'] = $consumaToken6['token']; //setam this param

		} else if ($current_token['name'] == 'NUMBER_HEXA') {
			$consumaToken4 = static::EatToken('NUMBER_HEXA');//first consume a token and assign it to a variable
			$params['immediate_hexa_value_to_load_from'] = $consumaToken4['token']; //then insert values to param
		} 

		//build mnemonica_instr node for this statement
		//for ldr(la fel ca str) : pune tokenii consumati pana la 4 si apoi verifica daca exista token 5 si pune si restul daca da -- frumos , pe o singura linie
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token'] . $consumeToken3['token'] . " " . $consumaToken4['token'] . 
			(isset($consumaToken5) ?  ($consumaToken5['token'] . " " . $consumaToken6['token'])   : '' ) ;
		//echo $mnemonica_instruction;


		//build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser LDR <br>";
			//die("Se executa functie parser X/curenta");
		}

		//finally , return this node
		return $node;
	}




	/*
		ADD method of Grammar (ADDition REGISTER OR DATA to first register)
		ADD R1, R2  - face adunare intre valorile R1 si R2 si stocheaza val finala in R1
		ADD R1, HEXA_VALUE(0x01) - face adunare intre valorile R1 si 0x01 si stocheaza val finala in R1
	*/
	public static function ADD()
	{
		$statement_type = 'ADD_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('ADD_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');
		$consumeToken3 = static::EatToken('COMMA');

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_first_register']  =  $consumeToken2['token'] ;

		//4.Figuring out what is the form of ADD instr : GENERAL_REGISTER or HEXA_VALUE
		//read current token
		$current_token = static::currentToken();
		//current token now can be REGISTER_GENERAL or NUMBER_HEXA
		if ($current_token['name'] == 'REGISTER_GENERAL') {
			$consumaToken4 = static::EatToken('REGISTER_GENERAL');//consumam un token de tip registru general
			$params['register_for_addition_2'] = $consumaToken4['token']; //setam this param

		} else if ($current_token['name'] == 'NUMBER_HEXA') {
			$consumaToken4 = static::EatToken('NUMBER_HEXA');//first consume a token and assign it to a variable
			$params['hexa_value_for_addition'] = $consumaToken4['token']; //then insert values to param
		} 

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token'] . $consumeToken3['token'] . " " . $consumaToken4['token']  ;
		//echo $mnemonica_instruction;


		//build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser ADD <br>";
			//die("Se executa functie parser X/curenta");
		}

		//finally , return this node
		return $node;
	}




	/*
		ADC method of Grammar (ADD WITH CARRY REGISTER OR DATA )
		ADC R1, R2  - face adunare intre valorile R1 si R2 si stocheaza val finala in R1 cu Carry
	*/
	public static function ADC()
	{
		$statement_type = 'ADC_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('ADC_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');
		$consumeToken3 = static::EatToken('COMMA');
		$consumaToken4 = static::EatToken('REGISTER_GENERAL');//consumam un token de tip registru general

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_first_register']  =  $consumeToken2['token'] ;
		$params['name_of_second_register'] =  $consumaToken4['token']; //setam this param

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token'] . $consumeToken3['token'] . " " . $consumaToken4['token']  ;
		//echo $mnemonica_instruction;


		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser ADC <br>";
			//die("Se executa functie parser X/curenta");
		}

		//finally , return this node
		return $node;
	}	




	/*
		SUB method of Grammar (SUBstract REGISTER OR DATA to first register)
		SUB R1, R2  - face scadere intre valorile R1 si R2 si stocheaza val finala in R1
		SUB R1, HEXA_VALUE(0x01) - face scadere intre valorile R1 si 0x01 si stocheaza val finala in R1
	*/
	public static function SUB()
	{
		$statement_type = 'SUB_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('SUB_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');
		$consumeToken3 = static::EatToken('COMMA');

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_first_register']  =  $consumeToken2['token'] ;

		//4.Figuring out what is the form of SUB instr : GENERAL_REGISTER or HEXA_VALUE
		//read current token
		$current_token = static::currentToken();
		//current token now can be REGISTER_GENERAL or NUMBER_HEXA
		if ($current_token['name'] == 'REGISTER_GENERAL') {
			$consumaToken4 = static::EatToken('REGISTER_GENERAL');//consumam un token de tip registru general
			$params['register_for_substraction_2'] = $consumaToken4['token']; //setam this param

		} else if ($current_token['name'] == 'NUMBER_HEXA') {
			$consumaToken4 = static::EatToken('NUMBER_HEXA');//first consume a token and assign it to a variable
			$params['hexa_value_for_substract'] = $consumaToken4['token']; //then insert values to param
		} 

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token'] . $consumeToken3['token'] . " " . $consumaToken4['token']  ;
		//echo $mnemonica_instruction;


		//build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser SUB <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}




	/*
		SBC method of Grammar (SUBSTRACT WITH CARRY REGISTER OR DATA )
		SBC R1, R2  - face scadere intre valorile R1 si R2 si stocheaza val finala in R1 cu Carry
	*/
	public static function SBC()
	{
		$statement_type = 'SBC_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('SBC_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');
		$consumeToken3 = static::EatToken('COMMA');
		$consumaToken4 = static::EatToken('REGISTER_GENERAL');//consumam un token de tip registru general

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_first_register']  =  $consumeToken2['token'] ;
		$params['name_of_second_register'] =  $consumaToken4['token']; //setam this param

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token'] . $consumeToken3['token'] . " " . $consumaToken4['token']  ;
		//echo $mnemonica_instruction;


		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser SBC <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}	




	/*
		AND method of Grammar (LOGICAL AND )
		AND R1, R2  - face si logic
	*/
	public static function AND_Parsing()
	{
		$statement_type = 'AND_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('AND_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');
		$consumeToken3 = static::EatToken('COMMA');
		$consumaToken4 = static::EatToken('REGISTER_GENERAL');//consumam un token de tip registru general

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_first_register']  =  $consumeToken2['token'] ;
		$params['name_of_second_register'] =  $consumaToken4['token']; //setam this param

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token'] . $consumeToken3['token'] . " " . $consumaToken4['token']  ;
		//echo $mnemonica_instruction;


		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser AND <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}




	/*
		ORR method of Grammar (LOGICAL OR )
		ORR R1, R2  - face SAU LOGIC
	*/
	public static function ORR()
	{
		$statement_type = 'ORR_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('ORR_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');
		$consumeToken3 = static::EatToken('COMMA');
		$consumaToken4 = static::EatToken('REGISTER_GENERAL');

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_first_register']  =  $consumeToken2['token'] ;
		$params['name_of_second_register'] =  $consumaToken4['token']; //setam this param

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token'] . $consumeToken3['token'] . " " . $consumaToken4['token']  ;
		//echo $mnemonica_instruction;


		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser ORR <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}		




	/*
		XOR method of Grammar (LOGICAL XOR )
		XOR R1, R2  - face XOR LOGIC
	*/
	public static function XOR_Parsing()
	{
		$statement_type = 'XOR_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('XOR_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');
		$consumeToken3 = static::EatToken('COMMA');
		$consumaToken4 = static::EatToken('REGISTER_GENERAL');

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_first_register']  =  $consumeToken2['token'] ;
		$params['name_of_second_register'] =  $consumaToken4['token']; //setam this param

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token'] . $consumeToken3['token'] . " " . $consumaToken4['token']  ;
		//echo $mnemonica_instruction;


		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params, $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser XOR <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}		




	/*
		CMP method of Grammar (COMPARE TWO REGISTER VALUES )
		CMP R1, R2  -  COMPARE
	*/
	public static function CMP()
	{
		$statement_type = 'CMP_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('CMP_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');
		$consumeToken3 = static::EatToken('COMMA');
		$consumaToken4 = static::EatToken('REGISTER_GENERAL');

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_first_register']  =  $consumeToken2['token'] ;
		$params['name_of_second_register'] =  $consumaToken4['token']; //setam this param

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token'] . $consumeToken3['token'] . " " . $consumaToken4['token']  ;
		//echo $mnemonica_instruction;


		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser CMP <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}	




	/*
		INV method of Grammar (INVERSE )
		INV R0  -  INVERSE THE REGISTER BITS
	*/
	public static function INV()
	{
		$statement_type = 'INV_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('INV_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_register']  =  $consumeToken2['token'] ;

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token']  ;
		//echo $mnemonica_instruction;


		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser INV <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}	




	/*
		SHL method of Grammar (SHIFT LEFT )
		SHL R0  -  SHIFT LEFT THE REGISTER BITS
	*/
	public static function SHL()
	{
		$statement_type = 'SHL_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('SHL_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_register']  =  $consumeToken2['token'] ;

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token']  ;
		//echo $mnemonica_instruction;


		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser SHL <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}			




	/*
		SHR method of Grammar (SHIFT RIGHT )
		SHR R0  -  SHIFT RIGHT THE REGISTER BITS
	*/
	public static function SHR()
	{
		$statement_type = 'SHR_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('SHR_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_register']  =  $consumeToken2['token'] ;

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token']  ;
		//echo $mnemonica_instruction;


		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser SHR <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}			




	/*
		ROL method of Grammar (ROTATE LEFT )
		ROL R0  -  ROTATE LEFT THE REGISTER BITS
	*/
	public static function ROL()
	{
		$statement_type = 'ROL_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('ROL_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_register']  =  $consumeToken2['token'] ;

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token']  ;
		//echo $mnemonica_instruction;


		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser ROL <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}		




	/*
		ROR method of Grammar (ROTATE Right )
		ROR R0  -  ROTATE right THE REGISTER BITS
	*/
	public static function ROR()
	{
		$statement_type = 'ROR_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('ROR_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_register']  =  $consumeToken2['token'] ;

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token'] . $consumeToken2['token']  ;
		//echo $mnemonica_instruction;


		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser ROR <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}		




	/*
		PSH method of Grammar (PUSH)
		PSH R0  -  PUSH the content of R0 to Stack
	*/
	public static function PSH()
	{
		$statement_type = 'PSH_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('PSH_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_register']  =  $consumeToken2['token'] ;

		//nu mai implementez mnemonica - nu folosesc instructiunea deloc in limbaj

		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser PSH <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}	




	/*
		POP method of Grammar (POP)
		POP R0  -  POP the address of R0 from Stack
	*/
	public static function POP()
	{
		$statement_type = 'POP_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('POP_IC'); 
		$consumeToken2 = static::EatToken('REGISTER_GENERAL');

		//3.prepare params array to build node ast
		$params = [];
		$params['name_of_register']  =  $consumeToken2['token'] ;

		//nu mai implementez mnemonica - nu folosesc instructiunea deloc in limbaj

		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser POP <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}	




	/*
		CLZ method of Grammar (CLEAR Z FLAG)
		CLZ  -  Clears the flag Z(set flag z = 0)
	*/
	public static function CLZ()
	{
		$statement_type = 'CLZ_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('CLZ_IC'); 

		//3.prepare params array to build node ast
		$params = null;

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token']  ;
		//echo $mnemonica_instruction;


		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser CLZ <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}	




	/*
		CLC method of Grammar (CLEAR C FLAG)
		CLC  -  Clears the flag C-Carry(set flag C = 0)
	*/
	public static function CLC()
	{
		$statement_type = 'CLC_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('CLC_IC'); 

		//3.prepare params array to build node ast
		$params = null;

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token']  ;
		//echo $mnemonica_instruction;


		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser CLC <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}	




	/*
		CLN method of Grammar (CLEAR N FLAG)
		CLN  -  Clears the flag N-Negative(set flag N = 0)
	*/
	public static function CLN()
	{
		$statement_type = 'CLN_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('CLN_IC'); 

		//3.prepare params array to build node ast
		$params = null;

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token']  ;
		//echo $mnemonica_instruction;

		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser CLN <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}	





	/*
		NOP method of Grammar (NO OPERATION)
		NOP  -  No operation is executed for one cycle
	*/
	public static function NOP()
	{
		$statement_type = 'NOP_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('NOP_IC'); 

		//3.prepare params array to build node ast
		$params = null;

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token']  ;
		//echo $mnemonica_instruction;


		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser NOP <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}	




	/*
		HALT method of Grammar (HALT - HOLD)
		HALT  -  STOPS THE EXECUTION OF THE CPU
	*/
	public static function HALT()
	{
		$statement_type = 'HALT_STMT';

		//1.get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];

		//2.consume the tokens and assign variables 
		$consumeToken1 = static::EatToken('HALT_IC'); 

		//3.prepare params array to build node ast
		$params = null;

		//build mnemonica_instr node for this statement
		$mnemonica_instruction = $consumeToken1['token']  ;
		//echo $mnemonica_instruction;


		//4.build Node in AST
		$node = static::buildNodeAST($statement_type, $current_line , $params , $mnemonica_instruction);

		if (SHOW_PARSING_MESSAGES == true) {
			echo "Se executa functie parser HALT <br>";
			//die("Se executa functie parser X/curenta");
		}
		//finally , return this node
		return $node;
	}	



	/*************************************************
		END The Grammar methods 
	**************************************************/








	/*************************************************************************************************
		helper functions for grammar
	**************************************************************************************************/

	/*
	this function preview the next token(se uita ce token urmeaza in array-ul de token-uri)

	@param Position , the position to look ahead in the tokens array , if want so (defaults to zero)

	@return mixed The token name if found(aka VARIABLE, STRING, etc..) , false if token not found

	eg. if i want to look after 2 positions , i will call static::TokenUrmator(2);
	*/
	public static function TokenUrmator($position = 1)
	{
		if(!empty(static::$tokens[static::$ti + ($position - 1)]) ){ //if token array not empty at given position
			//return token name
			return static::$tokens[static::$ti + ($position - 1)]['name'] ; 
		} else {
			return false;
		}
		
	}



	/*
	mananca(eats) - consuma token-ul current daca token-ul dat ca parametru e egal cu token-ul curent din array-ul tokens ( avanseaza index-ul token-urilor al clasei(Parser) )
	Must suppress last token in the tokens array (suppress with @),it will give a notice undefined offset in array(pentru ca nu mai gaseste urmatorul token , deaia da notice)

	@param String $token , token-ul parametru cu care se compara token-ul curent din array-ul tokens

	@return mixed , STILL CURRENT(not next) token if comparison is true , syntax error otherwise
	*/
	public static function EatToken($token)
	{
		if ( static::$tokens[static::$ti]['name'] == $token ) {//if current token name equals token param
			//increment current tokens index
			static::$ti++;
			//return STILL CURRENT token 
			return @static::$tokens[static::$ti - 1];
		} else {
			//set an error 
			//Eroare();//will put into Core class Errors an error

			//instead of dying , raise an Error and check for this error in parsing stage(where you parse every token)
			//die("Eroare Fatala. Nu s-a putut consuma token-ul.Token-ul dat ca parametru nu se potriveste cu token-ul curent. Eroare de sintaxa!!");
			$line_error = ( isset(static::$tokens[static::$ti]['linie']) ? static::$tokens[static::$ti]['linie'] : null );
			
			Error::raise("Eroare Parsare Fatala(linia " . $line_error . "). Nu s-a putut consuma token-ul.Token-ul dat ca parametru(". static::$tokens[static::$ti]['name'] .") nu se potriveste cu token-ul cerut curent(". $token ."). Eroare de sintaxa!!");
			return false;
		}
	}



	/*
	gets current token based on token_index

	@return currentToken based on token_index class attribute
	*/
	public static function currentToken()
	{
		return static::$tokens[static::$ti];
	}




	/*
		Builds a Node to be used inside grammar
		methods such as MOV,JMP, etc..

		@params:
		$statement_type String the type of statement
		$line_no String instruction line number
		$params Array the parameters of the statement
		$mnemonic_instruction String , the mnemonic(how current instruction looks) from the current statement

		@return: Array the built node

		Model(an example):
		Array
		(
		    [statement_type] => MOV_STMT
		    [line_no] => 1
		    [mnemonica_instruction] => "MOV R1, 0x01" (OR MOV,R11,R2 in this case)
		    [params] => Array
		         (  [name_of_first_register] => MOV 
		            [second_value] => R2
		            [ANOTHER_PARAM] => another value 
		            ..AND SO ON ..
		         )        
		)
	*/
	public static function buildNodeAST($statement_type, $line_no , $params , $mnemonica_instruction = " ")
	{
		//i made the $mnemonica_instruction parameter optional until i fill all the statements with the param(i can leave it optional)

		$node = [];
		$node['statement_type'] = $statement_type;
		$node['line_no'] = $line_no;
		$node['mnemonica_instruction'] = $mnemonica_instruction;
		$node['params'] = $params;

		return $node;
	}

	/***************************************************************************************************
		helper functions
	***************************************************************************************************/


	/*
	display the tokens in another method

	@return echoes the tokens as preformatted text
	*/
	public static function displayTokens()
	{
		echo "<pre>";
		print_r(static::$tokens);
		echo "</pre>";
	}


	/*
	check if class variable tokens_index (aka $ti) is at the end of the class variable $tokens

	@return boolean True if tokens_index meet the end of tokens array , False otherwise
	*/
	public static function endOfTokens()
	{
		if(static::$ti > (count(static::$tokens) -1) ) {//token_index >nr_of_tokens(0..25 => 26 tokens)
			return true;
		}

		return false;	
	}


}/*end of class Parser*/













?>