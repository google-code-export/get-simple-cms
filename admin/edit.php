<?php
/****************************************************
*
* @File: 		edit.php
* @Package:	GetSimple
* @Action:	Edit or create new pages for the website. 	
*
*****************************************************/

// Setup inclusions
$load['plugin'] = true;

// Relative
$relative = '../';

// Include common.php
include('inc/common.php');

// Variable settings
$userid = login_cookie_check();

// Get passed variables
$uri 		= @$_GET['uri'];
$id 		= @$_GET['id'];
$ptype 		= @$_GET['type'];
$path 		= '../data/pages/';

// Page variables reset
$theme_templates = ''; 
$parents_list = ''; 
$keytags = '';
$parent = '';
$template = '';
$menuStatus = ''; 
$private = ''; 
$menu = ''; 
$content = '';
$title = '';
$url = '';

if ($uri)
{
	// get saved page data
	$file = $uri .'.xml';
	
	if (!file_exists($path . $file))
	{ 
		header('Location: pages.php?error='.$i18n['PAGE_NOTEXIST']);
		exit;
	}

	$data_edit = getXML($path . $file);
	$title = stripslashes($data_edit->title);
	$pubDate = $data_edit->pubDate;
	$metak = stripslashes($data_edit->meta);
	$metad = stripslashes($data_edit->metad);
	$url = $data_edit->url;
	$content = stripslashes($data_edit->content);
	$template = $data_edit->template;
	$parent = $data_edit->parent;
	$menu = stripslashes($data_edit->menu);
	$private = $data_edit->private;
	$menuStatus = $data_edit->menuStatus;
	$menuOrder = $data_edit->menuOrder;
	$buttonname = $i18n['BTN_SAVEUPDATES'];
} 
else 
{
	$buttonname = $i18n['BTN_SAVEPAGE'];
}


// MAKE SELECT BOX OF AVAILABLE TEMPLATES
if ($template == '') { $template = 'template.php'; }
$themes_path = "../theme/". $TEMPLATE;

$themes_handle = @opendir($themes_path) or die("Unable to open $themes_path");
while ($file = readdir($themes_handle))
{
	if( isFile($file, $themes_path, 'php') ) 
	{
		$templates[] = $file;
	}
}

sort($templates);

foreach ($templates as $file)
{
	if ($template == $file)
	{ 
		$sel="selected"; 
	} 
	else
	{ 
		$sel=""; 
	}
	
	if ($file == 'template.php')
	{ 
		$templatename=$i18n['DEFAULT_TEMPLATE']; 
	} 
	else 
	{ 
		$templatename=$file;
	}
	
	$theme_templates .= '<option '.@$sel.' value="'.$file.'" >'.$templatename.'</option>';
}


// MAKE SELECT BOX FOR PARENT PAGES
$parents = getFiles($path);
sort($parents);

// Selected?
if ($parent == null) { $none="selected"; } else { $none=""; }

// Create base option
$parents_list .= '<option '.@$none.' value="" >-- '.$i18n['NONE'].' --</option>';

foreach ($parents as $fi)
{
	if( isFile($fi, $path, 'xml') )
	{
		$goodname = str_replace(".xml", "", $fi);
		
		if ($parent == $goodname) { $sel="selected"; } else { $sel=""; }
		
		if ($goodname != $uri )
		{
			$tmpData = getXML($path . $fi);
			
			if ($tmpData->parent == '')
			{ 
				$parents_list .= '<option '.@$sel.' value="'.$goodname.'" >'.$goodname.'</option>';
			}
		}
	}
}

// SETUP CHECKBOXES
if ($menuStatus != '') { $sel = 'checked';	}
if ($private != '') { $sel_p = 'checked';	}
if ($menu == '') { $menu = @$title; } 
?>		


