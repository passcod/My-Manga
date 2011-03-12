<?php

function show($template_name, $param_array = array()) {
  if ( in_array('ajax', array_keys($_GET)) ) {
    echo json_encode($param_array);
    exit;
  }
  
  $path = ROOT."/res/template/$template_name.html";
  
  if ( !file_exists($path) ) {
    throw new Exception("Template file not found: '$template_name'");
  }
  
  $file = file_get_contents($path);
  
  foreach ( $param_array as $key => $val ) {
    $file = str_replace("<$key />", $val, $file);
  }
  
  echo $file;
  exit;
}

function getMangaInfo($manga) {
  $info = json_decode( file_get_contents(ROOT."/dat/$manga/info"), true );
  
  $info['href'] = "/$manga/";
  $info['cover-href'] = "/dat/$manga/{$info['cover']}";
  $info['title-caps'] = strtoupper($info['title']);
  
  foreach ( $info as $key => $val ) {
    $data['manga-'.$key] = $val;
  }
  
  return $data;
}

function getChapterInfo($manga, $chapter) {
  $pc = str_pad($chapter, 3, "0", STR_PAD_LEFT);
  $info = json_decode( file_get_contents(ROOT."/dat/$manga/$pc/info"), true );
  
  $info['href'] = "/$manga/$chapter/";
  $info['title-caps'] = strtoupper($info['title']);
  $info['first-page-href'] = "/dat/$manga/$pc/".$info['first-page'];
  
  foreach ( $info as $key => $val ) {
    $data['chapter-'.$key] = $val;
  }
  
  return $data;
}

function getPartial($name_str, $data_array) {
  $prefix = $name_str.'-';
  
  $path = ROOT."/res/partial/$name_str.html";
  
  $file = file_get_contents($path);
  $part = "";
  $done = "";
  
  foreach ( $data_array as $sub_array ) {
    $part = $file;
    
    foreach ( $sub_array as $key => $val ) {
      $part = str_replace("<$prefix$key />", $val, $part);
    }
    
    $done .= $part;
  }
  
  return $done;
}

/****************/

function getMangaList() {
  $list = array();
  
  $path = ROOT."/dat";
  $dir_handle = @opendir($path);
  while ( false !== ($file = readdir($dir_handle)) ) {
    if ( $file!="." && $file!=".." ) {
      if ( is_dir($path."/".$file) ) {
        if ( preg_match("/^[a-z][a-z0-9\-]+$/i", $file) ) {
          $info = json_decode( file_get_contents(ROOT."/dat/$file/info"), true );
          
          $info['href'] = "/$file/";
          $info['cover-href'] = "/dat/$file/{$info['cover']}";
          $info['title-caps'] = strtoupper($info['title']);
          
          $list[$file] = $info;
        }
      }
    }
  }

  closedir($dir_handle);
  
  usort($list, 'sortByDate');
  
  return $list;
}

function getChapterList($manga) {
  $list = array();
  
  $path = ROOT."/dat/$manga";
  $dir_handle = @opendir($path);
  while ( false !== ($file = readdir($dir_handle)) ) {
    if ( $file!="." && $file!=".." ) {
      if ( is_dir($path."/".$file) ) {
        if ( preg_match("/^[0-9]{3}$/i", $file) ) {
          $chapter = (int)$file;
          $info = json_decode( file_get_contents(ROOT."/dat/$manga/$file/info"), true );
          
          $info['href'] = "/$manga/$chapter/";
          $info['title-caps'] = strtoupper($info['title']);
          $info['first-page-href'] = "/$manga/$chapter/".$info['first-page'];
          
          $list[$file] = $info;
        }
      }
    }
  }

  closedir($dir_handle);
  
  usort($list, 'sortByDate');
  
  return $list;
}


/****************/

function sortByDate($a, $b) {
  if ( empty($a['date']) ) {
    $a['date'] = "00-00-0000";
  }
  
  if ( empty($b['date']) ) {
    $b['date'] = "00-00-0000";
  }
  
  $ad = strtotime($a['date']);
  $bd = strtotime($b['date']);
  
  if ( $ad == $bd ) {
    return 0;
  } elseif ( $ad > $bd ) {
    return 1;
  } else {
    return -1;
  }
}

?>
