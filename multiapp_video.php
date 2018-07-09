<?php
require_once('Cache/Lite.php');
global $cache;
global $username;
global $password;
global $database;
global $traffic;
global $secured;
global $cache_group;
global $http_root;

$cache_group = "trailer_iFrame";

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
$cache =& new Cache_Lite($cache_config);


$traffic=(isset($_GET['niche']))?intval($_GET['niche']):3;
$secured = false;
if($_SERVER['HTTPS']){
  $secured = true;
}else{
  $secured = false;
}

$webid = loadval($_GET['w']);
$program = loadval($_GET['p']);
$camp = loadval($_GET['c']);
$site_list = loadval($_GET['s']);
$pack_id = loadval($_GET['m']);
$media_format = strtolower(loadval($_GET['mf']));
$media_size = loadval($_GET['ms']);
$media_scale = loadval($_GET['mz']);
$tube = loadbool($_GET['tube']);
$hide_playlist = loadbool($_GET['hpl']);
$playlist_size = loadval($_GET['pls'],250);
$output_limit=10;
$toolId=20;

if (!$webid <> "") {
  $webid = "100342";
}

if (!$program <> "") {
  $program="2";
}

if(!$site_list <> ""){
  $site_list = loadval($_GET["selectedSites"]);
}else{
  $site_list = "selectedSites:".$site_list."|".(($site_list <= 0)?'All Sites':'');
}
// var_dump($site_list);exit;
$cache_id = join(':',array($webid,$program,$camp,$media_size,$media_scale,$media_format,$site_list,$pack_id));

if ($_GET['clr'] != "") {
  // echo "cleaning";exit;
  $cache->clean($cache_group);
}

$data = null;
if($cdata = getCache($cache_id)){
  // echo "cached";exit;
  $data = unserialize($cdata);
}else{
  // echo "un-cached";exit;
  __DBopen();
  @mysql_select_db($database);
  $query="SELECT tblPacks.PackID, tblPacks.PaysiteID, tblPaysites.ShortName, tblPaysites.PaysiteName, tblDomains.DomainID, tblPacks.Title, tblPacks.Desc, tblPacks.Marketing, tblPacks.PackDate,tblPacks.Flag_Pub,tblPacks.Flag_HasFLV, tblPacks.FLV_Formats, tblPacks.FLV_Scales, tblPacks.FLV_Sizes, tblPacks.Flag_HasTube, tblPacks.Flag_MP4Only, tblPacks.VIST_ID ";
  $query.="FROM tblDomains INNER JOIN (tblPaysites INNER JOIN tblPacks ON tblPaysites.PaysiteID = tblPacks.PaysiteID) ON tblDomains.DomainID = tblPaysites.DomainID ";
  $query.="WHERE (tblPacks.Flag_Pub = 1) AND (tblPacks.Flag_HasFLV = 1) AND (tblPacks.Flag_isTG = 0) ";
  $sites = array();
  if ($pack_id == '') {
    $boolbstart = true;
    $sites = explode(":", $site_list);
    $boolstart = true;
    foreach($sites as $site) {
      if($site == 18) $site = 4;
      if ($site != "selectedSites") {
        $namevalue = explode("|", $site);
        $namevalue[1] = urldecode($namevalue[1]);
        $namevalue[1] = stripslashes($namevalue[1]);
        if ($namevalue[0] > 0) {
          if($namevalue[0]==4) $namevalue[0]=101;
          if($boolstart){
            $boolstart = false;
            $query.= "AND (";
          }else{
            $query.= "OR ";
          }
          $query.= "tblPacks.PaysiteID = " . $namevalue[0] . " ";
        }
      }
    }
    if(!$boolstart){
      $query.= ") ";
      $boolstart = false;
    }else{
    }
  } elseif ($pack_id != "") {
    $query.="AND tblPacks.PackID = '" . $pack_id . "' ";
  }
  if($media_format <> ""){
    $query.="AND tblPacks.FLV_Formats LIKE '%" . $media_format . "%' ";
  }
  if($media_scale <> ""){
    $query.="AND tblPacks.FLV_Scales LIKE '%" . $media_scale . "%' ";
  }
  if($media_size <> ""){
    $query.="AND tblPacks.FLV_Sizes LIKE '%" . $media_size . "%' ";
  }


  if ($traffic == 2) {
    $query.="AND ( tblPaysites.Niche=2 ) ";
  }
  if ($traffic == 1) {
    $query.="AND ( tblPaysites.Niche=1 ) ";
  }
  if(count($sites)==0 || ($theid != "" && $otype == "m")){
    $query.=" AND (tblPaysites.Flag_Public = 1) ";
  }
  $query.=" ORDER BY tblPacks.PackDate DESC ";
  $query.=" LIMIT $output_limit;";
  // echo $query;exit;
  $result=mysql_query($query);
  if (!$result) {
    die('Invalid query: ' . mysql_error());
  }
  $output = array();
  while($row = mysql_fetch_assoc($result)){
    $rdata = array(
    'DomainID' => $row['DomainID'],
    'PaysiteName'=>$row['PaysiteName'],
    'ShortName'=>$row['ShortName'],
    'PaysiteID'=>$row['PaysiteID'],
    'PackID'=>$row['PackID'],
    'Title'=>$row['Title'],
    'Desc'=>$row['Desc'],
    'Keywords'=>$row['Marketing'],
    'PackDate'=>$row['PackDate'],
    'VIST_ID'=>$row['VIST_ID'],
    'TubeFormat'=>(loadbool($row['Flag_MP4Only'])?"mp4":"flv"),
    'Formats' => explode(',',$row['FLV_Formats'])
    );
    $output[] = $rdata;
  }
  __DBclose();
  $data = $output;
  $cdata = serialize($data);
  setCache($cdata,$cache_id);
}


