<?php
/*
 * debug_message.php
 *
 *
 *
 */


class debug_message_class extends email_message_class
{

	private $logfile ="";

	function __construct() {
		$this->logfile = $GLOBALS['TMP_PATH'] . '/studip-mail-debug.log';
	}
	
	function SendMail($to,$subject,$body,$headers,$return_path) {
		if ($log = fopen($this->logfile, "a")){
			if(strlen($headers)) $headers.="\n";
			fwrite($log, "\n-- " . strftime("%x %X"). ' ' . $GLOBALS['auth']->auth['uname']);
			fwrite($log, "\nTo: ".$to."\nSubject: ".$subject."\n".$headers."\n");
			fwrite($log,$body."\n");
			fclose($log);
		}
	}
}
?>