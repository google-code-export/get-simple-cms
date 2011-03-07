<?php
/**
 * Upload Files
 *
 * Displays and uploads files to the website
 *
 * @package GetSimple
 * @subpackage Files
 * @todo Remove relative paths
 */
 
// Setup inclusions
$load['plugin'] = true;
include('inc/common.php');
login_cookie_check();


$path = (isset($_GET['path'])) ? "../data/uploads/".$_GET['path'] : "../data/uploads/";
$subPath = (isset($_GET['path'])) ? $_GET['path'] : "";
$path = tsl($path);

// if a file was uploaded
if (isset($_FILES["file"]))
{
	if ($_FILES["file"]["error"] > 0)
	{
		$error = i18n_r('ERROR_UPLOAD');
	} 
	else 
	{
		//set variables
		$count = '1';
		$file_loc = $path . clean_img_name($_FILES["file"]["name"]);
		$base = $_FILES["file"]["name"];
		
		//prevent overwriting
		while ( file_exists($file_loc) )
		{
			$file_loc = $path . $count.'-'. clean_img_name($_FILES["file"]["name"]);
			$base = $count.'-'. clean_img_name($_FILES["file"]["name"]);
			$count++;
		}
		//create file
		move_uploaded_file($_FILES["file"]["tmp_name"], $file_loc);
		exec_action('file-uploaded');
		//successfull message
		$success = i18n_r('FILE_SUCCESS_MSG').': <a href="'. $SITEURL .'data/uploads/'.$base.'">'. $SITEURL .'data/uploads/'.$base.'</a>';
	}
}
?>
<?php get_template('header', cl($SITENAME).' &raquo; '.i18n_r('FILE_MANAGEMENT')); ?>

	<h1><a href="<?php echo $SITEURL; ?>" target="_blank" ><?php echo cl($SITENAME); ?></a> <span>&raquo;</span> <?php echo i18n_r('FILE_MANAGEMENT'); ?></h1>
	<?php include('template/include-nav.php');?>
	<?php include('template/error_checking.php');?>
	<div class="bodycontent">
	<div id="maincontent">
		<div class="main" >
		<h3 class="floated"><?php echo i18n('UPLOADED_FILES'); ?><span id="filetypetoggle">&nbsp;&nbsp;/&nbsp;&nbsp;<?php echo i18n('SHOW_ALL'); ?></span></h3>
		<div id="file_load">
		<?php
			$count="0";
      		$dircount="0";
			$counter = "0";
			$totalsize = 0;
			$filesArray = array();
      		$dirsArray = array();
      
			$filenames = getFiles($path);
			if (count($filenames) != 0) { 
				foreach ($filenames as $file) {
					if ($file == "." || $file == ".." || $file == ".htaccess" ){
            // not a upload file
          	} elseif (is_dir($path . $file)) {
            $dirsArray[$dircount]['name'] = $file;
            $dircount++;
					} else {
						$filesArray[$count]['name'] = $file;
							$ext = substr($file, strrpos($file, '.') + 1);
						$extention = get_FileType($ext);
						$filesArray[$count]['type'] = $extention;
						clearstatcache();
						$ss = @stat($path . $file);
						$filesArray[$count]['date'] = @date('M j, Y',$ss['ctime']);
						$filesArray[$count]['size'] = fSize($ss['size']);
						$totalsize = $totalsize + $ss['size'];
						$count++;
					}
				}
				$filesSorted = subval_sort($filesArray,'name');
        $dirsSorted = subval_sort($dirsArray,'name');
			}
			echo '<div class="edit-nav" >';
			echo '<select id="imageFilter">';
			echo '<option value="All">'.i18n_r('SHOW_ALL').'</option>';
				foreach ($filesSorted as $filter) {
					$filterArr[] = $filter['type'];
				}
				if (count($filterArr) != 0) { 
					$filterArray = array_unique($filterArr);
					$filterArray = subval_sort($filterArray,'type');
					foreach ($filterArray as $type) {
						
						# check for image type
						if (strstr($type, ' Images')) { 
							$typeCleaned = 'Images';
							$typeCleaned_2 = str_replace(' Images', '', $type);
						} else {
							$typeCleaned = $type;
							$typeCleaned_2 = $type;
						}
						 
						echo '<option value="'.$typeCleaned.'">'.$typeCleaned_2.'</option>';
					}
				}
			echo '</select><div class="clear" ></div></div>';

		  echo '<table class="highlight" id="imageTable">'; 
    
     //echo "<tr><td>";     
     
     $pathParts=explode("/",$subPath);
     $urlPath="/";
     
     echo '<h5><img src="template/images/folder.png" width="13px" /> <a href="?">uploads</a> / ';
     //echo "</td></tr>";
     foreach ($pathParts as $pathPart){
       if ($pathPart!=''){
          $urlPath.=$pathPart."/";
          echo '<a href="?path='.$urlPath.'">'.$pathPart.'</a> / ';
       }
     }
      echo "</h5>";
     if (count($dirsSorted) != 0) {       

        foreach ($dirsSorted as $upload) {
          echo '<tr class="All" >';  
          echo '<td class="" colspan="5">';
        
          $adm = substr($path . $upload['name'] ,  16); 
          echo '<a href="upload.php?path='.$adm.'" title="'. $upload['name'] .'"  ><strong>'.$upload['name'].'</strong></a>';
                   
          echo '</td>';
          echo '</tr>';
        }
     }
			if (count($filesSorted) != 0) { 			
				foreach ($filesSorted as $upload) {
					$counter++;
					if ($upload['type'] == i18n_r('IMAGES') .' Images') {
						$cclass = 'iimage';
					} else {
						$cclass = '';
					}
					echo '<tr class="All '.$upload['type'].' '.$cclass.'" >';
					echo '<td class="imgthumb" >';
					if ($upload['type'] == i18n_r('IMAGES') .' Images') {
						$gallery = 'rel="facybox"';
						$pathlink = 'image.php?i='.$upload['name'].'&path='.$subPath;
						if (file_exists('../data/thumbs/thumbsm.'.$upload['name'])) {
							echo '<a href="'. $path . $upload['name'] .'" title="'. $upload['name'] .'" rel="facybox" ><img src="../data/thumbs/thumbsm.'.$upload['name'].'" /></a>';
						} else {
							echo '<a href="'. $path . $upload['name'] .'" title="'. $upload['name'] .'" rel="facybox" ><img src="inc/thumb.php?src='. $upload['name'] .'&amp;dest=thumbsm.'. $upload['name'] .'&amp;x=65&amp;f=1" /></a>';
						}
					} else {
						$gallery = '';
						$controlpanel = '';
						$pathlink = $path . $upload['name'];
					}
					echo '</td><td><a title="'.i18n_r('VIEW_FILE').': '. htmlspecialchars($upload['name']) .'" href="'. $pathlink .'" class="primarylink">'.htmlspecialchars($upload['name']) .'</a></td>';
					echo '<td style="width:80px;text-align:right;" ><span>'. $upload['size'] .'</span></td>';
             
            // get the file permissions.  
            // Next line fails on Windows host. 
            // $filePerms=substr(decoct(fileperms($upload['name'])),2);
            
            /* 
            $filePerms ="xxx";
            echo '<td style="width:70px;text-align:right;" ><span><b>';
            if ($filePerms){
              echo $filePerms;
            }
          echo '</span></td>';
			  
			 */
					echo '<td style="width:85px;text-align:right;" ><span>'. shtDate($upload['date']) .'</span></td>';
					echo '<td class="delete" ><a class="delconfirm" title="'.i18n_r('DELETE_FILE').': '. htmlspecialchars($upload['name']) .'" href="deletefile.php?file='. $upload['name'] .'&amp;nonce='.get_nonce("delete", "deletefile.php").'">X</a></td>';
					echo '</tr>';
					exec_action('file-extras');
				}
			} else {
				echo '<div id="imageTable"></div>';
			}
			echo '</table>';
			echo '<p><em><b>'. $counter .'</b> '.i18n_r('TOTAL_FILES').' ('. fSize($totalsize) .')</em></p>';
		?>	
		</div>
		</div>
	</div>
		<div id="sidebar" >
		<?php include('template/sidebar-files.php'); ?>
		</div>	
	<div class="clear"></div>
	</div>
<?php get_template('footer'); ?>