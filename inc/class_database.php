<?php

class DATABASE
{
		private $db = NULL;
		private $con = false;
		private $result = array();
		private $rows;
		private $connection_string = NULL;
		public  $r;

		
		public function __construct()
		{
				// SETUP DATABASE CONNECTION.
				$this->r['DATABASETYPE']	= 'SQLITE'; 	//  <-- default change to MYSQL and fill in host/user etc to use MYSQL.
				$this->r['DBUSER']			= '';			//  <-- sqlite does not have user as default.
				$this->r['DBPASS']			= '';			//	<-- db password.
				$this->r['DBNAME']			= '';			//	<-- db name.
				$this->r['DBHOST']			= '';			//  <-- db host.

				$dbtype=$this->r['DATABASETYPE'];
				$ty = strtolower(trim( $dbtype));
				if ($ty=='') {$ty='sqlite';}
				switch( $ty )  {
					case "mysql":
						$db_name	= $this->r['DBNAME'];
						$db_host	= $this->r['DBHOST'];
						$this->connection_string =  "mysql:host=".trim($db_host).";dbname=".trim($db_name);
						break;
					 
					case "sqlite":
						$r = rtrim(dirname(__file__),"/");
						$db_path= $r . "/db/small.sqlite";
						$this->connection_string = "sqlite:".$db_path;
//echo 'CON:'.$this->connection_string;
						break;
					 
					case "oracle":
						$this->connection_string = "OCI:dbname=".$db_name.";charset=UTF-8";
						break;
					 
					case "dblib":
						$this->connection_string = "dblib:host=".$db_host.";dbname=".$db_name;
						break;
					 
					case "postgresql":
						$this->connection_string = "pgsql:host=".$db_host." dbname=".$db_name;
						break;
				}

				return $this;

		}



		public function connect()
		{
			if(!$this->con) 	{
				try {
					error_reporting(0);
					$this->con = true;
					$database_type = $this->r['DATABASETYPE'];
					if (  trim($database_type) == 'MYSQL' )  {
							$user		= $this->r['DBUSER'];
							$pass		= $this->r['DBPASS'];
							$host		= $this->r['DBHOST'];
							$dbname		= $this->r['DBNAME'];
							/* Connect to an ODBC database using driver invocation */
							try {
									$this->db = new PDO(  $this->connection_string   , trim($user)  ,  trim($pass)   );
									$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
									$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
							} catch (PDOException $e) {
									$this->con=false;
									echo 'Connection failed: ' . $e->getMessage();
							}
					} else {
						try {
							$this->db = new PDO(  $this->connection_string  );
							$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
							$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
						} catch (  PDOException  $e) {
								echo 'Connection failed: ' . $e->getMessage();
								$this->con=false;
						}
					}
					return $this->con;
				}
				catch (PDOException $e)
				{
					return $e->getMessage();
				}
			}
			else
			{
				return true; //already connected, do nothing and show true
			}
		}



		public function disconnect()
		{
			if($this->con)
			{
				unset($this->db);
				$this->con = false;
				return true;
			}
		}





		public function select($table, $rows = "*", $where = null, $order = null)  {
					$q = 'SELECT '. $rows.' FROM '.$table;
					if($where != null)		$q .= ' WHERE '.$where;
					if($order != null)		$q .= ' ORDER BY '.$order;
					$q .= ';';
		//	echo 'Q:'.$q;
					$this->numResults = null;
					try {
							if ( !isset($this->db)) { die('FATAL ERROR: Cannot connect to DATABASE!!'); }
							$sql = $this->db->prepare($q);
							$sql->execute();
							$this->result = $sql->fetchAll(PDO::FETCH_ASSOC);
							$this->numResults = count($this->result);
							$this->numResults === 0 ? $this->result = null : true ;
							return true;
					}
					catch (PDOException $e) 
					{
							return $e->getMessage().''.$e->getTraceAsString();
					}
		}



		public function getResult()
		{
			return $this->result;
		}



		public function getRows()
		{
			return $this->numResults;
		}
		 


