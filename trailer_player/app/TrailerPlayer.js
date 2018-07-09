import $ from 'jquery'
import _ from 'lodash-compat'

class TrailerPlayer {
  videos = []
  els = {}
  constructor(container, loader, data = {}, config = {}) {
    this.data = data
    this.config = config
    this.config.playlist = this.config.playlist || {}
    this.els.$container = $(container)
    this.els.$loader = $(loader)
    this.id = this.els.$container.attr('id') || 'trailer'
    this.playerId = `${this.id}_player`
    this.els.$container.css(this.containerStyle())
    this.buildTrailerElements()
    this.player = videojs(this.playerId)
    this.bindEvents()
  }
  containerStyle() {
    var width = this.config.width || '100%'
    var height = this.config.height || '100%'
    return { width, height, display: 'none' }
  }
  playerSizeStyle() {
    if (this.config.width && this.config.playlist.orientation == 'right') {
      return {
        width: this.config.width - this.config.playlist.size,
        height: '100%'
      }
    }
    if (this.config.height && this.config.playlist.orientation == 'bottom') {
      return {
        width: '100%',
        height: this.config.height - this.config.playlist.size
      }
    }
    return { width: '100%', height: '100%' }
  }
  playlistSizeStyle() {
    if (this.config.width && this.config.playlist.orientation == 'right') {
      return { width: this.config.playlist.size, height: '100%' }
    }
    if (this.config.height && this.config.playlist.orientation == 'bottom') {
      return { width: '100%', height: this.config.playlist.size }
    }
    return { display: 'none' }
  }
  buildTrailerElements() {
    var playerSize = this.playerSizeStyle()
    var playlistSize = this.playlistSizeStyle()
    this.els.$player = $(
      `<video id="${this.playerId}" width="${playerSize.width}" height="${
        playerSize.height
      }" class="video-js vjs-default-skin" controls data-setup="" poster=""></video>`
    )
    this.els.$playlist = $(`<ul class="playlist"></ul>`)
    this.els.$clickThrough = $(`<a href="" class="click-through"></a>`)
    this.els.$clickThrough.css(playerSize).css({ display: 'none' })
    this.els.$playlist.css(playlistSize)
    this.els.$container
      .append(this.els.$player)
      .append(this.els.$playlist)
      .append(this.els.$clickThrough)
  }
  loadVideos(playlistUrl) {
    this.els.$loader.css({ display: 'block' })
    $.post(playlistUrl, this.data).then(data => {
      if (data.videos.length > 0) {
        this.videos = data.videos
        this.updateVideoList()
        this.els.$container.css({ display: 'block' })
        this.els.$loader.css({ display: 'none' })
      }
    })
  }
  updateVideoList() {
    this.player.playList(this.videos)
    var videos = _.map(this.videos, (video, index) => {
      var $videoImg = $(`<img />`)
        .attr('src', video.poster)
        .attr('alt', video.title)
      var $videoImgWrap = $(`<div class="playlist-item-img"></div>`).append(
        $videoImg
      )
      var $videoDetailHeading = $(
        `<div class="heading"><span class="title">${video.title}</title></div>`
      )
      var $videoDetailDescription = $(
        `<div class="description">${video.description}</div>`
      )
      var $videoDetail = $(`<div class="playlist-item-detail"></div>`)
        .append($videoDetailHeading)
        .append($videoDetailDescription)
      return $(`<li data-key="${index}" class="playlist-item"></li>`)
        .append($videoImgWrap)
        .append($videoDetail)
    })
    this.els.$playlist.empty().append(videos)
    this.updateCurrentVideo()
    if (this.config.autoplay) this.player.play()
  }
  generateLinkCode(site) {
    var webmaster = this.data.webmaster ? this.data.webmaster : ''
    var campaign = this.data.campaign ? this.data.campaign : ''
    var program = this.data.program ? this.data.program : ''
    return `http://test.com/hit.php?w=${webmaster}&s=${site}&p=${program}&c=${campaign}`
  }
  updateCurrentVideo() {
    var index = this.player.pl.current
    var video = this.player.pl.currentVideo
    this.els.$clickThrough
      .attr('href', this.generateLinkCode(video.paysite.id))
      .attr('target', '_blank')
      .attr('title', `${video.paysite.name} - ${video.title}`)
    this.els.$playlist.find(`li[data-key!=${index}]`).removeClass('active')
    this.els.$playlist.find(`li[data-key=${index}]`).addClass('active')
  }
  onPlay(event) {
    this.els.$clickThrough.css({ display: 'none' })
  }
  onPause(event) {
    this.els.$clickThrough.css({ display: 'block' })
  }
  onPlaylistEnd(event) {
    if (this.config.loop) {
      this.player.playList(0)
      this.player.play()
    }
  }
  changeVideo(event) {
    var index = $(event.currentTarget).data('key')
    this.player.playList(index)
    this.updateCurrentVideo()
    if (this.config.autoplay) this.player.play()
  }
  clickThrough(event) {
    this.els.$clickThrough.css({ display: 'none' })
  }
  bindEvents() {
    var updateCurrentVideo = _.bind(this.updateCurrentVideo, this)
    var onPause = _.bind(this.onPause, this)
    var onPlay = _.bind(this.onPlay, this)
    var onPlaylistEnd = _.bind(this.onPlaylistEnd, this)
    var changeVideo = _.bind(this.changeVideo, this)
    var clickThrough = _.bind(this.clickThrough, this)
    this.player.on('play', onPlay)
    this.player.on('pause', onPause)
    this.player.on('next', updateCurrentVideo)
    this.player.on('prev', updateCurrentVideo)
    this.player.on('lastVideoEnded', onPlaylistEnd)
    this.els.$playlist.on('click', '.playlist-item', changeVideo)
    this.els.$clickThrough.on('click', clickThrough)
  }
}

export default TrailerPlayer
