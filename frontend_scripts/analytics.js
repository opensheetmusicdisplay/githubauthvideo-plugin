//This file will only be enqueued if enabled on the settings page.
(function(){
    const CHECK_MAX = 6;
    let checkCount = 1;
    function checkIfAnalyticsLoaded() {
        if (window._gaq && window._gaq._getTracker) {
          const playerList = document.getElementsByClassName('video-js');
          if(videojs){
              for(let i = 0; i < playerList.length; i++){
                  const currentPlayer = videojs.getPlayer(playerList[i]);
                  if(currentPlayer.analytics){
                      currentPlayer.analytics();
                  } else {
                      console.warn("Player analytics not loaded");
                  }
              }
          } else {
            console.warn("videojs not loaded");
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
      checkIfAnalyticsLoaded();
})();