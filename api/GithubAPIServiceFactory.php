<?php
    class GithubAPIServiceFactory {
        private static $singleton = NULL;

        private function __construct(){
        }

        public static function getGithubAPIServiceInstance(){
            return GithubAPIServiceFactory::$singleton;
        }

        public static function registerGithubAPIService(GithubAPIService $service){
            if(isset($service)){
                GithubAPIServiceFactory::$singleton = $service;   
            }
        }
    }

?>