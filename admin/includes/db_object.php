<?php 


	class Db_object{


		public static function find_all(){
			
			return static::find_by_query("SELECT * FROM " . static::$db_table . "");
		}


		public static function find_by_id($id){

			$the_result_array = static::find_by_query("SELECT * FROM " . static::$db_table . " WHERE id = $id LIMIT 1");

			return !empty($the_result_array) ? array_shift($the_result_array) : false; // ternary comparisn (if/else)
		}


		public static function find_by_query($sql){
			global $database;
			$result_set = $database->query($sql);   // custom query method

			$the_object_array = array();

			while ($row = $result_set->fetch_array()) {
				$the_object_array[] = static::instantiation($row);
			}
			return $the_object_array;
			
		}





		public static function instantiation($the_record){

			$calling_class = get_called_class(); // Late static binding

			$the_object = new $calling_class;

	        // $the_object->id         = $user_found['id'];
	        // $the_object->username   = $user_found['username'];
	        // $the_object->password   = $user_found['password'];
	        // $the_object->first_name = $user_found['first_name'];
	        // $the_object->last_name  = $user_found['last_name'];

	        // return $the_object;
	        
			foreach ($the_record as $the_attribute => $value) {
				if ($the_object->has_the_attribute($the_attribute)) {
					$the_object->$the_attribute = $value;
				}
			}

			return $the_object;
		}

		private function has_the_attribute($the_attribute){
			$object_properties = get_object_vars($this);
			return array_key_exists($the_attribute, $object_properties);
		}




		protected function properties()
		{
			$properties = array();

			foreach (static::$db_table_fields as $db_field) {
				
				if (property_exists($this, $db_field)) {

					$properties[$db_field] = $this->$db_field;
				} 
			}

			return $properties;
		}



		protected function clean_properties()
		{
			global $database;

			$clean_properties = array();

			foreach ($this->properties() as $key => $value) {
				
				$clean_properties[$key] = $database->escape_string($value);
			}

			return $clean_properties;
		}




		public function save(){
			return isset($this->id) ? $this->update() : $this->create();
		}


		public function create(){
			global $database;

			$properties = $this->clean_properties();

			$sql = "INSERT INTO " . static::$db_table . " (". implode("," , array_keys($properties)) .") ";
			$sql .= "VALUES ('"  .  implode("','", array_values($properties)) . "')";
			

			if ($database->query($sql)) {

				$this->id = $database->the_insert_id();
				return true;

			} else {
				return false;
			}

		} // create method end



		public function update(){
			global $database;

			$properties = $this->clean_properties();

			$properties_pairs = array();

			foreach ($properties as $key => $value) {
				
				$properties_pairs[] = " {$key} = '{$value}' ";
			}

			$sql = "UPDATE " . static::$db_table . " SET ";
			$sql .= implode(", ", $properties_pairs);
			$sql .= " WHERE id = " .$database->escape_string($this->id);

			$database->query($sql);

			return (mysqli_affected_rows($database->connection) == 1) ? true : false;

		} // update method end
		



		public function delete(){
			global $database;

			$sql = "DELETE FROM " . static::$db_table . " WHERE id =" . $database->escape_string($this->id);
			$sql .= " LIMIT 1";

			$database->query($sql);

			return (mysqli_affected_rows($database->connection) == 1) ? true : false;


		} // delete method end



		public static function count_all()
		{
			global $database;

			$sql = "SELECT count(*) FROM ". static::$db_table;

			$result_set = $database->query($sql);

			$row = $result_set->fetch_array();

			return array_shift($row);
		}

	}

?>