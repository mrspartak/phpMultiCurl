<?

include 'MultiCurl.php';

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

$start = gettimeofday(true);

$curl = new MultiCurl();
foreach($urls as $i => $url) {
	$requests[$i] = new Request($url);
}

$curl->addRequest($requests)
	->setConcurrency(3)
	->sendRequest();

echo gettimeofday(true) - $start, '<Br>';


foreach($urls as $i => $url) {
	var_dump( $curl->getResponseInfo($i) );
}