
//This JS calls hdFlagsProcessor.php and then processes the JSON response.


//Global Variables
var iReq = new XMLHttpRequest();
var url = "hdFlagsProcessor.php";


//Script starts here 
function initialize(){		
    //Make the Logs Div visible
    document.getElementById('logs').style.display = "block";
    logThis("### Starting Now .... \n");
 	//send the first request request with starting page no 1, and true and false flags count as 0
    postRequest(1,0,0);
}


//This function prepares the POST request, sends it to hdFlagsProcessor.php, calls updateStatus() on getting response
function postRequest(pn, tf, ff){
    
    //Create Parameter string
    var vars = "starting_page_no="+pn+"&total_true_flags="+tf+"&total_false_flags="+ff;

   	logThis(":: NEW REQUEST :: \n");
    logThis(">Sending Following Data to PHP for next batch : " + vars + "\n" );
   	

    iReq.open("POST", url, true);
  
    // Set content type header information
    iReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    // Send the data to PHP now... and wait for response to update the status 
    iReq.send(vars); 
    
   	logThis(">Request Sent \n");
    
    // Access the onreadystatechange event for the Request object
    iReq.onreadystatechange = function() {
	    if(iReq.readyState == 4 && iReq.status == 200) {
	    	logThis(">Got JSON from PHP " + "\n");
	     	updateStatus();    	 
	    	}
	    }
}


//This function parses JSON and prepares variables for the next call to PHP. 
function updateStatus(){
	//get the json reponse in a variable
	var return_data = iReq.responseText;
	logThis(return_data + "\n\n");

	//Parse JSON
	var parsedReturnData = JSON.parse(return_data);
		   
	if(parsedReturnData.gotMore){ //IF more=true
		//Then Send the Request with the next page to be processed, total number of true flags and total number of false flags.
		postRequest(parsedReturnData.nextPage,parsedReturnData.totalTrueFlags,parsedReturnData.totalFalseFlags);
		//And Display the running status of flags nicely.
		displayRunningStats(parsedReturnData);
    } else  {    //Else - wrap things up and display the final values. Dont send Request to PHP.
		//Display the end result 
		displayFinalStats(parsedReturnData);
		logThis("### Finished the processing. found more=false flag");
	} //Close Else

} 


//DISPLAY FUNCTIONS
//Logs display Function. Updates the logBoxer TextArea
function logThis(info){
	document.getElementById("logBoxer").value = document.getElementById("logBoxer").value + info;
	//document.getElementById("logBoxer").scrollTop = document.getElementById("logBoxer").scrollHeight;
}

//Displays Running Flags Count . Also displays on which page we are..
function displayRunningStats(parsedData){
		document.getElementById("finalTrueStats").innerHTML = "<strong>"+parsedData.totalTrueFlags + "</strong> hd:true flags found till now. <br\>";
		document.getElementById("finalFalseStats").innerHTML = "<strong>"+ parsedData.totalFalseFlags + "</strong> hd:false flags found till now. <br\>";
		document.getElementById("pageInfo").innerHTML = "Follow Along.. Our Script is currently on Page Number : " + parsedData.lastPageProcessed;
}

//Displays Final Flag Count. Also  Re-enables the button for a new execution.
function displayFinalStats(parsedData){
		document.getElementById("pageInfo").innerHTML = "Argh!! Encountered more=false flag on page " + parsedData.lastPageProcessed + ". Wrapped up the stats.";
		document.getElementById("finalTrueStats").innerHTML = "<strong>"+ parsedData.totalTrueFlags + "</strong> hd:true flags found in total. <br\>";
		document.getElementById("finalFalseStats").innerHTML = "<strong>"+ parsedData.totalFalseFlags + "</strong> hd:false flags found in total. <br\>";
		document.getElementById("startBtn").disabled = false;
}