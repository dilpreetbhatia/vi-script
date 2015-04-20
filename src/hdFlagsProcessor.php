<?php

### LOGIC ####################
# This script interacts with controller.js to count HD true/False flags till we get more=false.
#                                              
# It processes a batch of pages in one go.                                    
# The Higher the Batch size more time it will take for the script to run.
# 
# After each batch completion, it hands over the JSON object to JS. 
# Javascripts calls the PHP with 3 parameters, the page to start processing from, and the current comulative count of True And False Flags. 
#
# Author       : Dilpreet Bhatia
# Last Updated : 04-20-2015
#################################

## Initializing Variables

## CONSTANTS FIRST
#  Results to be Included Per Page
$PER_PAGE = 10;  

# Pages to Process in one batch
$BATCH_SIZE = 1; 


## MORE VARIABLES
#Starting page for the batch. 
$starting_page = 1;

#cumulative counter for number of HD flags for entire run
$cnt_true_flags = 0;
$cnt_false_flags = 0;

#Variables for tracking true and false flags in a batch.
$falseFlagsInBatch = 0;
$trueFlagsInBatch = 0;

# To keep track of Page we are at,and what next page should be.
$page_no_processed = 0;
$next_page = 0;

# Toggle Switch for More flag
$more_flag = 0;


##########################
#Get Parameters from Request sent by JS
$starting_page = $_POST['starting_page_no'];
$cnt_true_flags = $_POST['total_true_flags'];
$cnt_false_flags = $_POST['total_false_flags'];


#Loop for cycles = $BATCH_SIZE. Each cycle processes one page of response.
#The loop starts from whatever page number is sent by JS, determined by $starting_page.

for ($p=$starting_page ; $p < $starting_page + $BATCH_SIZE ; $p++){

	$page_no_processed = $p; #Page we are currently processing.
	
	#Form the URL
	$url = "http://api.viki.io/v4/videos.json?app=100250a&per_page=".$PER_PAGE."&page=".$p;

	#Call and Load the json
	$jsondata = @file_get_contents($url);
	
		#Check if the response is empty, If Yes then return the Error Message
		if(!$jsondata){
			$error = error_get_last();
	      echo "HTTP request failed. Error " . $error['message'];
		} else {   	#START BIG ELSE LOOP
				   	#If response !empty, Decode JSON.
				$json = json_decode($jsondata,true);

					#Set the $more_flag toggle
					if ($json['more']){
					 	  $more_flag = 1;
					} else{ 
						$more_flag = 0;
					}

	 			#START FOREACH
	 			#Run through JSON to get hd flag values and increament counters	
				foreach ($json['response'] as $video) {

					if($video["flags"]["hd"]){
						$trueFlagsInBatch = $trueFlagsInBatch + 1;
					}else{
						$falseFlagsInBatch = $falseFlagsInBatch + 1;
					}

				} #CLOSE FOREACH

			} #CLOSE BIG ELSE LOOP

	#Break if we get more=false within a batch.
	if ($more_flag == 0) break; 

} #CLOSE FOR LOOP


#Set $next_page variable to 0, if more=false.
if ($more_flag == 0){
	$next_page = 0;
} else {
	$next_page = $p;
}


#Update Cumulative Flags Counters
$cnt_true_flags = $cnt_true_flags + $trueFlagsInBatch;
$cnt_false_flags = $cnt_false_flags + $falseFlagsInBatch;


#Creating array of info required by JS
$responseArr = array('lastPageProcessed' => $page_no_processed,  
	                 'batchTrueFlagCount' => $trueFlagsInBatch, 
	                 'batchFalseFlagCount' => $falseFlagsInBatch, 
	                 'totalTrueFlags' => $cnt_true_flags, 
	                 'totalFalseFlags' => $cnt_false_flags, 
	                 'gotMore' => $more_flag,  
	                 'nextPage' => $next_page );

#Encoding the Array to JSON and Sending it.
echo json_encode($responseArr);

################## 

?>
