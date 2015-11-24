<?php
/*
	NAME:			class_startup.php
	VERSION:		1.0
	DESCRIPTION:	This class initialises the blog gets/sets initial blog values etc.
	AUTHOR:			I.M. SHERMAN.
	Functions:		
					#Create _options  Table with initial option values.
					#Create _users    Table with one admin user.
					#Create _blogs    Table to hold blog entries.
					#Create _comments Table to hold users comments.
*/

class StartUp {

		private $r; 
		private $dbtype;
		
		function __construct( $r )		{
				//echo 'CMS START...';
				if ( !empty($r)) {
						$this->dbtype = $r['DATABASETYPE'];
				}
				$db = $this->reset();
				$db = $this->createDatabase();
		}

				
		private function reset()
		{
			$db = new DATABASE();		
			$db_connect = $db->connect();
			$t1   = $db->drop("foo"); 
			$t1   = $db->drop("users ");
			$t1   = $db->drop("_sessions"); 
			$t1   = $db->drop("posts"); 
			$t1   = $db->drop("options"); 
			$t1   = $db->drop("categories"); 
		}


		private function createDatabase()
		{
				// instantiate new db object...#
				// PARAMS::  $db_user. $db_pass. $db_name.  $db_type. $db_host.
				try {
					// pass parameters to database class.				
					$db = new DATABASE();		
					//connect to database.....
					$db_connect = $db->connect();
					// main tables present ?
					$tables_exist = $db->table_exists("options");
					if ( empty ( $tables_exist )) {
							// NO so create them....
							//
							// create tables.
							//
							if ( strtolower($this->dbtype)=='sqlite') {
									if ( $db_connect == false ) { die('CANNOT CONNECT!!'); }
									// USERS table.
									$t = array ('id INTEGER PRIMARY KEY  '  ,   'user TEXT '  ,  ' password TEXT' );
									$d1 = $db->create("users", $t);
									// SESSIONS table.
									$t = array ('sessionid INTEGER PRIMARY KEY   '  ,   'session TEXT ' );
									$d1 = $db->create("_sessions", $t);
									// POSTS table
									$t = array ('id INTEGER PRIMARY KEY '  ,   'user int '  ,  ' post TEXT'  ,  'title TEXT ' , 'category int' , 'date_of_post TIMESTAMP DEFAULT CURRENT_TIMESTAMP' );
									$d1 = $db->create("posts", $t);
									$t = array(   "1" , "Welcome to simplest CMS in the West - this is a sample page.","HELLO", "1"   );
									$f = array(   "user" , "post", "title","category" );
									$e = $db->insert( "posts"  ,  $t , $f );
									$t = array(   "1" , "This is a test page...","TEST 1", "1"   );
									$f = array(   "user" , "post", "title","category" );
									$e = $db->insert( "posts"  ,  $t , $f );
									$t = array(   "1" , "Another test page","TEST 2", "1"   );
									$f = array(   "user" , "post", "title","category" );
									$e = $db->insert( "posts"  ,  $t , $f );

									// OPTIONS table
									$t = array ('id INTEGER PRIMARY KEY'  ,   'opt TEXT '  ,  ' value TEXT'  );
									$d1 = $db->create("options", $t);
									// insert default option values.
									$t = array( "1" , "installed" , "0"   );
									$f = array( "id" , "opt" , "value" );
									$e = $db->insert( "options"  ,  $t , $f );
									$t = array( "2" , "theme" , "basic1"   );
									$f = array( "id" , "opt" , "value" );
									$e = $db->insert( "options"  ,  $t , $f );
									// CATEGORIES table.
									$t = array ('id INTEGER PRIMARY KEY '  ,  'reserved INTEGER', 'CATEGORY TEXT' );
									$d1 = $db->create("categories", $t);
									// insert default category.
									$t = array( "1" , "main" );
									$f = array( "reserved" , "CATEGORY" );
									$e = $db->insert( "categories"  ,  $t , $f );
									$t = array( "1" , "gallery"  );
									$f = array( "reserved" , "CATEGORY"  );
									$e = $db->insert( "categories"  ,  $t , $f );
							} else {
									if ( $db_connect == false ) { die('CANNOT CONNECT!!'); }
									// USERS table.
									$t = array ('id INTEGER PRIMARY KEY AUTO_INCREMENT '  ,   'user TEXT '  ,  ' password TEXT' );
									$d1 = $db->create("users", $t);
									// SESSIONS table.
									$t = array ('sessionid INTEGER PRIMARY KEY AUTO_INCREMENT  '  ,   'session TEXT ' );
									$d1 = $db->create("_sessions", $t);
									// POSTS table
									$t = array ('id INTEGER PRIMARY KEY AUTO_INCREMENT'  ,   'user int '  ,  ' post TEXT'  ,  'title TEXT ' , 'category int' , 'date_of_post TIMESTAMP DEFAULT CURRENT_TIMESTAMP' );
									$d1 = $db->create("posts", $t);
									$t = array(   "0" , "Welcome to simplest CMS in the West - this is a sample page.","HELLO", "1"   );
									$f = array(   "user" , "post", "title","category" );
									$e = $db->insert( "posts"  ,  $t , $f );
									$t = array(   "0" , "This is a test page...","TEST 1", "1"   );
									$f = array(   "user" , "post", "title","category" );
									$e = $db->insert( "posts"  ,  $t , $f );
									$t = array(   "0" , "Another test page","TEST 2", "1"   );
									$f = array(   "user" , "post", "title","category" );
									$e = $db->insert( "posts"  ,  $t , $f );

									// OPTIONS table
									$t = array ('id INTEGER PRIMARY KEY AUTO_INCREMENT'  ,   'opt TEXT '  ,  ' value TEXT'  );
									$d1 = $db->create("options", $t);
									// insert default option values.
									$t = array(  "installed" , "0"   );
									$f = array(  "opt" , "value" );
									$e = $db->insert( "options"  ,  $t , $f );
									$t = array(  "theme" , "basic1"   );
									$f = array(  "opt" , "value" );
									$e = $db->insert( "options"  ,  $t , $f );
									// CATEGORIES table.
									$t = array ( 'id INTEGER PRIMARY KEY AUTO_INCREMENT'  ,  'reserved INTEGER', 'CATEGORY TEXT' );
									$d1 = $db->create("categories", $t);
									// insert default category.
									$t = array( "1" , "main" );
									$f = array( "reserved" , "CATEGORY" );
									$e = $db->insert( "categories"  ,  $t , $f );
									$t = array( "1" , "gallery"  );
									$f = array( "reserved" , "CATEGORY"  );
									$e = $db->insert( "categories"  ,  $t , $f );
							}
					} else { echo 'TABLES ALREADY EXIST!!'; }
					return $db;
				}
				catch (PDOException $e) 
				{
							return $e->getMessage();
				}
		}
		
		
		
		private function setup_settings()
		{
				// is text file there?
				
		
		}
		
		
}
?>
