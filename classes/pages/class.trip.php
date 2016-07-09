<?php

	require_once("../classes/class.Database.php");
	require_once("../classes/class.Logging.php");
	require_once("../classes/class.GeneralPageClass.php");
	
	$this->logging = new TLogging();
	$this->database = new TDatabase();
	
	 
	if(isset($_POST)){
		$count = $_POST['count'];
		//echo "number of destinations:".$count."<br/>";			
	
		for ($k = 1; $k < $count; $k++){
			$state = array();
			if($k == 1){
				$origin = $_POST['destins'];
				$destination = $_POST['destins'.($k+1)];
			}else{
				$origin = $_POST['destins'.$k];
				$destination = $_POST['destins'.($k+1)];
			}

			$orig = array();
			$orig = explode(",", $origin);
			$currentState = $orig[1];
			$orig[0] = preg_replace('/\s+/','%20',$orig[0]);
			$orig = $orig[0].",".trim($orig[1]);

			$dest = array();
	        $dest = explode(",", $destination);	
   			$dest[0] = preg_replace('/\s+/','%20',$dest[0]);
			$dest = $dest[0].",".trim($dest[1]);
		
			//echo($orig)."<br/>";
			//echo($dest)."<br/>";
			
			$url="https://maps.googleapis.com/maps/api/directions/json?origin=".$orig."&destination=".$dest."&key=AIzaSyAt3T9OxkVUK28OrR_HEb3I1BtJJo5oMVk";

			$jsonData = file_get_contents($url);
			$jsonData = json_decode($jsonData, true);
			$numSteps = count($jsonData['routes'][0]['legs'][0]['steps']);
			
			$cnt = 0;
			$miles = 0;
			for ($q = 0; $q < $numSteps; $q++) {
				$string = $jsonData['routes'][0]['legs'][0]['steps'][$q]['polyline']['points'];
				$byte_array = array_merge(unpack('C*', $string));
				$results = array();			
				$index = 0; 
				do {
					$shift = 0;
					$result = 0;
					do {
						$char = $byte_array[$index] - 63; 
						$result |= ($char & 0x1F) << (5 * $shift);
						$shift++; $index++;
					} while ($char >= 0x20);

					if ($result & 1)
						$result = ~$result;
					$result = ($result >> 1) * 0.00001;
					$results[] = $result;
				} while ($index < count($byte_array));
										
				for ($i = 2; $i < count($results); $i++) {
					$results[$i] += $results[$i - 2];
				}
					
				foreach($results as $val){
					$cnt++;
					if ($cnt % 2 == 0){
						$lng = $val;
						if($cnt == 2){
							$b_lng = $lng;
							$b_lat = $lat;		
						}
						$sql = "SELECT stusps FROM tl_2009_us_state WHERE st_contains(shape, geomfromtext('point(".$lng." ".$lat.")',1))";
						$result= $this->database->singleRowQuery($sql);
						if (trim($result['stusps']) != trim($currentState)){
							$e_lng = $lng;
							$e_lat = $lat;
							$details = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=".$b_lat.",".$b_lng."&destinations=".$e_lat.",".$e_lng."&mode=driving&key=AIzaSyAt3T9OxkVUK28OrR_HEb3I1BtJJo5oMVk";
							  	
						  	$json = file_get_contents($details);
							$json  = json_decode($json, TRUE);
							$miles = $json['rows'][0]['elements'][0]['distance']['text'];
							$state[$currentState] = $miles;

							$currentState = $result['stusps'];
						
							$b_lng = $e_lng;
							$b_lat = $e_lat;					
						}
						
					}
					else{
						$lat = $val;
					}																
				}	
			}	
			$e_lng = $lng;
			$e_lat = $lat;
			$details = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=".$b_lat.",".$b_lng."&destinations=".$e_lat.",".$e_lng."&mode=driving&key=AIzaSyAt3T9OxkVUK28OrR_HEb3I1BtJJo5oMVk";
						  	
			$json = file_get_contents($details);
			$json  = json_decode($json, TRUE);
			$miles = $json['rows'][0]['elements'][0]['distance']['text'];
			$state[$currentState] = $miles;

			echo "STATE TOTALS"."<br/>";
			echo "<table border=\"5\" cellpadding=\"10\">";
			
			foreach($state as $key=>$val){
				echo '<tr>';
				echo '<td>'.$key.'</td>';
				echo '<td>'.$val.'</td>';
				echo '</tr>';
									
			}
			echo "</table>";
		}
	
	}
//	$this->createContent();
//	$this->assignPlaceholder('navbar');
	//$this->assignPlaceholder('applySuccess');
	//$this->assignPlaceholder('slides');
//	$this->assignPlaceholder('copyright');
//	$this->showContent();