<?php get_template('header', cl($SITENAME).' &raquo; '.$i18n['PAGE_MANAGEMENT']); ?>
	
	<h1 align="right">
		<a href="<?php echo $SITEURL; ?>" target="_blank" ><?php echo cl($SITENAME); ?></a> <span>&raquo;</span> <?php echo $i18n['PAGE_MANAGEMENT']; ?> <span>&raquo;</span> <?php if(isset($data_edit)) { echo $i18n['PAGE'].' &lsquo;<span class="filename" >'. @$url .'</span>&rsquo;'; } else { echo $i18n['NEW_PAGE']; } ?>		
	</h1>
	
	<?php 
		include('template/include-nav.php');
		include('template/error_checking.php'); 
	?>
	
	<div class="bodycontent">
	
	<div id="maincontent">
		<div class="main">
		
		<label><?php if(isset($data_edit)) { echo $i18n['PAGE_EDIT_MODE']; } else { echo $i18n['CREATE_NEW_PAGE']; } ?></label>	

		<!-- pill edit navigation -->
		<div class="edit-nav" >
			<?php 
			if( (isset($uri)) && ($private != 'Y' )) {
				if ($PRETTYURLS == '1') {
					if ($parent != '') {$parent = tsl($parent);}
					echo '<a href="'. $SITEURL . @$parent . $uri .'" target="_blank" accesskey="v" >'.$i18n['VIEW'].'</a>'; 
				} else {
					echo '<a href="'. $SITEURL .'index.php?id='.$uri.'" target="_blank" accesskey="v" >'.$i18n['VIEW'].'</a>'; 
				}
			} 
			?>
			<a href="#" id="metadata_toggle" accesskey="o" ><?php echo $i18n['PAGE_OPTIONS']; ?></a>
			<div class="clear" ></div>
		</div>	
			
		<form class="largeform" id="editform" action="changedata.php" method="post" accept-charset="utf-8" >
			
			<!-- page title toggle screen -->
			<p id="edit_window">
				<label for="post-title" style="display:none;"><?php echo $i18n['PAGE_TITLE']; ?></label>
				<input class="text title set-example-text" id="post-title" name="post-title" rel="<?php echo $i18n['PAGE_TITLE']; ?>" type="text" value="<?php echo @$title; ?>" />
			</p>
				

			<!-- metadata toggle screen -->
			<div style="display:none;" id="metadata_window" >
				<table class="formtable">

				<tr>
					<td><b><?php echo $i18n['SLUG_URL']; ?>:</b><br />
          <input class="text short" type="text" id="post-uri" name="post-uri" value="<?php echo @$url; ?>" <?php echo (@$url=='index'?'readonly="readonly" ':''); ?>/></td>

					<td><b><?php echo $i18n['TAG_KEYWORDS']; ?>:</b><br />
					<input class="text short" id="post-metak" name="post-metak" type="text" value="<?php echo @$metak; ?>" /></td>

				</tr>
				<tr>
					<td colspan="2">
						<b><?php echo $i18n['META_DESC']; ?>:</b><br />
						<input class="text" id="post-metad" name="post-metad" type="text" value="<?php echo @$metad; ?>" />
					</td>
				</tr>
				<tr>
					<td><b><?php echo $i18n['PARENT_PAGE']; ?>:</b><br />
					<select class="text short" id="post-parent" name="post-parent" >
						<?php echo @$parents_list; ?>
					</select></td>
					
					<td><b><?php echo $i18n['TEMPLATE']; ?>:</b><br />
					<select class="text short" id="post-template" name="post-template" >
						<?php echo $theme_templates; ?>
					</select></td>
				</tr>

				<tr>
					<td><b><?php echo $i18n['KEEP_PRIVATE']; ?></b> &nbsp;&nbsp;&nbsp;<input type="checkbox" id="post-private" name="post-private" <?php echo @$sel_p; ?> />
					</td>
					
					<td>
							<b><a href="navigation.php" style="display:inline;font-weight:bold !important;" rel="facybox" ><?php echo $i18n['ADD_TO_MENU']; ?></a>?</b> &nbsp;&nbsp;&nbsp;<input type="checkbox" id="post-menu-enable" name="post-menu-enable" <?php echo @$sel; ?> /><br />
							<div id="menu-items"><span><?php echo strtolower($i18n['MENU_TEXT']); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo strtolower($i18n['PRIORITY']); ?></span><input class="text" style="width:175px;" id="post-menu" name="post-menu" type="text" value="<?php echo @$menu; ?>" />&nbsp
							<select class="text"  style="width:50px;" id="post-menu-order" name="post-menu-order" >
								<?php if($menuOrder) { 
									if($menuOrder == 0) {
										echo '<option value="" selected>-</option>'; 
									} else {
										echo '<option value="'.$menuOrder.'" selected>'.$menuOrder.'</option>'; 
									}
								} ?>
									<option value="">-</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option>
							</select></div>
					</td>
				</tr>
			<?php exec_action('edit-extras'); ?>		
			</table>

			</div>	
				
		
			<!-- page body -->
			<p>
				<label for="post-content" style="display:none;"><?php echo $i18n['LABEL_PAGEBODY']; ?></label>
				<textarea class="set-example-text" id="post-content" rel="<?php echo $i18n['LABEL_PAGEBODY']; ?>" name="post-content"><?php echo @$content; ?></textarea>
			</p>
			
			<?php if(isset($data_edit)) { 
				echo '<input type="hidden" name="existing-url" value="'. @$url .'" />'; 
			} ?>	
			
			<p><input class="submit" type="submit" name="submitted" value="<?php echo $buttonname; ?>" /> &nbsp;&nbsp;<?php echo $i18n['OR']; ?>&nbsp;&nbsp; <a class="cancel" href="pages.php?cancel"><?php echo $i18n['CANCEL']; ?></a></p>
			
			<small><?php 
					if (isset($pubDate)) { 
						echo $i18n['LAST_SAVED'].': '. lngDate(@$pubDate).'&nbsp; ';
					}
					if ( file_exists('../backups/pages/'.@$url.'.bak.xml') ) {	
						echo '-&nbsp; <a href="backup-edit.php?p=view&uri='.@$url.'" target="_blank" >'.$i18n['BACKUP_AVAILABLE'].'</a>';
					} 
			?></small>
		</form>
		

		<?php if ($HTMLEDITOR != '') { ?>
		<script type="text/javascript" src="template/js/ckeditor/ckeditor.js"></script>

			<script type="text/javascript">

				var editor = CKEDITOR.replace( 'post-content', {
	        skin : 'getsimple',
	        forcePasteAsPlainText : true,
	        language : 'en',
	        defaultLanguage : 'en',
	        entities : true,
	        uiColor : '#FFFFFF',
					height: '500px',	
	        toolbar :
	        [
	          ['Bold', 'Italic', 'Underline', 'NumberedList', 'BulletedList', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', 'Table', 'TextColor', 'BGColor', 'Link', 'Unlink', 'RemoveFormat', 'Source'],
	          '/',
	          ['Styles','Format','Font','FontSize']
	        ]
	        //filebrowserBrowseUrl : '/browser/browse.php',
	        //filebrowserImageBrowseUrl : '/browser/browse.php?type=Images',
	        //filebrowserWindowWidth : '640',
	        //filebrowserWindowHeight : '480'
    		});

			</script>
		
		<?php } ?>
	</div>
	</div><!-- end maincontent -->
	
	
	<div id="sidebar" >
		<?php include('template/sidebar-pages.php'); ?>	
	</div>
	
	<div class="clear"></div>
	</div>
<?php get_template('footer'); ?>