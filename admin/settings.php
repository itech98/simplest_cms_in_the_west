<?php
include_once('../inc/class_database.php');
include_once('../inc/class_general.php');
session_start();
$db = new DATABASE();
if (isset($_SESSION['loggedin']) && (isset($_SESSION['loguser']) ) )  {
	if  (  ( $_SESSION['loggedin']==1 ) || (  $_SESSION['loguser']=='admin') ) {
		$logged_in=true;
	} else {$logged_in=false; }
} else { $logged_in=false; }
if ( $logged_in==false) { $url =  config::get_path_url( "url_no_file" );  header( 'Location:http://'.$url.'/login.php' ) ;  }
$db_connect = $db->connect();
if ( isset( $_POST['submit'])) {
	// Submit Form - Add new //
	if ( $_POST['submit']=='SUBMIT') {

				// CHANGE THEME.
				if (isset($_POST['selDir'])) {
					$t = $_POST['selDir'];
					$w=' opt  = "theme"';
					$t1 = array( '"'. $t.'"' );
					$f = array( "value" );
					$e = $db->update( "options"  ,  $f , $t1 , $w );
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
				<div id="siteDescription"><p>SETTINGS</p></div>
			</div></a>
			<?php
			if (isset($error)) { echo '<h3 style="color:#f30b21;"><color ="red">'.$error.'</h3>'; }
			?>
		
			<div id="main">
				<div class="post">
					<h2>SETTINGS</h2>
					<?php
						try {
								// get list of directories in theme/ directory for list of themese....
								// get current theme...
								$result = $db->select('options','*');
								//, 'option = "theme"'   );
								$t = $db->getResult();
								$current='';
								foreach($t as $s) { if ( $s['opt']=="theme" ) { $current=$s['value']; }  }
								
								echo '<form method="post" >';
										echo '<fieldset>';
										echo '<strong>Current Theme:</strong> ' . $current.'<br /><br />';
							
										echo '<strong>Select New Theme: </strong>';
										$dir=scandir('../theme/');
										$out1 = '<select id="selDir" name="selDir" />';
										foreach( $dir as $d ) {
												if ( strstr($current,$d)) {
														if ( $d=='.' || $d=='..' ) { } else { $out1 .= '<option selected value="'.$d.'">'.$d.'</option>'; }
												} else {
														if ( $d=='.' || $d=='..' ) { } else { $out1 .= '<option value="'.$d.'">'.$d.'</option>'; }
												}
										}
										$out1.='</select>';
										echo $out1;
																
								echo '<label class="formlabel"><input type="submit"  class="button"  value="SUBMIT" name="submit" id="submit" /></label>';
								echo '<input type="hidden" value="submit" />';
								echo '</fieldset>';
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
				<h2>MENU</h2>
					<a href="add_page1.php">Add Page</a><br />
					<a href="categories.php">Categories</a><br />
					<a href="settings.php">Settings</a><br />
					<a href="add_gallery.php">Add Image Gallery</a><br />
					<a href="logout.php">Logout</a><br />
			</div>
		</div>
</div>


</body>
</html>

