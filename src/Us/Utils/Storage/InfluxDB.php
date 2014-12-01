<?php
namespace Us\Utils\Storage;
use \crodas\InfluxPHP\Client;

class InfluxDB implements StorageInterface
{
	protected  $_DbObj = null;
	protected  $_db_name = '';
	protected  $_db_host = '127.0.0.1';
	protected  $_db_username = '';
	protected  $_db_password = '';

	function __construct($db_name, $db_username = 'user', $db_password = 'password', $db_host = '127.0.0.1')
	{
		$this->_db_username = $db_username;
		$this->_db_password = $db_password;
		$this->_db_name = $db_name;
		$this->_db_host = $db_host;
		$this->doConnection();
	}

	private function doConnection()
	{
		$client = new Client(
			$this->_db_host, // host
			8086, // port
			$this->_db_username, // username
			$this->_db_password // password
		);
		$this->_DbObj = $client->{$this->_db_name}; // DB name
	}

	public function InsertNews($array, $table_name = 'table')
	{
		$this->_DbObj->insert($table_name, $array);
	}
}
