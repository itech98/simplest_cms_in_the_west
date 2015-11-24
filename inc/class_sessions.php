<?php
//Save PHP Sessions to a database
//CREATE TABLE IF NOT EXISTS `sessions` (  `id` varchar(32) NOT NULL,  `access` int(10) unsigned DEFAULT NULL,  `data` text,  PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;
////


class Session {
		/**
		* Db Object
		*/
		private $db;
 

		public function __construct(){
			  // Instantiate new Database object
			  $this->db = new Database;
	 
			//Next we need to override the Session handler to tell PHP we want to use our own methods for handling Sessions.
			// Set handler to overide SESSION
			session_set_save_handler(
			  array($this, "_open"),
			  array($this, "_close"),
			  array($this, "_read"),
			  array($this, "_write"),
			  array($this, "_destroy"),
			  array($this, "_gc")
			);
	 
			//This looks complicated, but all it is saying is we want to use our own methods for storing and retrieving data that is associated with the Session. Take a look at the PHP Manual to read more about this.
			//And finally we need to start the Session.
			// Start the session
			session_start();
		}

 

		// Next we need to create each of the methods for handling our Session data. Each of these methods are really simple. If you are unfamiliar with the database abstractions from the PDO tutorial, have a read through that post for a more detailed explanation of the methods that will be interacting with the database.
		/**
		* Open
		*/
		public function _open(){
			// If successful
			if($this->db){
					// Return True
					return true;
			}
					// Return False
					return false;
			}

			
//Here we are simply checking to see if there is a database connection. If there is one, we can return true, otherwise we return false.

//Close

//Very similar to the Open method, the Close method simply checks to see if the connection has been closed.


/**
 * Close
 */
public function _close(){
  // Close the database connection
  // If successful
  if($this->db->close()){
    // Return True
    return true;
  }
  // Return False
  return false;
}
 

//Read

//The Read method takes the Session Id and queries the database. This method is the first example of where we bind data to the query. By binding the id to the :id placeholder, and not using the variable directly, we use the PDO method for preventing SQL injection.


/**
 * Read
 */
public function _read($id){
  // Set query
  $this->db->query('SELECT data FROM _sessions WHERE id = :id');
   
  // Bind the Id
  $this->db->bind(':id', $id);
 
  // Attempt execution
  // If successful
  if($this->db->execute()){
    // Save returned row
    $row = $this->db->single();
    // Return the data
    return $row['data'];
  }else{
    // Return an empty string
    return '';
  }
}
 

//If the query returns data, we can return the data. If the query did not return any data, we simple return an empty string. The data from this method is passed to the Global Session array that can be accessed like this:
//echo "<pre>";
//print_r($_SESSION);
//echo "</pre>";
 //Write
//Whenever the Session is updated, it will require the Write method. The Write method takes the Session Id and the Session data from the Global Session array. The access token is the current time stamp.
//Again, in order to prevent SQL injection, we bind the data to the query before it is executed. If the query is executed correctly, we return true, otherwise we return false.


 
/**
 * Write
 */
public function _write($id, $data){
  // Create time stamp
  $access = time();
     
  // Set query  
  $this->db->query('REPLACE INTO _sessions VALUES (:id, :access, :data)');
     
  // Bind data
  $this->db->bind(':id', $id);
  $this->db->bind(':access', $access);  
  $this->db->bind(':data', $data);
 
  // Attempt Execution
  // If successful
  if($this->db->execute()){
    // Return True
    return true;
  }
   
  // Return False
  return false;
}
 

//Destroy

//The Destroy method simply deletes a Session based on it’s Id.


 
/**
 * Destroy
 */
public function _destroy($id){
  // Set query
  $this->db->query('DELETE FROM _sessions WHERE id = :id');
     
  // Bind data
  $this->db->bind(':id', $id);
     
  // Attempt execution
  // If successful
  if($this->db->execute()){
    // Return True
    return true;
  }
 
  // Return False
  return false;
} 
 

//This method is called when you use the session destroy global function, like this:
// Destroy session
//session_destroy();
//Garbage Collection
//And finally, we need a Garbage Collection function. The Garbage Collection function will be run by the server to clean up any expired Sessions that are lingering in the database. The Garbage Collection function is run depending on a couple of settings that you have on your server.

/**
 * Garbage Collection
 */
public function _gc($max){
  // Calculate what is to be deemed old
  $old = time() - $max;
 
  // Set query
  $this->db->query('DELETE * FROM _sessions WHERE access < :old');
     
  // Bind data
  $this->db->bind(':old', $old);
     
  // Attempt execution
  if($this->db->execute()){
    // Return True
    return true;
  }
 
  // Return False
  return false;
}

} 
