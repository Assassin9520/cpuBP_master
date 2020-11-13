<?php 
//phpinfo(); die;

ini_set('display_errors', 1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);



//get directory name for this file(index.php)
$nd = dirname(__FILE__);//name directory(nd)
//echo $nd;



//include Helper files
include($nd . "/Helper/helper_functions.php");

//include Core files
include($nd . "/Core/Config.php");
include($nd . '/Core/Lexer.php');
include($nd . '/Core/Parser.php');

include($nd . "/Core/SymbolTable.php");
include($nd . "/Core/Interpretor.php");
include($nd . "/Core/Error.php");
include($nd . "/Core/App.php");

use \Core\Interpretor;
use \Core\Error;
use \Core\App;

//now follows the main UI of cpuBP(cpuBranchPredictor)
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
                    <a href="#" class="logo-main-app-ui">logo of app here(minified)</a>
                </div>


                <div class="section-form-data clearfix">
                	<form method="POST" action="http://localhost/cpubp_master/" class="form-this">
                    	<textarea id="main-textarea" class="input-this on-textarea" name="text" spellcheck="false" placeholder="Inserati Codul aici..."></textarea>
                        
                        <div>
                        	<div class="form-this-choose-branch-prediction-mode">
                        		<div class="bp-select-title">Alege mod predictie </div>
                        		<div class="inputs-holder">
	                        		<span class="input-holder"><input type="radio" id="bp_mode_0" name="branch_prediction_mode" value="0" /><label for="bp_mode_0" class="input-title">Predictie nivel 0</label></span>
	                        		<span class="input-holder"><input type="radio" id="bp_mode_1" name="branch_prediction_mode" value="1" /><label for="bp_mode_1" class="input-title">Predictie nivel 1</label></span>
	                        		<span class="input-holder"><input type="radio" id="bp_mode_2" name="branch_prediction_mode" value="2" /><label for="bp_mode_2"class="input-title">Predictie nivel 2</label></span>
	                        		<span class="input-holder"><input type="radio" id="bp_mode_3"name="branch_prediction_mode" value="3" /><label for="bp_mode_3"class="input-title">Predictie nivel 3</label></span>
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
                	<div class="display-results clearfix">
                		<div class="view-app-results clearfix">
	                		<div class="div-view-registers">
	                			<h2 class="title-main"> (icon) Vizualizare registrii</h2>
	                			<div>
		                			<div class="view-register">
		                				<h4 class="title-register">SP <!--mai pot atasa icoana cu ? si la ea sa afiseze cu popup:(Stack pointer register)--></h4>
		                				<span class="value-register"> 0DH</span>
		                			</div>
		                			<div class="view-register">
		                				<h4 class="title-register">PC <!--mai pot atasa icoana cu ? si la ea sa afiseze cu popup:(Stack pointer register)--></h4>
		                				<span class="value-register"> 0DH</span>
		                			</div>
		                			<div class="view-register">
		                				<h4 class="title-register">IR <!--mai pot atasa icoana cu ? si la ea sa afiseze cu popup:(Stack pointer register)--></h4>
		                				<span class="value-register"> 0DH</span>
		                			</div>
		                			<div class="view-register">
		                				<h4 class="title-register">SP <!--mai pot atasa icoana cu ? si la ea sa afiseze cu popup:(Stack pointer register)--></h4>
		                				<span class="value-register"> 0DH</span>
		                			</div>
	                			</div>
	                		</div>

	                		<div class="div-view-pipeline-process">
	                			<h2 class="title-main"> (icon) Vizualizare proces Pipeline</h2>
	                			<div class="figure-pipeline-table">

	                			</div>

	                			<div class="figure-navigate-pipeline-table">
	                				<div class="nav-pipeline">
	                					<span class="nav-this on-back"> &laquo; </span>
	                					<span class="nav-this on-autoplay"> Au </span>
	                					<span class="nav-this on-pause"> Pa </span>
	                					<span class="nav-this on-next"> &raquo; </span>
	                				</div>
	                			</div>
	                		</div>

	                		<div class="div-view-time-of-running">(icon) Timpul de executie pentru BP mode 0 a fost de X microSec</div>
                		</div>
                    </div>
                    
                    <!-- button parent is section-display-results -->
                    <a href="javascript:void(0)" class="button-full-screen-this" title="Minimizeaza/Maximizeaza Fereastra"></a>
                </div>
                


                <!-- section-errors had clearfix class -->
                <div class="section-errors">
                	<h2 class="title-this <?php if(Error::occur()): ?> have-errors <?php endif;?>">
                        Consola  
                        <?php if(Error::occur()): ?>  <span>avem_erori</span> <?php endif; ?>
                    </h2>
                    
                    <!-- here comes the errors -->
                    <div class="error-this">
                    	
                        <span class="type-info">Info1 </span>
                        <span class="type-info">Info2 </span>
                        <span class="type-error">Eroare1 </span>
                        <span class="type-warning">Atentionare</span> 
                        

                    </div>
                    <!-- end here comes the errors -->
                    
                </div>
                
            <!-- END MAIN CONTENT -->    
            </div>
            
        </div>

    </body>
</html>
