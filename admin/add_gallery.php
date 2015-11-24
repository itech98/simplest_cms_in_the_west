<?php
	if (!isset($_SESSION)) {
	session_start(); }
	// include the database class.
	include_once('../inc/class_database.php');
	include_once('../inc/class_general.php');
	$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$u = explode("/", $url);
	if (isset($_SESSION['loggedin']) && (isset($_SESSION['loguser']) ) )  {
		if  ( $_SESSION['loggedin']==1 ) {
			$logged_in=true;
		} else {$logged_in=false; }
	} else { $logged_in=false; }
	if ( $logged_in==false) { $url =  config::get_path_url( "url_no_file" );  header( 'Location:http://'.$url.'/login.php' ) ;  }
	$db = new DATABASE();
	$db_connect = $db->connect();

	// edit existing page...
	$ti='';
	$ed='';
	if ( (isset($_GET['id'])) || ( isset($_POST['id'])) ) {
			$id = ( isset($_GET['id'])   ? $_GET['id']  : $_POST['id'] );
			$result  =  $db->select('posts t1 INNER JOIN categories t2 ON t1.category = t2.id ' , 	't1.id , t1.title , t1.post,t1.date_of_post, t2.CATEGORY', ' t1.id='.$id );
			$pages   =  $db->getResult();
			if ( $result == 1 )  {
				if (isset($pages)) {
					foreach ( $pages as $page )  { $id = $page['id']; $ti=$page['title']; $ed=$page['post']; }
				}
			}
	}

	
	function chk_post_name ( $t ) {
		$db = new DATABASE();
		$db_connect = $db->connect();
		$result = $db->select('posts','*', 'title="'.$t.'"'  );
		$u = $db->getResult();
		if ( isset($u) == 1 )  { return true; } else {return false; }
	}
	
	
	// New Page submitted so add to the database...
