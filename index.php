<?php

define('ROOT', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));

if ( !file_exists(ROOT."/res/show.php") ) {
  header('Status: 301 Moved Permanently');
  header('Location: http://manga.passcod.net/');
  exit;
}

require ROOT."/res/show.php";

function check($GET_str, $regex) {
  if ( !preg_match($regex, $_GET[$GET_str]) ) {
    throw new Exception('Invalid input.');
  }
}

if ( !empty($_GET['manga']) && !empty($_GET['chapter']) ) {
  $show = 2;
  
  try {
    check('manga', "/^[a-z0-9\-]+$/i");
    check('chapter', "/^[0-9]+$/i");
    
    $pc = str_pad($_GET['chapter'], 3, "0", STR_PAD_LEFT);
    
    if ( !file_exists(ROOT."/dat/{$_GET['manga']}/$pc") ) {
      throw new Exception('Not Found');
    }
    
  } catch(Exception $e) {
    $show = 0;
  }
} elseif ( !empty($_GET['manga']) ) {
  $show = 1;
  
  try {
    check('manga', "/^[a-z0-9\-]+$/i");
    
    if ( !file_exists(ROOT."/dat/{$_GET['manga']}") ) {
      throw new Exception('Not Found');
    }
    
  } catch(Exception $e) {
    $show = 0;
  }
} else {
  $show = 0;
}

switch($show) {
  case 2:
    show("chapter", array_merge(getMangaInfo($_GET['manga']), getChapterInfo($_GET['manga'], (int)$_GET['chapter'])));
    break;
  
  case 1:
    $array = getMangaInfo($_GET['manga']);
    $array['chapters'] = getPartial('chapter', getChapterList($_GET['manga']));
    show("manga", $array);
    break;
  
  default:
  case 0:
    show("index", array(
      "mangas"=>getPartial('shelf', getMangaList())
    ));
    break;
}

?>