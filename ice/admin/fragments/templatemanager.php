<?php
	namespace Ice;
	use \PDO;
	use Ice\Models\File;

	define('SYSINIT',true);

	require '../../ice-config.php';
	require '../../lib/DB.php';
	require '../../lib/Auth.php';
	require '../../models/File.php';
	
	Auth::init(2);
	
	if(!empty($_POST['nicename'])) {
		$_POST = Auth::sanitize($_POST);
		if(!file_exists($_SERVER['DOCUMENT_ROOT'] . $_POST['path'])) {
			die('Incorrect path: File not found.' . $_POST['path']);
			// TODO: THIS IS NOT WORKING
		}
		if(!empty($_POST['nicename']) || !empty($_POST['path']) || !empty($_POST['url'])) {

			$file = new File(0, $_POST['nicename'], $_POST['path'], $_POST['url']);

			$file->save();

			die();
		}
	}
	if(!empty($_POST['del'])) {
		$_POST = Auth::sanitize($_POST);
		$file = File::byId(intval($_POST['id']));
		if($file !== null) {
			$file->delete();
		}
		die();
	}
	
	if(!isset($_POST['refresh'])) :
?>

<script type="text/javascript">
function templatemanager() {	
	var W = new ice.Window;
	W.name = "TMPLMAN";
	W.title = "Page File Manager";
	W.width = 600;
	W.allowRefresh = true;
	W.contentEndpoint = "fragments/templatemanager.php";
	W.onContentChange = function(W) {
		W.contentBox.find('#addFilesBtn').click(function() {
			var AFW = new ice.Window;
			AFW.title = "Add File to ICE.";
			AFW.width = 300;
			AFW.name = "AddFileDialog";
			AFW.closeable = false;	
			AFW.minimizeable = false;
			AFW.setContent(document.getElementById('addFilesDialog').innerHTML);
			AFW.contentBox.find(':submit').click(function(e) {
				e.preventDefault();
				var $this = $(this);
				if($this.siblings('[value=""]').length > 0) { // Detect empty fields
					ice.message('All fields must be used', 'warning');
				return false;
				}
				var data = $this.parent().serialize();
				$this.siblings().andSelf().attr('disabled', 'disabled');
				$.post('fragments/templatemanager.php', data, function(data) {
					if(data.length > 0) {
						ice.message(data, 'warning');
						$this.siblings().andSelf().removeAttr('disabled');
					} else {
						ice.message('File has been added', 'info');
						
						ice.publish('ice:template/new');
						ice.Manager.removeWindow('AddFileDialog');	
					}
				});
			});
			AFW.contentBox.find('input[type=button]').click(function(){ ice.Manager.removeWindow("AddFileDialog"); });
			ice.Manager.addWindow(AFW);
		});
		W.contentBox.find('#delFilesBtn').click(function() {
			var res = prompt('Please enter the id of the file You want to remove from the system.', "#");
			if(res !== null && res !== "" && confirm("This will delete all pages using this template, and all their text.")) {
				$.post("fragments/templatemanager.php", {del: true, id: res}, function() {
					ice.message('File was deleted', 'info');
					ice.publish('ice:template/delete');
				});
			}	
		});
		W.contentBox.find('#scanFilesBtn').click(function() {
			ice.fragment.load('filescanner');
		})
	}

	W.handle(ice.subscribe('ice:template/new', function() {W.refresh();}));
	W.handle(ice.subscribe('ice:template/delete', function() {W.refresh();}));
	
	W.setContent(document.getElementById('pageFileManager').innerHTML);
	ice.Manager.addWindow(W);
}

</script>

<script type="text/template" id="pageFileManager">
<?php endif; ?>

<p class="enlight">Use this interface to add finished files to the cms. </p>
<table>
<thead>
	<tr>
		<td>ID</td>
		<td>Name</td>
		<td>Path</td>
		<td>URL</td>
	</tr>
</thead>
<tbody>

	<?php 
	$files = File::find();
	if($files === null) {
		echo "<tr><td>No pages</td></tr>";
	} else {
		foreach ($files as $file) {
			echo '<tr><td>', $file->getId(),
			'</td><td>', $file->getName(),
			'</td><td>', $file->getPath(),
			'</td><td>', $file->getUrl(), '</td>';
		}
	}
	?>

</tbody>
</table>

<input type="button" id="scanFilesBtn" value="Find files automatically" style="float:right" />
<input type="button" id="addFilesBtn" value="Add file manually" style="float:right" />
<input type="button" id="delFilesBtn" value="Delete file" style="color:#B00;float:right" />
<?php if(isset($_POST['refresh'])) {die();} ?>

</script>
<script type="text/template" id="addFilesDialog">
<div class="enlight">
	<p>You have to put the files on the server yourself</p>
</div>
<form>
	<dl class="form">
		<dt><label>Name</label></dt>
		<dd><input type="text" name="nicename" style="width:90%" placeholder="Article page"/></dd>
	</dl>

	<p class="enlight"><b>Relative File Path. </b><i>(Relative to the document root) I.e. /templates/about.php</i></p>
	<dl class="form">
		<dt><label for="">Rel. path</label></dt>
		<dd><input type="text" name="path" style="width:90%" placeholder="/templates/article.php"/></dd>
	</dl>
	<p class="enlight"><b>Relative Url.</b><i> (Relative to the root)I.e. if url to the page is example.com/tmp/a.php, write /tmp/a.php in this box.</i></p>
	<dl class="form">
		<dt><label for="">Rel URL</label></dt>
		<dd><input type="text" name="url" style="width:90%" placeholder="/templates/article.php"/></dd>
	</dl>
	<input type="button" value="Abort" style="float:left"/>
	<input type="submit" value="Add file" style="float:right" />
</form>
<div style="clear:both"></div>

</script>


