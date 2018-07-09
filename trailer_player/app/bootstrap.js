import $ from 'jquery';
import TrailerPlayer from './TrailerPlayer';

document.createElement('video');
document.createElement('audio');
document.createElement('track');

videojs.options.flash.swf = "bower_components/video.js/dist/video-js/video-js.swf";

var URLS = {
  playlist: 'playlist.php',
};
var videos = [], player;
$(document).ready(()=>{
  var trailer = new TrailerPlayer('#trailer','#loader', playlistData, playerConfig);
  trailer.loadVideos(URLS.playlist);
});

