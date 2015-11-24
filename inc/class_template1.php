<?php
	/**
	 * Simple template engine class (use [@tag] tags in your templates).
	 * 
	 * @link http://www.broculos.net/ Broculos.net Programming Tutorials
	 * @author Nuno Freitas <nunofreitas@gmail.com>
	 * @version 1.0
	 */
    class theme {
        protected $file;		    	//  filename of the template to load.
		private $db;					//  database ref.
        protected $values = array();
        private $url;
		private $main_url;
        private $themef;
		private $tpath;
        private $theme_path;
        private $theme_root;
		private $theme_file;
		private $root;
		private $is_this_a_page;
		private $no_page = false;
		public  $webpage;
		const   page_extension = "._x_";
		const   tag_surround_content = "p";	// default tag around retrieved data - e.g. title-> <p>title</p>
		

        public function __construct() {
				// load required classes.
				include_once('class_general.php');
				include_once('class_database.php');
				// new instance of database.
				$this->db = new DATABASE();
				$db_connect = $this->db->connect();

				// get theme paths.
				$this->theme_path   =   config::get_path_url("url_theme_path");
				$this->theme_root   =   config::get_path_url("url_theme_root");
				$u 					=   config::get_path_url("url_theme");
				$this->tpath		=   config::get_path_url("current_file");
				$this->root			=	config::get_options( "webroot" );
				$this->themef		=	config::get_options( "theme" );
				$this->theme_file	= 	config::get_path_url("file_path");
				$this->url = $u;
				$file = $this->url;
				$this->file = $file;			
        }

        

        public function output_curl() {
			$this->no_page = false;
			$this->get_web_page();

			if ( ( trim($this->webpage) == "" ) || ( $this->no_page == true ) ) 
			{ 
					// page not found ... make up a pseudo 404 page...
					$name 			= basename( $this->tpath );
					$html  = '';
					$html .= '<html><head></head><body><h1>SORRY PAGE NOT FOUND !!! 404 ERROR</h1>';
					$html .= '<h3>The webpage : ' . $name . ' cannot be found !!!</h3>';
					$html .= '<h3>Please check your theme to make sure the file is there!!!</h3>';
					$html .= '<input action="action" type="button" value="Back" onclick="history.go(-1);" />';
					$html .= '</body></html>';
					$this->webpage = $html;
			}

			// Process returned webpage. get css and js , images, tags <post><category><contact><googlemap>....
			if ( ( $this->webpage != "" ) && ( $this->no_page ==false ) ) { $this->process_elements();  }
			//if   ( $this->webpage != "" )  { $this->process_elements();  }
		}

		
		
		function get_web_page_contents() {
			$url = $this->file;
			$url=$this->file;
			// check the page (URL) is there....
			if (  $this->check_url($url) == true ) {
					//$r = @file_get_contents($url);
					$r = json_decode(file_get_contents( $url ));
			} else { $r=''; }
			return $r;
		}


		
	   function get_web_page()
		{
			$url = $this->file;
			$this->is_this_a_page = $this->check_page();
			if (!empty( $this->is_this_a_page )) {
					// p1 will return data from the database matching the page name-e.g. site1.com/about <-- get about page.
					$p1 = $this->is_this_a_page['page']; 
			} else {
					// no matching data in database for page name.
					$p1='/theme/'.basename( $this->file); 
			}
			if (!strstr($p1,".html")) {
					$this->tpath=$this->theme_root.$p1.".html"; 
			} else {
					$this->tpath=$this->theme_root.$p1;  
			}
			$url_there =  $this->check_url();
			// there is not a page present BUT the page is in the database so set page to index.
			if ($url_there == false) {
				if (count($this->is_this_a_page)!=0) {  
				$this->tpath=$this->theme_root."index.html"; 
				$url_there=true;
			}
			}
	
			if (  $url_there  ==  true ) {
						if ( !empty($this->is_this_a_page)) { $url = $this->tpath;  }
						$file = $url;
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL,$url);
						curl_setopt ($ch, CURLOPT_REFERER, $url); 
						curl_setopt($ch, CURLOPT_HEADER, 0);
						curl_setopt($ch, CURLOPT_NOBODY, false);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						$result= curl_exec ($ch);
						$this->webpage = $result;
			}
			else { $result=''; }
		}

		
		
		function process_elements()
		{
			//
			// process_elements :	process all elements in returned webpage.
			// Tags processed   :	<a>		<rel>	<blog>		<page>		<cat>
			// link will be like: 	href="css/css1/css1.css" we need to transform it
			// 						into :   theme/default/  TO: absolute:		http://site.com/theme/default/css/css1/css1.css
			//
			
			try {
				$doc = new DOMDocument();					// dom class.
				libxml_use_internal_errors(true);			// supresss errors from unknow tags.
				$doc->loadHTML(  $this->webpage  );			// use DOMDocument to load xml representation of page.
				libxml_use_internal_errors(false);			// turn on supression.
				$xml = simplexml_import_dom($doc);
				/* Search for <link rel=stylesheet> */
				$page_HTML			= 	$xml->xpath('//html');
				$page_H				= 	$xml->xpath('//head');
				$page_body			= 	$xml->xpath('//body');
				$page_css			= 	$xml->xpath('//link');
				$page_links			= 	$xml->xpath('//a');
				$page_images		= 	$xml->xpath('//img');
				$page_script		= 	$xml->xpath('//script');
				// special tags.. <post><cat><gallery><googlemap><contact>
				$page_posts			=	$xml->xpath('//post');
				$page_categories	= 	$xml->xpath('//cat');
				$page_gallery		= 	$xml->xpath('//gallery');
				$page_maps			= 	$xml->xpath('//googlemap');
				$page_contact		= 	$xml->xpath('//contact');
				// page specific tags - e.g. <page_title><page_post><page_date>....
				$custom_page		=	$xml->xpath('//custom_page');
				
				$page_title			=	false;
				$page_category		= 	false;
				$page_post			= 	false;
				$page_date			= 	false;
				$page_user			= 	false;
				
				

						// the <custom_page> tag is there. so check if sub tags are there --- page_title..page_date etc..
						if (count($custom_page) != 0) {
								$pp = $custom_page[0];
								foreach ( $custom_page as $child) {
										foreach ($child->children() as $n ) {
												$c = $n->getName();
								//			echo 'C:'.$c.'#';
												if (strstr("page_title" , $c    )) { $page_title    = true; }
												if (strstr("page_category" , $c )) { $page_category = true; }
												if (strstr("page_post" , $c     )) { $page_post     = true; }
												if (strstr("page_date" , $c     )) { $page_date     = true; }
												if (strstr("page_user" , $c     )) { $page_user     = true; }
										} 
								}
						}
						
						// if all inner tags <page_title><page_post> etc then just output post in body.
						if ( $page_title == false && $page_category == false && $page_post == false && $page_date == false && $page_user == false ) {
							$p='';
							if (isset( $this->is_this_a_page[0]['id'] )) {
								foreach ( $this->is_this_a_page as $found_page ) {		// loop round ALL pages from database...
									$id    = $found_page['id'];				// get ID from db row.
									$title = $found_page['title'];			// get title from db row.
									$title = '<a href="index.php?id='.$id.'">'.$title.'</a>';
									$post  = $found_page['post'];			// get post from db.
									$date  = $found_page['date_of_post'];	// date of post.
									$user  = $found_page['user'];			// user..
									$cat   = $found_page['category'];		// category.
								
									$p .= '<p><strong>'.$title.'</strong></p>';
									$p .= '<p>'.$post.'</p>';
									$p .= '<p>'.$user.'&nbsp;&nbsp;'.$date.'&nbsp;&nbsp;'.$cat.'</p>';
									foreach( $page_body  as $body ) {  $body->nodeValue = $p; }
								}
							}
						}
						
						$pos = 0;
						foreach ( $custom_page as $child) {
							if (  !empty($page_body) ) {									// <body> tag is present in page ?
				
								if (!empty($this->is_this_a_page[0])) {						// data is returned from Database.
									// NO TAGS..<page_title><page_date>etc...
										foreach ( $this->is_this_a_page as $found_page ) {		// loop round ALL pages from database...
												if (isset($found_page['title'])) {				// page title set ?
														$id    = $found_page['id'];				// get ID from db row.
														$title = $found_page['title'];			// get title from db row.
														$title = '<a href="index.php?id='.$id.'">'.$title.'</a>';
														$post  = $found_page['post'];			// get post from db.
														$date  = $found_page['date_of_post'];	// date of post.
														$user  = $found_page['user'];			// user..
														$cat   = $found_page['category'];		// category.
														// loop round CHILD nodes of the custom page tag <custom_page>
														foreach ( $child->children() as $parts ) {
																// look for page_title within <custom_page>.
																$result = $parts->xpath("//page_title");
																// only set the title if attrib nid IS NOT set. 
																if ($page_title==true) {
																	foreach ($result as $r ) {
																			$check_attr = $r->attributes();
																			if (!isset($check_attr['nid'])) {  $r->addAttribute("nid", $pos);  $r->nodeValue=$title; }
																	}
																}
																if ($page_post==true) {
																	$result = $parts->xpath("//page_post");
																	foreach ($result as $r ) {
																			$check_attr = $r->attributes();
																			if (!isset($check_attr['nid'])) {  $r->addAttribute("nid", $pos);  $r->nodeValue=$post; }
																	}
																}
																if ($page_date==true) {
																	$result = $parts->xpath("//page_date");
																	foreach ($result as $r ) {
																			$check_attr = $r->attributes();
																			if (!isset($check_attr['nid'])) {  $r->addAttribute("nid", $pos);  $r->nodeValue=$date; }
																	}
																}
																if ($page_user==true) {
																	$result = $parts->xpath("//page_user");
																	foreach ($result as $r ) {
																			$check_attr = $r->attributes();
																			if (!isset($check_attr['nid'])) {  $r->addAttribute("nid", $pos);  $r->nodeValue=$user; }
																	}
																}
																if ($page_category==true) {
																	$result = $parts->xpath("//page_category");
																	foreach ($result as $r ) {
																			$check_attr = $r->attributes();
																			if (!isset($check_attr['nid'])) {  $r->addAttribute("nid", $pos);  $r->nodeValue=$cat; }
																	}
																}
														}
														$child->addChild("br");
														$child->addChild("br");

														if ( $page_title    == true )  {   $child->addChild("page_title"," ");    }
														if ( $page_post     == true )  {   $child->addChild("page_post"," ");     }
														if ( $page_category == true )  {   $child->addChild("page_category"," "); }
														if ( $page_date     == true )  {   $child->addChild("page_date");         }
														if ( $page_user     == true )  {   $child->addChild("page_user");         }

										}
																			
									}
							}
						}
				} 
			
				
				// PROCESS CSS FILES.
				foreach( $page_css  as $node) {
					$link = $node->attributes()->href;
					$t = $this->theme_root . $link;
					$t1		=	realpath( $t ) . PHP_EOL;							// convert relative to absolute e.g. http://,,
					$node->attributes()->href=$t; 
				}
				// PROCESS ALL <script> tags.
				$jquery_there=false;
				foreach( $page_script  as $node) {
						//
						// check if the head already has JQUERY included...
						//
						$check_attr = $node->attributes();
						// check if the element has a class tag - if so leave it as it is.
						if ( isset($check_attr['src'])) {
							// check for: JQUERY.MIN.JS		OR		JQUERY And min.js To cover:  jquery-1.11.3.min.js..
							$script_src  =  strtolower( $check_attr['src'] );
							if (strstr( $script_src , "jquery" )) { $jquery_there=true; }
						}
				}
				// PROCESS ALL A TAGS.			
				foreach( $page_links  as $node) {
					$link 	= 	$node->attributes()->href;
					if ( ( !strstr( $this->root , $link )) && ( !strstr($link,".html")) ) { $external=true; } else { $external=false;}
					
					if ($external==true) {
							$link = $this->addhttp($link);
							$t=$link;
					} else {
							if (!strstr($link,".html")) {						// no .html
									$s = $this->root;							// site root.
									$link = str_replace( $s , "" , $link ); 	// get the bit after main url...
									$name = substr( $link , 0, strpos(  $link , "/" ));
							} else {
									$name 		=	basename( $link );										// get the url.
									$name		= 	str_replace( ".html" , "" , $name );
							}
							$tt		= $this->root . $name;							// get the link.
							$t = $this->addhttp($tt);
					}
					// CHECK FOR .HTML FILES....
					$node->attributes()->href=$t;
				}
				// PROCESS ALL IMAGES.
				foreach( $page_images  as $node) {
					$link = $node->attributes()->src;	
					$t = $this->theme_root . $link;
					$node->attributes()->src=$t;  
				}
				// PROCESS ALL POST TAGS.
				foreach( $page_posts  as $node) {
					$post 		= $node->attributes()->name;
					$show_what  = $node->attributes()->show;
					$p 			= ucwords(str_replace(" ","-",$post ) );
					$get_post 	= $this->get_post( $p , $show_what );
					if ( $get_post != "" ) {
							// has get_post() returned a string or a array??
							if ( is_array($get_post))  {
								// array so cycle through array add each thing one at a time...
								$pnew='';
								foreach ( $get_post as $p ) { echo'P:'.$p; $pnew .= $p; }
								$node->nodeValue  =  html_entity_decode (  $pnew , ENT_COMPAT, 'UTF-8');
							} else {
								$node->nodeValue  =   html_entity_decode (  $get_post , ENT_COMPAT, 'UTF-8');
							}
					}
				}
				// PROCESS <HEAD> TAG.
				// Only process the head tag once - so use a flag for this.
				$flag_head=0;
				foreach( $page_H  as $node) {
						$flag_head++;
						// add the gallery CSS.
						$ins_css = " ul {  list-style-type: none;  } ";
						$x=$node->addChild("style" , $ins_css );

						// jquery already in this html page???
						if ( $jquery_there==false ) {
							$ins_js1 = 'http://code.jquery.com/jquery-latest.min.js';
							$n1 =  $node->addChild("script",' ');
							$n2 =  $n1->addAttribute("src", $ins_js1);
							$n1->addAttribute( "type" , "text/javascript" );
						}
						// add in the main gallery code in javascript.
						if (!empty($page_gallery)) {
							$ins_js = ' $(function () {  var change_img_time     = 5000;     var transition_speed    = 100;    var simple_slideshow    = $("#MainSlider"),        listItems           = simple_slideshow.children("li"),        listLen             = listItems.length,        i                   = 0,        changeList = function () {            listItems.eq(i).fadeOut(transition_speed, function () {                i += 1;                if (i === listLen) {                    i = 0;                }                listItems.eq(i).fadeIn(transition_speed);           });        };    listItems.not(":first").hide();    setInterval(changeList, change_img_time);}); ';
							$g1 = $node->addChild( "script", $ins_js  );
						}
					
						// for google maps...only add ref. if <googlemap> tag used.
						if ( !empty($page_maps)) {
								$g = 'http://maps.google.com/maps/api/js?sensor=false';
								$n1 =  $node->addChild("script"," ");
								$n2 =  $n1->addAttribute("src", $g  );
						}
						
						// for contact page...
						if (!empty($page_contact)) {
								//$f = ' function pressEmail() { var n1 = $("#name1").val(); var p1 = $("#phone1").val(); var e1 = $("#email1").val(); var q1 = $("#enq1").val(); var data = [  n1, p1 , e1 , q1];  alert(data); $.ajax({  type: "POST", 	async: false, url: "../../inc/email.php", data: data ,	success:function(data) { alert(data); }  }); }';
								$f = ' function pressEmail() {   var n1 = $("#name1").val(); var p1 = $("#phone1").val(); var e1 = $("#email1").val(); var q1 = $("#enq1").val();   $.ajax({  type: "POST", 	async: false, url: "../../inc/email.php", data: { name:  n1  , phone: p1 , email: e1 , enq:  q1   } ,	success:function(data) { alert(data);   }  }); }';
								$n1 = $node->addChild(  "script"  ,  $f  );
						}
						if ( $flag_head==1) { break; }
				}

				// PROCESS ALL GALLERY TAGS. loop round <gallery>
				foreach( $page_gallery  as $node1) {
						//
						//$g = strtolower( $node1->attributes()->name );
						$g = $node1->attributes()->name;
						$height = $node1->attributes()->height;
						if ( $height=='') { $height=200; }
						$width = $node1->attributes()->width;
						if ( $width=='' ) { $width=200;  }
						// get actual image tags...
						$get_gallery 	= $this->get_post( $g );
						$get_gallery    = html_entity_decode($get_gallery , ENT_COMPAT, 'UTF-8');
						// check gallery is found...
						if ( $get_gallery != '' ) {
								$doc = new DOMDocument();
								$doc->loadHTML($get_gallery);    
								$selector = new DOMXPath($doc);
								$result = $selector->query('//img');
								// GET IMG TAGS...
								$x1 		= 	$node1->addChild('ul' );
								$x1->addAttribute( 'id', 'MainSlider');
								foreach($result as $node) {
										//$im1		=	$node->nodeValue;
										$im1		=	$node->getAttribute('src');
										$gallery 	= 	$im1.'" height="'.$height.'" width="'.$width.'"';  
										if ( $gallery != "" ) {
												$x2 = $x1->addChild('li');
												$x3 = $x2->addChild( 'img' );
												$x3->addAttribute( 'src', $gallery );
										}
								}
						}
				}
				// PROCESS MAPS
				$maps=false;
				$lat1=0;
				$lat2=0;
				foreach( $page_maps  as $node) {
						// get the map postcode attribute.
						$pc = $node->attributes()->postcode;
						$height = $node->attributes()->height;
						if ( $height=='') { $height=200; }
						$width = $node->attributes()->width;
						if ( $width=='' ) { $width=200;  }
						// attempt to turn in to long/lat.
						if ( $pc!='') {
								$maps=true;
								$url ="http://maps.googleapis.com/maps/api/geocode/xml?address=" . $pc . "&sensor=false";
								$result = @simplexml_load_file($url);
								if ((bool)$result) { $lat1 =  $result->result->geometry->location->lat;  $lat2 =  $result->result->geometry->location->lng; }
								$x=$node->addChild('div'," ");
								$x->addAttribute("id","map");
								$x->addAttribute("style","width:".$width."px;  height:".$height."px;" );
								// add javascript for google maps.
								$m1  = 'function initialize() { var mapCanvas = document.getElementById("map");';
								$m1 .= ' var mapOptions = { center: new google.maps.LatLng('.$lat1.','.$lat2.'),';
								$m1 .= ' zoom: 16, mapTypeId: google.maps.MapTypeId.ROADMAP }'."\r\n";
								$m1 .= ' var map = new google.maps.Map(mapCanvas, mapOptions); ';
								$m1 .= ' var myLatLng = {lat:'.$lat1.',lng:'.$lat2. '}; ';
								$m1 .= ' var marker = new google.maps.Marker({ position: myLatLng,    map: map,    title: "We are here"  }); }';
								$m1 .= ' google.maps.event.addDomListener(window, "load", initialize);';
								if (  !empty($page_H) ) {									// <body> tag is present in page ?
									$flag_head=0; foreach ( $page_H as $b ) { $flag_head++; $b->addChild(  "script" , $m1 ); if ($flag_head==1) {break; } }
								}
						}
				}
				// PROCESS CONTACT PAGE.
				$contact=false;
				foreach ( $page_contact	as $con ) {
						// contact page...
						$contact=true;
						$frm  = '<form class="class_form_contact">';
						$frm .= '<label class="class_label_contact">Enter Name :</label>';
						$frm .= '<input class="class_input_contact"  type="text" id="name1" name="name1" REQUIRED /><br />';
						$frm .= '<label class="class_label_contact">Enter Phone :</label>';
						$frm .= '<input class="class_input_contact"type="text" id="phone1" name="phone1" REQUIRED /><br />';
						$frm .= '<label class="class_label_contact">Enter Email :</label>';
						$frm .= '<input class="class_input_contact"type="text" id="email1" name="email1" REQUIRED /><br />';
						$frm .= '<label class="class_label_contact">Enquiry :</label>';
						$frm .= '<textarea class="class_input_contact" rows="6" cols="20" name="enq1" id="enq1"  REQUIRED></textarea><br />';
						$frm .= '<input type="button" value="SEND" class="class_button_contact" onclick="javascript:pressEmail();"   />';
						$frm .= '</form>';
						$con->nodeValue= $frm;
				}

				// update public webpage var with changed values...
				$output = $xml->asXML();
				$this->webpage = $output;
			}
			catch(Exception $e) { }
		}
		
		

		//
		// get posts matching $p e.g. get_post("about"); <-- get the hello post from the database.
		// inputs			:	$p = post name to look for.
		// $show_fields		:	default is zero -> only show the actual post body - other values are:
		//					:	1 = show title only.
		//					:	2 = show title and body.
		//					:	3 = show title and body and user.
		//					:	4 = show title and body and user and category.
		//					:	5 = show title and body and user and category and date posted.
		//					:	6 = show user only.
		//					:	7 = show category of post only.
		//					:	8 = show date posted only.
		//					:	9 = show ID only.
		//					:	Using one or more of these it is possible to show anything related to 
		//					:	a post -> e.g. get_post(1); AND get_post(8); will return TITLE and Date.
		function get_post( $p , $show_fields = 0 )
		{
				$result = $this->db->select('posts','*', ' title ="'.$p.'"'  );
				$res = $this->db->getResult();
				if ( !empty($res)) {
						// get user.
						switch($show_fields ) { case 3:case 4: case 5: case 6: $include_user=true;break; default: $include_user=false;break; }
						if ( $include_user== true ) {		// only get user if we need to...
								$u=$res[0]['user'];
								$users = $this->db->select('users','*', ' id ="'.$u.'"'  );
								$uget = $this->db->getResult();
								if ( !empty($uget)) { $user_name = $uget[0]['user']; } else { $user_name=''; }
						}
						// get category.
						switch($show_fields ) { case 4: case 5: case 7: $include_cat=true;break; default: $include_cat=false;break; }
						if ( $include_cat== true ) {		// only get category if we need to...
								$c=$res[0]['category'];
								$cats = $this->db->select('categories','*', ' id ="'.$c.'"'  );
								$cget = $this->db->getResult();
								if ( !empty($cget)) { $cat_name = $cget[0]['CATEGORY']; } else { $cat_name=''; }
						}
						if ( $show_fields == 0 ) {		// default.
								$ret = $res[0]['post'];
						} else {						// return field values according to option value...
							$ret=array();
							$tag_1 = 	'<p class="class_post_';	// add classes to field values - e.g. date is class_post_date.
							switch($show_fields) {
								case 0:		$ret[]= $tag_1.'post">' .$res[0]['post'].$tag_2;			break;
								case 1:		$ret[]=	$tag_1.'title">'.$res[0]['title'];			break;
								case 2:		$ret[]= $tag_1.'title">'.$res[0]['title']; $ret[]=$tag_1.'post">'.$res[0]['post'];break;
								case 3:		$ret[]= $tag_1.'title">'.$res[0]['title']; $ret[]=$tag_1.'post">'.$res[0]['post'];$ret[]=$tag_1.'user">'.$user_name;break;
								case 4:		$ret[]= $tag_1.'title">'.$res[0]['title']; $ret[]=$tag_1.'post">'.$res[0]['post'];$ret[]=$tag_1.'user">'.$user_name;$ret[]=$tag_1.'category">'.$cat_name;break;
								case 5:		$ret[]= $tag_1.'title">'.$res[0]['title']; $ret[]=$tag_1.'post">'.$res[0]['post'];$ret[]=$tag_1.'user">'.$user_name;$ret[]=$tag_1.'category">'.$cat_name;$ret[]=$tag_1.'date">'.$res[0]['date_of_post'];break;
								case 6:		$ret[]= $tag_1.'user">'.$user_name;					break;
								case 7:		$ret[]= $tag_1.'category">'.$cat_name;				break;
								case 8:		$ret[]= $tag_1.'date">'.$res[0]['date_of_post'];	break;
								case 9:		$ret[]= $tag_1.'id">'.$res[0]['id'];				break;
							}
						}
				} else { $ret =''; }
				return $ret;
		}

		
		
		/* check page is there.....*/
		function check_url() {
				$name 		=	basename($this->tpath);										// get the url.
				if ( !strstr($name,".html")) { $name="index.html"; }	// does not contain .html - we are only interested in .html here.
				$f 				= $this->theme_file;										// get file path for theme e.g. public_html/site1/
				$f1				= str_replace( "inc" , "" , $f  );							// get rid of inc directory.
				$file_there		= $f1.'/theme/'.$this->themef.'/'.$name;					// file we are looking to check....

				if (file_exists( $file_there )) 
				{
						return true;
				}
				else 
				{
						return false;
				}
		}


		
	function simplexml_insert_after(SimpleXMLElement $insert, SimpleXMLElement $target)
	{
		$target_dom = dom_import_simplexml($target);
		$insert_dom = $target_dom->ownerDocument->importNode(dom_import_simplexml($insert), true);
		if ($target_dom->nextSibling) {
			return $target_dom->parentNode->insertBefore($insert_dom, $target_dom->nextSibling);
		} else {
			return $target_dom->parentNode->appendChild($insert_dom);
		}
	}



	function addhttp($url) {
		if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
			$url = "http://" . $url;
		}
		return $url;
	}

	
	
	private function check_page()
	{
		try {
			// this function check if the URL contains a page ref.
			// e.g. http://site.com/about <-- if about is in the database
			$u = $this->tpath;										// where we are going.
			$s = $this->root;										// site root.
			$p = str_replace( $s , "" , $u );					 	// get the bit after main url...
			
			$param = strpos ( $p , "/" );							// pos. of any / in url
			if ( $param==false ) { 	$param = strpos ( $p , "?" );  }
			//if (isset($_GET['id']))          { if (is_numeric( $_GET['id']))          {  $id   = $_GET['id'];       } }
			if (isset($_GET['id']))          { if (filter_var( $_GET['id'], FILTER_VALIDATE_INT, array ('min_range' => 1)))  {  $id   = $_GET['id'];       } }
			if (isset($_GET['category']))    { if (is_numeric( $_GET['category']))    {  $cat  = $_GET['category']; } }
			if (isset($_GET['page']))        { if (is_numeric( $_GET['page']))        {  $page = $_GET['id'];       } }
			if ($param != false) { $p=substr($p,0,$param);}
			if (!isset($id)) { $id=-1; }

			// remove any .html ext from it.
			$l1 = str_replace( ".html" , "" , $p );
			$l2 = trim($l1);
			// no request for page/post so default to main category...
			if ( strstr( $l2 , "index"  ) || ( strlen(trim($l2))==0 )) { $l2 = 'main'; }

			if ( strlen($l2) > 0 ) {
					$use_id=false;
					// search for the page in the database...
					if (isset($id))  {
						if ( $id!=-1) {
							$p_get  = $this->db->select('posts t1 left join categories t2 on t1.category=t2.id ','t1.id, t1.title, t1.post, t1.date_of_post, t2.CATEGORY as category , t1.user', ' t1.id ='.trim($id) ); $use_id=true;
						}
					}
					if (isset($cat)) {
							$p_get  = $this->db->select('posts t1 left join categories t2 on t1.category=t2.id ','t1.id, t1.title, t1.date_of_post, t2.CATEGORY as category , t1.user', ' cat ="'.strtolower($cat).'"'  ); $use_id=true;
					}
					if ( $use_id == false )  {
							$p_get  = $this->db->select('posts t1 left join categories t2 on t1.category=t2.id ','t1.id, t1.post, t1.title, t1.date_of_post, t2.CATEGORY as category , t1.user', ' title ="'.strtolower($l2).'" OR  t2.CATEGORY ="'.strtolower($l2).'"'  );
					}
					$p_get1 = $this->db->getResult();
//		print_r($p_get1);
					// add in page and any other params...
					$p_get1['page'] = $p;
					$p_get1['query'] = $id;
			} else { $p_get1=array(); }
		}
		catch (Exception $e) {
				$p_get1=array();
		}
		return $p_get1;
	}


//////// END CLASS //////////
}

?>