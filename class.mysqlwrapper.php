<?php
 /**
 * A Simple mysql wrapper.  
 * Not all the mysql functions are represented here, but you should be able to make do with what's here plus the mysql query function
 * 
 * See the dbcol class at the bottom on how to pass column objects
 * 
 * @author John Skrzypek <jms8142@gmail.com>
 * @version 1.0
 */

class mysqlwrapper
{
	public $num_rows = 0;
	private $result;
	private $link;
	private $showErrors;
	private $showQueries = false;
	
	/**
	 * Constructor : pass command to supress/show mysql errors
	 * @param bool $errors suppress mysql errors
	 */
	public function mysqlwrapper($errors = false){
		$this->showErrors = $errors;	
	}
	
	/**
	 * For debugging. Will echo queries.
	 * @param bool $showQueries
	 */
	public function setShowQueries($showQueries){
		$this->showQueries = $showQueries;	
	}
	
	/**
	 * Returns number of rows from last active query
	 * @return int Number of rows (SELECT queries only)
	 */
	public function getNumrows(){
		return $this->num_rows;
	}
	
	/**
	 * Attempt connection to database server
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @return boolean successful connection
	 */
	public function connect($host,$user,$password){		
		if(!$this->link = ($this->showErrors ? mysql_connect($host,$user,$password) : @mysql_connect($host,$user,$password))){
			return false;
		}
		return true;
	}
	
	/**
	 * Select database
	 * @param string $database
	 * @return boolean successful database select
	 */
	public function select_db($database){
		if(!($this->showErrors ? mysql_select_db($database,$this->link) : @mysql_select_db($database,$this->link))){
			return false;
		}
		
		return mysql_select_db($database,$this->link);
	}
	
	/**
	 * Execute query - You can work with the returned result directly, or access it through member functions like fetch_assoc_row, etc.
	 * @param string $query_str
	 * @return resource|NULL
	 */
	public function query($query_str){
		$query_str = trim($query_str);
		
		echo ($this->showQueries == true) ? "QUERY: $query_str<hr/>" : '';
		
		$this->result = ($this->showErrors ? mysql_query($query_str,$this->link) : @mysql_query($query_str,$this->link));
		if(!empty($this->result) && (stripos($query_str,"select") !== false)){
			if(mysql_num_rows($this->result)>0){
				$this->num_rows = @mysql_num_rows($this->result);
				return $this->result;	
			} else { //for select statements with no rows, return nothing
				$this->num_rows = 0;
				return NULL;	
			}
		} else {
			return $this->result;
		}
		
	}
	
	/**
	 * mysql_fetch_assoc wrapper
	 * @return array
	 */
	public function fetch_assoc_row(){
		return ($this->showErrors ? mysql_fetch_assoc($this->result) : @mysql_fetch_assoc($this->result));
	}
	
	/**
	 * mysql_data_seek wrapper
	 * @param int $num row_number
	 * @return boolean Returns TRUE on success or FALSE on failure. 
	 */
	public function data_seek($num){
		return ($this->showErrors ? mysql_data_seek($this->result,$num) : @mysql_data_seek($this->result,$num));
	}
	
	/**
	 * mysql_insert_id wrapper -Retrieves the ID generated for an AUTO_INCREMENT column by the previous query (usually INSERT). 
	 * @return int
	 */
	public function get_insert_id(){
		return ($this->showErrors ? mysql_insert_id() : @mysql_insert_id());
	}
	
	/**
	 * Returns the error text from the last MySQL function
	 * @return string
	 */
	public function getErrorMessage(){
		if($this->link == null){
			return "No valid connection found.<br/>";
		} else {
			return mysql_error($this->link);
		}
	}
	
	/**
	 * Returns the error code from the most recently executed MySQL function
	 * @return int
	 */
	public function getErrno(){
		return mysql_errno($link);
	}
	
	/**
	 * Checks if connection is still alive
	 * @return boolean
	 */
	private function checkConnect(){
		if($this->link == null){
			return false;
		}
	}
	
