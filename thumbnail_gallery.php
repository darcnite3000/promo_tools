<?

error_reporting(E_ERROR | E_WARNING | E_PARSE);
require_once('Cache/Lite.php');

global $username;
global $password;
global $database;
global $dbhost;
global $secure_root;
global $http_root;


global $traffic;

$traffic=(isset($_GET['niche']))?intval($_GET['niche']):3;

function __DBopen()
{
  global $username;
  global $password;
  global $dbhost;
  return mysql_connect($dbhost,$username,$password);
}


function __DBclose()
{
  return mysql_close();
}

function getPacks($theid,$otype)
{
  try {
    __DBopen();

    global $database;
    global $traffic;

    @mysql_select_db($database);
    $query="SELECT tblPacks.PackID, tblPacks.CategoryID, tblCategories.Category, tblPacks.PaysiteID, tblPaysites.ShortName, tblDomains.DomainID, tblPacks.Title, tblPacks.Desc, tblPacks.Marketing, tblPacks.Notes, tblPacks.PackDate, tblPacks.Flag_SMailOut, tblPacks.Flag_SMailRec, tblPacks.Flag_Clipped, tblPacks.MPA_FHG, tblPacks.MPA_VA, tblPacks.VIST_ID, tblPacks.Flag_Creative, tblPacks.Flag_AutoBan, tblPacks.Flag_Rdy, tblPacks.Flag_Pub, tblPacks.Flag_Email, tblPacks.Flag_HasFLV, tblPacks.Flag_HasTube, tblPacks.Flag_HasZIP ";
    $query.="FROM tblDomains INNER JOIN (tblPaysites INNER JOIN (tblCategories INNER JOIN tblPacks ON tblCategories.CategoryID = tblPacks.CategoryID) ON tblPaysites.PaysiteID = tblPacks.PaysiteID) ON tblDomains.DomainID = tblPaysites.DomainID ";
    $query.="WHERE ";
    $boolbstart = true;
    $sites = explode(":", $theid);
    $boolstart = true;
    foreach ($sites as $site) {
      if($site==18)$site=4;
      if ($site != "selectedSites") {
        $namevalue = explode("|", $site);
        $namevalue[1] = urldecode($namevalue[1]);
        $namevalue[1] = stripslashes($namevalue[1]);
        if ($namevalue[0] > 0) {
          if($namevalue[0]==4) $namevalue[0]=101;
          if($boolstart){
            $boolstart = false;
            $query.= "(";
          }else{
            $query.= "OR ";
          }
          $query.= "tblPacks.PaysiteID = " . $namevalue[0] . " ";
        }
      }
    }
    if (!$boolstart){
      $query.= ") AND ";
      $boolstart = false;
    }
    else {
      ///    $query.= " AND tblPacks.PaysiteID <> 18 ";
    }
    $query.="(tblPacks.Flag_Pub = 1) AND ((tblPacks.Flag_isTG)=0) ";//
    if ($otype == "va"){
      $query.="AND ((NOT(tblPacks.MPA_VA = ''))) ";
    }
    else if ($otype == "fhg"){
      $query.="AND ((NOT(tblPacks.MPA_FHG = ''))) ";
    }
    else {
      $query.="AND ((NOT(tblPacks.MPA_VA = '')) OR  (NOT(tblPacks.MPA_FHG = ''))) ";
    }
    if ($traffic == 2) {
      $query.="AND ( tblPaysites.Niche=2 ) ";
    }
    if ($traffic == 1) {
      $query.="AND ( tblPaysites.Niche=1 ) ";
    }
    if (count($sites)==0) {
		  $query.="AND (tblPaysites.Flag_Public = 1) ";
      $query.="AND ((tblPaysites.Flag_inAdmin) = 1) ";
    }
    $query.="ORDER BY tblPacks.PackDate DESC;";
    $result=mysql_query($query);
    __DBclose();

    return $result;

  } catch (Exception $e) {
    $err = mysql_error();
    if ($err != "") {
      return $err;
    }
    else {
      return $e;
    }
  }
}


