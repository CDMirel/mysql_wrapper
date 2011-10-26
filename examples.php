<?php
require_once 'class.mysqlwrapper.php';

/**
 * mysqlwrapper Examples
 * @author John Skrzypek jms8142@gmail.com
 * These examples will create a test database for you, so make sure your user has appropriate admin rights 
 */

/**
 * DB Config Info
 */
$host = "localhost";
$user = "root";
$password = "root";


/**
 * Create test database and grant permissions
 */
$database = "test_database";
$table = "test_table";

$db = new mysqlwrapper();
if(!$db->connect($host,$user,$password)) {
	echo 'Connection to server failed.  Reason: ' . $db->getError();
} else {
	
	
	//Populate with test data
	
	$query = "CREATE DATABASE IF NOT EXISTS $database";
	$db->query($query);
	$db->select_db($database);
	
	//create schema and add some test data
	$db->query("DROP TABLE IF EXISTS `$table`;");
	$db->query("CREATE TABLE `$database`.`$table` (
				`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`fname` VARCHAR( 50 ) NOT NULL ,
				`lname` VARCHAR( 50 ) NOT NULL ,
				`email` VARCHAR( 50 ) NOT NULL
				) ENGINE = MYISAM ;");

	if($db->getErrorMessage())
		echo $db->getErrorMessage() . "<br/>";
	
	$db->query("INSERT INTO $table VALUES(1, 'Steve', 'Jobs', 'steve.jobs@apple.com')");
	$db->query("INSERT INTO $table VALUES(2, 'David', 'Cross', 'david.cross@comedy.com')");
	$db->query("INSERT INTO $table VALUES(3, 'Steve', 'Martin', 'steve.martin@compuserv.com')");
	$db->query("INSERT INTO $table VALUES(4, 'Brian', 'Butterfield', 'bbuterfield@bbc.uk')");

	if($db->getErrorMessage())
		echo $db->getErrorMessage() . "<br/>";

	$db->setShowQueries(true); //show all queries
		
/**
 * Example 1 - mysql query function
 */
	echo "Example 1 -  mysql query function<hr/>";
	//straight forward query
	$sql = "SELECT * FROM $table order by lname";
	$result = $db->query($sql);
	
	while($row = mysql_fetch_assoc($result)){
		echo $row['fname'] . " " . $row['lname'] . "<br/>"; 
	}
	
	
	
	
/**
 * Example 2 - selectUnique function
 */	
	echo "<br/><br/>Example 2 - selectUnique function<hr/>";
	$colName = 'email';
	$colVal = 'steve.martin@compuserv.com';
	
	$db->selectUnique($table, $colName, $colVal);
	if($db->getNumrows()>0)
		echo "The $colName of $colVal exists. <br/> There is " . $db->getNumrows() . " entry";
		
		
		
		
/**
 * Example 3 - selectMulti function
 */	
	echo "<br/><br/>Example 3 - selectMulti function<hr/>";

	$colsArray = array(
					new dbcol('fname','Steve',dbcol::STR)
				);
				
	$db->selectMulti($table, $colsArray);
	while($row = $db->fetch_assoc_row()){
		echo $row['fname'] . " " . $row['lname'] . "<br/>"; 
	}
	
	

/**
 * Example 4 - insert function
 */	
	echo "<br/><br/>Example 4 - insert function<hr/>";

	$colsArray = array(
					new dbcol('fname','Michael',dbcol::STR),
					new dbcol('lname','Knight',dbcol::STR),
					new dbcol('email','m.knight@kitt.com',dbcol::STR)
				);

	if($db->insert($table, $colsArray)){
		$db->query("Select * from $table");
		while($row = $db->fetch_assoc_row()){
			echo $row['fname'] . " " . $row['lname'] . "<br/>"; 
		}
	}
	

/**
 * Example 5 - update function
 */	
	echo "<br/><br/>Example 5 - update function<hr/>";
	$newValue = array(
					new dbcol('lname','Crass',dbcol::STR)
				);
				
	$where = array(
				new dbcol('lname','Cross',dbcol::STR)
			);
	
	if($db->update($table, $newValue, $where)){
		$db->query("Select * from $table");
		while($row = $db->fetch_assoc_row()){
			echo $row['fname'] . " " . $row['lname'] . "<br/>"; 
		}
	}
	
	$db->close();
	
} //close if($db->connect())
