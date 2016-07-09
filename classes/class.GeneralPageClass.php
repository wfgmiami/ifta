<?php 
	require_once("../classes/class.Sanitization.php");
	require_once("../classes/class.Logging.php");

	class TGeneralPageClass {

		function __construct($className) {	

			$this->logging = new TLogging();
			$this->className = $className;
			$this->Sanitization = new TSanitization();
				
			if (isset($_POST['submit'])) {
				foreach($_POST as $key=>$value) {
					/* header("Location: http://54.218.87.145/application");
					   exit();
					} */
					$this->safePost[$key] = $this->Sanitization->alphaNumeric($value);
					
				}
			}
			
			$this->init();
			$this->flags['content_exists'] = 0;
		
		}
		
		function createContent() {

			if (preg_match('!^/css/(.*?)$!ismx',$_SERVER['REQUEST_URI'], $pmatches)) {
				$filename = "../templates/css/".$pmatches[1];			
				if (file_exists($filename)) {
					$content = file_get_contents($filename);		
				}

			} 			
			else if (preg_match('!^/pic/(.*?)$!ismx',$_SERVER['REQUEST_URI'], $pmatches)){			  		

				$filename = "../templates/pic/".$pmatches[1];
				$this->logging->log($filename);

				readfile($filename);
			}
			else if (preg_match('!^/js/(.*?)$!ismx',$_SERVER['REQUEST_URI'], $pmatches)) {

				$filename = "../templates/js/".$pmatches[1];
				if (file_exists($filename)) {
					$content = file_get_contents($filename);		
				}
			}
			else {		
					$className = $this->className;
					$this->logging->log($className);
					$filename ="../templates/pages/".$className.".html";	
					if (file_exists($filename)) {				
						$content = file_get_contents($filename);
						$content = str_replace('{submit_url}', '/'.$className.'/submit',$content);
						$this->flags['content_exists'] = 1;	
					}	
			   		else {
						echo "Page content not found.";
					}
				
			}	
			$this->content = $content;
		}
		

		function assignPlaceholder($placeholder) {
			$filename = "../templates/divcontent/".$placeholder.".html";	
			
			if (file_exists($filename)) {
				$newcontent = file_get_contents($filename);	
				$this->content = str_replace('{'.$placeholder.'}', $newcontent, $this->content);				
			}

		/*	if (preg_match('!^/img/(.*?)$!ismx',$_SERVER['REQUEST_URI'], $pmatches)){
				$filename = "../templates/img/".$pmatches[1];
			}*/

		}
		
		function assignCSS($placeholder) {
			$filename = "../templates/divcss/".$placeholder.".css";
			if (file_exists($filename)) {
				$newcontent = file_get_contents($filename);	
				$this->content = str_replace('{'.$placeholder.'}', $newcontent, $this->content);
			}

		}	
	
		function showContent() {
			if ($this->flags['content_exists'] == 1 || strlen($this->content) > 1) {
				echo $this->content;
			} else {
				return 0;
			}	
		}
	}


