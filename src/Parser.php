<?php
declare(strict_types=1);

namespace Kubomikita\iKROS;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Kubomikita\iKROS\Endpoint\IEndpoint;
use Nette\Http\UrlScript;
use Nette\Utils\Json;

class Parser {
	const REGEXP_DOWNLOAD_URL = '/<a href=\"(.*)\".*id="cmdDownload"/m';
	private $url;
	private $regexp;
	public function __construct(string $url,string $regexp = self::REGEXP_DOWNLOAD_URL) {
		$this->url= $url;
		$this->regexp = $regexp;
	}

	public function parse():?string{
		$client = new \GuzzleHttp\Client();
		try {
			bdump($this);
			$response = $client->get(
				$this->url,
				[
					"verify"  => false
				] );
			$body = (string) $response->getBody();

			preg_match($this->regexp, $body, $matches);
			bdump($matches);
			bdump($body);
			//echo $body;exit;
			$remoteUrl = new UrlScript($this->url);
			return $remoteUrl->getBaseUrl().substr($matches[1],1);

		} catch (\Exception $e){
			return null;
		}
	}
}