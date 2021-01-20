<?php
    class PhonicScore_GithubAuthVideo_GithubAPIServiceFactory {
        private static $singleton = NULL;

        private function __construct(){
        }

        public static function getGithubAPIServiceInstance(){
            return PhonicScore_GithubAuthVideo_GithubAPIServiceFactory::$singleton;
        }

        public static function registerGithubAPIService(PhonicScore_GithubAuthVideo_GithubAPIService $service){
            if(isset($service)){
                PhonicScore_GithubAuthVideo_GithubAPIServiceFactory::$singleton = $service;   
            }
        }
    }

?>