<?php 

class Photo extends Db_object{

	protected static $db_table = "photos";
	protected static $db_table_fields = array('id', 'title','caption', 'description', 'filename','alternate_text', 'type', 'size');
	public $id;
	public $title;
	public $caption;
	public $description;
	public $filename;
	public $alternate_text;
	public $type;
	public $size;

	public $tmp_path;
	public $upload_directory ="images";

	public $errors = array();

	public $upload_errors_array  = array(

		UPLOAD_ERR_OK         => "There is no error",
		UPLOAD_ERR_INI_SIZE   => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
		UPLOAD_ERR_FORM_SIZE  => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
		UPLOAD_ERR_PARTIAL    => "The uploaded file was only partially uploaded.",
		UPLOAD_ERR_NO_FILE    => "No file was uploaded.",
		UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder. Introduced in PHP 5.0.3.",
		UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk. Introduced in PHP 5.1.0.",
		UPLOAD_ERR_EXTENSION  => " A PHP extension stopped the file upload."
	);



	// This is passing $_FILES['uploaded_file'] as an argument

	public function set_file($file)
	{
		if (empty($file) || !$file || !is_array($file)) {
			
			$this->errors[] = "There was no file uploaded here.";
			return false;

		} elseif ($file['error'] != 0) {
			
			$this->errors[] = $this->upload_errors_array[$file['error']];
			return false;

		} {
			
			$this->filename = basename($file['name']);
			$this->tmp_path = $file['tmp_name'];
			$this->type     = $file['type'];
			$this->size     = $file['size'];
		}

	}  // set_file method end



	public function picture_path()
	{
		return $this->upload_directory .DS. $this->filename;
	}


	public function save()
	{
		if ($this->id) {

			$this->update();

		}else {
			
			if (!empty($this->errors)) {
			
			   return false;

		    }

			if (empty($this->filename) || empty($this->tmp_path)) {
				$this->errors[] = "The file was not available.";
				return false;
			}

			//$target_path = SITE_ROOT .DS. 'admin'.DS.$this->upload_directory. DS. $this->filename; //not work

			//$target_path = 'images/'.$this->filename;  // works

			$target_path = $this->upload_directory .DS. $this->filename;


			if (file_exists($target_path)) {
				$this->errors[] = "The file {$this->filename} already exists.";
				return false;
			}

			$move_uploaded_files = move_uploaded_file($this->tmp_path, $target_path);

			if ($move_uploaded_files) {
				
				if ($this->create()) {
					
					unset($this->tmp_path);
					return true;
				}

			}else {

				$this->errors[] = "The file directory probably does not have permission";
				return false;
			}

		}

	}  // save method end




	public function delete_photo()
	{
		if ($this->delete()) {
			
			$target_path = $this->picture_path();
			return unlink($target_path) ? true : false;

		} else{

			return false;
		}
		
	} // delete_photo method end



	public static function display_sidebar_data($photo_id)
	{
		$photo = Photo::find_by_id($photo_id);

		$output = "<a class='thumbnail' href='#'><img width='100' src='{$photo->picture_path() }'></a>";
		$output .= "<p>Filename : {$photo->filename}</p>";
		$output .= "<p>File Type : {$photo->type}</p>";
		$output .= "<p>File Size : {$photo->size}</p>";

		echo $output;
	}













} // Photo class end

?>


