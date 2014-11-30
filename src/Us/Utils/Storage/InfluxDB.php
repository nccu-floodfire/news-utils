<?php
namespace Us\Utils\Storage;
use \crodas\InfluxPHP\Client;

class InfluxDB implements StorageInterface
{
	public $db = null;
	private $_db_username = '';
	private $_db_password = '';

	function __construct($db_username, $db_password = '')
	{
		$this->_db_username = $db_username;
		$this->_db_password = $db_password;
		$this->doConnection();
	}

	private function doConnection()
	{
		$client = new Client(
			"192.168.56.100", // host
			8086, // port
			$this->_db_username, // username
			$this->_db_password // password
		);
		$this->db = $client->news; // DB name
	}

	public function InsertNews($array)
	{
		$this->db->insert("news4", $array);
	}
}
