<?php
if ( ! defined( 'ABSPATH' ) ) exit;

 //TODO: these should be config options probably
 const GITHUB_GRAPH_API_URL = 'https://api.github.com/graphql';
 const GITHUB_OAUTH_BEGIN_URL = 'https://github.com/login/oauth/authorize';
 const GITHUB_OAUTH_TOKEN_URL = 'https://github.com/login/oauth/access_token';

include_once 'api/VideoStream.php';
include_once 'authentication/GithubAuthCookies.php';
include_once 'api/GithubAPIService.php';
include_once 'api/GithubAPIServiceFactory.php';
include_once 'rendering/PlayerHtmlRendering.php';
include_once 'rendering/PlayerHtmlRenderingFactory.php';
include_once 'admin-pages/settings.php';
include_once 'admin-pages/post_type.php';

?>