<?php

namespace Core;
// use the following :  use \Core\HistoryTableOfChanges;  - where u want to use this class

/*
	Class HistoryTableOfChanges
	php ver 5.5.0
	*
	This is where the entire cpuBP app is holding/working with display data (this is the History Table Of Changes)
	*
	This is a class that is holding an array that is holding the "log" with what happened inside the cpuBP app/software
	*
	*
	RO:
	Aceasta clasa va tine: 
		-structura de date (array-ul) cu care fac afisarea generata php( array din care afisez tabel ordine executie instructiuni)
		-cu ajutorul acestei structuri voi afisa tot: tabel ordine executie instructiuni, cat si JSON javascript navigare(optional, daca 
			ajung sa implementez): tabel pipeline, tabel memorie date, registrii uz general , registrii speciali(PC,IC), flaguri - in 
			timp real astea
*/
class HistoryTableOfChanges
{
	/***************************************************************************************************************
		Attributes of this class
	****************************************************************************************************************/

	/*
		this is the main structure that will be used to display data
		Array
		Default value = empty;
		HTOC = History Table Of Changes (abbreviation)
	*/
	public static $HTOC = [] ;




	/****************************************************************************************************************
		Methods of this class
	*****************************************************************************************************************/

	/*********************
	HOW an HTOC node should look like:
	----
	HTOC = 
	[
		//always in data 
		//like:  PC,IC(nr_total_instr_executate), Tip_instructiune, Mnemonica_instructiune, counter predictions(for jumps)

		//not always in data (utils if i ll implement JSON javascript navigation - i think i ll just skip)
		//like:  action_type(update_register, update memory data, update flags)
	]

	*********************/







	/*
		adds a Node in the HTOC structure

		param int $PC , the program counter from each instruction
		param int $IC , the total number of executed instructions from each instruction(numar_total_instructiuni_executate)
		param String $type_instr(type_instruction) ,  the type of instruction stored currently in node(ex: MOV)
		param String $mnemonica_instr(mnemonica_instruction) , the mnemonica of the current instruction(ex: MOV R1, R2)
		param String Or Array(will see) $jump_taken , OPTIONAL , an field that displays only for jump statements and indicates
				if jump was taken or not along with info about prediction for this current jump(see table order of execution)
		param String Or Array(will see) $action ,OPTIONAL, the action itself - what happened at this current instruction (ex:
				at a current MOV instruction , R3 <= 0000 0001). - will see if implement(very useful for JSON javascript if 
				will do)		

		return void
	*/
	public static function addNode($IC,  $PC,  $type_instr,  $mnemonica_instr,  $jump_taken = "",  $action = ""  )
	{
		//create a node for working around with it
		$node = [];

		$node['PC'] = $PC;
		$node['IC'] = $IC;
		$node['type_instruction'] = $type_instr;
		$node['mnemonica_instruction'] = $mnemonica_instr;
		$node['jump_taken']  = $jump_taken;
		$node['action'] = $action;


		//add to main HTOC structure
		static::$HTOC[] = $node;
	}




	/*
		displays the entire HistoryTableOfChanges
	*/
	public static function displayHTOC()
	{
		echo "<pre>";
		print_r(static::$HTOC);
		echo "</pre>";
	}




	/*
		displays the HistoryTableOfChanges by a key(just a node of the HTOC)

		Function used for development
	
		param int $key , the key for the HTOC do be displayed
	*/
	public static function displayHTOCkey($key)
	{
		echo "<pre>";
		print_r(static::$HTOC[$key]);
		echo "</pre>";
	}


}
//end of HistoryTableOfChanges class


























?>