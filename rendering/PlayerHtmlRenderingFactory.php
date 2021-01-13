<?php 
    class PlayerHtmlRenderingFactory {
        private static $singleton = NULL;

        private function __construct(){
        }

        public static function getPlayerHtmlRenderingServiceServiceInstance(){
            return PlayerHtmlRenderingFactory::$singleton;
        }

        public static function registerPlayerHtmlRenderingService(PlayerHtmlRenderer $service){
            if(isset($service)){
                PlayerHtmlRenderingFactory::$singleton = $service;   
            }
        }
    }
?>