<?php

//phpinfo(); die;

ini_set('display_errors', 1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);



/*
NOTES:
-----
-Show predictor messages in development mode were disabled(set to false) in Config.php file
  These were being displayed at the top of page
-Dev data memory enabled in Interpretor->Interpret()
  The constant DEV_DATA_MEMORY_TEST_INSERT_ROWS_MEMORY is set to TRUE  
*/



//get directory name for this file(index.php)
$nd = dirname(__FILE__);//name directory(nd)
//echo $nd;



//include Helper files
include($nd . "/Helper/helper_functions.php");

//include Core files
include($nd . "/Core/Config.php");
include($nd . '/Core/Lexer.php');
include($nd . '/Core/Parser.php');

include($nd . "/Core/Predictor.php");
include($nd . "/Core/HistoryTableOfChanges.php");
include($nd . "/Core/Memory.php");
include($nd . "/Core/SymbolTable.php");
include($nd . "/Core/Interpretor.php");
include($nd . "/Core/Error.php");
include($nd . "/Core/App.php");

use \Core\Interpretor;
use \Core\Error;
use \Core\App;
use \Core\Memory;
use \Core\Predictor;
use \Core\HistoryTableOfChanges;

//Change Maximum execution time of 30 seconds exceeded time in php
//To support large images(code that takes more than 30 seconds to execute)
//EXPERIMENTAL FOR NOW
//ini_set('max_execution_time', 300); //300 seconds = 5 minutes


//main app gateway
if(isset($_POST['submit'])){//if form was submitted

	//Perform some sanitization
    //TO DO ...
    //...

    //before running the app, check if branch prediction mode has been selected
    if (!isset($_POST['branch_prediction_mode'])) {
        echo "inainte de rularea aplicatiei , va rugam sa alegeti un mod de predictie. Aceasta eroare este afisata in index.php";
        die;
    }


    //start the application and away we go
    $app = new App();
    $interpret_result = $app->run( $_POST['text'] );
    //or something like $result = App::run($_POST['text']);	

    // if ($interpret_result) {
    //     echo "Interpretarea s-a terminat cu success.";
    // }

} else if(isset($_POST['submit-check-code'])){

    $app = new App();
    $check_result = $app->checkCode( $_POST['text'] );

    if ($check_result == true) {
        //echo "Nu exista nici o eroare in scrierea codului dumneavoastra.";
        $check_code_msj = "Nu exista nici o eroare in scrierea codului dumneavoastra.";
    } else {
        //echo "Avem erori de scriere. Verificati codul.";
        //dump Errors here
        $check_code_msj = "Avem erori de scriere. Verificati codul :";
    }

}


//echo "<h1>die function in index.php before viewing html</h1>";
//die;


//Erori cu $_SESSION
// if (isset($_SESSION['have_error']) && $_SESSION['have_error'] == true ) {
//     echo "have_error";
//     echo $_SESSION['source_code'];
//     print_r($_SESSION['errors']);
// } else {
//     $_SESSION = array();
// }


//ERORI CU RETURN
//ACUM MERGE SI TESTUL ASTA
//VOI MERGE PE VARIANTA ASTA CU RETURN FALSE
// if (isset($interpret_result) && $interpret_result === false) {
//     echo "we got errors";
// }


//if we have any errors return back by interpretation(whole process:(lexer,parser,interpreter))
//Daca il pun deasupra codului de interpretare ,  nu va arata nimic.NU VA ARATA NIMIC PENTRU CA INCA NU AU FOST RIDICATE ERORI!!!
// if(Error::occur()){
//     Error::dumpErrors();
// }


