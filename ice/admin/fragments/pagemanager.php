<?php
	namespace Ice;
	use Ice\Models\Page;
	
	define('SYSINIT',true);

	require '../../ice-config.php';
	require '../../lib/DB.php';
	require '../../lib/Auth.php';
	require '../../models/Page.php';

	Auth::init(1);

	if(isset($_POST['del']) && $_POST['del'] == "true") {
		if($_SESSION['userlevel'] < 2) {
			die('You are not allowed to perform this action');
		}

		$page = Page::byId(intval($_POST['id']));

		$page->delete();
		
		die('true');
	} elseif (isset($_POST['rename']) && $_POST['rename'] == "true") {
		if($_SESSION['userlevel'] < 2) {
			die('You are not allowed to perform this action');
		}

		$page = Page::byId($_POST['id']);
		$page->setName($_POST['name']);
		$page->save();

		die('true');
	}
	if(!isset($_POST['refresh'])) :
?>
<script type="text/javascript">
	function pagemanager() {
		var W = new ice.Window();
		W.name = "IcePM";
		W.title = "Manage pages";
		W.icon = " ";
		W.width = 745;
		W.beforeClose = function(win) {
			$('#pageManagerMenu').attr('current', 'none').fadeOut();
		};
		
		W.allowRefresh = true;
		W.contentEndpoint = "fragments/pagemanager.php";
		
		W.onOpen = function(win) {
			$s = $('<input id="schPages" type="text" style="float: right; margin:0" placeholder="Search" />');
			win.element.find('.winBar').append($s);
			$s.keyup((function() {
				var targets, $this;
				return function() {
					if(targets === undefined) {
						var W = ice.Manager.getWindow("IcePM");
						targets = $(".big_grid>li", W.element);
						$this = $(this);
					}
					targets.show()
					targets.not(":contains(" + $this.val() + ")").hide();
					
				}
			})());
		}
		
		W.onContentChange = function(W) {
			if("console" in window) {console.log('Ran onContentChange')}
			$('.big_grid>li', W.contentBox).bind("contextmenu", function() {
				var $this = $(this), off = $this.offset(), pm = $('#pageManagerMenu');
				pm
					.attr({
						'data-url': $this.attr('data-page-trac'),
						'data-id': $this.attr('data-page-id')
						})
					.css({left: off.left - ((pm.width() - 75) / 2), top: off.top, display:'none'})
					.fadeIn()
					.children('b')
					.text($this.children().text());
				return false; //Prevent the usual context menu from opening
			}).click(function() {
				$('#pageManagerMenu')
					.attr('data-current', $(this).attr('data-page-trac'))
					.find('li:eq(0)')
					.trigger('click');
			});
		};

		$('#pageManagerMenu li').click(function() {
			console.log('ran handler');
			var $this = $(this), current = $this.parent().parent().attr('data-current'),
			id = $this.parent().parent().attr('data-id');
			
			switch($this.attr('data-cmd')) {
				case 'editN':
					var dm = $('<form>').attr({
						id: "formPostShiv",
						action: current,
						method: "post",
						style: "display:none;",
						target: "_blank"
					});
					$('<input type="text" name="edit" value="true">').appendTo(dm);
					dm.appendTo('body');
					document.getElementById('formPostShiv').submit();
					$('#formPostShiv').remove();
					$('#pageManagerMenu').attr('current', 'none').fadeOut();
					break;
				case 'edit':
					ice.fragment.load('browser',{}, {url: current, postEdit: true});
					$('#pageManagerMenu').attr('current', 'none').fadeOut();
					break;
				case 'rename':
					var r = prompt('Please enter the new name');
					if(r != null && r !="") {
						$.post('fragments/pagemanager.php', {rename:true, id: id, name: r}, function(data) {
							ice.Manager.getWindow('IcePM').refresh();
							if(data=="true") {
								$('.pageBtn[data-page-id="' + id + '"] span').text(r);
								ice.message('Name changed', 'info');
							} else {
								ice.message(data, 'warning');
							}
						});
					}
				$('#pageManagerMenu').attr('current', 'none').fadeOut();
					break;
				case 'del':
						
					if(confirm('This action will delete the page and all accociated data. Continue?')) {
						$.post('fragments/pagemanager.php', {del:true, id: id}, function(data) {
							if(data == 'true') {
								ice.publish('ice:page/delete', [id]);
							} else {
								ice.message(data, 'warning');
							}
						});
					}
					$('#pageManagerMenu').attr('current', 'none').fadeOut();
					break;
				case 'close':
					$('#pageManagerMenu').attr('current', 'none').fadeOut();
					break;
			}
		});

		//Refresh when a page is altered
		W.handle(ice.subscribe("ice:page/new", function() {	W.refresh(); }));
		W.handle(ice.subscribe("ice:page/delete", function() { W.refresh(); }));

		W.setContent(document.getElementById('pageManager1').innerHTML);
		ice.Manager.addWindow(W);
	}
</script>

<script type="text/template" id="pageManager1">
<?php endif;?>

<div class="winpadd" style="padding-top:5px;">
	<div class="toolbar">
		Click on a page to edit, right click for options.
		<a href="#" style="float:right;" onclick="ice.fragment.load('pagewizard');">Create new page</a>
		
	</div>
	<br />
	<div style="clear:both;"></div>
	<ul class="big_grid">
		<?php

			$pages = Page::findAll();

			if ($pages) {
				foreach ($pages as $i => $page) {
					echo '<li data-page-trac="', $page->getUrl(), '" data-page-id="',$page->getId() , '" ><h3>', $page->getName(), '</h3></li>';
				}
			} else {
				echo 'This was awkward.';
			}

		?>

	</ul>
	<div style="clear:both;"></div>
</div>

<?php if(isset($_POST['refresh'])) { die(); } ?>
</script>
<div id="pageManagerMenu" class="shadow rounded6" style="display: none;">
	<br />
	<b></b>
	<br />
	<ul class="nicelist">
		<li data-cmd="editN">Edit</li>
		<li data-cmd="edit">|_ in a window</li>
		<li data-cmd="rename">Rename</li>
		<li data-cmd="del">Delete</li>
		<li data-cmd="close">Close this</li>
	</ul>
	<br />
</div>