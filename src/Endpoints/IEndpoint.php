<?php
declare(strict_types=1);

namespace Kubomikita\iKROS\Endpoint;

interface IEndpoint {

	public function getResource() :string;
	public function getMethod():string ;
	public function getData():?array;
	public function getCallback():callable;
}