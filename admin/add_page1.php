<?php
	session_start();
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
				$tmp_xml = set_xml_attribs1( $ed );
				if ( $tmp_xml != '' ) {
					$ed = $tmp_xml;
				}
			}
	}
	

	function seo_friendly_url($string){
			$string = str_replace(array('[\', \']'), '', $string);
			$string = preg_replace('/\[.*\]/U', '', $string);
			$string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
			$string = htmlentities($string, ENT_COMPAT, 'utf-8');
			$string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string );
			$string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/') , '-', $string);
			return strtolower(trim($string, '-'));
	}

	function stripAccents($stripAccents){
		return strtr($stripAccents,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
	}
	
	function chk_post_name ( $t ) {
		$db = new DATABASE();
		$db_connect = $db->connect();
		$result = $db->select('posts','*', 'title="'.$t.'"'  );
		$u = $db->getResult();
		if ( isset($u) == 1 )  { return true; } else {return false; }
	}
	
	function set_xml_attribs1( $ed )
	{
		try {
			// resize images...
			$doc = new DOMDocument();						// dom class.
			libxml_use_internal_errors(true);				// supresss errors from unknow tags.
			@$doc->loadHTML(  $ed  );						// use DOMDocument to load xml representation of page.
			libxml_use_internal_errors(false);			// turn on supression.
			$xml = simplexml_import_dom($doc);
			$page_images		= 	$xml->xpath('//img');
			// loop round <img> tags.
			foreach( $page_images  as $node) {
				$check_attr = $node->attributes();
				if (!isset($check_attr['width']))  {  $node->addAttribute("width" , "100" );  } else {  $node->attributes()->width="100";   }
				if (!isset($check_attr['height'])) {  $node->addAttribute("height" , "100" ); } else {  $node->attributes()->height="100";  }
			}
			$output = $xml->asXML();
			$ed = $output;
			
			return $ed;
		}
		catch (Exception $e) { return ''; }
	}

	function set_xml_attribs2( $p , $t ) {
		try {
			// insert custom class into images and links.
			// e.g. an image:
			// <img src="about.png" />
			// will have a class added based on the post name : so if the user enters a post
			// name of "my boat" it will add a class to image tag called: "class_image_my_boat"
			// e.g. so the above becomes:
			// <img src="about.png" class="class_image_my_boat" />
			//
			$doc = new DOMDocument();					// dom class.
			libxml_use_internal_errors(true);			// supresss errors from unknow tags.
			@$doc->loadHTML(  $p  );					// use DOMDocument to load xml representation of page.
			libxml_use_internal_errors(false);			// turn on supression.
			$xml = simplexml_import_dom($doc);
			$page_links			= 	$xml->xpath('//a');
			$page_images		= 	$xml->xpath('//img');
			// loop round <a> links.
			foreach( $page_links  as $node) {
					$check_attr = $node->attributes();
					// check if the element has a class tag - if so leave it as it is.
					if (!isset($check_attr['class'])) {
							$a_class = "class_a_post class_a_".$t;
							$node->addAttribute("class"  ,  $a_class );
					}
			}
			// loop round <img> tags.
			foreach( $page_images  as $node) {
					$check_attr = $node->attributes();
					// ignore if it has a class already.
					if (!isset($check_attr['class'])) {
							$img_class = "class_img_post class_img_".$t;
							$node->addAttribute("class" , $img_class );
					}
			}
			$output = $xml->asXML();
			$p = $output;
			
			return $p;
		}
		catch ( Exception $e ) { return ''; }	
	}
	
	
	// New Page submitted so add to the database...
	if (isset($_POST['OK'])) {
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
					
					// find all occurances of width="100" and height="100" and remove them.
					// when a image is inserted in get_image it is given the size 150x150 as 
					// it was found large images took up too much space in the editor.
					if ($error=='') {
						$p = ucwords(str_replace( 'width="100" height="100"' , "" ,  $p ) );

						$tmp_xml = set_xml_attribs2 ( $p , $t );
						if ( $tmp_xml != '' ) { $p = $tmp_xml; }

						if (isset($_SESSION['loggedin'])) { $u=$_SESSION['loggedin']; } else {  $u = 0;  }
						$c = $_POST['catsel'];
						if ( $c == '' ) { $cat=1; } else { $cat = $c; }
						$p = html_entity_decode (  $p  , ENT_COMPAT, 'UTF-8');
						if ( isset($id)) {
							// ID was passed in so do update...
							$v = array(  $u     ,  "'".$t."'"  ,  "'".$p."'"    ,   $cat          );
							$f = array(  "user" ,  "title" ,  "post" ,   "category"  );
							$e = $db->update( "posts"  ,  $f , $v , ' id ='.$id );
						} else {
							$v = array(  $u     ,  $t  ,  $p  ,   $cat   );
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
												tinyMCE.activeEditor.selection.setNode( tinyMCE.activeEditor.dom.create("img", {src : data, title : "some title", height : "100", width : "100" } ) );
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
								plugins: [
									"advlist autolink lists link image charmap print preview hr anchor pagebreak",
									"searchreplace wordcount visualblocks visualchars code fullscreen",
									"insertdatetime media nonbreaking save table contextmenu directionality",
									"emoticons template paste textcolor colorpicker textpattern imagetools"
								],
								toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
								toolbar2: "print preview media | forecolor backcolor emoticons",
								image_advtab: true
							});
							</script>
						</head>
						<body>
						<?php //if ( isset($sHeader)) {  echo $sHeader;  }  ?>						
						<?php
							// get list of categories....
							$list_cat = '';
							$result = $db->select('categories' );
							$c = $db->getResult();
							if (!empty($c)) {
								if ( $result == 1 )  {
									if (isset($c)) {
										// got some categories....
										$list_cat= '<select id="catsel" name="catsel">';
										foreach ( $c as $cat )
										{
											$list_cat .= '<option value="'.$cat['id'].'">'.$cat['CATEGORY'].'</option>';
										}
										$list_cat .= '</select>';
									} else { $list_cat.= ''; }
								}
							}
							// create the form...
							try {
								if (isset($ti)) { $pn=$ti; } else { if ( isset($_POST['post_name'])) { $pn=$_POST['post_name']; } else {$pn='';} }
								if (isset($ed)) { $el=$ed; } else { if ( isset($_POST['elsm1'])) { $el=$_POST['elm1']; } else {$elm1='';} }

								echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" >';
								echo '<label>PAGE TITLE :</label><input type="text" value="'.$pn.'" id="post_name" name="post_name" maxlength="35" REQUIRED /><br /><br />';
								echo '<label>Add Image to Page:</label><input type="file" name="userImage" id="userImage" /><input type="button" value="Insert Image in Page"  onclick="javascript:get_image(); return false;" /><br /><br />';
								echo '<label>SLECT CATEGORY :</label>'.$list_cat;
								if (isset($id)) {
									echo '<input type="hidden" value ="'.$id.'" />';
								}
								echo '<label>MAIN CONTENT :</label><textarea rows="12" cols="30" value="'.$el.'" id="elm1" name="elm1"></textarea>';
								echo '<input type="submit" id="OK" name="OK" value="OK"  />';
								echo '<input type="button" name="cancel" value="CANCEL" onclick="history.go(-1);"   />';
								echo '</form>';
							}
							catch (Exception $e ) { echo $e->getMessage(); }
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
			</div>
			
		</div> <!-- END OF CONTENT -->
</div> <!-- END OF CONTAINER DIV -->


</body>
</html>
