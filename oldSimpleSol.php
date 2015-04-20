<?php

#Increasing the Execution Time before the Script Times Out
ini_set('max_execution_time', 100);

#Initializing the Variables
$hdFlagTrue = 0;
$hdFlagFalse = 0;
$pageNo = 1;
$moreFlagCount = 0;

$moreFlag = 1;
$falseFlagsInPg = 0;


for ($pageNo=1 ;; $pageNo++){


	$moreFlag = 0;
	$jsondata = file_get_contents("http://api.viki.io/v4/videos.json?app=100250a&per_page=10&page=".$pageNo);
	$json     = json_decode($jsondata,true);

	if ($json['more'])
		{
		 $moreFlagCount =  $moreFlagCount + 1;
		 $moreFlag = 1;
		}
		else
		{
			$moreFlag = 0;
		}


		$falseFlagsInPg = 0;

		foreach ($json['response'] as $video) {
			#$output .= "<h4>". $video["titles"]["en"]. "</h4>";
			#$output .= "  ";
			#$output .= $video["flags"]["hd"];

			if($video["flags"]["hd"])
			{
				$hdFlagTrue = $hdFlagTrue + 1;
			}
			else
			{
				$hdFlagFalse = $hdFlagFalse + 1;
				$falseFlagsInPg = $falseFlagsInPg + 1;
			}
			
			
		}
		if ($falseFlagsInPg > 0) {
			ob_flush(); 
		    flush();
			echo "We found ". $falseFlagsInPg . " HD:false flags on page number : " . ($pageNo) . " <br />";
			
		}


	if ($moreFlag == 0)
	  break;
}

#echo $output;
echo "No of HD FLAG TRUE = " . $hdFlagTrue . " <br /> No of HD FLAG FALSE = ".$hdFlagFalse .  " <br /> No of MORE True flags = " . $moreFlagCount ;
ob_flush();
flush();
?>
