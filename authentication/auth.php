<?php
    use Lcobucci\JWT\Configuration;
    use Lcobucci\JWT\Validation\Constraint\IssuedBy;
    use Lcobucci\JWT\Signer\Hmac\Sha256;
    use Lcobucci\JWT\Signer\Key\InMemory;

    $main_settings_options = get_option( 'main_settings_option_name' ); // Array of All Options
    const CLIENT_ID = $main_settings_options['github_app_client_id_0']; // Github App Client ID
    const CLIENT_SECRET = $main_settings_options['github_app_client_secret_1']; // Github App Client Secret
    const JWT_PRIVATE_KEY = $main_settings_options['jwt_private_key_2']; // JWT Private Key

    if(!isset(CLIENT_ID) || !isset(CLIENT_SECRET) || !isset(JWT_PRIVATE_KEY)) {
        echo 'Github video authentication has not been configured properly. If you are the admin, please register your Github App and set a private key.';
        exit;
    }

    if($_SERVER['REQUEST_SCHEME'] != 'https'){
        echo 'Server must have SSL enabled for Github video Authentication.';
        exit;
    }

    const REDIRECT_URI = 'https://'. $_SERVER['HTTP_HOST'] . '/github_auth';

    //setup JWT configuration used for generating 
    $configuration = Configuration::forSymmetricSigner(
        new Sha256(),
        InMemory::base64Encoded(JWT_PRIVATE_KEY)
    );
    //No Code present. Means we are just beginning the auth flow.
    if(!isset($_GET['code'])){
        $returnPath = '/';
        if(isset($_GET['return_path'])){
            $returnPath = $_GET['return_path'];
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
                        -withClaim('ip', $_SERVER['REMOTE_ADDR'])
                        // Builds a new token
                        ->getToken($configuration->signer(), $configuration->signingKey());


        //Need to start auth process
        $url = "https://github.com/login/oauth/authorize";
        $url .= "?client_id=" . CLIENT_ID;
        $url .= "&redirect_uri=" . REDIRECT_URI;
        $url .= "&scope=read:user&scope=read:org";
        $url .= "&state=" . $token->toString();
        header("Location: " . $url);
        die();        
        exit;
    } else { //Else, code query param is set. Coming back from Github
        const CODE = $_GET['code'];

        if(!isset($_GET('state'))){
            echo '"state" query parameter is missing. Authentication failed.';
            exit;
        }
        const STATE = $_GET['state'];
        try {
            //Verify the state provided (JWT Token)
            $stateToken = $config->parser()->parse(STATE);
            assert($stateToken instanceof Plain);
        } catch(Exception e){
            echo 'Error validating state. Authentication failed.';
            exit;
        }
    
        if (! $config->validator()->validate($stateToken, new IssuedBy($_SERVER['HTTP_HOST']))) {
            echo 'State was not issued by this server. Authentication failed.';
            exit;
        }
    
        //Extra security since our state variable is not random
        //This is a fairly good way of mitigating third-party interference
        $stateTokenAgentClaim = $stateToken->claims().get('user_agent', '');
        $stateTokenIpClaim = $stateToken->claims().get('ip', '');
    
        if ($stateTokenAgentClaim != $_SERVER['HTTP_USER_AGENT'] || $stateTokenIpClaim != $_SERVER['REMOTE_ADDR']) {
            echo 'User agent or IP address do not match state token claim. Authentication failed.';
            exit;
        }
    
        $url = 'https://github.com/login/oauth/access_token';
        $data = array('client_id' => CLIENT_ID, 'client_secret' => CLIENT_SECRET,
                        'code' => CODE, 'state' => STATE, 'redirect_uri' => REDIRECT_URI);
        
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
        $result = json_decode(file_get_contents($url, false, $context), true);
        if ($result === FALSE) {
            echo 'error retrieving access token.';
            exit;
        } else {
            $returnPathFromToken = $stateToken->claims().get('return_path', '/'); // Retrieves the return path
            setcookie('githubauthvideo' . $_SERVER['HTTP_HOST'] . '_token', $result['access_token'], 
                      time()+60*60*24*1, '/', '', true);
            setcookie('githubauthvideo' . $_SERVER['HTTP_HOST'] . '_token_type', $result['token_type'], 
                      time()+60*60*24*1, '/', '', true);
            header('Location: ' . $returnPathFromToken);
            die();
        }
    }
    
?>