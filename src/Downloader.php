<?php
declare(strict_types=1);

namespace Kubomikita\iKROS;



use Nette\Utils\FileSystem;

class Downloader {

	const FILE_PDF = [
		"mime" => "application/pdf",
		"ext" => "pdf",
	];

	private $url;
	private $savePath = __DIR__;
	private $type;
	private $prefix = "invoice";

	public function __construct(string $url, string $prefix, array $type = self::FILE_PDF) {
		$this->url = $url;
		$this->prefix = $prefix;
		$this->type = $type;
	}

	/**
	 * @param string $prefix
	 */
	public function setPrefix( string $prefix ): void {
		$this->prefix = $prefix;
	}

	/**
	 * @return string
	 */
	public function getPrefix(): string {
		return $this->prefix;
	}
	/**
	 * @param string $savePath
	 */
	public function setSavePath( string $savePath ): void {
		$this->savePath = $savePath;
	}

	/**
	 * @return string
	 */
	public function getSavePath(): string {
		return $this->savePath;
	}

	public function save():?array {
		$return =[];
		$client = new \GuzzleHttp\Client();
		$save_to = $this->savePath."/".$this->prefix.".".$this->type["ext"];
		if(!file_exists($this->savePath)){
			FileSystem::createDir($this->savePath);
		}
		try {
			$response = $client->get(
				$this->url,
				[
					'save_to' => $save_to,
					"verify"  => false
				] );
			$return["file"] = $save_to;
			$return["mimeType"] = $response->getHeader("content-type")[0];
			$return["fileSize"] = $response->getHeader("content-length")[0];
			$return["originalName"] = $this->getOriginalName($response->getHeader("content-disposition")[0]);
			$return["number"] = $this->getFileNumber($return["originalName"]);

			if($return["number"] !== null && strlen($return["number"]) > 0){
				$return["file"] = $this->renameFile($save_to, $this->savePath.DIRECTORY_SEPARATOR.$this->prefix.$return["number"].".".$this->type["ext"]);
			}

			return $return;
		} catch (\Exception $e){
			return null;
		}

	}

	public function getOriginalName( string $content_disposition):?string{
		preg_match('/\"(.*)\"/m',$content_disposition,$matches);
		return $matches[1];
	}
	public function getFileNumber(string $original_name):?string{
		preg_match('/([_A-Za-z]+)([0-9]+)\.pdf/m',$original_name,$matches);
		return $matches[2];
	}
	public function renameFile(string $temp,string $new):string {
		//FileSystem::rename($temp,$new);
		FileSystem::copy($temp,$new); // bugfix - rename sometimes throws exception unable to rename 
		return $new;
	}
}