<?php
return [
	'settings' => [
		'displayErrorDetails' => true,
		'db' => [
			'driver' => 'mysql',
			'host' => 'localhost',
			'database' => 'xxx',
			'username' => 'root',
			'password' => 'root',
			'charset' => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix' => ''
		],
		'jwt' => [
			'issuer' => 'ACME Sdn Bhd',
			'notBefore' => 0,
			'expired' => 3600,
			'secret' => '09FA7BE0A09C30D0050B05C6EE9974938094E5FF1C0FC59A3A6776242D0787D4'
		],
		'config' => [
			'base_url' => 'http://127.0.0.1:2000',
			'app_name' => 'ACME Sdn Bhd'
		],
		'logger' => [
			'name' => 'slim-app',
			'path' => __DIR__ . '/../logs/app.log',
			'level' => \Monolog\Logger::DEBUG,
		],
		'email' => [
			'sender' => 'ACME Sdn Bhd',
			'protocol' => 'smtp',
			'host' => 'smtp.gmail.com',
			'smtpauth' => true,
			'username' => 'ahmadmuhamad101@gmail.com',
			'password' => 'xxxxxxxxx',
			'smtpsecure' => 'tls',
			'port' => 587,
		],
		'shipping' => [
			'self_name' => 'ACME Sdn Bhd',
			'self_address' => '111 ACME, foo bar',
			'self_poscode' => 14500, //local town delivery
			'self_city' => 'Kota Bharu',
			'self_state' => 'Kelantan',
			'self_phone' => '+60171111111',
			'self_fax' => '-',
			'self_email' => 'arma7x@live.com',
			'self_zone' => 'zone_1',
			'zone_1' => ['kuala-lumpur' => 'Kuala Lumpur',
				     'pulau-pinang' => 'Pulau Pinang',
				     'selangor' => 'Selangor',
				     'melaka' => 'Melaka',
				     'negeri-sembilan' => 'Negeri Sembilan',
				     'pahang' => 'Pahang',
				     'johor' => 'Johor',
				     'terengganu' => 'Terengganu',
				     'perak' => 'Perak',
				     'perlis' => 'Perlis',
				     'kedah' => 'Kedah',
				     'kelantan' => 'Kelantan'],
			'zone_2' => ['sarawak' => 'Sarawak'],
			'zone_3' => ['sabah' => 'Sabah'],
			'tax' => 15 + 10 + 6 + 1.5, //15% fuel surcharge, 10% handling charges 6% GST and 1.5% misc
		],
	],
];


