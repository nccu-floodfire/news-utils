<?php namespace Us\Utils\NewsService;
use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;

class NewsClient
{
	private $_client = null;
	private $_hot_api_url = 'http://news-ckip.source.today/api/news/v1/hot/';
	private $_all_api_url = 'http://news-ckip.source.today/api/news/v1/all/';

	public function __construct()
	{
		$this->_client = new Client();
	}

	public function getHotNewsByDate($date)
	{
		try {
			$request = $this->_client->createRequest('GET', $this->_hot_api_url . $date);
			$response = $this->_client->send($request);
			$json = $response->json();
			return $json;
		} catch (RequestException $e) {
			$this->error_output($e->getRequest() . "\n");
			if ($e->hasResponse()) {
				$this->error_output($e->getResponse() . "\n");
			}
		}
	}

	public function getAllNewsByDate($date)
	{
		try {
			$request = $this->_client->createRequest('GET', $this->_all_api_url . $date);
			$response = $this->_client->send($request);
			$json = $response->json();
			return $json;
		} catch (RequestException $e) {
			$this->error_output($e->getRequest() . "\n");
			if ($e->hasResponse()) {
				$this->error_output($e->getResponse() . "\n");
			}
		}
	}

	private function error_output($message)
	{
		$fh = fopen('php://stderr', 'w');
		fwrite($fh, $message);
		fclose($fh);
	}
}