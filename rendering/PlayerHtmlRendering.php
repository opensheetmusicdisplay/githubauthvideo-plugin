<?php
    class PlayerHtmlRenderer {
        protected $VIDEO_ID;
        protected $ORG_ID;
        protected $RETURN_PATH;

        public function __construct(int $videoId = -1, string $orgId = '', string $returnPath = NULL){
            $this->VIDEO_ID = $videoId;
            $this->ORG_ID = $orgId;
            if(!isset($returnPath)){
                $this->RETURN_PATH = '/';
            } else {
                $this->RETURN_PATH = $returnPath;
            }
        }

        private function get_splash_image(){
            $splashUrl = esc_url( plugins_url( 'images/blur.png', __FILE__ ) );
            if($this->VIDEO_ID > -1) {
                $metaSplash =  get_post_meta( $this->VIDEO_ID, 'githubauthvideo_splash-screen', true );
                if(isset($metaSplash) && !empty($metaSplash)){
                    $splashUrl = $metaSplash;
                }
            }
        
            return $splashUrl;
        }

        public function get_auth_html(){
            $authUrl = esc_url( '/githubauthvideo_auth/1?returnPath=' . urlencode($this->RETURN_PATH) );
            $splashUrl = $this->get_splash_image();
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

        public function get_sponsor_html(){
            $splashUrl = $this->get_splash_image();
            $ghIconUrl = esc_url( plugins_url( '../images/github-icon.png', __FILE__ ) );
            //TODO: Have reasonable default
            $sponsorUrl = esc_url('https://github.com/sponsors/' . $this->ORG_ID);
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

        public function get_video_html(){
            $videoUrl = '/githubauthvideo_video/' . $this->VIDEO_ID;
            $location = get_post_meta( $this->VIDEO_ID, 'githubauthvideo_video-location-uri', true );
            $textContent = get_post_meta( $this->VIDEO_ID, 'githubauthvideo_video-description', true );
            $title = '';
            $post = get_post($this->VIDEO_ID);
            if($post){
                $title = $post->post_title;
            }
            $mimeType = get_video_mime_type($location);
            return <<<EOT
                <h5 class="githubvideoauth-video-title-container">$title</h5>
                <div class="githubvideoauth-video-container">
                    <video class="githubvideoauth-video"
                    title="$title"
                    alt="$this->VIDEO_ID"
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

        public function get_video_placeholder_html(){
            return <<<EOT
                <div class="githubvideoauth-video-placeholder">
                    <input type="hidden" class="videoId" value="$this->VIDEO_ID"/>
                    <input type="hidden" class="orgId" value="$this->ORG_ID"/>
                </div>
            EOT;
        }
    }
?>