	/**
	 * Selects a single field based on a specific parameter
	 * @param string $tableName
	 * @param string $colName
	 * @param string $val
	 * @return array
	 */
	public function selectUnique($tableName,$colName,$val){
		$sql = "SELECT $colName from $tableName WHERE $colName = '$val'";
		return $this->query($sql);
	}
	
	/**
	 * Selects * records from a table based on the column objects and values passed
	 * @param string $tableName
	 * @param dbcol arraylist $cols
	 * @param string $orderBy
	 * @return void|result
	 */
	public function selectMulti($tableName,$cols,$orderBy=''){
		if(!is_array($cols))
			return;
			
			$where = '';
			
		foreach($cols as $colObj){
			$tick = ($colObj->dataType == dbcol::INT) ? "" : "'";
			
			$where .= "{$colObj->colName} = $tick{$colObj->colVal}$tick AND ";
		}
		
		//trim off trailing AND
		$where = substr($where,0,strlen($where)-4);
		
		$sql = "SELECT * FROM $tableName WHERE $where";
		
		if($orderBy){
			$sql .= " ORDER BY " . $orderBy;	
		}
				
		return $this->query($sql);
			
	}
	
	/**
	 * Inserts records to table based on the column objects and their values passed
	 * @param string $tableName
	 * @param dbcol arraylist $vals
	 * @return bool
	 */
	public function insert($tableName,$vals){
		if(!is_array($vals))
			return;
			
		$cols = '';
		$values = '';
			
		foreach($vals as $dataObj){
			$tick = ($dataObj->dataType == dbcol::INT) ? "" : "'";
			$cols .= $dataObj->colName . ",";
			$values .= "$tick" . $dataObj->colVal . "$tick,";
		}
		
		//trim comma
		$cols = substr($cols,0,strlen($cols)-1);
		$values = substr($values,0,strlen($values)-1);
		
		
		$sql = "INSERT INTO $tableName ($cols) VALUES ($values)";
			
		return $this->query($sql);
	}
	
	/**
	 * updates table based on the column objects and their values passed
	 * @param string $tableName
	 * @param dbcol arraylist $vals
	 * @param dbcol arraylist $where
	 * @return bool
	 */
	public function update($tableName,$vals,$where){
		if(!is_array($vals))
			return;
			
		$updates = '';
		$wherestr = '';
			
		foreach($vals as $dataObj){
			$tick = ($dataObj->dataType == dbcol::STR || $dataObj->dataType == dbcol::DATETIME) ? "'" : "";
			$updates .= $dataObj->colName . " = $tick" . $dataObj->colVal . "$tick,";
		}
		
		if($where){
			foreach($where as $dataObj){
			$tick = ($dataObj->dataType == dbcol::STR || $dataObj->dataType == dbcol::DATETIME) ? "'" : "";
				$wherestr .= $dataObj->colName . " = $tick" . $dataObj->colVal . "$tick AND ";
			}
			$wherestr = "WHERE " . substr($wherestr,0,strlen($wherestr)-4);
		}
		
		//trim comma
		$updates = substr($updates,0,strlen($updates)-1);
		
		$sql = "UPDATE $tableName SET $updates $wherestr";
			
		return $this->query($sql);
	}
	
	/**
	 * Closes the non-persistent connection to the MySQL server that's associated with the specified link identifier.
	 * This isn't usually necessary, as non-persistent open links are automatically closed at the end of the script's execution.
	 */
	public function close(){
		mysql_close($this->link);
	}
	
}


/**
 * DB Column Class
 * Holds name,value, datatype info for each field
 * @author John Skrzypek
 *
 */
class dbcol {
	var $colName;
	var $colVal;
	var $dataType;
	
	const STR = 1;
	const INT = 2;
	const DATETIME = 3;
	const SUBQUERY = 4;
	
	public function dbcol($_colName,$_colVal,$_dataType){
		$this->colName = $_colName;
		$this->colVal = $_colVal;
		$this->dataType = $_dataType;	
	}
	
}
