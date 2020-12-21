import Cookies from 'js-cookie';
import axios from 'axios';
/**
 * 
 * @param {*} type string value representing what should be rendered for our videos:
 *              -video is the actual video
 *              -auth is the authentication splash screen
 *              -sponsor is the sponsor splash screen
 */
function renderPlaceholders(type, token, tokenType){
    const returnPath = window.location.pathname + window.location.search + window.location.hash;
    const placeholders = document.getElementsByClassName('githubvideoauth-video-placeholder');
    let promiseList = [];
    for(let i = 0; i < placeholders.length; i++){
        const currentPlaceholder = placeholders[i];
        const idInput = currentPlaceholder.getElementsByClassName('videoId')[0];
        if(idInput){
            const videoId = parseInt(idInput.getAttribute('value'));
            if(videoId > -1){
                let currentResolve = undefined;
                let currentReject = undefined;
                const currentExecutor = function(resolve, reject){
                    currentReject = reject;
                    currentResolve = resolve;
                };
                const currentPromise = new Promise(currentExecutor);
                promiseList.push(currentPromise);
                //Check for org sponsorship on this video
                if(type === 'video' && !githubauthvideo_player_js_data.ignore_sponsorship){
                    const orgInput = currentPlaceholder.getElementsByClassName('orgId')[0];
                    let orgId = "";
                    if(orgInput){
                        orgId = orgInput.getAttribute('value');
                    }
                    if(!orgId || orgId.length === 0){
                        currentPlaceholder.outerHTML = '<div>No Org Id Specified.</div>';
                        currentReject("No Org ID Specified.");
                    } else {
                        const ql = 'query {' +
                            'organization(login: "' + orgId + '") {' +
                            'viewerIsSponsoring' +
                            '}' +        
                          '}';
                        axios.post(githubauthvideo_player_js_data.github_api_url, JSON.stringify({query: ql}), 
                                      {headers: { 'Authorization': tokenType + ' ' + token }})
                        .then(function(response){
                            if(response.error){
                                currentPlaceholder.outerHTML = '<div>' + response.message + '</div>';
                                currentReject(response.message);
                            } else { 
                                if(response.data.data.organization.viewerIsSponsoring){
                                    axios.post('/video_html', {
                                        video_id: videoId,
                                        render_type: 'video'
                                    }).then(function(response){
                                        currentPlaceholder.outerHTML = response.data;
                                        currentResolve();
                                    },
                                    function(error){
                                        currentReject(error);
                                    });
                                } else {
                                    axios.post('/video_html', {
                                        video_id: videoId,
                                        render_type: 'sponsor'
                                    }).then(function(response){
                                        currentPlaceholder.outerHTML = response.data;
                                        currentResolve();
                                    },
                                    function(error){
                                        currentReject(error);
                                    });
                                }
                            }
                        }, 
                        function(errorResponse){
                            console.log(errorResponse);
                            currentReject(errorResponse);
                            //TODO: maybe remove cookies and render auth?
                        });
                    }
                } else {//Regular render.
                    axios.post('/video_html', {
                        video_id: videoId,
                        render_type: type,
                        return_path: returnPath
                    }).then(function(response){
                        currentPlaceholder.outerHTML = response.data;
                        currentResolve();
                    }).catch(function(error){
                        currentReject(error);
                    });
                }

            }
        } else {
            currentPlaceholder.outerHTML = '<div>No Video Specified.</div>';
        }
    }
    //can run our analytics now
    Promise.allSettled(promiseList).then(function(){
        if(window.githubauthvideo_checkIfAnalyticsLoaded && typeof window.githubauthvideo_checkIfAnalyticsLoaded === 'function'){
            window.githubauthvideo_checkIfAnalyticsLoaded();
        }
    });
}

(function(){
    /*      js_data:
            'github_api_url',
			'token_key',
            'token_type_key',
            'ignore_sponsorship'
    */
   const token = Cookies.get(githubauthvideo_player_js_data.token_key);
   
   if(token && token.length > 0){
       let tokenType = Cookies.get(githubauthvideo_player_js_data.token_type_key);
        if(!tokenType || tokenType.length < 1){
            //safe default
            tokenType = 'bearer';
        }
        //check if token is valid and not expired
        axios.post(githubauthvideo_player_js_data.github_api_url, {}, {headers: { 'Authorization': tokenType + ' ' + token }})
        .then(function(response){
            renderPlaceholders('video', token, tokenType);
        }, 
        function(errorResponse){
            //Token is invalid. Remove, render auth
            Cookies.remove(githubauthvideo_player_js_data.token_key);
            Cookies.remove(githubauthvideo_player_js_data.token_type_key);
            renderPlaceholders('auth', token, tokenType);
        });
   } else { //Render auth instead
    renderPlaceholders('auth');
   }
})();