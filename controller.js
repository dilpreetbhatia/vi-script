
//This JS creates the Request to call hdFlagsProcessor.php using POST method and then processes the JSON response to Display the results.


//Global Variables to be used
var iReq = new XMLHttpRequest();
var url = "hdFlagsProcessor.php";


function initialize(){		
    var _tf = 0; //Set total number of True flags to zero for the first request
    var _ff = 0; // Set total number of False flags to zero for first request
    var _pn = 1; // set starting page to 1 for the first request.
    //Make the Logs Div visible
    document.getElementById('logs').style.display = "block";
    logThis("### Starting Now .... \n");
 	//send the first request request 
    postRequest(_pn,_tf,_ff);
}


//This function prepares the POST request and sends the request. And once we get the response, calls updateStatus()
function postRequest(pn, tf, ff){
    
    //Create the Parameter string for sending POST request
    var vars = "starting_page_no="+pn+"&total_true_flags="+tf+"&total_false_flags="+ff;

   	logThis(":: NEW REQUEST :: \n");
    logThis(">Sending Following Data to PHP for next batch : " + vars + "\n" );
   	

    iReq.open("POST", url, true);
  
    // Set content type header information for sending url encoded variables in the request
    iReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    // Send the data to PHP now... and wait for response to update the status 
    iReq.send(vars); // Actually execute the request
    
   	logThis(">Request Sent \n");
    
    // Access the onreadystatechange event for the Request object
    //readyState - 4 indicates that response is ready for us. and 200 means its not an error. So we call updateStatus() function
    iReq.onreadystatechange = function() {
	    if(iReq.readyState == 4 && iReq.status == 200) {
	    	logThis(">Got JSON from PHP " + "\n");
	     	updateStatus();    	 
	    	}
	    }
}


//This function parses the JSON sent by PHP and prepares variables for the next call to PHP based on the More flag received. 
//If gotMore = 0, we dont sent any more requests to php
function updateStatus(){
	
	var return_data = iReq.responseText;

	logThis(return_data + "\n\n");
		    
	var parsedReturnData = JSON.parse(return_data);
		   
	if(parsedReturnData.gotMore){ //IF more=true
		//Then Send the Request Again with the next page to be processed, total number of true flags and total number of false flags.
		postRequest(parsedReturnData.nextPage,parsedReturnData.totalTrueFlags,parsedReturnData.totalFalseFlags);
		//And Display the running status of flags nicely.
		displayRunningStats(parsedReturnData);
    } else  {    //Else - we need to wrap things up and display the final values. No Request is sent to PHP.
		//Display the end result 
		displayFinalStats(parsedReturnData);
		logThis("### Finished the processing. found more=false flag");
	} //Close Else

} //Close updateStatus() Function


//DISPLAY FUNCTIONS
//Log Display Function. Updates the logBoxer TextArea
function logThis(info){
	document.getElementById("logBoxer").value = document.getElementById("logBoxer").value + info;
	document.getElementById("logBoxer").scrollTop = document.getElementById("logBoxer").scrollHeight;
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