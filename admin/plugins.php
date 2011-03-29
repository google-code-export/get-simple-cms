<?php
/**
 * All Plugins
 *
 * Displays all installed plugins 
 *
 * @package GetSimple
 * @subpackage Plugins
 */
 
// Setup inclusions
$load['plugin'] = true;

// Include common.php
include('inc/common.php');

// Variable settings
login_cookie_check();
$counter = '0';
$table = '';

$pluginfiles = getFiles(GSPLUGINPATH);
sort($pluginfiles);
foreach ($pluginfiles as $fi){
	$pathExt = pathinfo($fi,PATHINFO_EXTENSION );
	$pathName = pathinfo_filename($fi);
	
	if ($pathExt=="php") {
		$table .= '<tr id="tr-'.$counter.'" >';
		$table .= '<td><b>'.$plugin_info[$pathName]['name'] .'</b>';
		$api_data = json_decode(get_api_details('plugin', $fi));
		if ($api_data->status == 'successful') {
			if ($api_data->version != $plugin_info[$pathName]['version']) {
				$table .= '<br /><a class="updatelink" href="'.$api_data->path.'" target="_blank">'.i18n_r('UPDATE_AVAILABLE').' '.$api_data->version.'</a>';
			}
		}
		$table .= '</td>';
		$table .= '<td><span>'.$plugin_info[$pathName]['description'] .'<br />';
		$table .= i18n_r('PLUGIN_VER') .' '. $plugin_info[$pathName]['version'].' &mdash; '.i18n_r('AUTHOR').': <a href="'.$plugin_info[$pathName]['author_url'].'" target="_blank">'.$plugin_info[$pathName]['author'].'</a></span></td>';
		if ($live_plugins[$fi]=='true'){
			$cls_Enabled = 'hidden';
			$cls_Disabled = '';
		} else {
			$cls_Enabled = '';
			$cls_Disabled = 'hidden';
		}
	  $table.= '<td style="width:60px;" class="status" >
	  	<a href="plugins.php?set='.$fi.'" class="toggleEnable '.$cls_Enabled.'" title="'.i18n_r('ENABLE').': '.$plugin_info[$pathName]['name'] .'" >'.i18n_r('ENABLE').'</a>
	  	<a href="plugins.php?set='.$fi.'" class="cancel toggleEnable '.$cls_Disabled.'" title="'.i18n_r('DISABLE').': '.$plugin_info[$pathName]['name'] .'" >'.i18n_r('DISABLE').'</a>
	  </td>';	  
		$table .= "</tr>\n";
		$counter++;
	}	
}	
?>

<?php exec_action('plugin-hook');?>

<?php get_template('header', cl($SITENAME).' &raquo; '.i18n_r('PLUGINS_MANAGEMENT')); ?>
	
	<h1><a href="<?php echo $SITEURL; ?>" target="_blank" ><?php echo cl($SITENAME); ?></a> <span>&raquo;</span> <?php i18n('PLUGINS_MANAGEMENT'); ?></h1>
	
	<?php include('template/include-nav.php'); ?>
	<?php include('template/error_checking.php'); ?>
	
	<div class="bodycontent">
	
	<div id="maincontent">
		<div class="main" >
		<h3><?php i18n('PLUGINS_MANAGEMENT'); ?></h3>
		
		<table class="edittable highlight paginate">
			<?php echo $table; ?>
		</table>

		<p><em><b><span id="pg_counter"><?php echo $counter; ?></span></b> <?php i18n('PLUGINS_INSTALLED'); ?></em></p>
			
		</div>
	</div>
	
	<div id="sidebar" >
		<?php include('template/sidebar-plugins.php'); ?>
	</div>
	
	<div class="clear"></div>
	</div>

<?php get_template('footer'); ?>