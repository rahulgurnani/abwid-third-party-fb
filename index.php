
<?php


# Start the session 
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

# Autoload the required files
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/GetFacebookData.php';
use Facebook\Facebook;

/**
 * the following function gets app data of the abwid app on facebook.
 *
 * @param string $in the location of the file
 *
 * @return mixed array or false depending on presence of file
 */
function getAppData($in)
{
    if (is_file($in)) {
        return include $in;
    }

    return false;
}
# Set the default parameters
$appInfo = getAppData(__DIR__.'/AppInfo.php');
if ($appInfo === false) {
    echo 'app info not found';
    exit(1);
}
$fb = new Facebook($appInfo);

$redirect = 'https://abwid.me/';

# Create the login helper object
$helper = $fb->getRedirectLoginHelper();

# Get the access token and catch the exceptions if any
try {
    $accessToken = $helper->getAccessToken();
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
  echo 'Graph returned an error: '.$e->getMessage();
    exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
  echo 'Facebook SDK returned an error: '.$e->getMessage();
    exit;
}

# If the 
if (isset($accessToken)) {
    // Logged in!
    // Now you can redirect to another page and use the
    // access token from $_SESSION['facebook_access_token'] 
    // But we shall we the same page

    // Sets the default fallback access token so 
    // we don't have to pass it to each request

    $fb->setDefaultAccessToken($accessToken);
    $getFacebookData = new GetFacebookData($fb, $accessToken);

    //We can get photos using posts or photos, obviously getting it by getting posts permission gives us  more data (posts too) , so I am doing it that way.
    $getFacebookData->getData();
} else {
    // permissions to which we are giving access to. There are more permissions that you can ask for but facebook does not promote giving you more permissions. TODO: Required editing here.
    $permissions = ['email', 'user_likes', 'user_hometown', 'user_actions.music', 'user_posts', 'user_about_me', 'user_education_history'];
    $loginUrl = $helper->getLoginUrl($redirect, $permissions);
    echo '<a href="'.$loginUrl.'">Log in with Facebook!</a>';
}