/*---------------------------------------------------------------------------------------------------------------*/
//now COMES the main UI of cpuBP(cpuBranchPredictor)
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>cpuBP </title>
        
        <!-- CSS -->
        <link href="public/assets/css/style.css" type="text/css" rel="stylesheet" />
        <link href="public/assets/css/style_reset.css" type="text/css" rel="stylesheet" />
        
        <!--responsive styles -->
        <link href="public/assets/css/responsive_author.css" type="text/css" rel="stylesheet" />

        <!-- JS Scripts -->
        <script type="text/javascript" src="public/assets/js/jquery-3.2.1.min.js" > </script>
        <script type="text/javascript" src="public/assets/js/app-scripts.js" > </script>

        <?php if(Error::occur()): ?>
            <!-- if any error occured, scroll to console(bottom of page) -->
            <script type="text/javascript">
                window.onload=toBottom;
                function toBottom()
                {
                //alert("Scrolling to bottom ...");
                window.scrollTo(0, document.body.scrollHeight);
                }
            </script>
        <?php endif;?>

    </head>

    <body>
        <div class="wrapper-app "><!-- removed clearfix from class as it generated whitespace before </body> end -->
            <!-- full screen overlay -displays on execution of app -->
            <div class="overlay-app-executing">
                <span class="status-this">
                    <span class="images-this">
                        <img src="public/assets/css/resources/icon-settings-flat-24px.png" class="img-settings1" />
                        <img src="public/assets/css/resources/icon-settings-flat-24px.png" class="img-settings2" />
                    </span>
                    <span class="text-this">Se executa...</span>
                </span>
            </div>



            <div class="content"><!-- had clearfix -->
            <!-- START MAIN CONTENT -->

                <div class="notification-area-top clearfix">
                    <!-- 
                    <a href="#" class="notif-button has-animation">Vizualizeaza aplicatia fullscreen(F11)</a>
                    <a href="#" class="notif-button ">Prima data in aplicatie ? Vezi manualul</a> 
                    -->

                    <!-- <a href="#" class="logo-main-app-ui">logo of app here(minified)</a> -->



                    <!-- <span style="display:inline-block; padding:10px 0 0 400px;">TAB registrii , pipeline &nbsp;&nbsp;&nbsp;</span>  -->
                    <!--cel de sus: TAB registrii , pipeline, ordine rulare instructiuni -->    

                    <!-- <span style="display:inline-block; padding:10px 0 0 0 ;">TAB vizualizare memorie</span> -->
                    <!--cel de sus: TAB vizualizare memorie(in caz ca adaug taburi, sa vad astea sus acolo) 
                        tabel memorie de date si NEAPARAT DE ASEMENEA
                        tabel memorie de program(ai detalii cum sa il faci in Idei todo functionalitati DEV.txt)
                    -->



                    <!-- 
                    Titlu cod sursa:
                        Cod sursa
                        <span>Cod sursa</span>
                    

                    Titlu rezultate:(taburi)
                        Rezultate
                        <span class="tab1"> Rezultate </span>
                        Tabele/date aici(fix in ordinea asta):
                            -div rata predictie corecta
                            -div numar predictii corecte si numar predictii gresite
                            -tabel ordine executie instructiuni    

                        Navigare
                        <span class="tab2"> Navigare Structuri </span>    
                        Tabele/date aici(fix in ordinea asta):
                            -divuri mici PC, numar_total_instructiuni_rulate
                            -divuri mici flaguri
                            -divuri registrii
                            -tabel memorie date
                            -tabel pipeline (tabel memoria de program nu mai trebuie - e chiar tabelul de pipeline)
                     -->
                </div>


                <div class="section-form-data clearfix">
                    <span class="title-main-name"> Cod sursa <span class="round-corner-outside-right"><span class="corner"></span></span></span>

                    <form method="POST" action="http://localhost/<?php echo CURRENT_APP_FOLDER_NAME_DINAMICALLY_PHP;?>/" class="form-this">
                        <textarea id="main-textarea" class="input-this on-textarea" name="text" spellcheck="false" placeholder="Inserati Codul aici..."><?php echo (isset($_POST['text']) && !empty($_POST['text'])) ? $_POST['text'] : '' ; ?></textarea>
                        
                        <div>
                            <div class="form-this-choose-branch-prediction-mode">
                                <div class="bp-select-title">Alege mod predictie </div>
                                <div class="inputs-holder">
                                    <span class="input-holder">
                                        <!-- value 0 corespunde predictiei: STATIC NOT-TAKEN -fara predictie -->
                                        <!-- ACESTA este modul default -va avea atributul checked intotdeauna setat -->
                                        <input type="radio" id="bp_mode_0" name="branch_prediction_mode" value="0" checked/>
                                        <label for="bp_mode_0" class="input-title" title="(static not-taken)">Predictie mod 0</label>
                                    </span>
                                    <span class="input-holder">
                                        <!-- value 1 corespunde predictiei: STATIC TAKEN  -predictie DA(spune intotdeauna da)-->
                                        <!-- cod php in interiorul inputurilor radio ca sa tina minte la un nou submit acest mod(mod curent) -->
                                        <input type="radio" id="bp_mode_1" name="branch_prediction_mode" value="1"   <?php if(isset($_POST['branch_prediction_mode']) && $_POST['branch_prediction_mode']==1): ?> checked <?php endif;?>  />
                                        <label for="bp_mode_1" class="input-title" title="(static taken)">Predictie mod 1</label>
                                    </span>
                                    <span class="input-holder">
                                        <!-- value 2 corespunde predictiei: DYNAMIC 1BIT PREDICTOR - to findout how it works  -->
                                        <input type="radio" id="bp_mode_2" name="branch_prediction_mode" value="2"   <?php if(isset($_POST['branch_prediction_mode']) && $_POST['branch_prediction_mode']==2): ?> checked <?php endif;?>   />
                                        <label for="bp_mode_2" class="input-title" title="(dynamic 1-bit)">Predictie mod 2</label>
                                    </span>
                                    <span class="input-holder">
                                        <!-- value 3 NU corespunde niciunei predictii: fac doar 3   -->
                                        <input type="radio" id="bp_mode_3" name="branch_prediction_mode" value="3"   <?php if(isset($_POST['branch_prediction_mode']) && $_POST['branch_prediction_mode']==3): ?> checked <?php endif;?>   />
                                        <label for="bp_mode_3" class="input-title" title="(dynamic 2-bit)">Predictie mod 3</label>
                                    </span>
                                </div>
                            </div>
                            <input class="input-submit" type="submit" name="submit" value="Ruleaza Codul">
                            <!--<button type="submit" name="submit-check-code" class="aut-ui-button form-button have-icon icon-check"> Verifica cod </button>-->
                            <!--<a href="#" id="save_code_button" class="aut-ui-button have-icon icon-save" target="_blank"> Salveaza cod--> <!-- la click , va salva codul si il va arata userului pus la dispozitie pentru download--> <!--</a>-->
                            <!--<a href="#" class="aut-ui-button have-icon icon-book" target="blank"> Manual Ajutor --> <!-- La click trimite pe pagina cu comenzi sau popu despre comenzi si functionare - had icon-info1 class before--> <!--</a>-->
                        </div>    
                    </form>
                </div>

                
                <!-- support for child class  on-full-screen -->
                <div class="section-display-results clearfix">
                    <!-- tabs for rezultate , navigare(navigare optional) -->
                    <div class="section-tabs clearfix">
                        <div class="tabs-anchor idTabs">
                            <a href="#tab1" class="title-main-name selected" title="Vizualizare rezultate predictie">Rezultate</a>
                            <a href="#tab2" class="title-main-name" title="Vizualizare structuri de date">Structuri</a><!--OLD name: Navigare Structuri.  - tin tabel memorie date si tabel pipeline statice - restul display none -->
                        </div>
                    </div>
                    <!-- <span class="title-main-name"> Rezultate </span> -->
                    <!-- <div class="contents-tabs" id="tab1"> a </div> -->
                    <!-- <div class="contents-tabs" id="tab2"> b </div> -->


                    <?php if(Interpretor::$app_execution_ready == true): ?>
                    <?php /*if the execution of all statements finished successfully, then display all the results:*/ ?>    
                    <div class="display-results clearfix">



                        <!-- Tab1 - Rezultate -->
                        <!--
                        Contains main info:
                            -div rata predictie corecta
                            -div numar predictii corecte si numar predictii gresite
                            -tabel ordine executie instructiuni    
                         -->
                        <div class="contens-tabs" id="tab1">
                        <div class="view-app-results on-tab-results clearfix">

                            <div class="prediction-chosen-by-user">
                                <?php if($_POST['branch_prediction_mode'] == 0): ?> <p>S-a ales predictia : static  NOT TAKEN </p> <?php endif;?>
                                <?php if($_POST['branch_prediction_mode'] == 1): ?> <p>S-a ales predictia : static  TAKEN     </p> <?php endif;?>
                                <?php if($_POST['branch_prediction_mode'] == 2): ?> <p>S-a ales predictia : dynamic 1-BIT     </p> <?php endif;?>
                                <?php if($_POST['branch_prediction_mode'] == 3): ?> <p>S-a ales predictia : dynamic 2-BIT     </p> <?php endif;?>
                            </div>

                            <div class="figure-correct-prediction-rate clearfix">
                                <?php
                                    //calculate correct prediction rate before displaying
                                    //calculation is done like : nr_pred_corecte / (nr_pred_corecte + nr_pred_gresite)
                                    $no_correct_predictions = Predictor::getCounterGoodPredictions();
                                    $no_missed_predictions  = Predictor::getCounterMissPredictions();

                                    //calculate rate of correct prediction
                                    //value must be not higher than integer 1
                                    $correct_prediction_rate = $no_correct_predictions / ($no_correct_predictions + $no_missed_predictions);
                                    
                                    //format the correct prediction rate number obtained to display it as procents
                                    
                                    $pred_rate_formatted = 100 *  $correct_prediction_rate;

                                    //and display it with only 2 decimals
                                    $prediction_rate_final = number_format((float)$pred_rate_formatted, 2, '.', '');
                                    //echo $prediction_rate_final;
                                ?>
                                <span class="text-this">Rata de predictie corecta: <b><?php echo $prediction_rate_final;?><span>%</span></b></span>
                                
                                <span class="arrow-toggle-this" onclick="toggleDisplayRateCalc();"> toggle </span> <!-- this button uses function toggleDisplayRateCalc();  LA DUBLU CLICK-->
                                <div class="div-show-prediction-calculation">
                                    Rata predictie = C / (C + G)  = 
                                        <?php echo  $no_correct_predictions;?> / 
                                        (<?php echo  $no_correct_predictions;?> + <?php echo  $no_missed_predictions;?>) = <?php echo $prediction_rate_final; ?>

                                    <br>
                                    C = rata predictii corecte
                                    <br>
                                    G = rata predictii gresite
                                </div> 
                            </div>

                            <div class="figure-no-of-predictions clearfix">
                                <span class="text-this good-predictions"> Numar predictii corecte: <b><?php echo  $no_correct_predictions;?></b></span>
                                <span class="text-this bad-predictions "> Numar predictii gresite: <b><?php echo  $no_missed_predictions; ?></b></span>
                            </div>




                            <!-- Tabel de predictii (pe jumpuri) 
                                doar tabelul cu predictiile(fix cum le afisam eu in dezvoltare) - pentru ca ne intereseaza sa le vedem unde s-au intamplat
                                EX(de cum le afisam in dezvoltare):
                                1 0 exec bp mode 1 - Static taken
                                2 0 exec bp mode 1 - Static taken
                                3 0 exec bp mode 1 - Static taken 
                            -->
                            <div class="table-predictions-div clearfix">
                                <h2 class="title-main"> Tabel de predictii: </h2>

                                <?php if(isset(HistoryTableOfChanges::$HTOC) && !empty(HistoryTableOfChanges::$HTOC)): ?>
                                <table class="table-predictions">
                                    <tr>
                                        <th> IC <!-- numaru curent instr executata --></th>
                                        <th> PC <!-- program counter --></th>
                                        <th> GP <!--Good Prediction(GP)--> </th>
                                        <th> MP <!--Miss Prediction(MP)--> </th>
                                        <th> Mnemonica <!-- Mnemonica instructiune --> </th>                                        
                                        <th> Jump<!-- jump taken or not taken --> </th>                                        
                                    </tr>


                                    <?php foreach(HistoryTableOfChanges::$HTOC as $node_HTOC):?>    
                                        <?php if(isset($node_HTOC['jump_taken']) && !empty($node_HTOC['jump_taken'])) : ?>
                                        <?php $node_jump =  $node_HTOC['jump_taken']['HTOC_jump_details'] ;//definire var node_jump pentru navigare mai easy  ?> 
                                        <tr> 
                                            <td> <?php echo $node_HTOC['IC']; ?> </td>
                                            <td> <?php echo $node_HTOC['PC']; ?> </td>
                                            <td> <?php echo $node_jump['good_predictions_total']; ?> </td>
                                            <td> <?php echo $node_jump['miss_predictions_total']; ?> </td>
                                            <td> <?php echo $node_HTOC['mnemonica_instruction']; ?>  </td>
                                            <td> <?php echo $node_jump['jump_taken']; ?>             </td>
                                        </tr>
                                        <?php endif; ?>
                                    <?php endforeach;?>
                                </table>
                                <?php endif;?>
                            </div>


                            <!-- COMMENT TO DO NEXT -->
                            <!-- 
                            FINISHED UI DESIGN - i can start now writing the documentation of dizertation(master thesis)
                            -->


                            <!-- 
                            Tabel ordine executie instructiuni
                            CAMPURI:
                            IC = instruction counter (numar_total_instructiuni_executate)
                            PC = program counter (instructiunea curenta de executat)
                            Tip instructiune 
                            Mnemonica instructiune
                            Actiune(optional) = actiunile care s-au facut la aceasta instructiune (Ex MOV: in camp va fi: R0 : 0010 0100)
                            Jump taken or jump not_taken
                             -->
                            <div class="table-order-execution-div clearfix">
                                <h2 class="title-main"> Tabel ordine executie instructiuni:     </h2>
                                <div class="figure-hide-show-action-field-table clearfix">
                                        Camp actiune: 
                                        <button type="button" class="button-this" onclick=" $('.table-order-execution tr td.action-column, .table-order-execution tr.table-head-row th.action-column').css('display', 'table-cell');"> Arata </button> 
                                        <button type="button" class="button-this" onclick=" $('.table-order-execution tr td.action-column, .table-order-execution tr.table-head-row th.action-column').css('display', 'none'); " > Ascunde </button>
                                </div>

                                <table class="table-order-execution">
                                    <tr class="table-head-row">
                                        <th><span> IC </span></th>
                                        <th><span> PC </span></th>
                                        <th><span> Tip Instructiune </span></th>
                                        <th><span> Mnemonica instructiune </span></th>
                                        <th class="action-column"><span> Actiune</span></th> 
                                        <th><span> Jump </span></th>
                                    </tr>


                                    <!-- 
                                    <tr class="tr-content">
                                        <td><span>0</span></td>
                                        <td><span>0</span></td>
                                        <td><span>MOV</span></td>
                                        <td><span>MOV R1, R2</span></td>
                                        <td><span>Actiune for this </span> </td>
                                        <td><span></span></td>
                                    </tr> 
                                    -->

                                    <?php if(isset(HistoryTableOfChanges::$HTOC) && !empty(HistoryTableOfChanges::$HTOC)): ?>
                                    <?php foreach(HistoryTableOfChanges::$HTOC as $node_HTOC):?>    
                                    <tr class="tr-content">
                                        <td><span><?php echo $node_HTOC['IC']; ?></span></td> <!-- IC comes first -->
                                        <td><span><?php echo $node_HTOC['PC']; ?></span></td> <!-- PC -->
                                        <td><span><?php echo $node_HTOC['type_instruction']; ?></span></td> <!-- type of instruction -->
                                        <td><span><?php echo $node_HTOC['mnemonica_instruction']; ?></span></td> <!-- mnemonica instructiunii -->
                                        
                                        <!-- <td><span>Actiune for this </span> </td>  -->
                                        <td  class="action-column">
                                            <span>
                                                <?php  /* print_r($node_HTOC['action']); */ ?> 
                                                <?php if(isset($node_HTOC['action']) && !empty($node_HTOC['action'])) : ?>
                                                <?php foreach($node_HTOC['action'] as $key => $node_action): //foreach pe HTOC_update_gen_register ?> 

                                                    <?php /*print_r($node_action) ; */ ?>
                                                    <?php foreach($node_action as $key_in => $node_action_inside) : //foreach pe content din cheie HTOC ?>
                                                        <p style="display:inline-block; clear:both; float:left;">
                                                        <?php 
                                                            if($key == "HTOC_update_general_register"){
                                                                //cheia variabilei din foreach superior node_HTOC['action']
                                                                //display pentru general register
                                                                echo $key_in . " = " . $node_action_inside['hexa_value'] ; 
                                                            } elseif ($key == "HTOC_update_memory_data") {
                                                                //display pentru memory data store
                                                                echo $key_in . " = " . $node_action_inside['hexa_value'] ; 
                                                            } else{
                                                                //display normal(pe flaguri)
                                                                echo $key_in . " = " . $node_action_inside ; 
                                                            }
                                                            
                                                        ?>
                                                        </p>
                                                    <?php endforeach;?>

                                                <?php endforeach; ?> 
                                                <?php endif; ?>   
                                            </span> 
                                        </td> 

                                        <td>
                                            <?php  /* print_r($node_HTOC['jump_taken']); */ ?> 
                                            <?php if(isset($node_HTOC['jump_taken']) && !empty($node_HTOC['jump_taken'])) : ?>
                                            <?php $node_jump =  $node_HTOC['jump_taken']['HTOC_jump_details'] ;//definire var node_jump pentru navigare mai easy  ?>    
                                            <span class="span-jump"> <?php echo $node_jump['jump_taken']; ?> 
                                                <div class="details-prediction">
                                                    <div class="icon-this"> ? </div>
                                                    <div class="description-this">
                                                        <p title="(Good prediction doar pentru acest jump)">Predictie corecta : <b><?php echo $node_jump['good_prediction']; ?></b> </p> 
                                                        <p title="(Miss prediction doar pentru acest jump)">Predictie gresita : <b><?php echo $node_jump['miss_prediction']; ?></b> </p> 
                                                        <!-- sau WP : wrong prediction nume pentru asta -MP - miss prediction-->
                                                        <p>--------------- </p>
                                                        <p>Numar total predictii corecte: <b><?php echo $node_jump['good_predictions_total']; ?></b> </p> 
                                                        <!-- Numar total predictii corecte:(pana acum) -->
                                                        <p>Numar total predictii gresite: <b><?php echo $node_jump['miss_predictions_total']; ?></b> </p> 
                                                        <!-- Numar total predictii gresite:(pana acum) -->
                                                    </div>
                                                </div>
                                            </span> 
                                            <?php endif; ?>

                                            <!-- pus un icon cu ?(semnul intrebarii) aici si la hover pe ele arati numar total predictii corecte pana acum si numar total predictii gresite pana acum - fix cum afisam eu la teste 
                                                ATENTIE: poate bag si informatia: GP:1  MP:0 (care e acum direct in span) - in divul ascuns
                                            -->
                                            <!-- 
                                                mesajul jump not_taken vine din jump-ul propriu zis
                                                mesajul GP:1 vine din predictori(inseamna good prediction) inseamna ca s-a prezis bine
                                                mesajul MP:0 vine din predictori(inseamna miss prediction) inseamna ca predictorul nu a prezis bine(daca era 1)
                                             -->
                                        </td>

                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>



                                    <!--
                                    <tr class="tr-content">
                                        <td><span>2</span></td>
                                        <td><span>2</span></td>
                                        <td><span>MOV</span></td>
                                        <td><span>MOV R5, 0x70</span></td>
                                        <td><span></span></td>
                                    </tr>
                                    <tr class="tr-content">
                                        <td><span>1</span></td>
                                        <td><span>1</span></td>
                                        <td><span>JNZ</span></td>
                                        <td><span>JNZ to_label</span></td>
                                        <td>
                                            <span class="span-jump"> jump not_taken GP:1  MP:0
                                                 <div class="details-prediction">
                                                    <div class="icon-this"> ? </div>
                                                    <div class="description-this">
                                                        <p>Numar total predictii corecte: <b>1</b> </p> 
                                                        <p>Numar total predictii gresite: <b>2</b> </p> 
                                                    </div>
                                                </div>
                                            </span>
                                        </td>
                                    </tr>
                                    -->


                                </table>
                            </div>

                        </div>
                        </div>
                        <!-- end first tab #tab1 - rezultate -->








                        <!-- Tab2 - Navigare Structuri -optionala -->
                        <div class="contents-tabs" id="tab2">
                        <div class="view-app-results  on-tabs-nav-overflow  clearfix">
                            <div class="div-view-registers clearfix" style="display:none;">
                                <h2 class="title-main"> (icon) Vizualizare registrii</h2>
                                <div class="view-individual-registers">

                                    <div class="view-register">
                                        <h4 class="title-register">PC <!--mai pot atasa icoana cu ? si la ea sa afiseze cu popup:(Stack pointer register)--></h4>
                                        <span class="value-register"> 0DH</span>
                                    </div>
                                    <div class="view-register">
                                        <h4 class="title-register">IC <!--IC - numar_total_instructiuni_executate--></h4>
                                        <span class="value-register"> 0DH</span>
                                    </div>



                                    <div class="clearfix"> </div>

                                    <div class="view-flag">
                                        <h4 class="title-flag">Z_flag <!--IC - numar_total_instructiuni_executate--></h4>
                                        <span class="value-flag"> 0 </span>
                                    </div>
                                    <div class="view-flag">
                                        <h4 class="title-flag">C_flag <!--C - Carry Flag--></h4>
                                        <span class="value-flag"> 0 </span>
                                    </div>
                                    <div class="view-flag">
                                        <h4 class="title-flag">N_flag <!--N - Negative Flag--></h4>
                                        <span class="value-flag"> 0 </span>
                                    </div>
                                    <div class="view-flag">
                                        <h4 class="title-flag">O_flag <!--O - Overflow Flag -nu il folosesc niciodata in app mea(unsigned arithmetic) --></h4>
                                        <span class="value-flag"> 0 </span>
                                    </div>
                                </div>
                                <div class="figure-table-general-registers clearfix">
                                    <div class="title-main-table-gen-reg">
                                        <h4 class="title-this">Registru General</h4>
                                        <h4 class="title-this">Valoare Hexa</h4>
                                        <h4 class="title-this">Valoare Binar</h4>                                        
                                    </div>
                                    <div>
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">R0</span>
                                            <span class="desc-this">0x2D</span>
                                            <span class="desc-this">00101101</span>                                        
                                        </div>
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">R1</span>
                                            <span class="desc-this">0x2D</span>
                                            <span class="desc-this">00101101</span>                                        
                                        </div>
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">R2</span>
                                            <span class="desc-this">0x2D</span>
                                            <span class="desc-this">00101101</span>                                        
                                        </div>
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">R3</span>
                                            <span class="desc-this">0x2D</span>
                                            <span class="desc-this">00101101</span>                                        
                                        </div>
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">R4</span>
                                            <span class="desc-this">0x2D</span>
                                            <span class="desc-this">00101101</span>                                        
                                        </div>
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">R5</span>
                                            <span class="desc-this">0x2D</span>
                                            <span class="desc-this">00101101</span>                                        
                                        </div>
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">R6</span>
                                            <span class="desc-this">0x2D</span>
                                            <span class="desc-this">00101101</span>                                        
                                        </div>
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">R7</span>
                                            <span class="desc-this">0x2D</span>
                                            <span class="desc-this">00101101</span>                                        
                                        </div>
                                    </div>
                                </div>
                                <div class="figure-table-general-registers clearfix">
                                    <div class="title-main-table-gen-reg">
                                        <h4 class="title-this">Registru General</h4>
                                        <h4 class="title-this">Valoare Hexa</h4>
                                        <h4 class="title-this">Valoare Binar</h4>                                        
                                    </div>
                                    <div>
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">R8</span>
                                            <span class="desc-this">0x2D</span>
                                            <span class="desc-this">00101101</span>                                        
                                        </div>
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">R9</span>
                                            <span class="desc-this">0x2D</span>
                                            <span class="desc-this">00101101</span>                                        
                                        </div>
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">R10</span>
                                            <span class="desc-this">0x2D</span>
                                            <span class="desc-this">00101101</span>                                        
                                        </div>
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">R11</span>
                                            <span class="desc-this">0x2D</span>
                                            <span class="desc-this">00101101</span>                                        
                                        </div>
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">R12</span>
                                            <span class="desc-this">0x2D</span>
                                            <span class="desc-this">00101101</span>                                        
                                        </div>
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">R13</span>
                                            <span class="desc-this">0x2D</span>
                                            <span class="desc-this">00101101</span>                                        
                                        </div>
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">R14</span>
                                            <span class="desc-this">0x2D</span>
                                            <span class="desc-this">00101101</span>                                        
                                        </div>
                                        <!-- ultima casuta pusa pentru uniformitate in afisare: -->
                                        <div class="line-current-reg clearfix">
                                            <span class="desc-this">---</span>
                                            <span class="desc-this">----</span>
                                            <span class="desc-this">--------</span>                                        
                                        </div>
                                    </div>
                                </div>

                            </div>



                            <!-- tabel pipeline se construieste dupa program_memory. Deocamdata e construit STATIC cu php -->
                            <?php if(isset(Interpretor::$program_memory) && !empty(Interpretor::$program_memory)): ?>
                            <div class="div-view-pipeline-process">
                                <h2 class="title-main"> <span class="icon-this-inside">(icon)</span> Vizualizare proces Pipeline</h2>
                                <div class="figure-pipeline-table-div">
                                    <!--
                                        FA TABELUL DE PIPELINE
                                        Pune clase pe table,tr
                                        Pune span inauntrul fiecarui th,td
                                        Scoate numele ciclu si lasa decat 1,2,3...,n
                                        MAI VEZI TU
                                    -->
                                    <table class="table-pipeline">
                                        <tr>
                                            <th><span>Numar curent</span></th>
                                            <th><span>Mnemonica Instructiune</span></th>
                                            <th colspan="50"><span>Cicluri</span></th>
                                            <!--LA ACEST TH colspan mai mare sau egal cu numarul de cicluri ca sa dea bine in tabel
                                            https://stackoverflow.com/questions/3838488/html-table-different-number-of-columns-in-different-rows
                                            -->
                                        </tr> 

                                        <?php
                                            //display the cicles of pipeline first
                                            //print_r(Interpretor::$program_memory);

                                            //get program memory length
                                            $length_program_memory = count(Interpretor::$program_memory);
                                            //echo $length_program_memory;

                                            //set a var current_no to use in pipeline 
                                            //default is set to 0 , dar trebuie sa fie setat dupa cum e forul cu cicluri
                                            //in the foreach loop , this var will be incremented by 1
                                            $current_no = 0; 

                                            //internal pointer for placing the pipeline FDEMW constructors
                                            $ip = 0; //to be incremented after each FOREACH
                                        ?>
                                        <tr>
                                            <td> - </td>
                                            <td> - </td>
                                            <?php for($i = 0;  $i < $length_program_memory +4;  $i++):?>
                                                <td><span><!--Ciclul-->  <?php echo $i;?></span></td>
                                            <?php endfor;?>    
                                        </tr>


                                        <?php foreach(Interpretor::$program_memory as $row_prog_mem): ?>
                                        <tr>
                                            <td> <?php echo $current_no; $current_no++; ?> </td> <!-- numar curent -->
                                            <td><span><?php echo $row_prog_mem["mnemonica_instruction"]; ?></span></td>

                                            <?php // insert whitespace before pipeline steps ;?>
                                            <?php for($pipeline = 0; $pipeline< $ip; $pipeline++): ?>
                                                <td><span>...</span></td> 
                                            <?php endfor; ?>  

                                            <td><span>F <!--(Fetch)--></span></td>
                                            <td><span>D <!--(Decode)--></span></td>
                                            <td><span>E <!--(Execute)--></span></td>
                                            <td><span>M <!--(Memory)--></span></td>
                                            <td><span>W <!--(Writeback)--></span></td>

                                            <?php //acum trebuie sa merg de la ($ip +5[pentru ca atatia pasi imi consuma pipeline stages FDEMW]) pana la (length_of_program_memory - 1[asta inseamna in for strict < ] +4[+4 ca sa acoperim stage-urile ramase nedesenate in tabel])  ;?>
                                            <?php  for($pipeline = $ip + 5; $pipeline< $length_program_memory +4; $pipeline++): ?>
                                                <td><span>...</span></td> <!-- insert whitespace before pipeline steps -->
                                            <?php endfor; ?>  

                                            <?php $ip++; //aici voi incrementa ip-ul cu o unitate ca sa functioneze lucrurile; ?>
                                        </tr>
                                        <?php endforeach;?>


                                        <!--
                                        <tr>
                                            <td> 1 </td>
                                            <td><span>MOV R1, R3</span></td>
                                            <td><span>F (Fetch)</span></td>
                                            <td><span>D (Decode)</span></td>
                                            <td><span>E (Execute)</span></td>
                                            <td><span>M (Memory)</span></td>
                                            <td><span>W (Writeback)</span></td>
                                            <td><span>...</span></td>
                                            <td><span>...</span></td>
                                            <td><span>...</span></td>
                                            <td><span>...</span></td>
                                            <td><span>...</span></td>
                                            <td><span>...</span></td>
                                            <td><span>...</span></td>
                                        </tr>
                                          <tr>
                                            <td> 2 </td>
                                            <td><span>JMP R1, 0x1111</span></td>
                                            <td><span>...</span></td>
                                            <td><span>F (Fetch)</span></td>
                                            <td><span>D (Decode)</span></td>
                                            <td><span>E (Execute)</span></td>
                                            <td><span>M (Memory)</span></td>
                                            <td><span>W (Writeback)</span></td>
                                            <td><span>...</span></td>
                                            <td><span>...</span></td>
                                            <td><span>...</span></td>
                                            <td><span>...</span></td>
                                            <td><span>...</span></td>
                                            <td><span>...</span></td>
                                        </tr>
                                        -->
                                    </table>
                                </div>

                                <div class="figure-navigate-pipeline-table" style="display:none;">
                                    <div class="nav-pipeline">
                                        <span class="nav-this on-back"> &laquo; </span>
                                        <span class="nav-this on-autoplay"> Au </span>
                                        <span class="nav-this on-pause"> Pa </span>
                                        <span class="nav-this on-next"> &raquo; </span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>






                            <!-- tabel memorie de date -->
                            <?php if(isset(Memory::$data_memory) && !empty(Memory::$data_memory)): ?>
                            <div class="div-view-data-memory">
                                <h2 class="title-main"> <span class="icon-this-inside">(icon)</span> Vizualizare tabel memorie date</h2>
                                <div class="figure-data-memory-table-div">
                                    <table class="table-data-memory">
                                        <tr>
                                            <th><span>Adresa Hex</span></th>
                                            <th><span>Adresa int</span></th>
                                            <th><span>Valoare Hex</span></th>
                                            <th><span>Valoare int</span></th>
                                        </tr> 

                                        <?php foreach(Memory::$data_memory as $row_memory): ?>
                                        <tr>
                                            <td> <?php echo $row_memory['address_hex']; ?> </td> <!-- adresa in hexa -->
                                            <td> <?php echo $row_memory['address_int']; ?> </td> <!-- adresa integer -->
                                            <td> <?php echo $row_memory['value_hex']; ?> </td>  <!-- valoare hexa -->
                                            <td> <?php echo $row_memory['value_int']; ?> </td>   <!-- valoare int -->
                                        </tr>
                                        <?php endforeach; ?>

                                        <!-- 
                                        <tr>
                                            <td> 0x0000 </td>
                                            <td> 0 </td>
                                            <td> 0x78 </td> -COMMENT THIS IF UNCOMMENT SECTION- valoare stocata la adresa 0x0000 -COMMENT UNTIL HERE
                                            <td> 120 </td>
                                        </tr>
                                        -->
                                    </table>
                                </div>
                            </div>
                            <?php endif; ?>


                            <!-- <div class="div-view-time-of-running">(icon) Timpul de executie pentru BP mode 0 a fost de X microSec</div> -->

                        </div>
                        </div>
                        <!-- end second tab #tab2 Vizualizare Navigare Registrii -->

                    </div>
                    <!-- end display-results div -->
                    
                    <?php else: ?>
                    <?php /*on else case, displaying a message that all statements didn't executed successfully*/?>

                    <?php /* var_dump(Interpretor::$app_execution_ready); */ ?>

                    <div class="display-results on-not-finished-instructions-yet clearfix">
                        <div class="figure-not-executed"> <span>Rezultatele cpuBP se vor afisa aici. </span> </div>
                    </div>


                    <?php endif; ?>
                    <?php /*end if for testing if all statements executed succesfully*/ ?>

                    
                    <!-- button parent is section-display-results -->
                    <a href="javascript:void(0)" class="button-full-screen-this" title="Minimizeaza/Maximizeaza Fereastra"></a>
                </div>
                


                <!-- section-errors had clearfix class -->
                <div class="section-errors">
                    <h2 class="title-this <?php if(Error::occur()): ?> have-errors <?php endif;?>">
                        Consola  
                        <?php /* if(Error::occur()): ?>  <span>(Sunt prezente erori)</span> <?php endif; */?>
                    </h2>
                    
                    <!-- here comes the errors -->
                    <div class="error-this">
                        <?php if(Error::occur()): ?><span class="warn-errors">  <span>Sunt prezente erori:</span> </span><?php endif; ?>

                        <!-- 
                        <span class="type-info">Info1 </span>
                        <span class="type-info">Info2 </span>
                        <span class="type-error">Eroare1 </span>
                        <span class="type-warning">Atentionare</span> 
                        -->
                        
                        <?php if (isset($interpret_result) && $interpret_result): ?>
                            <!-- is interpret_result isset , show info in console -->
                            <span class="type-info">Interpretarea s-a terminat cu success.</span>
                        <?php endif; ?>


                        <?php if (isset($check_code_msj) && !empty($check_code_msj)): ?>
                            <!-- is check_code(parse) isset , show info in console -->
                            <span class="type-info"> <?php echo $check_code_msj; ?> </span>
                        <?php endif;?>


                        <?php if(Error::occur()): ?>
                            <!-- is errors occured , display them -->
                            <?php foreach(Error::getErrors() as $error): ?>
                                <span class="type-error"> <?php echo $error; ?> </span>
                            <?php endforeach; ?>    
                        <?php endif; ?>


                    </div>
                    <!-- end here comes the errors -->
                    
                </div>
                
            <!-- END MAIN CONTENT -->    
            </div>
            
        </div>


    <!-- before closing body , some js -->
    <script src="public/assets/js/jquery.idTabs.min.js" type="text/javascript"></script>
    </body>
</html>
