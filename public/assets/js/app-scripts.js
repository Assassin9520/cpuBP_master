/*Application JavaScript Code*/


/*
    toggles the display of div of rate calculation in index.php
*/
function toggleDisplayRateCalc()
{
    //$('.div-show-prediction-calculation').toggle();

    $( ".figure-correct-prediction-rate .arrow-toggle-this" ).click(function() {     
        if($('.figure-correct-prediction-rate .div-show-prediction-calculation:visible').length){
            $('.figure-correct-prediction-rate .div-show-prediction-calculation').hide();
            $('.section-display-results .figure-correct-prediction-rate').removeClass('on-div-calc');
        }
        else{
            $('.figure-correct-prediction-rate .div-show-prediction-calculation').show();  
            $('.section-display-results .figure-correct-prediction-rate').addClass('on-div-calc');      
        }
    });

}




/*
	append the value given as param to main textarea

	@param Object elem, the element from HTML that we'll substract the Attribute from(Object of type DocumentNode)

	@return NULL
*/
function addInstructionInTextArea(elem)
{
	//alert(elem);

	//get Attribute data-instruction from elem
	var instruction_to_append = elem.getAttribute('data-instruction');

	//append value given as param to our main textarea input
    document.getElementById("main-textarea").value += instruction_to_append;

    //scroll to bottom of textarea from main form
    var textarea = document.getElementById("main-textarea");
    textarea.scrollTop = textarea.scrollHeight;
}



/*
    function that is used in "Salveaza Cod" button from main app UI.
    On click , will go to download source code page .php
    These 2 functions are linked refreshHref() and goToDownloadPage()

    @return Void
*/
function goToDownloadPage()
{
    //alert("click");
}

/*
    refresh the link of a tag ("salveaza cod") button to send code to downloadSource.php for furtherprocessing
    These 2 functions are linked refreshHref() and goToDownloadPage()

    @return Void
*/
function refreshHref()
{
    //alert("refresh");

    //link to download code
    var link = "http://localhost/imageinterpreter/downloadSource.php?code=";

    //get text value from main textarea via jquery
    $(document).ready(function () {
    //var val = $.trim($("#main-textarea").val());
    var val = $("#main-textarea").val();
        if (val != "") {
            //alert(val);

            //encode value of textarea before appending it to link url
            //used this to ensure that i save \r\n from losing by injecting it into url
            //var encodedVal = encodeURI(val);
            var encodedVal = encodeURIComponent(val);

            //append the text into href="" attr of our "salveaza cod" button
            //$('.class').attr("title", function() { return $(this).attr("title") + "Appended text." });
            $('#save_code_button').attr("href", function() { return link + encodedVal });
        }
    });
}




/*
    make instruction info from aside appear on click
    previously was setup to appear on hover in css via this line (that i commented):
    .aside-body .nav-add-instructions-aside ul > li:hover > .instruction-wrapper-outer-this{ display: block; visibility: visible; }
*/
$( document ).ready(function(e) {
    $('.aside-body .nav-add-instructions-aside ul > li').click(function(e){
        
        //alert("da");
        $(this).children(".instruction-wrapper-outer-this").css({"display":"block"}).css({"visibility":"visible"});

    }).on( "mouseleave", function() {

        $(this).children(".instruction-wrapper-outer-this").css({"display":"none"}).css({"visibility":"hidden"});

    });
});



/*
	make display-results DIV full screen on click on btn
	on document ready event
*/
$( document ).ready(function(e) {

    $('.section-display-results .button-full-screen-this').click(function(e){
    	var main_display_div = $(this).parent();

    	//creating toggle via class existance
    	if( main_display_div.hasClass("on-full-screen") ){//if .section-display-results  has class on-full-screen //is fullscreen

    		main_display_div.removeClass("on-full-screen");
    		//change bg image of this btn to maximize(miimize icon)
    		//$(this).css("background-image", "url(public/assets/css/resources/icon-full-screen-minimize.png)");  
    		$(this).removeClass("icon-full-screen");

    	}else {//is NOT fullscreen

    		//scroll to top of page
    		window.scrollTo(0, 0);
    		//fadeOut the div
    		main_display_div.fadeOut(300);
    		//add class to our main display div after 300ms(timeout function)
    		setTimeout(function(){
    			main_display_div.addClass("on-full-screen");
    		}, 300);
    		//change opacity back to 1
    		main_display_div.fadeIn(300);

    		//change bg image back to minimize(maximize png)
    		//$(this).css({"background-image": "url(public/assets/css/resources/icon-full-screen-maximize.png)"});
    		$(this).addClass("icon-full-screen");

    	}/*end else*/


    });

});



/*
    display overlay "Se executa..." full screen on click on submit
*/
/*
$( document ).ready(function(e) {
    $('.section-form-data .form-this .input-submit').click(function(e){
        $('.overlay-app-executing').fadeIn(20);
    });

    window.onbeforeunload = function(){
      $('.overlay-app-executing').fadeOut(10);
    };

});
*/