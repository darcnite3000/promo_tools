<?php
error_reporting(E_ALL);
//ini_set("display_errors", "1");
include 'rss_img.php';
$title = "";
if(isset($_GET['t']) && $_GET['t']){
  $title = $_GET['t'];
}
$id="";
if(isset($_GET['i']) && $_GET['i']){
  $id = $_GET['i'];
}
$site_id = 4;
if(isset($_GET['s']) && $_GET['s']){
  $site_id = $_GET['s'];
}
$domain_id = "";
$packtype = 0; //0=va+fhg,1=fhg,2=va
if(isset($_GET['p']) && $_GET['p']){
  $packtype = $_GET['p'];
}
$overwrite = false;
if(isset($_GET['clr']) && $_GET['clr']){
  $overwrite = $_GET['clr']==1;
}
$main_scale = 0; //0=4:3,1=wide
if(isset($_GET['wide']) && $_GET['wide']){
  $main_scale = $_GET['wide'];
}


$outfile = dirname(__FILE__) . '/images/'.$id.'.jpg';
// print_r($outfile);exit;
$main_img = "";
$bottom_imgs = array();
$http_port = "";
$colors = array();
switch($site_id) {
	default:
	  $domain_id="SiteName.com";
    $colors = array("tcr"=>0xff,"tcg"=>0xff,"tcb"=>0xff,
                    "bgr"=>0x1b,"bgg"=>0x24,"bgb"=>0x4d);
		break;
}
switch($packtype){
  case 0:
  case 2:
    $main_img = "http://galleries.{$domain_id}{$http_port}/va/{$id}/images/tn_01.jpg";
    $bottom_imgs = array("left" => "http://galleries.{$domain_id}{$http_port}/va/{$id}/images/smallimage1.jpg",
                       "middle" => "http://galleries.{$domain_id}{$http_port}/va/{$id}/images/smallimage2.jpg",
                        "right" => "http://galleries.{$domain_id}{$http_port}/va/{$id}/images/smallimage3.jpg");
    break;
  case 1:
    //$main_img = "http://galleries.{$domain_id}:8080/{$id}/images/tn_01.jpg";
    $main_img = "http://galleries.{$domain_id}/{$id}{$http_port}/images/tn_01.jpg";
    break;
}
if($site_id==101) $site_id = 4;
$options = array(
"main" => $main_img,
"main_scale"=>$main_scale,
"logo" => "logos/logo{$site_id}.jpg",
"bottom" =>$bottom_imgs,
"color" => $colors
);

//print_r(wrap(11, 0, "arialbd_1.ttf", $title, (480)));exit;
// print_r($options);exit;
if($http_port!=''){
$http_port = "";
}
header('Content-Type: image/jpeg');
if (!file_exists($outfile) || $overwrite) {
 rss_img($title, $options, $outfile);
 chmod($outfile, 0666);
}else{

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
