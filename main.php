<?php

define("__URLFILE__","./urls.txt");
define("__EMAILFILE__","./emails.txt");
define("__QUIET__", false);
define("__AGENT__","Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
define("__TIMEOUT__",15);

$x = new checkmysite();
/**
* 
*/
class checkmysite {
	
	function __construct(){
		if (!__QUIET__) {
			print "\n-----------------------\nBeginning test of URLs\n";
		}
		$this->readURLlist();
		$this->readEmaillist();
		$this->initcurl();
		$this->go();
	}

	public function go() {
		foreach ($this->urls as $key => $value) {
			$this->check(trim($value));
		}

	}

	private function initcurl() {
		stream_context_set_default(
			array(
				'http' => array(
					'method' => 'HEAD'
					)
				)
			);
		$this->ch = curl_init();

		curl_setopt( $this->ch, CURLOPT_USERAGENT, __AGENT__ );
		curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $this->ch, CURLOPT_ENCODING, "" );
		curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->ch, CURLOPT_AUTOREFERER, true );
    	curl_setopt( $this->ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
    	curl_setopt( $this->ch, CURLOPT_CONNECTTIMEOUT, __TIMEOUT__ );
    	curl_setopt( $this->ch, CURLOPT_TIMEOUT, __TIMEOUT__ );
    	curl_setopt( $this->ch, CURLOPT_MAXREDIRS, 10 );
    }

    private function report($url,$code) {
    	if (!__QUIET__) {
    		print "\n";
    		print "\nError accesing:".$url;
    		print "\n emailing admin\n";
    	}
    	$subject = "Problem accessing".$url;
    	$message = "The URL:".$url." returned an HTTP code of:".$code;
    	$message = wordwrap($message, 70, "\r\n");
    	mail($this->emails, $subject, $message);

    }


    private function check($url) {
    	curl_setopt( $this->ch, CURLOPT_URL, $url );
    	$content = curl_exec( $this->ch );
    	$info = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    	if (!__QUIET__) {
    		print "\ncheck ".$url."\nresponse:".$info;
    		print "\n";
    	}
    	if ($info != "200") {
    		$this->report($url,$info);
    	}

    }

    private function readURLlist() {
    	$this->urls = file(__URLFILE__);
    }

    private function readEmaillist() {
    	$emails = file(__EMAILFILE__);
    	foreach ($emails as $key => $value) {
    		$emails[$key] = trim($value);
    	}
    	$this->emails = implode(",", $emails);
    }
}




