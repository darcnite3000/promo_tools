<?php

function rss_img($title, $options, $output)
{
    /*
Image layout:
----------------------------------------
title
main
logo
bottom_left bottom_middle bottom_right
----------------------------------------

Assumptions:
* All the images will exist.
* The images are valid and the sizes are correct.
* The title won't go off the image.
* The params are valid.
* All these functions will succeed. . . .
    */
    $is_bottom = (count($options['bottom'])==3);
    //return $is_bottom;
    // Hard-coded, don't change.
    $main_scale = $options['main_scale'];
    $logo_img = imagecreatefromjpeg($options["logo"]);
    if(!$logo_img) return failImage('Cannot load logo');
    $logo_img_height = imagesy($logo_img);
    $main_img = imagecreatefromjpeg($options["main"]);
    if(!$main_img) return failImage('Cannot load main img');

    if($is_bottom){
      $bottom_left_img = imagecreatefromjpeg($options["bottom"]["left"]);
      if(!$bottom_left_img){
        $is_bottom=false;
      }else{
        $bl_width = imagesx($bottom_left_img);
        $bl_height = imagesy($bottom_left_img);
      }
      $bottom_middle_img = imagecreatefromjpeg($options["bottom"]["middle"]);
      if(!$bottom_middle_img){
        $is_bottom=false;
      }else{
        $bm_width = imagesx($bottom_middle_img);
        $bm_height = imagesy($bottom_middle_img);
      }
      $bottom_right_img = imagecreatefromjpeg($options["bottom"]["right"]);
      if(!$bottom_right_img){
        $is_bottom=false;
      }else{
        $br_width = imagesx($bottom_right_img);
        $br_height = imagesy($bottom_right_img);
      }
    }


    $rss_width = 480;
    $font_path = "./arial-bold.ttf";
    $font_size = 11;
    $title_x = 7;
    $title_tb_margin = 8;
    $title_spacing = 5;
    $title_y = $title_tb_margin+$font_size;
    $wtitle = wrap($font_size, 0, $font_path, $title, ($rss_width-($title_y*2)));
    $atitle = explode("\n", $wtitle);
    $title_lines = count($atitle);
    $title_height = ($title_lines*$font_size)+($title_spacing*($title_lines-1))+($title_tb_margin*2);
    $bottom_height = ($is_bottom)?106:0;
    $main_height = (($main_scale!=1)?360:270);
    $logo_height = $logo_img_height;
    $rss_height = $title_height+$main_height+$logo_height+$bottom_height;
    $bottom_x_width = 160;

    $tcr = (isset($options['color']['tcr']))?$options['color']['tcr']:0xff;
    $tcg = (isset($options['color']['tcg']))?$options['color']['tcg']:0xff;
    $tcb = (isset($options['color']['tcb']))?$options['color']['tcb']:0xff;
    $bgr = (isset($options['color']['bgr']))?$options['color']['bgr']:0x1b;
    $bgg = (isset($options['color']['bgg']))?$options['color']['bgg']:0x24;
    $bgb = (isset($options['color']['bgb']))?$options['color']['bgb']:0x4d;
    $rss_img = imagecreatetruecolor($rss_width, $rss_height);
    $white = imagecolorallocate($rss_img, $tcr, $tcg, $tcb);
    $bg = imagecolorallocate($rss_img, $bgr, $bgg, $bgb);
    // echo "here";exit;
    // Create images.
    if($is_bottom){
      $bottom_img = imagecreatetruecolor($rss_width, $bottom_height);
      imagecopyresampled($bottom_img, $bottom_left_img, $bottom_x_width * 0, 0, 0, 0, $bottom_x_width, $bottom_height, $bl_width, $bl_height);
      imagecopyresampled($bottom_img, $bottom_middle_img, $bottom_x_width * 1, 0, 0, 0, $bottom_x_width, $bottom_height, $bm_width, $bm_height);
      imagecopyresampled($bottom_img, $bottom_right_img, $bottom_x_width * 2, 0, 0, 0, $bottom_x_width, $bottom_height, $br_width, $br_height);
    }

    // Paint rss_img background.
    imagefilledrectangle($rss_img, 0, 0, $rss_width - 1, $rss_height - 1, $bg);

    // Paint title.
    for($k = 0; $k < $title_lines; $k++){
      imagefttext($rss_img, $font_size, 0, $title_x, $title_y+(($font_size+$title_spacing)*$k), $white, $font_path, stripslashes($atitle[$k]));
    }

    $location=$rss_height;
    // Overlay bottom image.
    if($is_bottom){
    $location = $rss_height - $bottom_height;
    imagecopy($rss_img, $bottom_img, 0, $location, 0, 0, $rss_width, $bottom_height);
    }

    // Overlay logo.
    $location -= $logo_height;
    imagecopy($rss_img, $logo_img, 0, $location, 0, 0, $rss_width, $logo_height);

    // Overlay main.
    $location -= $main_height;
    $main_img_width = imagesx($main_img);
    $main_img_height = imagesy($main_img);
    imagecopyresampled($rss_img, $main_img, 0, $location, 0, 0, $rss_width, $main_height, $main_img_width, $main_img_height);
    if($output!=NULL){
    $aoutput = explode('/',$output);
    $boutput = explode('_',$aoutput[count($aoutput)-1]);
    if(!in_array("tn", $boutput)){
      if($boutput[0]>0){
        $boutput[1].="_tn";
      }else{
        $boutput[0].="_tn";
      }
      $aoutput[count($aoutput)-1] = implode('_',$boutput);
      $tnoutput = implode('/',$aoutput);
      imagejpeg($rss_img, $tnoutput, 90);
      chmod($tnoutput, 0666);
    }
    }
    // echo "here";exit;

    return imagejpeg($rss_img, $output, 90); //85 high quality, return image binary.
    //return $rss_img;
}

function wrap($fontSize, $angle, $fontFace, $string, $width){
    $ret = "";
    $arr = explode(' ', $string);

    foreach ( $arr as $word ){
        $teststring = $ret.' '.$word;
        $testbox = imagettfbbox($fontSize, $angle, $fontFace, $teststring);
        if ( $testbox[2] > $width ){
            $ret.=($ret==""?"":"\n").$word;
        } else {
            $ret.=($ret==""?"":' ').$word;
        }
    }
    return $ret;
}

function failImage($mess=""){
  $width = 480;
  $height = 360;
  $font_path = "./arial-bold.ttf";
  $font_size = 11;
  $title_x = 7;
  $title_tb_margin = 8;
  $title_spacing = 5;
  $title_y = $title_tb_margin+$font_size;
  $out = imagecreatetruecolor($width, $height);
  $bgc = imagecolorallocate($out, 255, 255, 255);
  $tc  = imagecolorallocate($out, 255, 0, 0);
  imagefttext($out, $font_size, 0, $title_x, $title_y+(($font_size+$title_spacing)*0), $tc, $font_path, 'Error loading: '.$mess);
  imagefttext($out, $font_size, 0, $title_x, $title_y+(($font_size+$title_spacing)*1), $tc, $font_path, 'Try Refreshing.');
  $bfile = dirname(__FILE__) . '/images/blank_'. $width . '_' . $height . ($crop > 0 ? '_crop' : '') .'.jpg';
  imagejpeg($out,$bfile,100);
  header('Content-type: image/jpeg');
  header('Content-Length: ' . filesize($bfile));
  readfile($bfile);
  exit;
}
?>
