<?php

    function process_domain(){
        return str_replace( '.', '', $_SERVER['HTTP_HOST'] );
    }

    function get_token_key(){
        return 'githubauthvideo_' . process_domain() . '_token';
    }

    function get_token_type_key(){
        return 'githubauthvideo_' . process_domain() . '_token_type';
    }

    function get_cookie_domain(){
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    }

    function set_auth_cookies($token, $token_type = 'bearer'){
        setcookie(get_token_key(), $token, 
            time()+60*60*24*1, '/', '', true);
        setcookie(get_token_type_key(), $token_type, 
            time()+60*60*24*1, '/', '', true);
    }

    function void_auth_cookies(){
        setcookie(get_token_key(), NULL, 
            time() - 3600, '/', '', true);
        setcookie(get_token_type_key(), NULL, 
            time() - 3600, '/', '', true);
    }

    function get_auth_cookies(){
        $token = NULL;
        $tokenType = NULL;
        $TOKEN_KEY = get_token_key();
        $TOKEN_TYPE_KEY = get_token_type_key();
        if (array_key_exists($TOKEN_KEY, $_COOKIE)){
            $token = $_COOKIE[$TOKEN_KEY];
        }
        if(array_key_exists($TOKEN_TYPE_KEY, $_COOKIE)){
            $tokenType = $_COOKIE[$TOKEN_TYPE_KEY];
        }

        return array(
            'token' => $token,
            'token_type' => $tokenType
        );
    }

?>