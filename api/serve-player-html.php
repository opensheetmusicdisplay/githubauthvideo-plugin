<?php
    //TODO: common code with authentication/serve-video.php
    //Create param parser utility function
    $json = file_get_contents('php://input');
    $request_data = json_decode($json, true);

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
        echo '<div>Video ID not provided.</div>';
        exit;
    }

    if(null == get_post($videoId)){
        header('HTTP/1.0 404 Not Found');
        echo '<div>Video mapping entry not found.</div>';
        exit;
    }

    $renderType = NULL;

    if($request_data !== null && array_key_exists('render_type', $request_data) && isset($request_data['render_type'])){
        $renderType = $request_data['render_type'];
    } else if(array_key_exists('render_type', $_POST)){
        $renderType = $_POST['render_type'];
    } else if(array_key_exists('render_type', $_GET)){
        $renderType = $_GET['render_type'];
    }

    if(!isset($renderType)){
        header('HTTP/1.0 400 Bad Request');
        echo '<div>Render type not provided.</div>';
        exit;
    }

    switch($renderType){
        case 'auth':
            $returnPath = NULL;

            if($request_data !== null && array_key_exists('return_path', $request_data) && isset($request_data['return_path'])){
                $returnPath = $request_data['return_path'];
            } else if(array_key_exists('return_path', $_POST)){
                $returnPath = $_POST['return_path'];
            } else if(array_key_exists('return_path', $_GET)){
                $returnPath = $_GET['return_path'];
            }
            echo render_github_auth($videoId, $returnPath);
            break;
        case 'sponsor':
            $orgId = get_post_meta( $videoId, 'githubauthvideo_github-organization-slug', true );
            echo render_sponsor($videoId, $orgId);
            break;
        case 'video':
            echo render_video($videoId);
            break;
        default:
            header('HTTP/1.0 400 Bad Request');
            echo '<div>Valid render type not provided.</div>';
            exit;
            break;
    }

    function get_splash_image($videoId = NULL){
        $splashUrl = esc_url( plugins_url( 'images/blur.png', __FILE__ ) );
        if(isset($videoId)) {
            $metaSplash =  get_post_meta( $videoId, 'githubauthvideo_splash-screen', true );
            if(isset($metaSplash) && !empty($metaSplash)){
                $splashUrl = $metaSplash;
            }
        }
    
        return $splashUrl;
    }

    function render_github_auth($videoId = NULL, $returnPath = NULL){
        if(!isset($returnPath)){
            $returnPath = '/';
        }
        $authUrl = esc_url( '/githubauthvideo_auth/1?returnPath=' . urlencode($returnPath) );
        $splashUrl = get_splash_image($videoId);
        $ghIconUrl = esc_url( plugins_url( '../images/github-icon.png', __FILE__ ) );
        return <<<EOT
        <div class="githubvideoauth-video-auth-splash-container">
        <img src="$splashUrl" class="githubvideoauth-video-auth-splash-image" />
            <div class="githubvideoauth-video-auth-splash-cover">
                <a href="$authUrl">
                    <button>
                        <img class="github-icon" src="$ghIconUrl"> <span class="github-button-text">Authenticate with Github</span>
                    </button>
                </a>
            </div>
        </div>
        EOT;
    }

    function render_sponsor($videoId = NULL, $orgId = ''){
        $splashUrl = get_splash_image($videoId);
        $ghIconUrl = esc_url( plugins_url( '../images/github-icon.png', __FILE__ ) );
        //TODO: Have reasonable default
        $sponsorUrl = esc_url('https://github.com/sponsors/' . $orgId);
        return <<<EOT
        <div class="githubvideoauth-video-auth-splash-container">
        <img src="$splashUrl" class="githubvideoauth-video-auth-splash-image" />
            <div class="githubvideoauth-video-auth-splash-cover">
                <div class="githubvideoauth-sponsor-message-block">
                    <text>Only Github sponsors have access to this video.</text>
                </div>
                <br>
                <a href="$sponsorUrl" target="_blank">
                    <button>
                        <img class="github-icon" src="$ghIconUrl"> <span class="github-button-text">Become a sponsor now!</span>
                    </button>
                </a>
            </div>
        </div>
        EOT;
    }

    function render_video($videoId){
        $videoUrl = '/githubauthvideo_video/' . $videoId;
        $location = get_post_meta( $videoId, 'githubauthvideo_video-location-uri', true );
        $textContent = get_post_meta( $videoId, 'githubauthvideo_video-description', true );
        $title = '';
        $post = get_post($videoId);
        if($post){
            $title = $post->post_title;
        }
        $mimeType = get_video_mime_type($location);
        return <<<EOT
            <h5 class="githubvideoauth-video-title-container">$title</h5>
            <div class="githubvideoauth-video-container">
                <video class="githubvideoauth-video"
                title="$title"
                alt="$videoId"
                controls
                preload="auto"
                >
                    <source src="$videoUrl" type="$mimeType"></source>
                </video>
            </div>
            <div class="githubvideoauth-video-text-content-container">
                $textContent
            </div>
        EOT;
    }
?>