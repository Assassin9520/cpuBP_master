<?php

namespace Core;

/*
config file
*/

define("DS" , DIRECTORY_SEPARATOR);

/************************************************************************************************
App BACKEND Configuration
************************************************************************************************/

/*
directory of images to upload in 
eg. D:\Php_xampp\htdocs\ImageInterpreter\Public\images\
*/
define("IMAGES_DIRECTORY",  dirname(__DIR__) . DS . 'public' . DS . 'images' . DS);

/*
the shorthand folder where images to display in our div are located
*/
define("IMAGES_SHORTHAND_DISPLAY",  'public/images/');



/***********************************************************************************************
App FRONTEND Configurations
************************************************************************************************/

/*
the name of the folder of the application
*/
define("CURRENT_APP_FOLDER_NAME", "cpubp_master");  


/*
the name of the folder of the application taken dinamically via PHP funct
*/
define("CURRENT_APP_FOLDER_NAME_DINAMICALLY_PHP", trim($_SERVER['REQUEST_URI'],"\/")); 
//echo  CURRENT_APP_FOLDER_NAME_DINAM_PHP; die;


/*show aside(sidebar)*/
//attr 


/* show predictors execution messages right before start of HTML
   eg. 1 0 exec bp mode 3- Dynamic 2bit predictor 
   eg. exec bp mode 0- Static not taken 
   ---
   to be shown just in dev , not in production
   ---
   @values Boolean true shows the messages, false won't display them
*/
const SHOW_PREDICTOR_MESSAGES_DEV = false;


/* load some data into data memory of cpuBP app (30 fields for example)
   Constant to be used in Interpretor->Interpret() for testing the application algorithms
   ---
   to be shown just in dev , and also in PRODUCTION for demonstrating that the app works
   ---
   @values Boolean true loads data into memory data, false won't load anything into memory(if you wish data into memory it should be loaded with STR instructions)
*/
const DEV_DATA_MEMORY_TEST_INSERT_ROWS_MEMORY = true;


/*show execution/interpretation messages right before start of HTML
  eg. Se executa afiseaza_imagine...
  eg. Se executa repeta...

  @values Boolean true shows the messages , false hides them
*/
const SHOW_EXECUTION_MESSAGES = false;  


/*show parsing messages right before start of html
	eg. se executa functie parser MOV
	eg. Se executa functie parser JMP...
	etc

	@values Boolean true shows the messages , false hides them
*/
const SHOW_PARSING_MESSAGES = false;


/*show the app big steps messages in App.php right before start of HTML
	eg. LEXER: S-a executat cu succes tokenizarea termenilor(tokens generat)
	eg. PARSER: S-a executat cu succes detectarea tipurilor de instructiuni(AST generat)

	@values Boolean true shows the messages , false hides them
*/
const SHOW_APP_BIG_STEPS_MESSAGES = false;


//si alte idei cand mai imi vin
//...



/************************************************************************************************
END App FRONTEND Configs
************************************************************************************************/



/*Token names constants*/
/*
	define some constants for the token names defined in Tokenizer::tokenize_input function
	This is some constants for the form of tokens to be used in parser(Instructiune() function )

	WARNING!
	Everytime you add a new token , make sure you add it's regex in TOkenizer::tokenize_input method
	Otherwise , it will not be recognized

	THIS TOKENS ARE BEING USED IN THE PARSE STAGE OF INTERPRETER
*/
/**********
General Tokens TO Constants
**********/
const T_REG_GEN = 'REGISTER_GENERAL';
const T_COMMA = 'COMMA';
const T_SL_COMM = 'SINGLELINE_COMMENT'; //comentariu multilinie
const T_NUM_HEXA = 'NUMBER_HEXA';
const T_LABEL = 'LABEL';
const T_EOL = 'T_EOL';

/**************
Instruction code Tokens to Constants
**************/
const T_STMT_MOV = 'MOV_IC';
const T_STMT_JNZ = 'JNZ_IC';
const T_STMT_JPZ = "JPZ_IC";
const T_STMT_JMP = "JMP_IC";
const T_STMT_JNC = "JNC_IC";
const T_STMT_JPC = "JPC_IC";
const T_STMT_JNN = "JNN_IC";
const T_STMT_JPN = "JPN_IC";
const T_STMT_JNO = "JNO_IC";
const T_STMT_JPO = "JPO_IC";
const T_STMT_STR = "STR_IC";
const T_STMT_LDR = "LDR_IC";
const T_STMT_ADD = "ADD_IC";
const T_STMT_ADC = "ADC_IC";
const T_STMT_SUB = "SUB_IC";
const T_STMT_SBC = "SBC_IC";
const T_STMT_AND = "AND_IC";
const T_STMT_ORR = "ORR_IC";
const T_STMT_XOR = "XOR_IC";
const T_STMT_CMP = "CMP_IC";
const T_STMT_INV = "INV_IC";
const T_STMT_SHL = "SHL_IC";
const T_STMT_SHR = "SHR_IC";
const T_STMT_ROL = "ROL_IC";
const T_STMT_ROR = "ROR_IC";
const T_STMT_PSH = "PSH_IC";
const T_STMT_POP = "POP_IC";
const T_STMT_NOP = "NOP_IC";
const T_STMT_HALT = "HALT_IC";

//added later
const T_STMT_CLZ = "CLZ_IC";
const T_STMT_CLC = "CLC_IC";
const T_STMT_CLN = "CLN_IC";




?>
