(function(){
  //This file will only be enqueued if enabled on the settings page.
  class GithubAuthVideoAnalytics {
    constructor(element, callback){
      this.videoTitle = '';
      this.videoId = -1;
      this.element = {};
      this.eventHandlerCallback = function(){
      }
      
      if(element instanceof HTMLVideoElement){
        this.videoTitle = element.getAttribute('title');
        this.videoId = parseInt(element.getAttribute('alt'), 10);
        this.element = element;
        this.eventHandlerCallback = callback;
        const self = this;
        element.addEventListener('seeked', function(event){
          self.playbackSeeked(event);
        });
        element.addEventListener('play', function(event){
          self.playbackStart(event);
        });
        element.addEventListener('pause', function(event){
          self.playbackPaused(event);
        });
        element.addEventListener('ended', function(event){
          self.playbackComplete(event);
        });
        element.addEventListener('volumechange', function(event){
          self.volumeChanged(event);
        });
      }
    }

    playbackComplete(event){
      this.eventHandlerCallback('complete', this.videoTitle, this.videoId, this.element.currentTime);
    }

    playbackPaused(event){
      this.eventHandlerCallback('pause', this.videoTitle, this.videoId, this.element.currentTime);
    }

    playbackStart(event){
      this.eventHandlerCallback('start', this.videoTitle, this.videoId, this.element.currentTime);
    }

    playbackSeeked(event){
      console.log(this.element.currentTime);
      this.eventHandlerCallback('seek', this.videoTitle, this.videoId, this.element.currentTime);
    }

    volumeChanged(event){
      let vol = this.element.volume * 100;
      if (this.element.muted){
        vol = 0;
      }
      this.eventHandlerCallback('volume-change', this.videoTitle, this.videoId, vol);
    }
  }

  function handleGa(action, title, id, value){
    ga('send', {
      hitType: 'event',
      eventCategory: 'Video',
      eventAction: action,
      eventLabel: id + ': ' + title,
      eventValue: Math.floor(value)
    });
  }

  function handleGtag(action, title, id, value){
    gtag('event', action, {
      'event_category': 'video',
      'event_label': id + ': ' + title,
      'value': value
    });
    
  }

  const CHECK_MAX = 6;
  let checkCount = 1;
  function checkIfAnalyticsLoaded() {
      if (typeof ga !== 'undefined' || typeof gtag !== 'undefined') {
        let callback = function(){};
        if(typeof ga !== 'undefined'){
          callback = handleGa;
        } else if (typeof gtag !== 'undefined'){
          callback = handleGtag;
        }
        const videoList = document.getElementsByClassName('githubvideoauth-video');
            for(let i = 0; i < videoList.length; i++){
              let videoEl = videoList[i];
              if(videoEl instanceof HTMLDivElement){
                videoEl = videoEl.getElementsByTagName('video')[0];
              }
              new GithubAuthVideoAnalytics(videoEl, callback);
            }
      } else {
        // Retry
        if(checkCount++ < CHECK_MAX){
          setTimeout(checkIfAnalyticsLoaded, 500);
        } else {
          console.warn("max wait time reached. Google analytics not loaded for player.");
        }
      }
    }

    //Need to run if server-side rendering (already complete).
    //Client-side will be delayed, so need to wait to run.
    if(githubauthvideo_analytics_js_data.server_side_rendering){
      checkIfAnalyticsLoaded();
    } else {
      window.githubauthvideo_checkIfAnalyticsLoaded = checkIfAnalyticsLoaded;
    }
})();