//print_r($_POST);
	if (isset($_POST['submit'])) {
			$out='';
			if ( ( $_POST['elm1']!='' ) && ( $_POST['post_name']!='' ) ) {
					$t = strip_tags(stripslashes( $_POST['post_name']));
					$t = ucwords(str_replace(" ","-",$t ) );
					$t = str_replace("'","''",$t );
					$t = seo_friendly_url ($t);
					$t = strtolower($t);
					$p = $_POST['elm1'];
					$p = stripAccents(  $p  );
					$p = str_replace("'","''",$p );
					$p = str_replace('\"','"',$p );

					$chk = chk_post_name ( $t );
					if ( $chk == true ) { $error="Post Name:".$t." already exists"; }

					if ( $error == '' ) {
						// find all occurances of width="100" and height="100" and remove them.
						// when a image is inserted in get_image it is given the size 150x150 as 
						// it was found large images took up too much space in the editor.
						$p = ucwords(str_replace( 'width="100" height="100"' , "" ,  $p ) );
						// get all img tags....
						$u = 0;
						$c = 1; 	// CAT: GALLERY.
						// write OR update a Gallery entry....
						if ( isset($id)) {
							// ID was passed in so do update...
							$v = array(  $u     ,  "'".$t."'"  ,  "'".$p."'"    ,  "2"          );
							$f = array(  "user" ,  "title" ,  "post" ,   "category"  );
							$e = $db->update( "posts"  ,  $f , $v , ' id ='.$id );
						} else {
							$v = array(  $u     ,  $t  ,  $p ,   "2"   );
							$f = array(  "user" ,  "title" ,  "post" ,   "category"  );
							$e = $db->insert( "posts"  ,  $v , $f       );
						}
						header( 'Location:index.php' ) ; 
					}
			} else {
					$error='NO TITLE OR CONTENT ENTERED!!';
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
					<h2>ADD NEW PAGE</h2>
						<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
						<?php
							$root            =	config::get_options( "webroot" );
							$path_to_tinymce =  $root . "inc/tinymce/tinymce.min.js";
							echo '<script language="javascript" type="text/javascript" src="http://'.$path_to_tinymce.'"></script>';
						?>
						<script language="javascript" type="text/javascript">
							function get_image() {
									var data = new FormData()
									data.append( 'userImage', $('#userImage')[0].files[0] );
									$.ajax({
										url: "upload.php",
										type: "POST",
										data:  data,
										contentType: false,
										cache: false,
										processData:false,
										success: function(data)
										{
												//bookmark = tinyMCE.selection.getBookmark(0);
												tinyMCE.activeEditor.selection.setNode( tinyMCE.activeEditor.dom.create('img', {src : data, title : 'some title', height : "100", width : "100" } ) );
												//tinyMCE.selection.moveToBookmark(bookmark);
										},
										error: function() 
										{
											
										} 	        
								   });
							}
						</script>
						<script language="javascript" type="text/javascript">						
						  tinymce.init({
								selector            : "textarea",
								relative_urls       : false,
								remove_script_host  : false,
								convert_urls        : true,
								entity_encoding		: "raw",
								force_br_newlines 	: true,
								force_p_newlines 	: false,
								forced_root_block	: false,
								theme               : "modern",   
								/*plugins: [
									"advlist autolink lists link image charmap print preview hr anchor pagebreak",
									"searchreplace wordcount visualblocks visualchars code fullscreen",
									"insertdatetime media nonbreaking save table contextmenu directionality",
									"emoticons template paste textcolor colorpicker textpattern imagetools"
								],
								toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
								toolbar2: "print preview media | forecolor backcolor emoticons",*/
								image_advtab: true
							});
							</script>
						</head>
						<body>
						<?php if ( isset($sHeader)) {  echo $sHeader;  }  ?>						
						<?php
							// get list of categories....
							$list_cat = '';
							$result = $db->select('categories' );
							$c = $db->getResult();
							if ( $result == 1 )  {
								if (isset($c)) {
									// got some categories....
									$list_cat= array();
									foreach ( $c as $cat )
									{
										$list_cat[ $cat['id'] ] = $cat['CATEGORY'];
									}
								} else { $list_cat.= ''; }
							}
							// create the form...
							try {
								if (isset($ti)) { $pn=$ti; } else { if ( isset($_POST['post_name'])) { $pn=$_POST['post_name']; } else {$pn='';} }
								if (isset($ed)) { $el=$ed; } else { if ( isset($_POST['elsm1'])) { $el=$_POST['elm1']; } else {$elm1='';} }
								
								echo '<form method="post">';
								echo '<legend>ADD GALLERY</legend>';
								echo '<label>GALLERY TITLE :</label><input  type="text" value="'.$pn.'" id="post_name" name="post_name" REQUIRED /><br /><br />';
								echo '<label>ADD IMAGE TO FILE: </label><input  type="file" id="userImage" name="userImage" /><br /><br />';
								echo '<input  type="button" value="Insert Image in Page"  onclick="get_image(); return false;" />';
								echo '<label>MAIN CONTENT :</label><textarea rows="12" cols="30" value="'.$el.'" id="elm1" name="elm1"></textarea>';
								echo '<input type="submit" name="submit" value="OK"  />';
								echo '<input type="button" name="cancel" value="CANCEL" onclick="history.go(-1);"   />';
								echo '</form>';
							}
							catch (Exception $e) { echo $e->getMessage(); }
							?>
				</div> <!-- END OF POST DIV -->
			</div> <!-- END OF MAIN DIV -->
		
			<div id="secondary">
				<h2>MENU</h2>
					<a href="add_page1.php">Add Page</a><br />
					<a href="categories.php">Categories</a><br />
					<a href="settings.php">Settings</a><br />
					<a href="add_gallery.php">Add Image Gallery</a><br />
					<a href="logout.php">Logout</a><br />

				<h2>SETUP</h2>
				<p>Initial setup of the CMS requires entry of some basic info - and email address which can be
				any valid email address you use - e.g. Hotmail or Yahoo or gmail. a password to get into the 
				admin area and a name for your site.</p>
			</div>
			
		</div> <!-- END OF CONTENT -->
</div> <!-- END OF CONTAINER DIV -->


</body>
</html>
