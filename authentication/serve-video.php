<?php
    if ( ! defined( 'ABSPATH' ) ) exit;
    
    function githubauthvideo_serve_video(){
        $Cookies = githubauthvideo_GithubAuthCookies::getCookiesInstance();

        $token = $Cookies->get_token();
    
        if(!isset($token)){
            header('HTTP/1.0 401 Unauthorized');
            echo 'Token not provided.';
            exit;
        }
    
        $tokenType = $Cookies->get_token_type();

        $videoId = intval(get_query_var( 'githubauthvideo_video' ));
    
        if(!isset($videoId) || !is_numeric($videoId) || $videoId < 1){
            header('HTTP/1.0 400 Bad Request');
            echo 'Video ID not provided.';
            exit;
        }
    
        if(null == get_post($videoId)){
            header('HTTP/1.0 404 Not Found');
            echo 'Video mapping entry not found.';
            exit;
        }

        $nonce = sanitize_text_field(get_query_var( 'nonce' ));
    
        if(!isset($nonce) || !is_string($nonce) || !wp_verify_nonce($nonce, 'githubauthvideo_serve_video_' . $videoId)){
            header('HTTP/1.0 400 Bad Request');
            echo '<div>Video nonce was invalid or absent.</div>';
            exit;
        }
    
        $GithubApi = githubauthvideo_GithubAPIServiceFactory::getGithubAPIServiceInstance();
    
        //use github api service methods, check here
        $result = $GithubApi->is_viewer_sponsor_of_video($videoId);
        if(gettype($result) === 'string'){
            header('HTTP/1.0 500 Internal Server Error');
            echo 'Error checking Github API for Sponsor status. See message(s) below:<br>';
            echo $result;
            exit;
        } else if (!$result){
            header('HTTP/1.0 401 Unauthorized');
            echo 'User is not sponsor of this video\'s Github organization';
            exit;
        } else {
            $location = get_post_meta( $videoId, 'githubauthvideo_video-location-uri', true );
            $mediaId = intval($location);
            $scheme = '';
            if(!$mediaId){
                $scheme = parse_url($location, PHP_URL_SCHEME);
            } else {
                //If we are using a locally hosted file, get the file path instead so we aren't using extra bandwidth
                $location = get_attached_file($mediaId);
                $scheme = 'file';
            }        
            //if it's a local file, do proper streaming
            if($scheme == 'file'){
                $stream = new githubauthvideo_VideoStream($location, githubauthvideo_get_video_mime_type($location));
                $stream->start();
            } else { //otherwise pass through from the URL
                //TODO: Maybe stream better. Detect stream headers, forward, etc.
                $headers = get_headers($location, TRUE);
                //Prep our response
                header('Content-Description: File Transfer');
                header('Content-Type: ' . $headers['Content-Type']);
                header('Cache-Control: max-age=2592000, public');
                header('Expires: '.gmdate('D, d M Y H:i:s', time()+2592000) . ' GMT');
                header('Pragma: public');
                header('Content-Length: '. $headers['Content-Length']);
                //Doesn't matter if we have a file
                $options = array(
                    'http' => array(
                        'header'  => array("Content-type: text/html",
                            'Accept: video/*',
                            'User-Agent: PHP',
                            'method'  => 'GET'
                        )
                    )
                );
                $context  = stream_context_create($options);
                $handle = fopen($location, 'r', false, $context);
                if(!(isset($handle))){
                    header('HTTP/1.0 404 Not Found');
                    echo 'Video file could not be found.';
                    exit;
                }
                fpassthru($handle);
                fclose($handle);
            }
        }
    }
    githubauthvideo_serve_video();
?>