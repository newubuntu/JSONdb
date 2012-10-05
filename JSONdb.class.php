<?php
/***********************************************
*	JSONdb
*	This is my first atempt at a noSQL db.
*	The class is both the db and a wrapper.
*	I've made some structures protected so 
*		you guys can extend it to it's
*		full potential.
*
*	This database has lots and lots of 
*	issues, but it's pretty neat if you're
*	hacking away in the comfort of your 
*	own home.
*	
*	Issue #1:
*	If this is to be used in a live 
*	production you'll need to put the db
*	files in a location outside server 
*	root. This isn't possible on shared 
*	hosting.
*	
*	Issue #2:
*	This won't scale up well as it reads the
*	whole database into memory at once.
*	And writes should use fseek() instead 
*	of writing back all to file, but this 
*	is meant for small data sets.
*	
*	And probably loads more that I'm to 
*	lazy to think of.
************************************************/

class JSONdb {
	/* These guys are private, but they could easily be modified with parent::__construct() */
	private $db_folder; // This is the folder in which the JSON files will be stored
	private $db_file; // This is the name of the JSON file

	protected $buffer; // A buffer to store the data structure
	protected $auto_commit; // Will write changes to disk immediatly if set to true
	

	/* Constructor: Checks if database exists and loads data */
	public function __construct($db_folder, $db_file, $auto_commit = true, $create_if_not_exists = false) {
		$this->db_folder = $db_folder;
		$this->db_file   = $db_file;
		$this->auto_commit = $auto_commit;
		
		if(!file_exists($this->db_folder.$this->db_file)) {
			if($create_if_not_exists) {
				$this->create_db();
			} else {
				throw new Exception(__DB_EXCEPT_NO_DB__);
			}
		}
		
		$this->load();
	}
	
	/* Atempts to create a database(JSON file) */
	protected function create_db() {
		@touch($this->db_folder.$this->db_file);
		
		if(!file_exists($this->db_folder.$this->db_file)) {
			throw new Exception(__DB_EXCEPT_NO_WRITE__);
		}
	}
	
	/* Atempts to load the data from the database */
	protected function load() {
		if(is_readable($this->db_folder.$this->db_file)) {
			$this->buffer = json_decode(file_get_contents($this->db_folder.$this->db_file));
		} else {
			throw new Exception(__DB_EXCEPT_NO_READ__);
		}
	}
	
	/* Makes the change to the buffer, and writes it if we have auto commit */
	public function save($data) {
		$this->buffer[] = $data;
		
		if($this->auto_commit) {
			$this->commit();
		}
	}

	/* Edit indices that hold the value search and replaces it with replace */
	public function edit_where($search, $key, $replace) {
		foreach($this->buffer as $index=>$row) {
			if(isset($row->$key) && $row->$key == $search) {
				$this->buffer[$index]->$key = $replace;
			}
		}

		if($this->auto_commit) {
			$this->commit();
		}
	}

	/* 
	 * Performes a search on one key and replaces the value with replace on rkey, 
	 * or creates rkey if it does not exist 
	*/
	public function edit_what_where($key, $search, $rkey, $replace) {
		foreach($this->buffer as $index=>$row) {
			if(isset($row->$key) && $row->$key == $search) {
				$this->buffer[$index]->$rkey = $replace;
			}
		}

		if($this->auto_commit) {
			$this->commit();
		}
	}
	
	/* Atempts to write the buffer to the database */
	protected function write() {
		$tmp = json_encode($this->buffer);
		$fp = @fopen($this->db_folder.$this->db_file, 'w');
		
		if($fp) {
			fwrite($fp, $tmp, strlen($tmp));
			fclose($fp);
		} else {
			throw new Exception(__DB_EXCEPT_NO_WRITE__);
		}
	}

	/* Returns the buffer(or throws on empty) */
	public function get_all() {
		if(empty($this->buffer)) {
			throw new Exception(__DB_EXCEPT_NO_POST__);
		}
		return $this->buffer;
	}
	
	/* Counts the number of rows in the buffer */
	public function get_count() {
		return count($this->buffer);
	}
	
	/* Returns count number of rows beginning from the offset */
	public function get_slice($offset, $count, $reverse = false) {
		if($offset+$count < $this->get_count()) {
			$tmp = array();
			for($i = $offset; $i <= $offset+$count; $i++) {
				$tmp[] = $this->buffer[$i];
			}
			
			return $tmp;
		} else {
			throw new Exception(__DB_EXCEPT_RANGE_ERROR__);
		}
	}

	/* Atempts to get a specific row from the buffer */
	public function get_row($row) {
		if(isset($this->buffer[$row])) {
			return $this->buffer[$row];
		} else {
			throw new Exception(__DB_EXCEPT_RANGE_ERROR__);
		}
	}

	/* Returns the entire row where value of key */
	public function get_row_where($key, $value) {
		foreach($this->buffer as $index=>$row) {
			if(isset($row->$key) && $row->$key == $value) {
				return $row;
			}
		}

		throw new Exception(__DB_EXCEPT_ROW_NOT_FOUND__);
	}

	/* Deletes a row from the buffer, and writes if auto commit */
	public function delete_row($row) {
		if(isset($this->buffer[$row])) {
			unset($this->buffer[$row]);
			
			if($this->auto_commit) {
				$this->commit();
			}
		} else {
			throw new Exception(__DB_EXCEPT_RANGE_ERROR__);
		}
	}
	
	/* Atempts to change a row in the buffer, writes if auto commit */
	public function edit_row($row, $data) {
		if(isset($this->buffer[$row])) {
			$this->buffer[$row] = $data;

			if($this->auto_commit) {
				$this->commit();
			}
		} else {
			throw new Exception(__DB_EXCEPT_RANGE_ERROR__);
		}
	}
	
	public function search($kv_array) {
		$tmp = array();
		
		if(empty($this->buffer) || empty($kv_array)) {
			return $tmp;
		}

		foreach($this->buffer as $num=>$row) {
			foreach($kv_array as $k=>$v) {
				if(isset($row->$k) && $row->$k == $v) {
					$tmp[] = $row;
				}
			}
		}
		
		return $tmp;
	}

	/* Commits changes made to buffer. Writes to file and reloads the buffer */
	public function commit() {
		$this->write();
		$this->load();
	}
}
?>
