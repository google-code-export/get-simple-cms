<?php 
/****************************************************
*
* @File:  caching_functions.php
* @Package: GetSimple
* @since 3.1
* @Action:  Plugin to create pages.xml and new functions  
*
*****************************************************/

$pagesArray = array();

add_action('index-pretemplate','getPagesXmlValues',array('false'));           		// make $pagesArray available to the theme 
add_action('header', 'create_pagesxml',array('true'));            					// add hook to save  $tags values 


/**
 * Get Page Content
 *
 * Retrieve and display the content of the requested page. 
 * As the Content is not cahed the file is read in.
 *
 * @since 2.0
 * @param $page - slug of the page to retrieve content
 *
 */
function getPageContent($page){   
	$thisfile = file_get_contents(GSDATAPAGESPATH.$page.'.xml');
	$data = simplexml_load_string($thisfile);
	$content = stripslashes(htmlspecialchars_decode($data->content, ENT_QUOTES));
	$content = exec_filter('content',$content);
	echo $content;
}

/**
 * Get Page Field
 *
 * Retrieve and display the requested field from the given page. 
 *
 * @since 3.1
 * @param $page - slug of the page to retrieve content
 * @param $field - the Field to display
 * 
 */
function getPageField($page,$field){   
	global $pagesArray;
	if ($field=="content"){
	  getPageContent($page);  
	} else {
	  echo strip_decode($pagesArray[(string)$page][(string)$field]);
	} 
}

/**
 * Echo Page Field
 *
 * Retrieve and display the requested field from the given page. 
 *
 * @since 3.1
 * @param $page - slug of the page to retrieve content
 * @param $field - the Field to display
 * 
 */
function echoPageField($page,$field){
	getPageField($page,$field);
}

/**
 * Return Page Content
 *
 * Return the content of the requested page. 
 * As the Content is not cahed the file is read in.
 *
 * @since 3.1
 * @param $page - slug of the page to retrieve content
 *
 */
function returnPageContent($page){   
  $thisfile = file_get_contents(GSDATAPAGESPATH.$page.'.xml');
  $data = simplexml_load_string($thisfile);
  $content = stripslashes(htmlspecialchars_decode($data->content, ENT_QUOTES));
  $content = exec_filter('content',$content);
  return $content;
}

/**
 * Get Page Field
 *
 * Retrieve and display the requested field from the given page. 
 * If the field is "content" it will call returnPageContent()
 *
 * @since 3.1
 * @param $page - slug of the page to retrieve content
 * @param $field - the Field to display
 * 
 */
function returnPageField($page,$field){   
	global $pagesArray;
	if ($field=="content"){
	  $ret=returnPageContent($page); 
	} else {
	  $ret=strip_decode(@$pagesArray[(string)$page][(string)$field]);
	} 
	return $ret;
}


/**
 * Get Page Children
 *
 * Return an Array of pages that are children of the requested page/slug
 *
 * @since 3.1
 * @param $page - slug of the page to retrieve content
 * 
 * @returns - Array of slug names 
 * 
 */
function getChildren($page){
	global $pagesArray;
	$returnArray = array();
	foreach ($pagesArray as $key => $value) {
	    if ($pagesArray[$key]['parent']==$page){
	      $returnArray[]=$key;
	    }
	}
	return $returnArray;
}

/**
 * Get Cached Pages XML Values
 *
 * Loads the Cached XML data into the Array $pagesArray
 * If the file does not exist it is created the first time. 
 *
 * @since 3.1
 *  
 */
function getPagesXmlValues(){
  global $pagesArray;
  $file=GSDATAPAGESPATH."pages.array";
  if (file_exists($file)){
  // load the xml file and setup the array. 
    $thisfile = file_get_contents($file);
    $data = simplexml_load_string($thisfile);
    $components = $data->item;
      foreach ($components as $component) {
        $key=$component->url;
        $pagesArray[(string)$key]=array();
        foreach ($component->children() as $opt=>$val) {
          if ($opt!="url"){
            $pagesArray[(string)$key][(string)$opt]=(string)$val;
          }
        }
        
      }
  } else {
    create_pagesxml('true');
    getPagesXmlValues();
  }
  
}

/**
 * Create the Cached Pages XML file
 *
 * Reads in each page of the site and creates a single XML file called 
 * data/pages/pages.array 
 *
 * @since 3.1
 *  
 */
function create_pagesxml($flag){
global $pagesArray;
global $plugin_info;

if ((isset($_GET['upd']) && $_GET['upd']=="edit-success") || $flag=true){

  $menu = '';
  $filem=GSDATAPAGESPATH."pages.array";

  $path = GSDATAPAGESPATH;
  $dir_handle = @opendir($path) or die("Unable to open $path");
  $filenames = array();
  while ($filename = readdir($dir_handle)) {
    $ext = substr($filename, strrpos($filename, '.') + 1);
    if ($ext=="xml"){
      $filenames[] = $filename;
    }
  }
  
  $count=0;
  $xml = @new SimpleXMLExtended('<channel></channel>');
  if (count($filenames) != 0) {
    foreach ($filenames as $file) {
      if ($file == "." || $file == ".." || is_dir(GSDATAPAGESPATH.$file) || $file == ".htaccess"  ) {
        // not a page data file
      } else {
        $thisfile = file_get_contents($path.$file);
        $data = simplexml_load_string($thisfile);
        $count++;   
        $id=$data->url;
        
        $components = $xml->addChild('item');
        $components->addChild('url', $id);
        $pagesArray[(string)$id]['url']=(string)$id;
        
        $note = $components->addChild('meta');
        $note->addCData($data->meta);
        $pagesArray[(string)$id]['meta']=(string)$data->meta;
   
        $note = $components->addChild('metad'); 
        $note->addCData($data->metad);     
        $pagesArray[(string)$id]['metad']=(string)$data->metad;
		
        $note = $components->addChild('menu');
        $note->addCData($data->menu);
        $pagesArray[(string)$id]['menu']=(string)$data->menu;

        $note = $components->addChild('title'); 
        $note->addCData($data->title);        
        $pagesArray[(string)$id]['title']=(string)$data->title;
        
        $note = $components->addChild('menuOrder'); 
        $note->addCData($data->menuOrder);        
        $pagesArray[(string)$id]['menuOrder']=(string)$data->menuOrder;
		
        $note = $components->addChild('menuStatus'); 
        $note->addCData($data->menuStatus);        
        $pagesArray[(string)$id]['menuStatus']=(string)$data->menuStatus;
		
        $note = $components->addChild('template');
        $note->addCData($data->template);        
        $pagesArray[(string)$id]['template']=(string)$data->template;
		
        $note = $components->addChild('parent');
        $note->addCData($data->parent);        
        $pagesArray[(string)$id]['parent']=(string)$data->parent;
		
        $note = $components->addChild('private'); 
        $note->addCData($data->private);        
        $pagesArray[(string)$id]['private']=(string)$data->private;
		
        $note = $components->addChild('pubDate');
        $note->addCData($data->pubDate);        
        $pagesArray[(string)$id]['pubDate']=(string)$data->pubDate;
		
        $note = $components->addChild('slug');
        $note->addCData($id);
        $pagesArray[(string)$id]['slug']=(string)$data->slug;
        
        $pagesArray[(string)$id]['filename']=$file;
        $note = $components->addChild('filename'); 
        $note->addCData($file);
        
        // Plugin Authors should add custome fields etc.. here
  			exec_action('caching-save');
	  
      } // else
    } // end foreach
  }   // endif      
  if ($flag==true){
    $xml->asXML($filem);
  }
}
}



?>