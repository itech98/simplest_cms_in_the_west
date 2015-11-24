<?php
	session_start();
//	include_once('../inc/class_sessions.php');
	// include the database class.
	include_once('../inc/class_database.php');
	include_once('../inc/class_general.php');
	$db = new DATABASE();
	$db_connect = $db->connect();
	// main tables present ?
	$tables_exist = $db->table_exists("users");
	if ( empty ( $tables_exist )) {				// no missing...
			header( 'Location: setup.php');
	}

	// setup form submitted - so set installed=0;  give admin new password.;
	if ( isset( $_POST['submit'])) {
			$error='';
			if (!empty($_REQUEST['captcha'])) {
				if (empty($_SESSION['captcha']) || trim(strtolower($_REQUEST['captcha'])) != $_SESSION['captcha']) {
						$error = "Invalid captcha";
				}
				$request_captcha = htmlspecialchars($_REQUEST['captcha']);
				unset($_SESSION['captcha']);
			} else { $error = "Captcha cannot be empty!!"; }

				
			$u = $_POST['user'];
			$p1 = $_POST['pass1'];
			// check EMAIL entered?
			if  (  trim($_POST['user'])  == "" )  {  $error .= '<br />' . 'USER MUST BE ENTERED!';  }
			if  (  trim($_POST['pass1']) == "" )  {  $error .= '<br />' . 'PASSWORD MUST BE ENTERED!';  }
			if ( $error == '' ) {
					// check user USER + PASSWORD....
					$pp=md5($p1);
					$result = $db->select('users','*', 'user="'.$u.'" AND  password = "'. md5($p1) . '"'  );
					$u = $db->getResult();
					if ( $result == 1 )  {
						if (isset($u)) {
								// get the current URL - url_no_file /site.com/ not the executing script /site.com/login.php
								$url =  config::get_path_url( "url_no_file" );
								// set session...
								$_SESSION['loggedin']=$u[0]['id'];
								$_SESSION['loguser']=$u[0]['user'];
								header( 'Location:http://'.$url.'/index.php' ) ; 
						}
						else 
						{ 
							$error='INVALID USER + PASSWORD'; 
						}
					}
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
<link rel="stylesheet" href="GOOGLEFORMS/prettify/prettify.css"  type="text/css" />
</head>

<body>
<div id="container" >
		<div id="headerWrap">
			<div id="header">
				<h1><a href="index.html">SIMPLEST CMS IN THE WEST</a></h1>
				<ul id="navigation">
					<li><a href="#">ABOUT</a></li>
					<li><a href="#">ADD PAGE</a></li>
					<li><a href="#">CATEGORIES</a></li>
					<li><a href="#">SETTINGS</a></li>
				</ul>
			</div>
		</div>
		<div id="content">
			<div id="contentHeader">
				<div id="siteDescription"><p>SIMPLEST CMS IN THE WEST !!!</p></div>
			</div>
			<?php
			if (isset($error)) { echo '<h3 style="color:#f30b21;"><color ="red">'.$error.'</h3>'; }
			?>
		
			<div id="main">
<!--				<div class="post">-->
					<h2>LOGIN TO YOUR CMS</h2>
					<?php
						try {
								echo '<form method="post">';
								echo '<label>User ID  :</label><input type="text" placeholder="enter admin id - not email" id="user" name="user" />';
								echo '<label>Password :</label><input type="password" placeholder="password entered when in setup" id="pass1" name="pass1" />';
								echo '<img src="cool-captcha/captcha.php" id="captcha" /><br/>';
								echo '<input type="text" name="captcha" id="captcha-form" placeholder="type text from image above" autocomplete="off" /><br/><br/>';
								echo '<input type="submit" name="submit" value="OK"  />';
								echo '</form>';

								}
						catch (PDOException $e) 
						{
							// error...
							$e->getMessage().''.$e->getTraceAsString();

						}
						?>
<!--				</div>-->
			</div>
		
		
		
			<div id="secondary">
				<h2>SETUP</h2>
				<p>Initial setup of the CMS requires entry of some basic info - and email address which can be
				any valid email address you use - e.g. Hotmail or Yahoo or gmail. a password to get into the 
				admin area and a name for your site.</p>
				<h2>Search</h2>
				<form method="get" id="searchform" action="#"><fieldset>
					<input type="text" name="s" id="searchtext" />
					<input type="submit" id="searchsubmit" value="search" />
				</fieldset></form>
			</div>
		</div>
</div>


</body>
</html>
