<?php
//TODO: Make this less shitty.
namespace Ice;

define('SYSINIT', true);

require '../ice-config.php';
require '../lib/Auth.php';
require '../lib/IceImage.php';

Auth::init(2);

if (isset($_GET['thumb'])) {
	$path = realpath('../media/' . $_GET['thumb']);
	$tpath = '../cache/thumb_' . $_GET['thumb'];
	if (!file_exists($tpath)) {
		$thumb = new IceImage($path);
		$thumb -> setCachePath($tpath);
		$thumb -> resizeToFit(150,112);

		header("Content-Type: image/jpeg");

		$thumb -> outputAndCache();
	} else {
		header("Content-Type: image/jpeg");
		readfile($tpath);
	}
	die();
}
?>
<!DOCTYPE html>
<html>
	<head>
		<link href="../admin/admin.css" rel="stylesheet" type="text/css" />
		<link href="../admin/fragments/mediamanager.css" rel="stylesheet" type="text/css" />
		<style>
			body {
				background: transparent !important;
			}
			img {cursor: pointer;}
		</style>
	</head>
	<body>
		<div style="overflow: auto">
			<div class="mediaList rounded6">
				<ul>
					<?php
					$images = IceImage::getImagePaths('../media/*.*');
					foreach ($images as $key => $value) {
						echo "<li data-name=\"$value\"><img src=\"imageexplorer.php?thumb=$value\"></li>";
					}
					?>
					<div style="clear: both"/>
				</ul>
				<div class="center" style="width: 620px">
					<input type="button" id="iceImageCancel" value="Cancel" style="top: 510px;float:right"/>
					<input type="button" onclick="#" value="Insert image by url" style="float:right"/>
				</div>
			</div>
			
		</div>
		<script src="../lib/jquery.js"></script>
		<script>
			var mediaRoot = "<?php echo $config['baseurl'], $config['sys_folder'], "media/"; ?>";
			$('li').click(function() {
				console.log
				if(document.popup.payload.isTypeImage) { 
					//If the *field type* is img.
					document.popup.exec(function(u) {
						this.iceEdit.objTarget.attr('src', u);
						this.iceEdit.saveImage(u)
					}, mediaRoot + $(this).attr('data-name'))
				} else {
					document.popup.exec(function(u) {
						this.iceEdit.objTarget.focus();
						this.document.execCommand('insertImage', false, u);
					}, mediaRoot + $(this).attr('data-name'));
				}
				document.popup.destroy();
			});
			$('#iceImageCancel').click(function() {
				if(document.popup.payload.isTypeImage) {
					document.popup.exec(function() {
						this.iceEdit.objTarget.removeClass('icemarked');
						this.iceEdit = null;
						this.iceEdit = new this.iceEditorClass();
						this.renderEditBubbles();
					});
				}
				document.popup.destroy();
			});
			$("li img").slice(0,12).hide().each(function(index, el) {
				$(el).delay(index * 200).fadeIn(500);
			});

		</script>
	</body>
</html>
