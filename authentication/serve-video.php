<?php
    $json = file_get_contents('php://input');
    $request_data = json_decode($json, true);
    $Cookies = GithubAuthCookies::getCookiesInstance();

    $token = $Cookies->get_token();

    if(!isset($token)){
        header('HTTP/1.0 401 Unauthorized');
        echo 'Token not provided.';
        exit;
    }

    $tokenType = $Cookies->get_token_type();

    $videoId = NULL;

    if($request_data !== null && array_key_exists('video_id', $request_data) && isset($request_data['video_id'])){
        $videoId = $request_data['video_id'];
    } else if(array_key_exists('video_id', $_POST)){
        $videoId = $_POST['video_id'];
    } else if(array_key_exists('video_id', $_GET)){
        $videoId = $_GET['video_id'];
    }

    if(!isset($videoId)){
        header('HTTP/1.0 400 Bad Request');
        echo 'Video ID not provided.';
        exit;
    }

    if(null == get_post($videoId)){
        header('HTTP/1.0 404 Not Found');
        echo 'Video mapping entry not found.';
        exit;
    }

    $GithubApi = new GithubAPIService(GITHUB_GRAPH_API_URL);
    $orgId = get_post_meta( $videoId, 'githubauthvideo_github-organization-slug', true );
    //$tierId = get_post_meta( $videoId, 'githubauthvideo_github-sponsorship-tier-id', true );

    //use github api service methods, check here
    $result = $GithubApi->is_viewer_sponsor_of_org($orgId);
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
        $scheme = parse_url($location, PHP_URL_SCHEME);

        //if it's a local file, do proper streaming
        if($scheme == 'file'){
            $stream = new VideoStream($location, get_video_mime_type($location));
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
?>