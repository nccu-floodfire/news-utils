<?php namespace Us\Utils\Facebook;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;

class FacebookSocialClient
{
	private $_session = null; // facebook request session
	private static $instance = null;

	private function __construct($config)
	{
		FacebookSession::setDefaultApplication(
			$config["app_id"],
			$config["app_secret"]
		);
		$this->_session = FacebookSession::newAppSession();
	}

	public static function getInstance($config)
    {
        if (null === self::$instance) {
            self::$instance = new static($config);
        }

        return self::$instance;
    }

	public function getLinkGraphObj($url)
	{
		/* Facebook PHP SDK v4.0.0 */
		$request = new FacebookRequest(
			$this->_session,
			"GET",
			"/{$url}"
			);
		$response = $request->execute();
		$GraphObj = $response->getGraphObject();

		return $GraphObj;
	}

}