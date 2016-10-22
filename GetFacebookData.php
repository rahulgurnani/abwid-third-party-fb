<?php


require_once __DIR__.'/vendor/autoload.php';
use Facebook\Facebook;
use Facebook\FacebookSession;

/**
 * Gets facebooks data.
 */
class GetFaceBookData
{
    protected $fb;
    protected $session;
    protected $accessToken;
    protected $appInfo;
    public function __construct(Facebook $fb, $accessToken, $appInfo)
    {
        $this->fb = $fb;
        $this->accessToken = $accessToken;
        $this->appInfo = $appInfo;
    }

    /**
     * initializations for setting sessions.
     */
    protected function setSession($accessToken)
    {
        FacebookSession::setDefaultApplication($this->appInfo['app_id'], $this->appInfo['app_id']);

        // If you already have a valid access token:
        $this->session = new FacebookSession('access-token');

        // If you're making app-level requests:
        $this->session = FacebookSession::newAppSession();

        // To validate the session:
        try {
            $this->session->validate();
        } catch (FacebookRequestException $ex) {
            // Session not valid, Graph API returned an exception with the reason.
          echo $ex->getMessage();
        } catch (\Exception $ex) {
            // Graph API returned info, but it may mismatch the current app or have expired.
          echo $ex->getMessage();
        }
    }
    /**
     * function that gets all the data.
     *
     * @return none
     */
    public function getData()
    {
        try {
            $response = $this->fb->get('/me?fields=education');
            $userNode = $response->getGraphUser();
            $educations = $userNode->asArray();
            $allEducations = $educations['education'];
            foreach ($allEducations as $education) {
                var_dump($education['school']['name']);
                echo '<br><br>';
            }
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
          echo 'Graph returned an error: '.$e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
          echo 'Facebook SDK returned an error: '.$e->getMessage();
            exit;
        }

        // Print the user Details
        echo 'Welcome !<br><br>';
        echo 'Name: '.$userNode->getName().'<br>';
        $userId = $userNode->getId();
        echo 'User ID: '.$userId.'<br>';
        echo 'Email: '.$userNode->getProperty('email').'<br><br>';
        $image = 'https://graph.facebook.com/'.$userNode->getId().'/picture?width=200';
        echo 'Picture<br>';
        echo "<img src='$image' /><br><br>";
        $this->getMovies($userId);
        $this->getMusic($userId);
        $this->getPosts($userId);
    }

    /**
     * get liked movies prints the data right now.
     *
     * @param stirng $userId
     *
     * @return none
     */
    protected function getMovies($userId)
    {
        echo 'Movies<br><br>';
        $response = $this->fb->get('/'.$userId.'/movies');
        $movieEdge = $response->getGraphEdge();
        echo '<br><br>movie<br>';
        while ($movieEdge !== null) {
            foreach ($movieEdge as $movie) {
                echo $movie->asArray()['name'].'<br>';
            }
            $movieEdge = $this->fb->next($movieEdge);
        }
    }
    /**
     * prints the liked music.
     *
     * @param [type] $userId [description]
     *
     * @return [type] [description]
     */
    protected function getMusic($userId)
    {
        $likedMusic = [];
        $response = $this->fb->get('/'.$userId.'/music');
        $musicEdge = $response->getGraphEdge();
        echo '<br><br>Music<br>';
        while ($musicEdge !== null) {
            foreach ($musicEdge as $music) {
                $likedMusic[] = $music->asArray()['name'];
                echo $music->asArray()['name'].'<br>';
            }
            $musicEdge = $this->fb->next($musicEdge);
        }
        var_dump($likedMusic);

        return $likedMusic;
    }
    /**
     * We can get photos using posts or photos, obviously getting it by getting posts permission gives us  more data (posts too) , so I am doing it that way. I am printing posts in this funciton.
     *
     * @param string $userId
     *
     * @return array array of posts
     */
    protected function getPosts($userId)
    {
        $response = $this->fb->get('/'.$userId.'/posts');
        $postsEdge = $response->getGraphEdge();
        $i = 10;        // displaying only 10 posts right now
        while ($postsEdge !== null) {
            foreach ($postsEdge as $post) {
                var_dump($post);
                $postArray = $post->asArray();
                $getId = $postArray['id'].'?fields=full_picture,picture';
                $response = $this->fb->get('/'.$getId);
                $pictureObject = $response->getGraphObject();
                $pictureObjectArray = $pictureObject->asArray();
                if (array_key_exists('picture', $pictureObjectArray)) {
                    if (array_key_exists('message', $postArray)) {
                        echo 'Message associated with post :';
                        echo $postArray['message'].'<br>';
                    }
                    if (array_key_exists('story', $postArray)) {
                        echo 'Story associated with post : ';
                        echo $postArray['story'].'<br>';
                    }
                    // var_dump($pictureObject->asArray()['picture']);
                    echo 'Picture:<br>';
                    echo '<img src='.$pictureObject->
                    asArray()['full_picture'].'><br>';
                    $getId = $postArray['id'].'?fields=likes.summary(true)';
                    $response = $this->fb->get('/'.$getId);
                    var_dump($response->getGraphObject()->asArray());
                    --$i;
                }

                if ($i === 0) {
                    break;
                }
            }
            if ($i === 0) {
                break;
            }
        }
    }
}
