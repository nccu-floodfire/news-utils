<?php namespace Us\Utils\NewsService;
use \Us\Utils\Storage\StorageInterface;
use Us\Utils\Facebook\FacebookSocialClient;

class HotNews
{
	private $_config = array(); // setting parameters array
	private $_storage = null;

	public function __construct(StorageInterface $storage, $config)
	{
		date_default_timezone_set("Asia/Taipei");
		$this->set_config($storage, $config);
	}

	public function set_config($storage, $config)
	{
		// facebook app id & secret
		$this->_config["app_id"] = $config["app_id"];
		$this->_config["app_secret"] = $config["app_secret"];
		// sleep time during every single connection request
		$this->_config["sleep"] = (!isset($config["sleep"])) ? 1 : $config["sleep"];
		// news date
		$this->_config["date"] = (!isset($config["date"])) ? date("Y-m-d") : $config["date"];
		$this->_storage = $storage;
	}

	public function run()
	{
		$this->error_output("application start for {$this->_config['date']} \n");
		$HttpHelper = new NewsClient();

		$news = $HttpHelper->getHotNewsByDate($this->_config["date"]);

		// parsing data from news api
		$count = 1;
		foreach ($news["data"] as &$data) {
			foreach ($data["news"] as &$item) {
				$input = array(
					'term' => $data['term'],
					'url' => $item['url'],
					'date' => strtotime($news['date']),
					'time' => $item['time'],
					'title' => $item['title'],
					'source' => $item['source'],
					'share_count' => $item['share_count'],
					'comment_count' => $item['comment_count'],
					'type' => 2
				);
				$count++;
				$this->_storage->InsertNews($input, $data['term']); // term as table name
				sleep($this->_config["sleep"]);
			}
		}
		$this->error_output("parsing finished. \n");
		//$news is an array which has been added share_count
		return true;
	}

	public function getGraphObj($url)
	{
		$FacebookSocialClient = FacebookSocialClient::getInstance($this->_config);
		$GraphObj = $FacebookSocialClient->getLinkGraphObj($url);

		return $GraphObj;
	}



	private function error_output($message)
	{
		$fh = fopen('php://stderr', 'w');
		fwrite($fh, $message);
		fclose($fh);
	}

}