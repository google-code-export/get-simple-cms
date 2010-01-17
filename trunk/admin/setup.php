<?php 
/****************************************************
*
* @File: 		setup.php
* @Package:	GetSimple
* @Action:	Installs the website if it has never been setup before. 	
*
*****************************************************/

	if(isset($_POST['lang'])) { $LANG = $_POST['lang']; }	else { $LANG = 'en_US'; }
	require_once('inc/functions.php');
	if(isset($_POST['lang'])) { $LANG = $_POST['lang']; }	else { $LANG = 'en_US'; }
	global $i18n;
	
	$kill = ''; $PASSWD = ''; $status=''; $err=null; $message=null; $random=null;
	$file = 'user.xml';
	$path = tsl('../data/other/');
	if (file_exists($path . $file)) {
		$data = getXML($path . $file);
		$USR = stripslashes($data->USR);
		$PASSWD = $data->PWD;
		$EMAIL = $data->EMAIL;
	}
	
	// get suggestion for website base url
	$path_parts = pathinfo($_SERVER['PHP_SELF']);
	$path_parts = str_replace("/admin", "", $path_parts['dirname']);
	$fullpath = "http://". $_SERVER['SERVER_NAME'] . $path_parts ."/";	

	// if the form was submitted...	
	if(isset($_POST['submitted'])) {
		
		if($_POST['sitename'] != '') { 
			$SITENAME1 = cl($_POST['sitename']); 
		} else { 
			$err .= $i18n['WEBSITENAME_ERROR'] .'<br />'; 
		}
		
		$urls = $_POST['siteurl']; 
		if(preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $urls)) {
			$SITEURL1 = tsl($_POST['siteurl']); 
		} else {
			$err .= $i18n['WEBSITEURL_ERROR'] .'<br />'; 
		}
		
		if($_POST['user'] != '') { 
			$USR1 = $_POST['user'];
			$USR = $_POST['user'];
		} else {
			$err .= $i18n['USERNAME_ERROR'] .'<br />'; 
		}
		
		$email = $_POST['email'];
		if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9.\+=_-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9._-]+)+$/", $email)) {
			 $err .= $i18n['EMAIL_ERROR'] .'<br />'; 
		} else {
			$EMAIL1 = $_POST['email'];
		}

		
		// if there were no errors, setup the site
		if ($err == '') {

			$random = createRandomPassword();
			$PASSWD1 = sha1($random);
	
			// create new users.xml file
			$bakpath = "../backups/other/";
			createBak($file, $path, $bakpath);
		
			$xml = @new SimpleXMLElement('<item></item>');
			$xml->addChild('USR', @$USR1);
			$xml->addChild('PWD', @$PASSWD1);
			$xml->addChild('EMAIL', @$EMAIL1);
			if (! $xml->asXML($path . $file)) {
				$kill = $i18n['CHMOD_ERROR'];
			}
			
			$flagfile = "../backups/other/user.xml.reset";
			copy($path . $file, $flagfile);
			
			// create new website.xml file
			$file = 'website.xml';
			$xmls = @new SimpleXMLExtended('<item></item>');
			$xmls->addChild('SITENAME', @$SITENAME1);
			$xmls->addChild('SITEURL', @$SITEURL1);
			$xmls->addChild('TEMPLATE', 'Default_Simple');
			$xmls->addChild('LANG', $LANG);
			$xmls->asXML($path . $file);
			
			// create new cp_settings.xml file
			$file = 'cp_settings.xml';
			$xmlc = @new SimpleXMLElement('<item></item>');
			$xmlc->addChild('HTMLEDITOR', '1');
			$xmlc->addChild('HELPSECTIONS', '1');
			$xmlc->addChild('PRETTYURLS', '2');
			$xmlc->asXML($path . $file);
			
		
			// create index.xml page
			$init = "../data/pages/index.xml"; 
			$temp = "inc/tmp/tmp-index.xml";
			if (! file_exists($init)) {
				copy($temp,$init);
			}

			
			// create components.xml page
			$init = "../data/other/components.xml";
			$temp = "inc/tmp/tmp-components.xml"; 
			if (! file_exists($init)) {
				copy($temp,$init);
			}

			
			// create 403.xml page
			$init = "../data/other/403.xml";
			$temp = "inc/tmp/tmp-403.xml"; 
			if (! file_exists($init)) {
				copy($temp,$init);
			}

			
			// create root .htaccess page
			$init = "../.htaccess";
			$temp_data = file_get_contents("inc/tmp/tmp.htaccess");
			$temp_data = str_replace('**REPLACE**',tsl($path_parts), $temp_data);
			$fp = fopen($init, 'w');
			fwrite($fp, $temp_data);
			fclose($fp);
						
			// send email to new administrator
			$subject  = $site_full_name .' '. $i18n['EMAIL_COMPLETE'];
			$message .= $i18n['EMAIL_USERNAME'] . ': '. stripslashes($_POST['user']);
			$message .= '<br>'. $i18n['EMAIL_PASSWORD'] .': '. $random;
			$message .= '<br>'. $i18n['EMAIL_LOGIN'] .': <a href="'.$SITEURL1.'admin/">'.$SITEURL1.'admin/</a>';
			$message .= '<br>'. $i18n['EMAIL_THANKYOU'] .' '.$site_full_name.'!';
			$status   = sendmail($EMAIL1,$subject,$message);
			
			// Set the login cookie, then redirect user to secure panel		
  		create_cookie();
			header("Location: welcome.php"); 
			
		}
	}
