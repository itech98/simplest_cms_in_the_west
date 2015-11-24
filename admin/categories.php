<?php
session_start();
if (isset($_SESSION['loggedin']) && (isset($_SESSION['loguser']) ) )  {
	if  ( $_SESSION['loggedin']==1 ) {
		$logged_in=true;
	} else {$logged_in=false; }
} else { $logged_in=false; }
if ( $logged_in==false) { $url =  config::get_path_url( "url_no_file" );  header( 'Location:http://'.$url.'/login.php' ) ;  }
// get URL....
$option ="LIST";
if (isset($_GET['option'])) {
	$opt = $_GET['option'];
} else { $opt=0; }
include_once('../inc/class_database.php');
include_once('../inc/class_general.php');
$db = new DATABASE();
$db_connect = $db->connect();
// process options...
switch ($opt) { 
	case 0:				// DEFAULT - List all //
			$out='';
			$result = $db->select('categories' );
			$c = $db->getResult();
			if ( $result == 1 )  {
				if (isset($c)) {
					// got some categories....
					$out=    '<table cellspacing="20">';
					$out .=  '<tr><th>ID</th><th>CATEGORY</th>';
					$out .=  '<th>Delete</th><th>Edit</th></tr>';
					
					foreach ( $c as $cat )
					{
						if  ( $cat['reserved']=='1' )  {
							$out .= '<tr><td>' . $cat['id'] . '</td><td>' . $cat['CATEGORY'] . '</td></tr>';
						} else {
							$out .= '<tr><td>' . $cat['id'] . '</td><td>' . $cat['CATEGORY'] . '</td><td><a href="categories.php?option=3&id=' . $cat['id'] . '">Delete</a></td><td><a href="categories.php?option=2&id=' . $cat['id'] . '&c='.$cat['CATEGORY'] . '">Edit</a></td></tr>';
						}
					}
					$out .= '</table>';
					$out .= '<a href="categories.php?option=1">Add New Category</a>';

				} else { $out.= '<strong>No Categories Listed!</strong>'; }
			}
	break;
	case 3:				// DELETE ///
			if ( !isset($_GET['id'] ) ) { echo 'NODEL';} else {
				$id=$_GET['id'];
				// insert values into database.
				$e = $db->delete( "categories"  , " id = " . $id );		
				header( 'Location: categories.php');
			}
			$out='';
	break;				
}
	
	
// setup form submitted - so set installed=0;  give admin new password.;
if ( isset( $_POST['submit'])) {
	// Submit Form - Add new //
	if ( $_POST['submit']=='OK') {
		// Store FORM Data in OPTIONS table.
		$em = $_POST['cat1'];
		// check category entered?
		$error='';
		if  (  trim($_POST['cat1']) == "" )  {  $error .= '<br />' . 'CATEGORY MUST BE ENTERED!';  }

		if ( $error == '' ) {
				// insert values into database.
				$t = array(  "0" , $_POST['cat1'] );
				$f = array( "reserved" , "CATEGORY" );
				$e = $db->insert( "categories"  ,  $t , $f );		
		}
		header( 'Location: categories.php');
	}
	// Submit Form - Edit    //
	if ( $_POST['submit']=='Update') {
		// Store FORM Data in OPTIONS table.
		$em = $_POST['cat1'];
		// check category entered?
		$error='';
		if  (  trim($_POST['cat1']) == "" )  {  $error .= '<br />' . 'CATEGORY MUST BE ENTERED!';  }

		if ( $error == '' ) {
				$id = $_POST['ided'];
				$w=' id = '.$id;
				// update values into database.
				$t = array( '"'. $_POST['cat1'].'"' );
				$f = array( "CATEGORY" );
				$e = $db->update( "categories"  ,  $f , $t , $w );
		}
		header( 'Location: categories.php');
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
				<div id="siteDescription"><p>CATEGORIES</p></div>
			</div></a>
			<?php
			if (isset($error)) { echo '<h3 style="color:#f30b21;"><color ="red">'.$error.'</h3>'; }
			?>
		
			<div id="main">
				<div class="post">
					<h2>MANAGE CATEGORIES</h2>
					<?php 
					if (isset($out)) {
						echo $out;
					}
					
					// ADD NEW CATEGORY...
					if ($opt== 1) {
							echo '<form method="post">';
							echo '<legend>ADD NEW CATEGORY</legend>';
							echo '<label>Category  :</label><input type="text" name="cat1" id="cat1" REQUIRED /><br /><br />';
							echo '<input type="submit" name="cancel" value="CANCEL" onclick="history.go(-1);"   />';
							echo '<input type="submit" name="submit" value="OK"  />';
							echo '</form>';
					}
					if ( $opt == 2 ) {
							// EDIT Category //
							if ( ( !isset($_GET['id'] ) ) || ( !isset($_GET['c'])) ) {  } else {
									$c=$_GET['c'];
									$i=$_GET['id'];
									echo '<form method="post">';
									echo '<legend>ADD NEW CATEGORY</legend>';
									echo '<label>Category  :</label><input type="text" id="cat1" name="cat1" REQUIRED value="'.$c.'" /><br /><br />';
									echo '<input type="submit" name="submit" value="Update"  />';
									echo '<input type="submit" name="cancel" value="CANCEL" onclick="history.go(-1);"   />';
									echo '<input type="hidden" value="'.$i.'" id="ided" name="ided" />';
									echo '</form>';
							}
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
</html