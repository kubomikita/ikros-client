<?php
declare(strict_types=1);

namespace Kubomikita\iKROS\Endpoint;

use Kubomikita\iKROS\Client;
use Kubomikita\iKROS\NoDataException;
use Kubomikita\iKROS\NoResponseException;
use Kubomikita\iKROS\Parser;
use Nette\Utils\Strings;

class Invoice implements IEndpoint {
	const INVOICE_RESOURCE = "/api/v1/invoices";
	const INVOICE_METHOD = "POST";
	/** @var array  */
	private $client = [];
	/** @var array  */
	private $sender = [];
	/** @var string  */
	private $currency = "EUR";
	/** @var string */
	private $numberingSequence;
	/** @var string */
	private $openingText;
	/** @var string */
	private $closingText;
	/** @var string  */
	private $paymentType = "bankovým prevodom";
	/** @var array  */
	private $items = [];

	public function getMethod(): string {
		return self::INVOICE_METHOD;
	}
	public function getResource(): string {
		return self::INVOICE_RESOURCE;
	}
	public function getData():?array {
		if(empty($this->items)){
			throw new NoDataException("Items on invoice missing.");
		}
		if(empty($this->client)){
			throw new NoDataException("Client inforamtion on invoice missing.");
		}
		$data = [
			"currency" => $this->currency,
			"openingText" => $this->openingText,
			"closingText" => $this->closingText,
			"paymentType" => $this->paymentType,
			"items" => $this->items,
		];
		if($this->numberingSequence !== null){
			$data["numberingSequence"] = $this->numberingSequence;
		}
		$data += $this->getClient();
		$data += $this->getSender();

		return $data;
	}
	public function getCallback(): callable {
		return [$this, "downloadInvoice"];
	}
	public function downloadInvoice(array $data, Client $client) : ?array {
		foreach ($data["documents"] as $key => $document){
			if(isset($document["downloadUrl"])) {
				$parser = new Parser( $document["downloadUrl"] );
				$d = new \Kubomikita\iKROS\Downloader( $parser->parse(),Strings::webalize( $client->getCompany() ) . "_" );
				if ($savePath = $client->getSavePath()) {
					$d->setSavePath( $client->getSavePath() );
				}
				$result = $d->save();
				if ( $result == null ) {
					throw new NoResponseException( "Downloader response is null" );
				}
				$data["documents"][ $key ]["downloadedFile"] = $result;
			} else {
				throw new NoResponseException("Download url of invoice is empty!");
			}
		}
		return (array) $data;
	}

	/**
	 * @param array $client
	 */
	public function setClient( array $client ): void {
		$this->client = $client;
	}

	/**
	 * @return array
	 */
	public function getClient(): array {
		$client = [];
		foreach($this->client as $k => $v){
			$client["client".Strings::firstUpper($k)] = $v;
		}
		return $client;
	}

	/**
	 * @param string $currency
	 */
	public function setCurrency( string $currency ): void {
		$this->currency = $currency;
	}

	/**
	 * @param array $sender
	 */
	public function setSender( array $sender ): void {
		$this->sender = $sender;
	}

	/**
	 * @return array
	 */
	public function getSender(): array {
		$sender = [];
		foreach($this->sender as $k => $v){
			$sender["sender".Strings::firstUpper($k)] = $v;
		}
		return $sender;
	}

	/**
	 * @param string $numberingSequence
	 */
	public function setNumberingSequence( string $numberingSequence ): void {
		$this->numberingSequence = $numberingSequence;
	}

	/**
	 * @param array $items
	 */
	public function setItems( array $items ): void {
		$this->items = $items;
	}

	/**
	 * @param string $closingText
	 */
	public function setClosingText( string $closingText ): void {
		$this->closingText = $closingText;
	}

	/**
	 * @param string $openingText
	 */
	public function setOpeningText( string $openingText ): void {
		$this->openingText = $openingText;
	}

	/**
	 * @param string $paymentType
	 */
	public function setPaymentType( string $paymentType ): void {
		$this->paymentType = $paymentType;
	}

}