		public function insert($table,$values,$rows = null)
		{
				$insert = 'INSERT INTO '.$table;
				if($rows != null)
				{
					$insert .= ' ('. implode( "," , $rows) .')';
				}
				 
				for($i = 0; $i < count($values); $i++)
				{
					if(is_string($values[$i]))
					$values[$i] = "'".$values[$i]."'";
				}
				$values = implode(',',$values);
				$insert .= ' VALUES ('.$values.')';
		//echo 'I:'.$insert;exit;
				$this->numResults = null;
				try {
					$ins = $this->db->prepare($insert);
					$ins->execute();
					$this->lastId = $this->db->lastInsertId();
					$this->numResults = $ins->rowCount();
					return true;
				}
				catch (PDOException $e) 
				{
					return $e->getMessage();
				}
		}

		
		
		public function create ($table,$values,$rows = null)
		{
//		echo 'IN CREATE..';
				$create = 'CREATE TABLE '.$table;
				if($values != null)
				{
					$values = implode(',' , $values);
					$create .= ' ('  .  $values .  ')';
				}
//		echo 'C::'.$create;
				$this->numResults = null;
				try {
					$ins = $this->db->prepare( $create );
					$ins->execute();
					$this->lastId = $this->db->lastInsertId();
					$this->numResults = $ins->rowCount();
					return true;
				}
				catch (PDOException $e) 
				{
					return $e->getMessage();
				}
		}

		
		
		public function drop ($table)
		{
				$create = 'DROP TABLE '.$table;
				try {
					$ins = $this->db->prepare( $create );
					$ins->execute();
					return true;
				}
				catch (PDOException $e) 
				{
					return $e->getMessage();
				}
		}

		
		
		public function show_tables()
		{
			if ( $this->r['DBTYPE'] == 'MYSQL' ) {
					$sq = "SHOW TABLES";
			} else {
					$sq = "SELECT name FROM  sqlite_master WHERE type='table'";
			}
 			try {
				$sql = $this->db->prepare( $sq);
				$sql->execute();
				$r = $sql->fetchAll(PDO::FETCH_ASSOC);
				echo '<br /><strong>LIST TABLES:</strong>';echo '<table>';
				foreach( $r as $t ) {
					foreach ( $t as $tt ) {
						echo '<tr><td>TABLE: ' . $tt . '</td></tr>';
					}
				}
				echo '</table>';
				return true;
			}
			catch (PDOException $e) 
			{
				return $e->getMessage().''.$e->getTraceAsString();
			}
		}
		
		
		
		public function table_exists( $tableName )
		{
			if ((trim($this->r['DATABASETYPE']))=='MYSQL') {
					$sq = "DESCRIBE ".$tableName.";";
			} else {
					$sq = "SELECT name FROM  sqlite_master WHERE type='table' AND name='$tableName'";
			}
//		echo $sq;
		
 			try {
				$sql = $this->db->prepare( $sq);
				$sql->execute();
				$r = $sql->fetchAll(PDO::FETCH_ASSOC);
				//return true;
				return $r;
			}
			catch (PDOException $e) 
			{
				//return $e->getMessage().''.$e->getTraceAsString();
				return array();
			}
		}

		
		
		public function update( $table , $fields , $values , $id )
		{
				$insert = 'UPDATE ' . $table . ' SET ';
				 
				for($i = 0; $i < count($fields); $i++)
				{
					$insert .= " " . $fields[$i] . "=" . $values[$i].",";
				}
				$insert = rtrim( $insert , ",");
				$insert .= ' WHERE '. $id;
				//echo 'U:'.$insert;
				try {
					$ins = $this->db->prepare($insert);
					$ins->execute();
					return true;
				}
				catch (PDOException $e) 
				{
					return $e->getMessage();
				}
		}

		
		
		public function delete( $table ,  $id )
		{
				$del = 'DELETE FROM ' . $table . ' WHERE ' . $id;
				echo 'D:'.$del;
				try {
					$ins = $this->db->prepare($del);
					$ins->execute();
					return true;
				}
				catch (PDOException $e) 
				{
					return $e->getMessage();
				}
		}
		
////////// END CLASS ////////////////
}
