<?php
	session_start();
	include_once('../inc/class_general.php');
	include_once('../inc/class_database.php');	//
	include_once('../inc/class_startup.php');
	$db = new DATABASE();
	$db_connect = $db->connect();
	if ( $db_connect == false ) { 
			die('CANNOT CONNECT TO DATABASE WITH THE CREDENTIALS SUPPLIED...Please check :  inc/class_database.php for connection details to SQLITE or MYSQL !!');
	}
	// check if tables setup....
	if (isset($_SESSION['loggedin']) && (isset($_SESSION['loguser']) ) && ( $_SESSION['loggedin']==1 ) ) { } else
	{
		$tables_exist = $db->table_exists("users");
		if (!empty ( $tables_exist )) { die('CMS IS ALREADY INSTALLED!!!'); }
	}
	$user='';
	$bname='';
	$email='';
	// setup form submitted - so set installed=0;  give admin new password.;
	if ( isset( $_POST['submit'])) {
		function valid_email($e) {
 			return ( filter_var(  $e , FILTER_VALIDATE_EMAIL ));
		}
		$error='';
		if (isset($_POST['user']))	{ $user = $_POST['user']; } else { $user='';   } 
		if (isset($_POST['email']))	{ $email=$_POST['email']; } else { $email='';  }
		if (isset($_POST['bname']))	{ $bname=$_POST['bname']; } else { $bname='';  }
		if (isset($_POST['pass1'])) { $pass1=$_POST['pass1']; } else { $pass1='';  }
		if (isset($_POST['pass2'])) { $pass2=$_POST['pass2']; } else { $pass2='';  } 
		
		if  (   trim($user) == "" )												{  $error .= '<br />' . 'USER MUST BE ENTERED!';  }
		if  ( ( trim($email) == "" ) || ( valid_email($email) ) == false )		{  $error .= '<br />' . 'EMAIL MUST BE VALID AND ENTERED!';  }
		if  (  trim($bname) == "" )												{  $error .= '<br />' . 'BLOG NAME MUST BE ENTERED!';  }
		if  (  trim($pass1) == "" )  											{  $error .= '<br />' . 'PASSWORD MUST BE ENTERED!';  }
		if  (  trim($pass2) == "" )  											{  $error .= '<br />' . 'CONFIRMATION MUST BE ENTERED!';  }
		if  (  trim($pass1) != trim($pass2) ) 									{  $error .= '<br />' . 'PASSWORD AND PASSWORD CONFIRMATION MUST BE THE SAME'; }
		// no form errors so continue...
		if ( $error == '' ) {
						// connect to Db....
						// create new tables....
						$s= new StartUp( $db->r );

						// insert values into database.
						$t = array(  "email" , $email  );
						$f = array(  "opt" , "value" );
						$e = $db->insert( "options"  ,  $t , $f );		
						$t = array(  "blog_name", $bname  );
						$f = array(  "opt" , "value" );
						$e = $db->insert( "options"  ,  $t , $f );
						// remove admin...
						$r  =  $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
						$r  =  str_replace(  "admin" , ''  , $r );

						$t = array(  "webroot", $r  );
						$f = array(  "opt" , "value" );
						$e = $db->insert( "options"  ,  $t , $f );

						
						// create default USER in USERS table.
						$p =  md5( $pass1 );							// password
						$t = array( "1" , $user , $p  );
						$f = array( "id" , "user" , "password" );
						$db->insert( "users"  ,  $t , $f );

						
						// SET OPTIONS installed = 1.
						// Check OPTIONS installed == 0 -> not entered email/blog name ?
						$table="options";
						$fields=array('value');
						$values=array('1');
						$id="opt='installed'";
						$result = $db->update( $table , $fields , $values , $id );
						
						header('location:login.php');
		}
	}
?>
<html>
<head>
<title>SIMPLEST CMS IN THE WEST</title>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=iso-8859-1" />
<meta name="description" content="Your website description goes here" />
<meta name="keywords" content="your,keywords,goes,here" />
<link rel="stylesheet" href="style.css" type="text/css" />
</head>

<body>
<div id="container" >
		<div id="headerWrap">
			<div id="header">
				<h1><a href="index.html">SIMPLEST CMS IN THE WEST</a></h1>
				<ul id="navigation">
					<li><a href="#">ABOUT</a></li>
					<li><a href="add_page1.php">ADD PAGE</a></li>
					<li><a href="categories.php">CATEGORIES</a></li>
					<li><a href="settings.php">SETTINGS</a></li>
				</ul>
			</div>
		</div>
		<div id="content">
			<a href="index.php"><div id="contentHeader">
				<div id="siteDescription"><p>SIMPLEST CMS IN THE WEST !!!</p></div>
			</div></a>
			<?php
			if (isset($error)) { echo '<h3 style="color:#f30b21;"><color ="red">'.$error.'</h3>'; }
			?>
		
			<div id="main">
				<div class="post">
					<h2>SETUP YOUR CMS</h2>
					<?php
						try {
								if (isset($_POST['user']))		{ $u=$_POST['user'];}   else {$u=''; } 
								if (isset($_POST['email']))		{ $e=$_POST['email'];}  else {$e=''; }
								if (isset($_POST['bname']))		{ $b=$_POST['bname'];}  else {$b=''; }
								if (isset($_POST['_host']))		{ $h=$_POST['_host'];}  else {$h=''; }
								if (isset($_POST['_db'])) 		{ $d=$_POST['_db'];}    else {$d=''; }
								if (isset($_POST['_user'])) 	{ $_u=$_POST['_user'];} else {$_u='';}

								echo '<form method="post">';
								echo '<legend>SETUP SITE</legend>';
								echo '<label>ENTER ADMIN USER NAME:</label><input  type="text" id="user" value="'.$u.'" name="user" maxlength="35" REQUIRED /><br /><br />';
								echo '<label>ENTER BLOG NAME  :</label><input   type="text" id="bname" value="'.$b.'" maxlength="40" name="bname" REQUIRED /><br /><br />';
								echo '<label>ENTER EMAIL ADDRESS :</label><input    type="text" id="email" value="'.$e.'" maxlength="80" name="email" REQUIRED /><br /><br />';
								echo '<label>ENTER PASSWORD  :</label><input   type="password" id="pass1" name="pass1" REQUIRED /><br /><br />';
								echo '<label>PLEASE CONFIRM PASSWORD :</label><input   type="password" id="pass2" name="pass2" REQUIRED /><br /><br />';
								echo '<input type="submit" name="submit" value="OK"  />';
								echo '</form>';
							}
						catch (PDOException $e) 
						{
							// error...
						
						}
						?>
				</div>
			</div>
		
		
		
			<div id="secondary">
				<h2>SETUP</h2>
				<p>Initial setup of the CMS requires entry of some basic info - and email address which can be
				any valid email address you use - e.g. Hotmail or Yahoo or gmail. a password to get into the 
				admin area and a name for your site.</p>
			</div>
		</div>
</div>


</body>
</html>
