<?php
    $json = file_get_contents('php://input');
    $request_data = json_decode($json, true);

    $token = NULL;

    //TODO: Check if token still valid
    if($request_data !== null && array_key_exists('access_token', $request_data) && isset($request_data['access_token'])){
        $token = $request_data['access_token'];
    } else if(array_key_exists('access_token', $_POST)){
        $token = $_POST['access_token'];
    } else if(array_key_exists('access_token', $_GET)){
        $token = $_GET['access_token'];
    }

    if(!isset($token)){
        header('HTTP/1.0 401 Unauthorized');
        echo 'Token not provided.';
        exit;
    }

    $tokenType = 'bearer';

    if($request_data !== null && array_key_exists('token_type', $request_data) && isset($request_data['token_type'])){
        $tokenType = $request_data['token_type'];
    } else if(array_key_exists('token_type', $_POST)){
        $tokenType = $_POST['token_type'];
    } else if(array_key_exists('token_type', $_GET)){
        $tokenType = $_GET['token_type'];
    }

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

    $orgId = get_post_meta( get_the_ID(), 'githubauthvideo_github-organization-id', true );
    $tierId = get_post_meta( get_the_ID(), 'githubauthvideo_github-sponsorship-tier-id', true );

    //TODO: Once sponsorship tiers exist in Github, need to update this to query properly
    $ql = <<<EOT
        query {
            viewer {
                login
                sponsorshipsAsSponsor(after: "test", first: 10) {
                edges {
                    node {
                    sponsorEntity {
                        ... on Organization {
                        id
                        email
                        }
                    }
                    }
                }
                }
            }
        }
    EOT;
    $data = array('query' => $ql);
    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => array('Content-type: application/json',
                'Accept: application/json',
                'User-Agent: PHP',
                'Authorization: ' . $tokenType . ' ' . $token
            ),
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
    $context  = stream_context_create($options);
    $result = json_decode(@file_get_contents(GITHUB_GRAPH_API_URL, false, $context), true);
    if ($result == FALSE) {
        header('HTTP/1.0 500 Internal Server Error');
        echo "Error checking Github API for Sponsor status. Token may be expired. Try clearing cache and authenticating again.";
        exit;
    } else if (array_key_exists('message', $result)) {
        header('HTTP/1.0 500 Internal Server Error');
        echo 'Error calling Github API. Error from Github: ' . $result['message'];
        exit;
    } else {
        //TODO: actually check results for sponsorships
        //$isUrl = get_post_meta( $videoId, 'githubauthvideo_is-url-video', true );
        $location = get_post_meta( $videoId, 'githubauthvideo_video-location-uri', true );
        /*if($isUrl){
            $location = get_post_meta( $videoId, 'githubauthvideo_video-location-url', true );
        } else {
            $location = get_post_meta( get_the_ID(), 'githubauthvideo_video-location-server-path', true );
        }*/
        //Prep our response
        header('Content-Description: File Transfer');
        //TODO: detect mime type
        header('Content-Type: video/mp4');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        //Doesn't matter if we have a file
        $options = array(
            'http' => array(
                'header'  => array("Content-type: text/html",
                // "Accept: video/*",
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
        /*
        if(!$isUrl){
            if (file_exists($location)) {
                header('Content-Length: ' . filesize($location));
                $result = readfile($location);
                exit;
            } else {
                header('HTTP/1.0 404 Not Found');
                echo 'Video file not found on server.';
                exit;
            }            
        } else {
            $options = array(
                'http' => array(
                    'header'  => array("Content-type: text/html",
                   // "Accept: video/*",
                    'User-Agent: PHP',
                    'method'  => 'GET'
                )
            );
            $context  = stream_context_create($options);
            $handle = fopen($location, 'r', false, $context);
        }*/
    }
?>