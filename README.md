phpMultiCurl
============

PHP Class for Multiple Requests

Sample usage
```PHP
<?
include 'MultiCurl.php';

$localhost = 'http://several-work.test/MultiCurl/';
$urls = array(
	//404 error
	$localhost . 'tmp2.php?time=200000', 
	
	//get with cookie
	array(								
		'url' => $localhost . 'tmp.php?time=200000', 
		'cookie' => 'foo=bar2;',
	),
	
	//post with post fields and cookie
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
	//response info 'curl_getinfo'
	var_dump( $curl->getResponseInfo($i) );
	//response 'curl_multi_getcontent'
	var_dump( $curl->getResponse($i) );
}
```
