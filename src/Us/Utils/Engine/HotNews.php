<?php
namespace Us\Utils\Engine;

class HotNews
{
	private $_config = array(); // setting parameters array

	public function __construct($config)
	{
		date_default_timezone_set("Asia/Taipei");
		$this->set_config($config);
	}

	public function set_config($config)
	{
		// facebook app id & secret
		$this->_config["app_id"] = $config["app_id"];
		$this->_config["app_secret"] = $config["app_secret"];
		// sleep time during every single connection request
		$this->_config["sleep"] = (!isset($config["sleep"])) ? 1 : $config["sleep"];
		// news date
		$this->_config["date"] = (!isset($config["date"])) ? date("Y-m-d") : $config["date"];
	}

	public function run()
	{
		$this->error_output("application is starting... \n");
		$HttpHelper = new HttpHelper();

		$news = $HttpHelper->getHotNewsByDate($this->_config["date"]);

		// parsing data from news api
		$count = 1;
		foreach ($news["data"] as &$data) {
			foreach ($data["news"] as &$item) {
				$this->error_output("parsing url[" . $count . "]: "  . $item["url"] . "\n");
				$item["share_count"] = $this->getShareCount($item["url"]);
				$count++;
				sleep($this->_config["sleep"]);
			}
		}
		$this->error_output("parsing finished. \n");
		//$news is an array which has been added share_count
	}

	public function getShareCount($url)
	{
		$NewsClient = NewsClient::getInstance($this->_config);
		$share_count = $NewsClient->getShareCount($url);

		return $share_count;
	}

	private function error_output($message)
	{
		$fh = fopen('php://stderr', 'w');
		fwrite($fh, $message);
		fclose($fh);
	}

}