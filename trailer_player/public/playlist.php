<?php
// error_reporting(E_ALL);
require 'Cache/Lite.php';
$clr=false;

global $username;
global $password;
global $dbhost;
global $secure_root;

$cache_config = array(
  'cacheDir'=>'/usr/www/cache/',
  'caching'=>true,
  'lifetime'=>86400,
  'fileLocking'=>true,
  'writeControl'=>true,
  'readControl'=>true,
  'readControlType'=>'crc32',
  'memoryCaching'=>false,
  'onlyMemoryCaching'=>false,
  'memoryCachingLimit'=>1000,
  'fileNameProtection'=>true,
  'automaticSerialization'=>true,
  'automaticCleaningFactor'=>1,
  'hashedDirectoryLevel'=>2,
  'hashedDirectoryUmask'=>0700
);

$cache = &new Cache_Lite($cache_config);

$conf = array(
  'sites' => (isset($_REQUEST['sites']) ? $_REQUEST['sites'] : array()),
  'pack' => (isset($_REQUEST['pack']) && $_REQUEST['pack']!='' ? $_REQUEST['pack'] : null),
  'scale' => (isset($_REQUEST['scale']) && $_REQUEST['scale']!='' ? $_REQUEST['scale'] : null),
  'limit' => (isset($_REQUEST['limit']) && $_REQUEST['limit']!='' ? intval($_REQUEST['limit'],10) : 10),
  'tube'=> (isset($_REQUEST['tube']) && intval($_REQUEST['tube']) == 1)
);
// var_dump($_REQUEST['sites']);exit;
$query="SELECT tblPacks.PackID, tblPacks.PaysiteID, tblPaysites.PaysiteName, tblPaysites.DomainID, tblPaysites.ShortName, tblPacks.Title, tblPacks.Desc, tblPacks.Marketing, tblPacks.PackDate, tblPacks.MPA_FHG, tblPacks.MPA_VA, tblPacks.VIST_ID, tblPacks.Flag_Pub, tblPacks.Flag_HasFLV, tblPacks.FLV_Formats, tblPacks.FLV_Scales, tblPacks.FLV_Sizes, tblPacks.Flag_HasTube ";
$query.="FROM tblPaysites INNER JOIN tblPacks ON tblPaysites.PaysiteID = tblPacks.PaysiteID ";
$query.="WHERE (tblPacks.Flag_Pub = 1) AND (tblPacks.Flag_HasFLV = 1) AND (tblPacks.Flag_isTG = 0) AND (tblPaysites.Flag_inAdmin = 1) AND (tblPaysites.Flag_Public = 1) ";
$siteFilter = implode(' OR ', array_map(querySiteWrap, $conf['sites']));
if($siteFilter!=''){
  $query.="AND ($siteFilter) ";
}
if($conf['pack']){
  $query.="AND (tblPacks.PackID = '{$conf['pack']}') ";
}
// if($conf['scale']){
//   $query.="AND tblPacks.FLV_Scales LIKE '%{$conf['scale']}%' ";
// }
$query.="ORDER BY tblPacks.PackDate DESC ";
$query.="LIMIT {$conf['limit']} ";
// var_dump($query);exit;
$cachekey = md5($query.($conf['tube']?1:0));
$videoList = array();

$data = getCache($cache, $cachekey);
if(!$clr && $data !== false){
  $videoList = json_decode($data,true);
}else{
  __DBopen();
  @mysql_select_db($database);
  $result=mysql_query($query);
  while ($row=mysql_fetch_assoc($result)) {
    $video = array(
      'id'=>$row['PackID'],
      'title'=>$row['Title'],
      'src'=> array(getVideoSrc($row,$conf)),
      'poster'=> getVideoImg($row),
      'description'=>$row['Desc'],
      'date'=>$row['PackDate'],
      'timestamp'=>strtotime($row['PackDate']),
      'paysite'=> array(
        'id' => $row['PaysiteID'],
        'domain'=>$row['DomainID'],
        'name'=>$row['PaysiteName'],
        'shortName'=>$row['ShortName'],
        )
    );
    $videoList[] = $video;
  }
  mysql_free_result($result);
  __DBclose();

  setCache($cache,json_encode($videoList), $cachekey);
}

$totalVideos = count($videoList);

header('Content-type: application/json');
echo json_encode(array(
  'totalCount'=>$totalVideos,
  'videos'=>$videoList
));

exit;

function querySiteWrap($siteId){
  if($siteId!=''){
    return "tblPacks.PaysiteID = $siteId";
  }
}

function getCache($cache, $cacheID, $region = 'gbTrailer'){
  if(isset($cache) && $data = $cache->get($cacheID,$region)){
    return $data;
  }
  return false;
}
function setCache($cache, $data, $cacheID, $region = 'gbTrailer'){
  $cache->save($data,$cacheID,$region);
}
function getVideoImg($pack){
  global $secure_root;
  if($pack['Flag_HasTube'] == 1){
    $imgurl = "http://galleries.{$pack['DomainID']}/va/{$pack['PackID']}/images/tn_01.jpg";
  }else{
    $imgurl = "http://galleries.{$pack['DomainID']}/{$pack['PackID']}/images/tn_01.jpg";
  }
  return $secure_root."/secimages/?t=va_{$pack['PackID']}&u=".urlencode($imgurl);
}

function getVideoSrc($pack,$conf){
  $isTube = ($conf['tube'] && $pack['Flag_HasTube'] == 1);
  $scale = ($conf['scale']) ? "_{$conf['scale']}" : "";
  if($isTube){
    return "http://galleries.{$pack['DomainID']}/va/{$pack['PackID']}/{$pack['PackID']}.mp4";
  }
}

function __DBopen(){
  global $username;
  global $password;
  global $dbhost;
  return mysql_connect($dbhost,$username,$password);
}

function __DBclose(){
  return mysql_close();
}

