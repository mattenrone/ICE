<?php
	namespace Ice;
	use Ice\Models\User;

	define('SYSINIT',true);
	
	require_once '../../ice-config.php';
	require_once '../../lib/DB.php';
	require_once '../../lib/Auth.php';
	require_once '../../models/User.php';
	
	Auth::init(2);
	
	if(!isset($_POST['refresh'])) :
?>

<script type="text/javascript">
function usermanager() {	
	var W = new ice.Window;
	W.name = "USRMAN";
	W.title = "User Manager";
	W.width = 600;
	W.contentEndpoint = "fragments/usermanager.php";
	W.allowRefresh = true;
	W.onContentChange = function(win) {
		$('tbody tr', W.contentBox).click(function() {
			var id = parseInt($(this).children().eq(0).text());
			ice.fragment.load('userwin', {}, {id:id});
		});
	};

	W.handle(ice.subscribe('ice:user/new', function() {
		W.refresh();
	}));

	W.setContent(document.getElementById('userManager').innerHTML);
	ice.Manager.addWindow(W);
}

</script>
<script type="text/template" id="userManager">
	
<?php endif; ?>

<div class="winpadd">
	<div class="toolbar">
		Click users below to edit.
		<a href="#" style="float:right;" onclick="ice.fragment.load('userwizard');">Create new user</a>
	</div>
<br />
<table class="rounded6" style="cursor:pointer">
<thead>
	<tr>
		<td>ID</td>
		<td>Username</td>
		<td>Userlevel</td>
		<td>Have WebID?</td>
	</tr>
</thead>
<tbody>

	<?php 
	$users = User::findAll();

	foreach($users as $i => $user) {
		echo '<tr><td>', $user->getId(), '</td><td>',
			$user->getUsername(), '</td><td>',
			$user->getUserLevel(), '</td><td>',
			$user->hasKeyCard() ? "Yes":"No" , '</td>';
	}

	?>

</tbody>
</table>

<div style="clear:both"></div>
</div>
<?php if(!isset($_POST['refresh'])) echo '</script>'; ?>
