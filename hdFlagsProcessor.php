<?php

### LOGIC ####################
# The php script interacts with javascript to complete its task of counting all the HD true and False flags till we get to more=false
#                                              
# Script process a batch of pages in one go. You can set the batch to 1 to process one page at a time.                                     
# The Higher the Batch size more time it will take for the script to run and return, and there are chances that default execution time limit of 30 secs will be exceeded.
# Implementing Batch Processing helped to eliminate dependency on the execution time limit, as we are not aware of how long the script will run, till we get to more=false flag.
# This script should be able to scale upto any number of pages.
#
# After each batch completion, PHP script hands over the JSON formatted result object to javascript. Javascript reads and calls the script again, based on the value of gotMore flag and 
# other stats that we send. 
# Javascripts calls the PHP with 3 pieces of information, the page to start processing from, and the current comulative count of True And False Flags till now. 
#
# Author       : Dilpreet Bhatia
# Last Updated : 04-20-2015
#################################

## Initializing Variables

## CONSTANTS FIRST
#  Results to be Included Per Page
$PER_PAGE = 10;  

# $BATCH_SIZE will determine how many pages will be processed in one go by the script. Currently its set as 1 page at a time. 
# Increasing this Constant may result in Script timeout errors. Keep it to Minimum for better user experience. 
$BATCH_SIZE = 1; 


## MORE VARIABLES
#Tells the Script from which page to Start for current batch. This Variable will be passed to the script by calling JS, for every run.
$starting_page = 1;

#counter for number of HD flags set to true within entire run. This is also set by arguments passed by Javascript.
#It will store the commulative value of Flags, and thus javascript will tell us where to start the counter from for each batch.
$cnt_true_flags = 0;

#counter for number of HD flags set to false.This is also set by arguments passed by Javascript.
#They will store the commulative value of Flags, and thus javascript will tell us where to start the counter from for each batch.
$cnt_false_flags = 0;

#Variables for tracking true and false flags in a batch.
$falseFlagsInBatch = 0;
$trueFlagsInBatch = 0;

# Handy Variables to keep track of Page we are at,and what next page should be. These are also passed to our JS
$page_no_processed = 0;
$next_page = 0;

# Toggle Switch for More flag, will be set according to the value we find on each page.
$more_flag = 0;


##########################
## STARTING THE REAL THING 
#Get Parameters from the Post Request from the calling JS
$starting_page = $_POST['starting_page_no'];
$cnt_true_flags = $_POST['total_true_flags'];
$cnt_false_flags = $_POST['total_false_flags'];


#Start FOR loop, the loop will be run for cycles = Batch_Size. Each cycle processes one page of response. and increament to call next page. 
#The loop starts from whatever page number is sent to us via JS, determined by $starting_page.


for ($p=$starting_page ; $p < $starting_page + $BATCH_SIZE ; $p++){

	$page_no_processed = $p; #Page we are currently processing. This will be passed in the return json object
	
	#Form the URL based on the value of the page we want to process
	$url = "http://api.viki.io/v4/videos.json?app=100250a&per_page=".$PER_PAGE."&page=".$p;

	#Call and Load the response into a variable
	$jsondata = @file_get_contents($url);
	
		#Check if the response is empty, If Yes then return the Error Message
		if(!$jsondata){
			$error = error_get_last();
	      echo "HTTP request failed. Error " . $error['message'];
		} else {   	#START BIG ELSE LOOP
				   	#If response is not empty, then we proceed with decoding the JSON received.
				$json = json_decode($jsondata,true);

					#If we encounter more flag as true we set the toggle $more_flag to 1,  Else we set it to 0, as we set it to zero, so that we dont get any
					#further requests. This should also handle the case where more is 'null' or 'not defined for some reason'
					if ($json['more']){
					 	  $more_flag = 1;
					} else{ 
						$more_flag = 0;
					}

	 			#START FOREACH
	 			#We will process the JSON for each obj in the response to find out values of the flags	
				foreach ($json['response'] as $video) {

					if($video["flags"]["hd"]){
						$trueFlagsInBatch = $trueFlagsInBatch + 1;
					}else{
						$falseFlagsInBatch = $falseFlagsInBatch + 1;
					}

				} #CLOSE FOREACH

			} #CLOSE BIG ELSE LOOP

	#When processing a batch of pages greater than 1 we need to exit the FOR loop on the page we encountered more=false
	#So, break if $more_flag toggle is 0
	if ($more_flag == 0) break; 

} #CLOSE FOR LOOP


#We set the Next Page variable to 0, if more=false, as we do not expect JS to call the script in that case.
#In more==true case, we would like the $next_page to be set at (page where we processed + 1), thats what $p contains after exiting for loop.
if ($more_flag == 0){
	$next_page = 0;
} else {
	$next_page = $p;
}


#Update Flags which keep track of total number of True and False Flags till now. We simply add the flags found in the current batch to the value
#passed to us by the calling JS
$cnt_true_flags = $cnt_true_flags + $trueFlagsInBatch;
$cnt_false_flags = $cnt_false_flags + $falseFlagsInBatch;


#Creating a nice array of all the information about the run for sending back as response - that can be used by JS.
$responseArr = array('lastPageProcessed' => $page_no_processed,  
	                 'batchTrueFlagCount' => $trueFlagsInBatch, 
	                 'batchFalseFlagCount' => $falseFlagsInBatch, 
	                 'totalTrueFlags' => $cnt_true_flags, 
	                 'totalFalseFlags' => $cnt_false_flags, 
	                 'gotMore' => $more_flag,  
	                 'nextPage' => $next_page );

#Encoding the Array to nice JSON format. and Sending it.
echo json_encode($responseArr);

################## THE END

?>
