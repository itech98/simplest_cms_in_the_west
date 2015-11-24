<?php
class config {
		private static $options=array();
		private static $p;
		

			private static function generatePassword($length) {
					$lowercase = "qwertyuiopasdfghjklzxcvbnm";
					$uppercase = "ASDFGHJKLZXCVBNMQWERTYUIOP";
					$numbers = "1234567890";
					$specialcharacters = "{}[];:,./<>?_+~!@#";
					$randomCode = "";
					mt_srand(crc32(microtime()));
					$max = strlen($lowercase) - 1;
					for ($x = 0; $x < abs($length/3); $x++) {
						$randomCode .= $lowercase{mt_rand(0, $max)};
					}
					$max = strlen($uppercase) - 1;
					for ($x = 0; $x < abs($length/3); $x++) {
						$randomCode .= $uppercase{mt_rand(0, $max)};
					}
					$max = strlen($specialcharacters) - 1;
					for ($x = 0; $x < abs($length/3); $x++) {
						$randomCode .= $specialcharacters{mt_rand(0, $max)};
					}
					$max = strlen($numbers) - 1;
					for ($x = 0; $x < abs($length/3); $x++) {
						$randomCode .= $numbers{mt_rand(0, $max)};
					}
					//return str_shuffle($randomCode);
					self::$p =  str_shuffle($randomCode);
			}
			public static function new_password($l)
			{
					self::generatePassword($l);
					return self::$p;
			}


			private static function send_email ( $to , $message , $from , $subject ) 
			{
					$email_to      = $to;
					$email_subject = $subject;
					$email_message = $message;
					$server_email  = $from;
 
					// Create email headers
					$headers = "MIME-Version: 1.0" . "\r\n";
					$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
					$headers .= '"From:     info <' . $server_email . '>"' . "\r\n";
					$headers .= '"Reply-To: info <' . $server_email . '>"' . "\r\n";

					mail($email_to, $email_subject, $email_message, $headers);
			}

			
			
			public static function get_path_url( $what )
			{
				$u='';
				switch($what) {

					// e.g. http://localhost/site1/
					case "url":
							$u = "http://" . $_SERVER['SERVER_NAME'];
					break;
					
					// e.g. http://localhost/site1/report/report1.php
					case "current_file":
							$u = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
					break;
					
					// e.g. site1.com
					case "url_no_http":
							$u = $_SERVER['SERVER_NAME'];
					break;
					
					// e.g. site1.com/db/
					case "url_no_file":
							$u  =  $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
					break;

					// e.g. /home/public_html/site1/db/
					// this is path where code is called - so if you have index.php in root
					// and you include inc/file1.php which calls this you will get site/inc/
					case "file_path":
							$u = realpath(dirname(__FILE__));
					break;

					// e.g. http://site1.com/theme/default/
					case "url_theme":
							// get currently selected theme - default root is index.html._x_.
							$opts=config::get_options("theme");
							//$u  =  "http://" . $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']) . '/' . $opts . "/index.html";
							$u  =  "http://" . $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/theme/'.$opts . "/index.html";
					break;

					// e.g.  /theme/theme1/
					case "url_theme_path":
							// this maybe changed later to get theme DIR from DB...#####
							$u  =  'theme/default/';
					break;

					// e.g.  default/
					case "theme_name":
							// this maybe changed later to get theme DIR from DB...#####
							$u  =  'default/';
					break;

					case "url_theme_root":
							$opts=config::get_options("theme");
							$d = dirname($_SERVER['PHP_SELF']);
							if   ( substr( $d , -1 ) == '/') {
									$u  =  "http://" . $_SERVER['HTTP_HOST']. $d . 'theme/'.$opts.'/';
							} else {
									$u  =  "http://" . $_SERVER['HTTP_HOST']. $d . '/theme/'.$opts.'/';
							}
					break;

					// default -> empty.
					case "default":
							$u="";
					break;
				}
				
				return $u;
			}
	


			public  function get_options( $opt = '' )
			{
					$db = new DATABASE();		
					$db_connect = $db->connect();
					$result = $db->select('options','*');
					$options = $db->getResult();
					$o_ret='';
					if ( $opt =='') { $o_ret = $options; } else {
						foreach( $options as $o) {  echo 'O:'.$o['opt']; if ( strstr($opt,$o['opt']) ) { $o_ret = $o['value']; break; } else { $o_ret=''; }	}
					}
					return $o_ret;
			}


			
			public static function get_category( $id_or_cat , $ret_field='id') {
				try {
					$db = new DATABASE();		
					$db_connect = $db->connect();
					if ( $ret_field=='id') {
							$result = $db->select('categories','id','CATEGORY="'.$id_or_cat.'"');		// get ID for category.
					} else {
							$result = $db->select('categories','CATEGORY','id='.$id_or_cat);			// get CATEGORY for ID.
					}
					$cats = $db->getResult();
					$ret_val =  (  $ret_field=='id' ? $cats[0]['id'] : $cats[0]['CATEGORY'] );
					return $ret_val;
				}
				catch ( Exception $e ) {  return NULL; }
			}
			
			
///////// END OF CLASS /////////////			
}

?>