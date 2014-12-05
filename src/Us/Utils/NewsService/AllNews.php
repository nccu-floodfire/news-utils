<?php namespace Us\Utils\NewsService;
use \Us\Utils\Storage\StorageInterface;
use \PDO;

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
		fwrite(STDERR, "application start for {$this->_config['date']} \n");
		$NewsClient = new NewsClient();

		$news = $NewsClient->getAllNewsByDate($this->_config["date"]);
		$Dbh = new PDO(
			'mysql:host=127.0.0.1;dbname=newsdiff',
			'root',
			'',
			array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
		);
		$Dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$date = $this->_config['date'];
		$ts_start = strtotime($date);
		$ts_end = $ts_start + 86400 - 1;

		// saving data from news api to influxDB
		foreach ($news["data"] as $data) {
			try {
				$term = $data['term'];
				fwrite(STDERR, "[{$date}] TERM: {$term} ...");

				$stmt = $Dbh->prepare("select sum(share_count) as share_c, sum(comment_count) as comment_c from news where id in (select news_id from news_info where `time` between $ts_start and $ts_end and title like '%$term%');");
				$stmt->execute();
				$res = $stmt->fetch(PDO::FETCH_ASSOC);
				$share = $res['share_c'];
				$comment = $res['comment_c'];
				fwrite(STDERR, " share: $share, comment: $comment\n");

				$input = $data;
				$input["time"] = strtotime($date);
				$input['share_count'] = (int)$share;
				$input['comment_count'] = (int)$comment;
				$input['term'] = $term;
				$this->_storage->InsertNews($input, $term);

			} catch (\Exception $e) {
				$this->error_output($e->getMessage() . "\n");
				continue;
			}
		}
		$this->error_output("\nfinished.\n");
	}

	private function error_output($message)
	{
		$fh = fopen('php://stderr', 'w');
		fwrite($fh, $message);
		fclose($fh);
	}

}