function url_exists($url)
{
  $a_url = parse_url($url);
  if (!isset($a_url['port'])) $a_url['port'] = 80;
  $errno = 0;
  $errstr = '';
  $timeout = 1;
  if(isset($a_url['host']) && $a_url['host']!=gethostbyname($a_url['host'])){
    $fid = fsockopen($a_url['host'], $a_url['port'], $errno, $errstr, $timeout);
    if (!$fid) return false;
    $page = isset($a_url['path'])  ?$a_url['path']:'';
    $page .= isset($a_url['query'])?'?'.$a_url['query']:'';
    fputs($fid, 'HEAD '.$page.' HTTP/1.0'."\r\n".'Host: '.$a_url['host']."\r\n\r\n");
    $head = fread($fid, 4096);
    $head = substr($head,0,strpos($head, 'Connection: close'));
    fclose($fid);

    //echo "|" . $head . "|<BR>\n";

      if (preg_match('#^HTTP/.*\s+[404]+\s#i', $head)) {
        return false;
      } elseif (preg_match('#^HTTP/.*\s+[200|206|302]+\s#i', $head)) {
        return true;
      } else {
        return false;
      }

      if (preg_match('#^HTTP/.*\s+[200|206|302]+\s#i', $head)) {
        $pos = strpos($head, 'Content-Type');
        return $pos !== false;
      }
    } else {
      return false;
    }
  }

  function getCache($cacheID)
  {
    global $cache;

    if (isset($cache) && $data = $cache->get($cacheID,'thumbgal')) {
      return $data;
    }

    return false;
  }

  function setCache($data, $cacheID)
  {
    global $cache;

    $cache->save($data, $cacheID,'thumbgal');
  }

  function getContent($data, $webid, $program, $camp,$limit,$galtype, $style,$target,$height,$width,$quality)
  {
    global $secure_root;
    global $http_root;
    $array = array();
    $toolId='12';
    $secured = false;
    if($_SERVER['HTTPS']){
      $secured = true;
    }else{
      $secured = false;
    }
    $x = 0;
    __DBopen();
    global $database;
    @mysql_select_db($database);
    //echo mysql_num_rows($data);exit;


    if($style == "latest"){
      while($row = mysql_fetch_assoc($data)){
        $array[] = $row;
        if ($x < $limit) {
          if($galtype == 'both'){
            if($row['MPA_VA'] != "" && $row['MPA_FHG'] != ""){
              $curtype = (rand(1, 2) == 1)?"va":"fhg";
            }else if($row['MPA_VA'] != ""){
              $curtype = 'va';
            }else{
              $curtype = 'fhg';
            }
          }else{
            $curtype = $galtype;
          }
          if ($curtype == "va") {
            $mpaid = $row['MPA_VA'];
            $thisThumbURL = "http://galleries.".$row['DomainID']."/va/".$row['PackID']."/images/tn_01.jpg";
          } else {
            $mpaid = $row['MPA_FHG'];
            $thisThumbURL = "http://galleries.".$row['DomainID']."/".$row['PackID']."/images/tn_01.jpg";

          }
          //if($row['PaysiteID'] == 49){
            //$thisThumbURL = str_replace('galleries', 'g', $thisThumbURL);
            //echo "<!--replaced-->";
            //}
            $paysiteid = $row['PaysiteID'];

            $currProgram=($row['PaysiteID'] == 10 || $row['PaysiteID'] == 13 || $row['PaysiteID'] == 90)?2:$program;
            $packid = $row['PackID'];

            if($secured){
              $thisLinkURL = $secure_root."/gallhit.php?".$webid.",".$mpaid.",".$paysiteid.",".$currProgram.",0,".$camp.",".$toolId;
            }else{
                $thisLinkURL = $http_root."/gallhit.php?".$webid.",".$mpaid.",".$paysiteid.",".$currProgram.",0,".$camp.",".$toolId;
                }
                $title = urlencode($row['Title']);
                echo "<a href='$thisLinkURL' class='galimg' target=\"$target\"><img src='r.php?u=$thisThumbURL&w=$width&h=$height&q=$quality&c=1&t=".$packid."' alt='$title' title='$title' border=0/></a>";
                $x++;
              }else{
                //break;
              }
            }


          }else{
            $dbarray = Array();
            while($row = mysql_fetch_assoc($data)){
              $dbarray[] = $row;
            }
            $len = count($dbarray);
            //echo  $len;exit;////
            $holdarray = Array();
            while(count($holdarray) < $limit){
              $pos = rand(0, $len-1);
              if(!in_array($pos , $holdarray)){
                $holdarray[] = $pos;
                $row = $dbarray[$pos];
                if($galtype == 'both'){
                  if($row['MPA_VA'] != "" && $row['MPA_FHG'] != ""){
                    $curtype = (rand(1, 2) == 1)?"va":"fhg";
                  }else if($row['MPA_VA'] != ""){
                    $curtype = 'va';
                  }else{
                    $curtype = 'fhg';
                  }
                }else{
                  $curtype = $galtype;
                }
                if ($curtype == "va") {
                  $mpaid = $row['MPA_VA'];
                  $thisThumbURL = "http://galleries.".$row['DomainID']."/va/".$row['PackID']."/images/tn_01.jpg";
                } else {
                  $mpaid = $row['MPA_FHG'];
                  $thisThumbURL = "http://galleries.".$row['DomainID']."/".$row['PackID']."/images/tn_01.jpg";
                }
                //if($row['PaysiteID'] == 49){
                  //$thisThumbURL = str_replace('galleries', 'g', $thisThumbURL);
                  //echo "<!--replaced-->";
                  //}
                  $paysiteid = $row['PaysiteID'];
                  if($secured){
                    $thisLinkURL = $secure_root."/gallhit.php?".$webid.",".$mpaid.",".$paysiteid.",".$program.",0,".$camp.",".$toolId;
                  }else{
                    $thisLinkURL = $http_root."/gallhit.php?".$webid.",".$mpaid.",".$paysiteid.",".$program.",0,".$camp.",".$toolId;
                  }
                  $title = urlencode($row['Title']);
                  echo "<a href='$thisLinkURL' class='galimg' target=\"$target\"><img src='r.php?u=$thisThumbURL&w=$width&h=$height&q=$quality&c=1&t=".$row['PackID']."' alt='$title' title='$title' border=0/></a>";
                }
              }
            }
            __DBclose();

          }

          $secured = false;

          if ($_SERVER['HTTPS']) {
            $secured = true;
          }
          else{
            $secured = false;
          }

          $lifetime = 604800;
          $webid = ($_GET['w'] ? $_GET['w'] : $ourwebid);
          $program = $_GET['p'];
          $camp = $_GET['c'];
          $site = $_GET['s'];
          $galtype = $_GET['t'];
          $galstyle = $_GET['st'];
          $limit = ($_GET['l'] ? $_GET['l'] : 15);
          $target = ($_GET['tr'] ? $_GET['tr'] : "_blank");
          $height=($_GET['height'])?$_GET['height']:90;
          $width=($_GET['width'])?$_GET['width']:90;
          $quality=($_GET['quality'])?$_GET['quality']:90;
          $pleft=($_GET['pl']?$_GET['pl']:'3px');
          $ptop=($_GET['pt']?$_GET['pt']:'3px');
          $pright=($_GET['pr']?$_GET['pr']:'3px');
          $pbottom=($_GET['pb']?$_GET['pb']:'3px');


          if (!$webid <> "") {
            $webid = "100342";
          }
          if (!$program <> "") {
            $program="2";
          }

          if(!$site <> ""){
            if ($_GET["selectedSites"]) {
              $site = $_GET["selectedSites"];
            }
          }else{
            $site = "selectedSites:".$site."|";
          }
          switch(strtolower($galtype)){
            case "fhg":
            $galtype = "fhg";
            break;
            case "va":
            $galtype = "va";
            break;
            default:
            $galtype = "both";
          }
          switch(strtolower($galstyle)){
            case "random":
            $galstyle = "random";
            $lifetime = 60;
            break;
            default:
            $galstyle = "latest";
            $lifetime = 259200;
          }


          $cacheID = urlencode($webid.$program.$site.$width.$height.$limit.$galtype.$galstyle.$ptop.$pright.$pbottom.$pleft.$secured.$traffic);

          $bgcolor = ($_GET['bg'] ? $_GET['bg'] : $dbgcolor);
          $textcolor = ($_GET['tx'] ? $_GET['tx'] : $dtextcolor);
          $linkcolor = ($_GET['lk'] ? $_GET['lk'] : $dlinkcolor);
          $scrollcolor = ($_GET['sc'] ? $_GET['sc'] : $dlinkcolor);

          ////
          $cache_config = array(
          'cacheDir'=>'/usr/www/cache/',
          'caching'=>true,
          'lifetime'=>$lifetime,
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

          if ($_GET['clr'] != "") {
            $cache->clean('thumbgal');
          }

          if ($data = getCache($cacheID)) {
            echo $data;
            exit;
          }

          $packlist = getPacks($site,$galtype);

          ob_start();

          ?>
          <html>
          <head>
            <style type="text/css">
              body{
                background:transparent;
                padding:0px;
                margin:0px;
              }

              .galimg{
                display:block;
                float:left;
                padding: <?php echo $ptop." ".$pright." ".$pbottom." ".$pleft."";?>;
              }
            </style>
          </head>
          <body>
            <?
            getContent($packlist, $webid, $program, $camp,$limit,$galtype,$galstyle,$target,$height,$width,$quality);
            ?><!--sloaded-->
          </body>
          </html>
          <?

          $data = ob_get_contents();

          setCache($data, $cacheID);

          exit;