function loadbool($val, $default = false){
  $val = loadval($val, $default);
  return ($val == true || $val == 1 || strtolower($val) == 'true' || strtolower($val) == 't' || strtolower($val) == '1');
}
function loadval($val, $default = ''){
  return (isset($val) && $val != '')?$val:$default;
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

function getCache($cacheID)
{
  global $cache;
  global $cache_group;
  if (isset($cache) && $data = $cache->get($cacheID,$cache_group)) {
    return $data;
  }
  return false;
}

function setCache($data, $cacheID)
{
  global $cache;
  global $cache_group;
  $cache->save($data, $cacheID,$cache_group);
}
$videos = array();
foreach ($data as $video) {
  $info = array();
  $info['label'] = "{$video['Title']}";
  $info['title'] = "{$video['Title']}";
  $info['description'] = "{$video['Desc']}";
  $info['pack_id'] = $video['PackID'];
  $info['ext'] = ($media_format!='flv' && in_array('mp4',$video['Formats']))?".mp4":".flv";
  $info['image'] = "http://galleries.{$video['DomainID']}/va/{$video['PackID']}/images/tn_01.jpg";

  if ($video['PaysiteID']==10) {
    $info['link'] = $http_root."/hit.php?w=" . $webid . "&s=10&p=2&c=" . $camp . "&t=0&cs=&tool=$toolId&u=".urlencode("http://store.ragingstallion.com/show.php?m=" . $video['VIST_ID']);
  } elseif ($video['PaysiteID']==12) {
    $info['link'] = $http_root."/hit.php?w=" . $webid . "&s=13&p=2&c=" . $camp . "&t=0&cs=&tool=$toolId&u=".urlencode("http://www.alphamales.com/show.php?m=" . $video['VIST_ID']);
  } elseif ($video['PaysiteID']==13) {
    $info['link'] = $http_root."/hit.php?w=" . $webid . "&s=13&p=2&c=" . $camp . "&t=0&cs=&tool=$toolId&u=".urlencode("http://www.gaydvd.com/show.php?m=" . $video['VIST_ID']);
  } elseif ($video['PaysiteID']==90) {
    $info['link'] = $http_root."/hit.php?w=" . $webid . "&s=" . $video['PaysiteID'] . "&p=2&c=" . $camp . "&t=0&cs=&tool=$toolId&u=";
  } else {
    $info['link'] = $http_root."/hit.php?w=" . $webid . "&s=" . $video['PaysiteID'] . "&p=" . $program . "&c=" . $camp . "&t=0&cs=&tool=$toolId&u=";
  }
  if(!$tube){
    if($video['DomainID']!="tylersroom.net"){
      $info['file'] = "http://videos.{$video['DomainID']}/{$video['PackID']}{$info['ext']}";
    }else{
      $info['file'] = "http://vids.{$video['DomainID']}/{$video['PackID']}{$info['ext']}";
    }
  }else{
    $info['ext'] = ".{$video['TubeFormat']}";
    $info['file'] = "http://galleries.{$video['DomainID']}/va/{$video['PackID']}/{$video['PackID']}{$info['ext']}";
  }
  $videos[] = $info;
}

?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-type" content="text/html; charset=utf-8">
  <title><?php echo $videos[0]['label']; ?></title>
  <style type="text/css" media="screen">
    html,body{
      height:100%;
      width:100%;
      padding: 0;
      margin: 0;
    }
    #frame_wrap{
      width:100%;
      height:100%;
      overflow:hidden;
      position:relative;
    }
    #player_overlay{
      position:absolute;
      display:block;
      bottom:24px;
      right:0;
      z-index: 2;
    }
    #player{
      position:relative;
      z-index:0;
    }
    #player_jwplayer_playlistcomponent ul {
    height: 100% !important;
    }
  </style>
</head>
<body>
  <div id="frame_wrap">
    <a id="player_overlay" href="<?php echo $videos[0]['link'];?>" target="_blank"></a>
    <div id="player"></div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script src="jwplayer.js" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript">
      $(function(){
        videos = <?php echo json_encode($videos); ?>;
        hide_playlist = <?php echo (($hide_playlist)?'true':'false'); ?>;
        playlist_width = <?php echo $playlist_size; ?>;
        updateOverlay = function(){
          var playlistItem = jwplayer('player').getPlaylistItem();
          $('title').text(playlistItem.label);
          $('#player_overlay').attr('href',playlistItem.link);
        }
        clearOverlay = function(){
          $('#player_overlay').css({
            width: '0%',
            height: '0%'
          });
          if(videos.length > 1){
            $('#player_overlay').css({
              right: playlist_width
            });
          }else{
            $('#player_overlay').css({
              right: 0
            });
          }
          $('body').off('click','#player_overlay');
        }
        setOverlay = function(){
          $('#player_overlay').css({
            width: '100%',
            height: '100%'
          });
          $('body').on('click','#player_overlay',{},function(event){
            jwplayer('player').pause(true);
            clearOverlay();
          });
        }
        jwplayer('player').setup({
          width:'100%',
          height:'100%',
          playlist: videos,
          'controlbar.position': 'bottom',
          <?php
          if(count($videos)>1 && !$hide_playlist){
            ?>
            'playlist.size':'250',
            'playlist.position':'right',
            <?php
          }
          ?>
          repeat: 'list',
          modes: [
          {type: 'html5'},
          {type: 'flash', src: 'jwplayer.swf'}
          ],
          events: {
            onBeforePlay: function(){
              clearOverlay();
              updateOverlay();
              setOverlay();
            },
            onPlaylistItem: function(index){
              updateOverlay();
            }
          }
        });
      });
    </script>
  </div>
</body>
</html>
