<?php 
	require_once("../classes/class.GeneralPageClass.php");
 	require_once("../classes/class.Database.php");

	class TPageClass extends TGeneralPageClass {
		function init() {
			$this->showHeaders();
			$this->createContent();
			$this->showContent();
		
		}
		

		function showHeaders(){
			header("Content-Style-Type: text/css");
			header("Content-Type: text/css");	
		}

		function handleFormSubmission() {	

		}

	}
