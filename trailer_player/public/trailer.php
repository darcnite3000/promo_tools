<?php
// error_reporting(E_ALL);
$pack = (isset($_GET['pack']) ? $_GET['pack'] : null);
$data = array(
  'webmaster' => (isset($_GET['w']) ? $_GET['w'] : null),
  'campaign' => (isset($_GET['c']) ? $_GET['c'] : null),
  'program' => (isset($_GET['p']) ? $_GET['p'] : null),
  'sites' => (isset($_GET['sites']) ? explode(':', $_GET['sites']) : array()),
  'scale' => (isset($_GET['scale']) ? $_GET['scale'] : null),
  'resolution' => (isset($_GET['resolution']) ? $_GET['resolution'] : null),
  'limit' => (isset($_GET['limit']) ? intval($_GET['limit'],10) : 10),
  'tube' => ((isset($_GET['tube']) && $_GET['tube']=='true')),
  'pack' => $pack
);
if($pack){
  include 'single-pack.php';
}

$config = array(
  'autoplay'=> ($_GET['autoplay']=='true'),
  'loop'=> ($_GET['loop']=='true'),
  'width'=> (isset($_GET['width']) ? intval($_GET['width'],10) : null),
  'height'=> (isset($_GET['height']) ? intval($_GET['height'],10) : null),
  'playlist' => array(
    'orientation' => (isset($_GET['playlistOrientation']) && $pack==null ? $_GET['playlistOrientation'] : null),
    'size' => (isset($_GET['playlistSize']) ? intval($_GET['playlistSize'],10) : 0)
  )
);

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trailer</title>
<?php if($pack && count($videoList)){
  $currentVid = $videoList[0];
  $link = "https://test.com/trailers/trailer.php?w=".$data['webmaster']."&p=".$data['program']."&c=".$data['campaign']."&width=480&height=270&pack=".$pack."&format=mp4&scale=".$data['scale']."&resolution=".$data['resolution']."&tube=".$data['tube'];
?>
  <meta property="twitter:card" content="player">
  <meta property="twitter:title" content="<?php echo $currentVid['paysite']['name']." - ".$currentVid['title']; ?>">
  <meta property="twitter:description" content="<?php echo $currentVid['description']; ?>">

  <meta property="twitter:image:src" content="<?php echo $currentVid['poster'];?>">
  <meta property="twitter:image:width" content="480">
  <meta property="twitter:image:height" content="270">
  <meta property="twitter:site" content="@test">
  <meta property="twitter:player:width" content="480">
  <meta property="twitter:player:height" content="270">
  <meta property="twitter:player" content="<?php echo $link; ?>">

<?php
} ?>

  <link href="bower_components/video.js/dist/video-js/video-js.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/styles/trailer.css">
</head>
<body>
  <div id="trailer" class="trailer-player"></div>
  <div id="loader" class="trailer-player-loader"><i class="fa fa-3x fa-spinner fa-pulse"></i></div>
  <script src="bower_components/video.js/dist/video-js/video.js"></script>
  <script src="bower_components/videojs-playlists/dist/videojs-playlists.min.js"></script>
  <script>
    var playlistData = <?php echo json_encode($data); ?>;
    var playerConfig = <?php echo json_encode($config); ?>;
  </script>
  <script src="assets/trailer.js"></script>
</body>
</html>
