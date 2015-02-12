<?php

$sd = realpath(dirname(__FILE__)); // where the script and config files live
define("__URLFILE__",$sd."/urls.txt");
define("__EMAILFILE__",$sd."/emails.txt");
define("__QUIET__", false);// turn to true to stop terminal output
define("__AGENT__","Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
define("__TIMEOUT__",15);


new checksite();

class checksite {
	
	function __construct(){
		if (!__QUIET__) $this->termcol = @exec('tput -T xterm-color cols');
		$this->log("Beginning test of URLs");
		$this->log("","=");
		$this->readURLlist();
		$this->readEmaillist();
		$this->initcurl();
		$this->go();
		$this->log("End of test of URLs");
	}

	public function go() {
		foreach ($this->urls as $key => $value) {
			$this->check(trim($value));
		}
	}

	public function log($msg="",$pad=" ") {
		if (!$this->termcol or $this->termcol > 80) {
			$this->termcol = 80;//max width
		}
		$width = $this->termcol-3;// width from terminal output
		if (strlen($msg)> $width) {
			$mul = ceil(strlen($msg)/$width);
			$width = $this->termcol*$mul-3;
		}
		$msg = str_pad($msg,$width ,$pad);
		$msg = "\n|".$pad.$msg."|";
		if (!__QUIET__) {
			print $msg;
		}
	}

	private function initcurl() {
		stream_context_set_default(array('http' => array('method' => 'HEAD')));
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
    	$this->log("Error accessing:".$url." emailing admin");
    	$subject = "Problem accessing".$url;
    	$message = "The URL: ".$url." returned an HTTP code of:".$code;
    	$message = wordwrap($message, 70, "\r\n");
    	mail($this->emails, $subject, $message);

    }

    private function check($url) {
    	curl_setopt( $this->ch, CURLOPT_URL, $url );
    	$content = curl_exec( $this->ch );
    	$info = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    	$this->log(" check ".$url);
    	$this->log("  response:".$info);
    	$this->log();
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

    public function __destruct() {
       if (!__QUIET__) print "\n\n";
   }
}