?>

<?php get_template('header', $site_full_name.' &raquo; '. $i18n['INSTALLATION']); ?>
	
	<h1><?php echo $site_full_name; ?> <span>&raquo;</span> <?php echo $i18n['INSTALLATION']; ?></h1>
</div>
</div>
<div class="wrapper">
	

	<?php
	
	// display errors or success messages 
	if ($status == 'success') {
		echo '<div class="updated">'. $i18n['NOTE_REGISTRATION'] .' '. $_POST['email'] .'</div>';
	} elseif ($status == 'error') {
		echo '<div class="error">'. $i18n['NOTE_REGERROR'] .'.</div>';
	}
	if ($kill != '') {
		echo '<div class="error">'. $kill .'</div>';
	}	
	if ($err != '') {
		echo '<div class="error">'. $err .'</div>';
	}
	if ($random != '') {
		echo '<div class="updated">'.$i18n['NOTE_USERNAME'].' <b>'. stripslashes($_POST['user']) .'</b> '.$i18n['NOTE_PASSWORD'].' <b>'. $random .'</b> &nbsp&raquo;&nbsp; <a href="index.php">'.$i18n['EMAIL_LOGIN'].'</a></div>';
	}
	
?>
	<div id="maincontent">
	<?php 
		//if there is no reason to kill the install, show the form
		if ($kill == '') {
	?>
		<div class="main" >
	<h3><?php echo $site_full_name .' '. $i18n['INSTALLATION']; ?></h3>

	<form action="setup.php" method="post" accept-charset="utf-8" >
		<p><b><?php echo $i18n['LABEL_WEBSITE']; ?>:</b><br /><input class="text" name="sitename" type="text" value="<?php if(isset($_POST['sitename'])) { echo $_POST['sitename']; } ?>" /></p>
		<!-- p><b><?php echo $i18n['LABEL_BASEURL']; ?>:</b><br /><input class="text" name="siteurl" type="text" value="<?php if(isset($_POST['siteurl'])) { echo $_POST['siteurl']; } else { echo $fullpath;} ?>" /><br />
		<p style="margin:-15px 0 20px 0;color:#D94136;font-size:11px;" ><?php echo $i18n['LABEL_SUGGESTION']; ?>: &nbsp; <code><?php echo @$fullpath; ?></code></p -->
		<input name="siteurl" type="hidden" value="<?php if(isset($_POST['siteurl'])) { echo $_POST['siteurl']; } else { echo $fullpath;} ?>" />
		<p><b><?php echo $i18n['LABEL_USERNAME']; ?>:</b><br /><input class="text" name="user" type="text" value="<?php if(isset($_POST['user'])) { echo $_POST['user']; } ?>" /></p>
		<p><b><?php echo $i18n['LABEL_EMAIL']; ?>:</b><br /><input class="text" name="email" type="text" value="<?php if(isset($_POST['email'])) { echo $_POST['email']; } ?>" /></p>
		<p><input class="submit" type="submit" name="submitted" value="<?php echo $i18n['LABEL_INSTALL']; ?>" /></p>
	</form>
	</div>
	<?php } ?>
</div>

<div class="clear"></div>
<?php get_template('footer'); ?>