<?php
    class GithubAuthCookies {
        private $domain;
        private $token_key;
        private $token_type_key;
        private $cookie_domain;
        private static $singleton = NULL;

        private function __construct(){
            $this->domain = str_replace( '.', '', $_SERVER['HTTP_HOST'] );
            $this->token_key = 'githubauthvideo_' . $this->domain . '_token';
            $this->token_type_key = 'githubauthvideo_' . $this->domain . '_token_type';
            $this->cookie_domain = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
            GithubAuthCookies::$singleton = $this;
        }

        public static function getCookiesInstance(){
            if(isset(GithubAuthCookies::$singleton)){
                return GithubAuthCookies::$singleton;
            } else {
                return new GithubAuthCookies;
            }
        }

        public function set_auth_cookies(string $token, string $token_type = 'bearer'){
            setcookie($this->token_key, $token, 
                time()+60*60*24*1, '/', '', true);
            setcookie($this->token_type_key, $token_type, 
                time()+60*60*24*1, '/', '', true);
        }
    
        public function void_auth_cookies(){
            setcookie($this->token_key, NULL, 
                time() - 3600, '/', '', true);
            setcookie($this->token_type_key, NULL, 
                time() - 3600, '/', '', true);
        }

        public function get_token(){
            if (array_key_exists($this->token_key, $_COOKIE)){
                return $_COOKIE[$this->token_key];
            } else {
                return NULL;
            }
        }

        public function get_token_type(){
            if (array_key_exists($this->token_type_key, $_COOKIE)){
                return $_COOKIE[$this->token_type_key];
            } else {
                return 'bearer';
            }
        } 
    }
?>