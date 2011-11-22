<?php
//TODO: Make this less shitty.
define('SYSINIT',true);
require '../ice-config.php';
require '../lib/auth.class.php';
require '../lib/image.class.php';
$Auth->init(2);



function get_images() {
	$images = Array();
	foreach(glob('../media/*.*') as $v) {
		$e = explode('/',$v);
		$e[0] = '';
		$v = join('/',$e);
		$images[] =  array($v, $e[count($e)-1]);
	}
	return $images;
}

if(isset($_GET['thumb'])) {
	$path = realpath('../media/' . $_GET['thumb']);
	$tpath = realpath('../cache/img_' . $_GET['thumb']);
	if(!file_exists($tpath)) {
		$thumb = new IceImage($path);
		$thumb->resizeWidth(80);
	
		header("Content-Type: image/jpeg");
	
		$thumb->outputAndCache();
	} else {
		header("Content-Type: image/jpeg");
		readfile($tpath);
	}
	die();
} 
?>
<h4>Images in media folder</h4>

<div style="height: 350px; overflow: auto">
<?php
foreach(get_images() as $i) { 
	$url = $config['baseurl'] . str_replace('/','',$config['sys_folder']) . $i[0];
	$thumb = $config['baseurl'] . $config['sys_folder'] . 'editor/imageexplorer.php?thumb=' . $i[1];
	echo "<a class=\"iceImgLink\" onclick=\"iceEdit.saveImage('$url');\">
		<img src=\"$thumb\" />$i[1]</a>\n";
}
?>

</div>
<input type="button" onclick="iceEdit.saveImage(prompt('Enter the url','http://www.example.com'));" value="Insert image by url"></input>
<input type="button" onclick="iceEdit.saveImage(null);" value="Cancel" style="top: 510px"></input>