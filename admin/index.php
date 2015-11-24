<?php
	if (!isset($_SESSION)) {
	session_start(); }
	include_once('../inc/class_general.php');
	include_once('../inc/class_database.php');
	if (isset($_SESSION['loggedin']) && (isset($_SESSION['loguser']) ) )  {
		if  (  ( $_SESSION['loggedin']==1 ) || (  $_SESSION['loguser']=='admin') ) {
			$logged_in=true;
		} else {$logged_in=false; }
	} else { $logged_in=false; }
	if ( $logged_in==false) { $url =  config::get_path_url( "url_no_file" );  header( 'Location:http://'.$url.'/login.php' ) ;  }
	// include the database class.
	$db = new DATABASE();
	$db_connect = $db->connect();
	// main tables present ?
	$tables_exist = $db->table_exists("users");
	if ( empty ( $tables_exist )) {				// no missing...
			header( 'Location: setup.php');
	}

	// check for options set....
	if ( isset($_GET['option']) && isset($_GET['id']) ) {
		if ( $_GET['option'] == 1 ) {
				$id=$_GET['id'];	// id to delete.
				// delete post...
				$e = $db->delete( "posts"  , " id = " . $id );
				header( 'Location: index.php');
		}
	}

	
	// get all posts....
		$out='';
	if (isset($_GET['searchtext'])) {
			$stxt = $_GET['searchtext'];
			$result  =  $db->select('posts t1 LEFT JOIN categories t2 ON t1.category = t2.id  WHERE t1.title LIKE "%'.$stxt.'%" OR t1.post LIKE "%'.$stxt.'%" ' , 	't1.id , t1.title , t1.date_of_post, t2.CATEGORY' );
	} else {
			$result  =  $db->select('posts t1 LEFT JOIN categories t2 ON t1.category = t2.id ' , 	't1.id , t1.title , t1.date_of_post, t2.CATEGORY' );
	}
	$pages   =  $db->getResult();
	if ( $result == 1 )  {
		if (isset($pages)) {
			// got some pages....
			$out=    '<table cellspacing="20">';
			$out .=  '<tr><th>ID</th><th>Title</th><th>Post</th><th>Category</th>';
			$out .=  '<th>Delete</th><th>Edit</th></tr>';
			
			foreach ( $pages as $page )
			{
					$cc		=	$page['CATEGORY'];
					$p 		= 	'...';
					if ( $cc == 'gallery' ) {
							$out .= '<tr><td>' . $page['id'] . '</td><td>' . $page['title'] . '</td><td>' . $p . '</td><td>' . $cc . '</td><td>' . $page['date_of_post'] . '</td><td><a href="index.php?option=1&id=' . $page['id'] . '">Delete</a></td><td><a href="add_gallery.php?id=' . $page['id'] . '">Edit</a></td></tr>';
					} else {
							$out .= '<tr><td>' . $page['id'] . '</td><td>' . $page['title'] . '</td><td>' . $p . '</td><td>' . $cc . '</td><td>' . $page['date_of_post'] . '</td><td><a href="index.php?option=1&id=' . $page['id'] . '">Delete</a></td><td><a href="add_page1.php?id=' . $page['id'] . '">Edit</a></td></tr>';
					}
			}
			$out .= '</table>';
		} else { $out.= '<strong>No Posts to List -- use the "Add Page" option on the left to add new pages.'; }
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
			<div id="contentHeader">
				<div id="siteDescription"><p>SIMPLEST CMS IN THE WEST !!!</p></div>
			</div>
			<?php
			if (isset($error)) { echo '<h3 style="color:#f30b21;"><color ="red">'.$error.'</h3>'; }
			?>
		
			<div id="main">
				<div class="post">
					<h2>SETUP YOUR CMS</h2>
					<?php
						try {
								echo $out;
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
				
				<h2>Search</h2>
				<form method="get" id="searchform" action="#"><fieldset>
					<input type="text" name="searchtext" id="searchtext" />
					<input type="submit" id="searchsubmit" value="search" />
				</fieldset></form>
			</div>
		</div>
</div>


</body>
</html>
