phpMultiCurl
============

PHP Class for Multiple Requests

Sample usage
```PHP
<?

$localhost = 'http://several-work.test/MultiCurl/';
$urls = array(
	$localhost . 'tmp2.php?time=200000',
	array(
		'url' => $localhost . 'tmp.php?time=200000',
		'cookie' => 'foo=bar2;',
	),
	array(
		'method' => 'POST',
		'url' => $localhost . 'tmp.php',
		'postParams' => array(
			'time' => 100000
		),
		'cookie' => 'foo=bar;'
	),
);

$curl = new MultiCurl();
foreach($urls as $i => $url) {
	$requests[$i] = new Request($url);
}

$curl->addRequest($requests)
	->setConcurrency(3)
	->sendRequest();

foreach($urls as $i => $url) {
	var_dump( $curl->getResponseInfo($i) );
}
```
