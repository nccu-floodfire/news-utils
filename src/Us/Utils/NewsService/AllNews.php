<?php namespace Us\Utils\NewsService;
use \Us\Utils\Storage\StorageInterface;

class AllNews
{
	private $_storage = null; //storage object
	private $_config = array(); // setting parameters array

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
		$NewsClient = new NewsClient();

		$news = $NewsClient->getAllNewsByDate($this->_config["date"]);

		// saving data from news api to influxDB
		foreach ($news["data"] as $data) {
			$data["time"] = strtotime(date($this->_config["date"]));
			$data['type'] = 1;
			try {
				$this->_storage->InsertNews($data, $data['term']); // term as table name
			} catch (\Exception $e) {
				$this->error_output($e->getMessage() . "\n");
				return false;
			}
			$this->error_output("data inserted. \n");
		}
		$this->error_output("finished. \n");
	}

	private function error_output($message)
	{
		$fh = fopen('php://stderr', 'w');
		fwrite($fh, $message);
		fclose($fh);
	}

}