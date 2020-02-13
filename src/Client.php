<?php
declare(strict_types=1);

namespace Kubomikita\iKROS;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Kubomikita\iKROS\Endpoint\IEndpoint;
use Nette\Utils\Json;

class Client {
	/** @var \GuzzleHttp\Client */
	private $curlClient;
	/** @var string  */
	private $token;
	/** @var string  */
	private $apiUrl = 'https://eshops.inteo.sk';
	/** @var IEndpoint[]  */
	private $items = [];
	/** @var callable  */
	public $onSuccess = [];
	/** @var string  */
	private $company = 'invoice';

	private $savePath;

	public function __construct(string $token){
		$this->token = $token;
		$this->curlClient = new \GuzzleHttp\Client([
			"base_uri" => $this->getApiUrl(),
			"verify" => false,
			"decode_content" => false
		]);
	}

	/**
	 * @param mixed $savePath
	 */
	public function setSavePath( $savePath ): void {
		$this->savePath = $savePath;
	}

	/**
	 * @return mixed
	 */
	public function getSavePath() {
		return $this->savePath;
	}
	/**
	 * @param string $company
	 */
	public function setCompany( string $company ): void {
		$this->company = $company;
	}

	/**
	 * @return string
	 */
	public function getCompany(): string {
		return $this->company;
	}

	/**
	 * @return string
	 */
	public function getToken(): string {
		return $this->token;
	}

	/**
	 * @param string $apiUrl
	 */
	public function setApiUrl( string $apiUrl ): void {
		$this->apiUrl = $apiUrl;
	}

	/**
	 * @return string
	 */
	public function getApiUrl(): string {
		return $this->apiUrl;
	}

	public function add(IEndpoint $endpoint) : self {
		$this->items[] = $endpoint;
		return $this;
	}

	public function getHeaders():array {
		return [
			//"Content-Encoding" => "UTF-8",
			//"Content-Type" => "application/json",
			//"Accept" => "application/json",
			"Authorization" => "Bearer " . $this->getToken(),
			//"X-Foo" => "Bar",
		];
	}

	public function send():?array{
		$responses = [];
		$items = [];
		try {

			foreach ( $this->items as $item ) {
				$items[$item->getResource()][] = $item;
			}

			foreach($items as $endpoint => $endpoints){
				$data = [];
				$method = "GET";
				$callback = [];
				/** @var IEndpoint $item */
				foreach ($endpoints as $item){
					$d = $item->getData();
					if($d !== null){
						$data[] = $d;
					}
					$method = $item->getMethod();
					$callback = $item->getCallback();
				}
				$this->onSuccess[] = $callback;
				$options = [
					RequestOptions::HEADERS => $this->getHeaders(),
				];
				if(!empty($data)){
					$options[RequestOptions::JSON] = $data;
				}
				$call = $this->curlClient->request( $method, $endpoint, $options );
				$response = Json::decode((string) $call->getBody(), Json::FORCE_ARRAY);

				foreach($this->onSuccess as $callback){
					$result = $callback($response, $this);
					$response = $result + $response;
				}

				$responses[$endpoint] = $response;
			}

			return $responses;

		} catch (ClientException $e){
			trigger_error($e->getMessage(),E_USER_WARNING);
		}

	}



}