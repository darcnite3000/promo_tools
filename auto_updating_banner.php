<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', 1);
require_once('Cache/Lite.php');
$debug=false;
if($debug){

 echo '1';

$data = shell_exec('uname -a');
echo $data;
}
global $username;
global $password;
global $database;
global $logfile;
global $dbhost;
global $secure_root;
global $http_root;

function __DBopen() {
	global $username;
	global $password;
  global $dbhost;
	return mysql_connect($dbhost,$username,$password);
}

function __DBclose() {
	return mysql_close();
}

$cache_config = array(
	'cacheDir' => '/usr/www/cache/',
	'lifetime' => 86400,
	'automaticCleaningFactor' => 50,
	'hashedDirectoryLevel' => 2,
	'hashedDirectoryUmask' => 0700
);

$cache = new Cache_Lite($cache_config);
if($debug) echo '2<br>';
$selectedSites = "";
$selectedWidth = 0;
$selectedHeight = 0;
if ($_GET["selectedSites"]) {
	$selectedSites = $_GET["selectedSites"];
}
if ($_GET["width"]) {
	$selectedWidth = $_GET["width"];
}
if ($_GET["height"]) {
	$selectedHeight = $_GET["height"];
}
$traffic = (isset($_GET['niche'])) ? intval($_GET['niche']) : 3;

$secured = false;
if ($_SERVER['HTTPS']) {
	$secured = true;
} else {
	$secured = false;
}
$webid = $_GET['w'];
$program = $_GET['p'];
$camp = $_GET['c'];
$target = ($_GET['tr'] ? $_GET['tr'] : "_blank");
if (!$webid <> "") {
	$webid = "100342";
}
if (!$program <> "") {
	$program = "2";
}
$clr = ($_GET['clr'] == 'false' || $_GET['clr'] == 'no' || $_GET['clr'] == '0') ? false : true;
$styled = ($_GET['styled'] == 'false' || $_GET['styled'] == 'no' || $_GET['styled'] == '0') ? false : true;
$showAll = ($_GET['all'] == 'yes' || $_GET['all'] == 'true') ? true : false;
$shuffleOn = ($_GET['shuffle'] == 'yes' || $_GET['shuffle'] == 'true') ? true : false;
$wheresites = "";
$bStart = false;
$sites = explode(":", $selectedSites);
$sitelist = array();
foreach ($sites as $site) {
	if ($site == 18)
		$site = 4;
	if ($site != "selectedSites") {
		$namevalue = explode("|", $site);
		if ($namevalue[0] > 0) {
		  if($namevalue[0]==4) $namevalue[0]=101;
			$wheresites.= ( $bStart) ? " OR " : "";
			$wheresites .= " ((tblBanners.PaysiteID)=$namevalue[0]) ";
			$bStart = true;
			$sitelist[] = $namevalue[0];
		}
	}
}
if($wheresites==""){
  $wheresites=" ((tblPaysites.Flag_inAdmin) = 1) ";
}
$whereWidth = "";
if ($selectedWidth > 0) {
	$whereWidth = "(tblBanners.Width)='$selectedWidth' ";
}
$whereHeight = "";
if ($selectedHeight > 0) {
	$whereHeight = "(tblBanners.Height)='$selectedHeight' ";
}

$bStart = false;
$query = "SELECT * FROM tblBanners JOIN tblPaysites ON tblBanners.PaysiteID = tblPaysites.PaysiteID ";
$query.= "WHERE tblBanners.BannerTypeID = 1 ";
if ($wheresites != "") {
	$query.="AND ($wheresites) AND (tblPaysites.Flag_Public = 1) ";
	$bStart = true;
} else {
	$query .= "AND (tblPaysites.Flag_Public = 1)"; //AND ((tblBanners.PaysiteID)<>53)
	$bStart = true;
}
if ($whereWidth != "") {
	$query.="AND ($whereWidth) ";
	$bStart = true;
}
if ($whereHeight != "") {
	$query.="AND ($whereHeight) ";
	$bStart = true;
}
if ($whereHeight == "" && $whereWidth != "") {
	$query.="AND ((tblBanners.Width=468 AND tblBanners.Height=60) OR ";
	$query.="(tblBanners.Width=250 AND tblBanners.Height=250) OR ";
	$query.="(tblBanners.Width=300 AND tblBanners.Height=250) OR ";
	$query.="(tblBanners.Width=160 AND tblBanners.Height=600) OR ";
	$query.="(tblBanners.Width=728 AND tblBanners.Height=90) OR ";
	$query.="(tblBanners.Width=120 AND tblBanners.Height=450) OR ";
	$query.="(tblBanners.Width=120 AND tblBanners.Height=600) OR ";
	$query.="(tblBanners.Width=627 AND tblBanners.Height=126)) ";
}
$query.="AND ((tblBanners.PaysiteID <> 12 AND tblBanners.PaysiteID <> 13 AND tblBanners.PaysiteID <> 0 AND tblBanners.PaysiteID <> 1  AND tblBanners.PaysiteID <> 35 AND tblBanners.PaysiteID <> 27)) "; //AND (tblBanners.PaysiteID <> 18)
if ($traffic == 2) {
			$query.="AND ( tblPaysites.Niche=2 ) ";
		}
		if ($traffic == 1) {
			$query.="AND ( tblPaysites.Niche=1 ) ";
		}
