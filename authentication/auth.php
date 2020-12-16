<?php
    use Lcobucci\JWT\Configuration;
    use Lcobucci\JWT\Validation\Constraint\IssuedBy;
    use Lcobucci\JWT\Signer\Hmac\Sha256;
    use Lcobucci\JWT\Signer\Key\InMemory;
    use Lcobucci\JWT\Token\Plain;

    $main_settings_options = get_option( 'main_settings_option_name' ); // Array of All Options
    if(!$main_settings_options){
        echo 'Github video authentication has not been configured properly. If you are the admin, please register your Github App and set a private key.';
        exit;
    }
    $CLIENT_ID = $main_settings_options['github_app_client_id_0']; // Github App Client ID
    $CLIENT_SECRET = $main_settings_options['github_app_client_secret_1']; // Github App Client Secret
    $JWT_PRIVATE_KEY = $main_settings_options['jwt_private_key_2']; // JWT Private Key
    $DO_NOT_ENFORCE_HTTPS = FALSE;
    if(array_key_exists('do_not_enforce_https_5', $main_settings_options)){
        $DO_NOT_ENFORCE_HTTPS = $main_settings_options['do_not_enforce_https_5'];
    }
    

    if(!isset($CLIENT_ID) || !isset($CLIENT_SECRET) || !isset($JWT_PRIVATE_KEY)) {
        echo 'Github video authentication has not been configured properly. If you are the admin, please register your Github App and set a private key.';
        exit;
    }
    if($DO_NOT_ENFORCE_HTTPS == FALSE && $_SERVER['REQUEST_SCHEME'] != 'https'){
        echo 'Server must have SSL enabled for Github video Authentication.';
        exit;
    }
    
    
    $REDIRECT_URI = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/github_auth';

    //setup JWT configuration used for generating 
    $configuration = Configuration::forSymmetricSigner(
        new Sha256(),
        InMemory::base64Encoded($JWT_PRIVATE_KEY)
    );
    $Cookies = GithubAuthCookies::getCookiesInstance();
    //No Code present. Means we are just beginning the auth flow.
    if(!array_key_exists('code', $_GET)){
        //Void out auth cookies
        $Cookies->void_auth_cookies();
        $returnPath = '/';
        if(array_key_exists('return_path', $_GET)){
            $returnPath = urldecode($_GET['return_path']);
        }
        //generate state with JWT
        $now   = new DateTimeImmutable();
        $token = $configuration->builder()
                        // Configures the issuer (iss claim)
                        ->issuedBy($_SERVER['HTTP_HOST'])
                        // Configures the audience (aud claim)
                        ->permittedFor($_SERVER['HTTP_HOST'])
                        // Configures the time that the token was issue (iat claim)
                        ->issuedAt($now)
                        // Configures the time that the token can be used (nbf claim)
                        ->canOnlyBeUsedAfter($now)
                        // Configures the expiration time of the token (exp claim)
                        ->expiresAt($now->modify('+1 hour'))
                        //Return path claim
                        ->withClaim('return_path', $returnPath)
                        //user agent claim
                        ->withClaim('user_agent', $_SERVER['HTTP_USER_AGENT'])
                        //IP claim
                        ->withClaim('ip', $_SERVER['REMOTE_ADDR'])
                        // Builds a new token
                        ->getToken($configuration->signer(), $configuration->signingKey());


        //Need to start auth process
        $url = GITHUB_OAUTH_BEGIN_URL;
        $url .= "?client_id=" . $CLIENT_ID;
        $url .= "&redirect_uri=" . $REDIRECT_URI;
        $url .= "&scope=read:user&scope=read:org";
        $url .= "&state=" . $token->toString();
        header("Location: " . $url);
        die();        
        exit;
    } else { //Else, code query param is set. Coming back from Github
        $code = $_GET['code'];

        if(!array_key_exists('state', $_GET)){
            echo '"state" query parameter is missing. Authentication failed.';
            exit;
        }
        $state = $_GET['state'];
        try {
            //Verify the state provided (JWT Token)
            $stateToken = $configuration->parser()->parse($state);
            assert($stateToken instanceof Plain);
        } catch(Exception $e){
            echo 'Error validating state. Authentication failed.';
            exit;
        }
    
        if (! $configuration->validator()->validate($stateToken, new IssuedBy($_SERVER['HTTP_HOST']))) {
            echo 'State was not issued by this server. Authentication failed.';
            exit;
        }
    
        //Extra security since our state variable is not random
        //This is a fairly good way of mitigating third-party interference
        $stateTokenAgentClaim = $stateToken->claims()->get('user_agent', '');
        $stateTokenIpClaim = $stateToken->claims()->get('ip', '');
    
        if ($stateTokenAgentClaim != $_SERVER['HTTP_USER_AGENT'] || $stateTokenIpClaim != $_SERVER['REMOTE_ADDR']) {
            echo 'User agent or IP address do not match state token claim. Authentication failed.';
            exit;
        }
    
        $data = array('client_id' => $CLIENT_ID, 'client_secret' => $CLIENT_SECRET,
                        'code' => $code, 'state' => $state, 'redirect_uri' => $REDIRECT_URI);
        
        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => array("Content-type: application/json",
                                    "Accept: application/json"),
                'method'  => 'POST',
                'content' => json_encode($data)
            )
        );
        $context  = stream_context_create($options);
        $result = json_decode(@file_get_contents(GITHUB_OAUTH_TOKEN_URL, false, $context), true);
        if ($result == FALSE) {
            echo 'Error retrieving access token.';
            exit;
        } else if(array_key_exists('error', $result)){
            echo 'Error authenticating with Github. Error from github: ' . $result['error_description'];
            exit;
        } else if(array_key_exists('access_token', $result)){
            $returnPathFromToken = $stateToken->claims()->get('return_path', '/'); // Retrieves the return path
            $Cookies->set_auth_cookies($result['access_token'], $result['token_type']);
            header('Location: ' . $returnPathFromToken);
            die();
        } else {
            echo 'Unknown error authenticating with Github. Enable error logging in auth.php if you are the admin.';
            //error logging... This is probably the easiest way to get the full info. Not wise to provide to end users though.
            //var_dump($result);
            exit;
        }
    }
    
?>