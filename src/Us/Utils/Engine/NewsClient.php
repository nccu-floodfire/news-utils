<?php
namespace Us\Utils\Engine;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;

class NewsClient
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

	public function getLinkData($url)
	{
		/* Facebook PHP SDK v4.0.0 */
		$request = new FacebookRequest(
			$this->_session,
			"GET",
			"/{$url}"
			);
		$response = $request->execute();
		$graphArr = $response->getGraphObject()->asArray();
		$shareObj = $graphArr["share"];

		return $shareObj;
	}

	public function getShareCount($url)
	{
		$shareObj = $this->getLinkData($url);
		$result = $shareObj->share_count;

		return $result;
	}
}