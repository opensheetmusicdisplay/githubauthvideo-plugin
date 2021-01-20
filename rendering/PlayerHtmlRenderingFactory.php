<?php 
    class PhonicScore_GithubAuthVideo_PlayerHtmlRenderingFactory {
        private static $singleton = NULL;

        private function __construct(){
        }

        public static function getPlayerHtmlRenderingServiceServiceInstance(){
            return PhonicScore_GithubAuthVideo_PlayerHtmlRenderingFactory::$singleton;
        }

        public static function registerPlayerHtmlRenderingService(PhonicScore_GithubAuthVideo_PlayerHtmlRenderer $service){
            if(isset($service)){
                PhonicScore_GithubAuthVideo_PlayerHtmlRenderingFactory::$singleton = $service;   
            }
        }
    }
?>