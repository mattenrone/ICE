<?php defined('SYSINIT') or die('<b>Error:</b> No direct access allowed');

class IceImage {
	private $name;
	private $image;
	private $type;
	private $cachepath = '../cache/img_';
	
	public function getWidth() {
		return imagesx($this->image);
	}
	public function getHeight() {
		return imagesy($this->image);
	}
	
	public function __construct($filename) {
		$size = getimagesize($filename);
		$this->type = $size[2];
		$this->name = $filename;

		switch ($this->type) {
			case IMAGETYPE_JPEG:
				$this->image = imagecreatefromjpeg($filename);
				break;
			
			case IMAGETYPE_GIF:
				$this->image = imagecreatefromgif($filename);
				break;
			
			case IMAGETYPE_PNG:
				$this->image = imagecreatefrompng($filename);
				break;
				
			default:
				throw new Exception("Unrecognized file type",1);
				break;
		}
		
	}
	
	public function __destruct() {
		imagedestroy($this->image);
	}
	
	public function output($type=IMAGETYPE_JPEG) {
		switch ($type) {
			case IMAGETYPE_JPEG:
				imagejpeg($this->image);
				break;
			
			case IMAGETYPE_GIF:
				imagegif($this->image);
				break;
			
			case IMAGETYPE_PNG:
				imagegif($this->image);
				break;
				
			default:
				throw new Exception("Unrecognized file type",1);
				break;
		}
		return 0;
	}
	
	public function outputAndCache() {
		imagejpeg($this->image, $this->cachepath);
		readfile($this->cachepath);
	}
	
	public function resize($width, $height) {
		$new = imagecreatetruecolor($width, $height);
		imagecopyresampled($new,
			$this->image,
			0, 0, 0, 0,
			$width,
			$height,
			$this->getWidth(),
			$this->getHeight());
		$this->image = $new;
	}
	
	public function resizeWidth($width) {
		$height = $this->getHeight() * ($width / $this->getWidth());
		$this->resize($width, $height);
	}
	public function resizeHeight($height) {
		$width = $this->getWidth() * ($height / $this->getHeight());
		$this->resize($width, $height);
	}
	
	public function setCachePath($pre) {$this->cachepath = $pre; }
	
	private function getCachePath() {
		return $this->cachepath;
	}
	public static function isAllowedType($path) {
		global $config;
		
		$parts = pathinfo($path);
		return in_array(strtolower($parts['extension']), $config['allowed_ext']);
	}
	public static function getImagePaths($patt='../media/*.*') {
		
		$images = array();
		foreach(glob($patt) as $v) {
			if(IceImage::isAllowedType($v)) {
				$images[] = basename($v);
			}
		}
		return $images;
	}

}
