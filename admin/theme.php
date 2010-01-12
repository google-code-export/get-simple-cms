<?php 
/****************************************************
*
* @File: 		theme.php
* @Package:	GetSimple
* @Action:	Displays and changes website settings 	
*
*****************************************************/

	require_once('inc/functions.php');
	require_once('inc/plugin_functions.php');
	$path = "../data/other/"; 
	$file = "website.xml"; 
	$theme_options = '';
	
	$userid = login_cookie_check();

	// were changes submitted?
	if( (isset($_POST['submitted'])) && (isset($_POST['template'])) ) {
		
		$TEMPLATE = $_POST['template'];
		
		// create new site data file
		$bakpath = "../backups/other/";
		createBak($file, $path, $bakpath);
		
		
		$xml = @new SimpleXMLElement('<item></item>');
		$xml->addChild('SITENAME', @$SITENAME);
		$xml->addChild('SITEURL', @$SITEURL);
		$xml->addChild('TEMPLATE', @$TEMPLATE);
		$xml->addChild('TIMEZONE', @$TIMEZONE);
		$xml->addChild('LANG', @$LANG);
		$xml->asXML($path . $file);
		$success = $i18n['THEME_CHANGED'];
	}
	
	// get available themes (only look for folders)
	$themes_path = "../theme";
	$themes_handle = @opendir($themes_path) or die("Unable to open $themes_path");
	while ($file = readdir($themes_handle)) {
		$curpath = $themes_path .'/'. $file;
		if( is_dir($curpath) && $file != "." && $file != ".." ) {
			$sel="";
			if (file_exists($curpath.'/template.php')) {
				if ($TEMPLATE == $file) { $sel="selected";}
				$theme_options .= '<option '.@$sel.' value="'.$file.'" >'.$file.'</option>';
			}
		}
  	}
?>

<?php get_template('header', cl($SITENAME).' &raquo; '.$i18n['ACTIVATE_THEME']); ?>
	
	<h1><a href="<?php echo $SITEURL; ?>" target="_blank" ><?php echo cl($SITENAME); ?></a> <span>&raquo;</span> <?php echo $i18n['THEME_MANAGEMENT'];?></h1>
	<?php include('template/include-nav.php'); ?>
	<?php include('template/error_checking.php'); ?>
	
	<?php 
	if (isset($_GET['err'])) {
		echo '<div class="error"><b>'.$i18n['ERROR'].':</b> '.$_GET['err'].'</div>';
	} elseif (isset($_GET['success'])) {
		echo '<div class="updated">'.$_GET['success'].'</div>';
	} elseif (isset($success)) {
		echo '<div class="updated">'.$success.'</div>';
	}
	?>
<div class="bodycontent">
	
	<div id="maincontent">
		
		
		<div class="main">
		<h3><?php echo $i18n['CHOOSE_THEME'];?></h3>
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" accept-charset="utf-8" >
		<p style="display:none" id="waiting" ><?php echo $i18n['SITEMAP_WAIT'];?></p>

		<p><select class="text" style="width:250px;" name="template" >
					<?php echo $theme_options; ?>
			</select>&nbsp;&nbsp;&nbsp;<input class="submit" type="submit" name="submitted" value="<?php echo $i18n['ACTIVATE_THEME'];?>" /></p>
		</form>
		<?php
			if ( $SITEURL ) {	
				echo '<p><b>'.$i18n['THEME_PATH'].': &nbsp;</b> <code>'.$SITEURL.'theme/'.$TEMPLATE.'/</code></p>';
			}
			echo '<p><img style="border:2px solid #333;" ';
		 	if (file_exists('../theme/'.$TEMPLATE.'/images/screenshot.png')) { 
				echo 'src="../theme/'.$TEMPLATE.'/images/screenshot.png"';
			} else {
				echo 'src="template/images/screenshot.jpg"';
			}
			echo ' alt="'.$i18n['THEME_SCREENSHOT'].'" /></p>';
		?>
			
		</div>
	
	</div>
	
	<div id="sidebar" >
		<?php include('template/sidebar-theme.php'); ?>
	</div>

	<div class="clear"></div>
	</div>
<?php get_template('footer'); ?>