<?php
    $json = file_get_contents('php://input');
    $request_data = json_decode($json, true);

    $token = NULL;

    if(isset($request_data['access_token'])){
        $token = $request_data['access_token'];
    } else {
        $token = $_POST['access_token'];
    }

    if(!isset($token)){
        header('HTTP/1.0 401 Unauthorized');
        echo 'Token not provided.';
        exit;
    }

    $tokenType = 'bearer';

    if(isset($request_data['token_type'])){
        $tokenType = $request_data['token_type'];
    } else {
        $tokenType = $_POST['token_type'];
    }

    $videoId = NULL;

    if(isset($request_data['video_id'])){
        $videoId = $request_data['video_id'];
    } else {
        $videoId = $_POST['video_id'];
    }

    if(!isset($videoId)){
        header('HTTP/1.0 400 Bad Request');
        echo 'Video ID not provided.';
        exit;
    }

    if(!isset(get_post($videoId))){
        header('HTTP/1.0 404 Not Found');
        echo 'Video mapping entry not found.';
        exit;
    }

    $orgId = get_post_meta( get_the_ID(), 'githubauthvideo_github-organization-id', true );
    $tierId = get_post_meta( get_the_ID(), 'githubauthvideo_github-sponsorship-tier-id', true );

    $url = 'https://api.github.com/graphql';
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
            'Authorization: ' . $tokenType ' ' . $token),
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
    $context  = stream_context_create($options);
    $result = json_decode(file_get_contents($url, false, $context), true);
    if ($result === FALSE) {
        header('HTTP/1.0 500 Internal Server Error');
        echo "Error checking Github API";
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