$toolId = '10';
if(count($sitelist) == 1 && $sitelist[0] == 57){
$toolId = '11';
//$query.="AND tblBanners.AddDate > currentTS ";
//$query.="ORDER BY tblBanners.AddDate ASC LIMIT 1; ";
$query.="ORDER BY tblBanners.AddDate DESC LIMIT 1; ";
}else{
$query.="ORDER BY tblBanners.AddDate DESC LIMIT 4; ";
}
//echo $query;exit;
$hash = md5($query);
if($debug) echo '3<br>';
$rows = $cache->get($hash, 'aubTool');
if (!$clr && $rows) {
	$rows = unserialize($rows);
} else {
	__DBopen();
	global $database;
	@mysql_select_db($database);
	$result = mysql_query($query);
	$rows = array();

	while ($row = mysql_fetch_assoc($result)) {
		$rows[] = $row;
	}
	__DBclose();

	$cache->save(serialize($rows), $hash, 'aubTool');

}
?><?php
if ($styled) {
?><html>
		<head>
			<style type="text/css">
				body{
					background:transparent;
					padding:0px;
					margin:0px;
				}
			</style>
		</head>
		<body>
<?php
}
?><?
if($debug) echo '4<br>';
if (count($rows) > 0) {
if($debug) echo '5<br>';
//banner orororororo
	$bannerlist = array();
	$bannerfound = false;
	foreach ($rows as $row) {
		$bannerfound = true;
  //echo "<!--".$row['AddDate']."-->";

		$cursite = $row['PaysiteID'];
		$curid = $row['BannerID'];
		$folderSuffix = $row['FolderSuffix'];
		$currProgram = ($row['PaysiteID'] == 10 || $row['PaysiteID'] == 13 || $row['PaysiteID'] == 90) ? 2 : $program;
		if ($secured) {
			//$linkcode = $secure_root."/hit.php?w=$webid&s=$cursite&p=$program&c=$camp&t=0&cs=";
			$linkcode = $secure_root."/hit.php?w=$webid&s=$cursite&p=$currProgram&c=$camp&t=0&cs=&tool=".$toolId;
			$imgurl = $secure_root."/webmasters/promo/banners$folderSuffix/$curid";
		} else {
			$linkcode = $http_root."/hit.php?w=$webid&s=$cursite&p=$currProgram&c=$camp&t=0&cs=&tool=".$toolId;
			$imgurl = $http_root."/webmasters/promo/banners$folderSuffix/$curid";
		}

		$width = $row['Width'];
		$height = $row['Height'];

		$bannerlist[count($bannerlist)] = "<a href='$linkcode' target='$target'><img src='$imgurl' width=$width height=$height border=0></a>";
	}
	if (!$bannerfound) {
		echo "The banner '$curid' could not be found.";
	} else {
		if (!$showAll) {
			$randnum = rand(0, count($bannerlist) - 1);
			echo $bannerlist[$randnum];
		} else {
			if ($shuffleOn)
				shuffle($bannerlist);
			for ($i = 0; $i < count($bannerlist); $i++) {
				echo $bannerlist[$i];
			}
			//print_r($bannerlist);
		}
	} ?><!--sloaded--><?php
} else {
if($debug) echo '99<br>';
//no banner
	echo "No Banner of that Combination of Site, Height and Width exists.";
?><!--floaded--><?php
}
if ($styled) {
?>
	</body>
</html>
<?
}
exit;
?>
