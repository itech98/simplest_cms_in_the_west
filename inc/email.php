<?php
$fileName =  '../../inc/db/options.txt';
$f= file_exists($fileName);
include_once('class_database.php');
include_once('class_general.php');
$email_to = config::get_options('email');
if (  $f == true ) {
		$fp = fopen(  $fileName  , "r" );
		while ($line = fgets ( $fp ))  {
			if (strstr($line,"DEFAULTEMAIL")) {
					$email_to = $line;
			}
			fclose( $fp );
		}
}
			
//$opt = config::get_options("DEFAULTEMAIL");			// email to send to.
$name   =  $_POST['name'];							// persons name
$phone  =  $_POST['phone'];							// persons phone
$email  =  $_POST['email'];							// persons email
$enq    =  $_POST['enq'];							// persons enquiry

$email_message = "Form details below.\n\n";
function clean_string($string) {
	$bad = array("content-type","bcc:","to:","cc:","href");
	return str_replace($bad,"",$string);
}
$email_message .= "Name:     ".clean_string( $name  )."\n";
$email_message .= "Phone:    ".clean_string( $phone )."\n";
$email_message .= "Email:    ".clean_string( $email )."\n";
$email_message .= "Comments: ".clean_string( $enq   )."\n";
$headers = 'From: '.$email."\r\n".
'Reply-To: '.$email."\r\n" .
'X-Mailer: PHP/' . phpversion();
@mail( $email_to, $email_subject, $email_message, $headers);   
echo "Thank You for contacting us -- we will reply shortly!!";
?>
