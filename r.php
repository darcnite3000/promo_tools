<?php
  $image = $_GET['u'];
  $width = $_GET['w'];
  $height = $_GET['h'];
  $crop = $_GET['c'];
  $title = $_GET['t'];
  $qlty = ($_GET['q'])?$_GET['q']:75;
  function getname($url){
    $xurl = explode('/',$url);
    return $xurl[count($xurl)-1];
  }
  function getid($name){
    $xname = explode('.',$name);
    return $xname[0];
  }
  $id = getid(getname($image));
  
  $outfile = dirname(__FILE__) . '/reimages/' .($title != ''? $title :  $id ). '_' . $width . '_' . $height . ($crop > 0 ? '_crop' : '').'_'.$qlty. '.jpg';
  if (!file_exists($outfile)) {
      $in = @imagecreatefromjpeg($image);
      if(!$in){
      $out = imagecreatetruecolor($width, $height);
      $bgc = imagecolorallocate($out, 255, 255, 255);
      $tc  = imagecolorallocate($out, 255, 0, 0);

      imagefilledrectangle($out, 0, 0, 150, 30, $bgc);

        /* Output an error message */
      imagestring($out, 1, 5, 5, 'Error loading ' . $id, $tc);
      imagestring($out, 1, 5, 15, 'Try Refreshing.', $tc);
      $bfile = dirname(__FILE__) . '/reimages/blank_'. $width . '_' . $height . ($crop > 0 ? '_crop' : '') .'.jpg';
      imagejpeg($out,$bfile,$qlty);          
      header('Content-type: image/jpeg');
      header('Content-Length: ' . filesize($bfile));
      readfile($bfile);
      exit;
      }
      $in_width = imagesx($in);
      $in_height = imagesy($in);
      $width_ratio = $width / $in_width;
      $height_ratio = $height / $in_height;
      if ($crop == 1) {
          if ($width_ratio > $height_ratio) {
              $ratio = $width_ratio;
          } else {
              $ratio = $height_ratio;
          }
          $out_width = $in_width * $ratio;
          $out_height = $in_height * $ratio;
          $out = imagecreatetruecolor($width, $height);
          imagecopyresampled($out, $in, ($width - $out_width) / 2, ($height - $out_height) / 2, 0, 0, $out_width, $out_height, $in_width, $in_height);
      } else {
          if ($width_ratio < $height_ratio) {
              $ratio = $width_ratio;
          } else {
              $ratio = $height_ratio;
          }
          $out_width = $in_width * $ratio;
          $out_height = $in_height * $ratio;
          $out = imagecreatetruecolor($out_width, $out_height);
          imagecopyresampled($out, $in, 0, 0, 0, 0, $out_width, $out_height, $in_width, $in_height);
      }
      imagejpeg($out, $outfile,$qlty);
  }
  if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && !empty($_SERVER['HTTP_IF_NONE_MATCH'])) {
      $modified = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
      $old_etag = $_SERVER['HTTP_IF_NONE_MATCH'];
      if (($modified >= filemtime($outfile)) && ($old_etag == md5($outfile . filemtime($outfile) . filesize($outfile)))) {
          header('HTTP/1.0 304 Not Modified');
          header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (30 * 24 * 60 * 60)) . ' GMT'); // 30 days
          header('Cache-Control: max-age=' . (30 * 24 * 60 * 60) . ', must-revalidate'); // 30 days
          header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($outfile)) . ' GMT');
          header('Pragma: public');
          header('ETag: ' . md5($outfile. filemtime($outfile) . filesize($outfile)));
          exit;
      }
  }
  header('Content-type: image/jpeg');
  header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (30 * 24 * 60 * 60)) . ' GMT'); // 30 days
  header('Cache-Control: max-age=' . (30 * 24 * 60 * 60) . ', must-revalidate'); // 30 days
  header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($outfile)) . ' GMT');
  header('Pragma: public');
  header('ETag: ' . md5($outfile . filemtime($outfile) . filesize($outfile)));
  header('Content-Length: ' . filesize($outfile));
  readfile($outfile);
  exit;

?>
