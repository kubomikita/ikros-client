<?php

include __DIR__.'/../vendor/autoload.php';

\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT);

//dump(get_declared_classes());

$client = new \Kubomikita\iKROS\Client('3c7bdf3a-19cf-42a4-9b7e-beda42044f06');
$client->setCompany("adv accounting s.r.o.");
$client->setSavePath(__DIR__."/temp/");


$invoice = new \Kubomikita\iKROS\Endpoint\Invoice();
//$invoice->setNumberingSequence("OF2");
$invoice->setOpeningText(
	"Na základe zmluvy č. XXXXXX Vám fakturujeme vykonané služby."
);
$invoice->setClient([
	"name" => "Jakub Mikita",
	"street" => "Okružná 105",
	"postCode" => "06401",
	"town" => "Stará Ľubovňa",
	"phone" => "+421911531872",
	"email" => "kubomikita@gmail.com",

	"registrationId" => 50305964,
	"taxId" => 1079478048,
	"vatId" => "SK1079478048",
]);

$invoice->setSender([
	"contactName" => "Kolejová janka",
	"phone" => "055664411",
	"web" => "advaccounting.sk",
	"email" => "adv.ucto.sl@gmail.com"
]);

$invoice->setItems([
	[
		"name" => "Vypracovanie danoveho priznania",
		"description" => "za rok 2018",
		"unitPrice" => 120.99,
		"count" => 2,
		// Typ produktu (0 voľná položka, 1 tovar, 2 služba)
		"typeId" => 2
	],
	[
		"name" => "Poradenstvo a administratívne sluzby",
		"unitPrice" => 15,
		"measureType" => "hod.",
		"count" => 12,
		"typeId" => 2
	]
]);

$client->add($invoice);
$response = $client->send();

dump($response[\Kubomikita\iKROS\Endpoint\Invoice::INVOICE_RESOURCE]["documents"]);
