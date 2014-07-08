<?

class MultiCurl
{
	private $_requests = array();
	private $_response = array();

	private $_concurrency = 10;

	public function __construct()
	{

	}

	public function addRequest($mixed)
	{
		if(is_array($mixed)) {
			foreach ($mixed as $request)
				$this->_pushRequest($request);	
		} else {
			$this->_pushRequest($mixed);
		}

		return $this;
	}

	private function _pushRequest($request)
	{
		if(get_class($request) != 'Request')
			throw new \Exception("Requests should be instances of the Request class.");

		$this->_requests[] = $request;
	}

	public function sendRequest()
	{
		if(empty($this->_requests))
			return false;

		$requestChunks = array_chunk($this->_requests, $this->_concurrency);
		foreach ($requestChunks as $requestChunk) {
			$this->_sendRequestChunk($requestChunk);
		}
	}

	private function _sendRequestChunk($requestChunk)
	{
		$curlMultipleHandler = curl_multi_init();

		$curlHandlers = array();
		foreach ($requestChunk as $i => $request) {
			$curlHandlers[$i] = curl_init();

			switch ($request->getMethod()) {
				case 'GET':

				break;
				
				case 'POST':
					$query = http_build_query($request->getPostParams(), '', '&');
					curl_setopt($curlHandlers[$i], CURLOPT_POST, 1);
					curl_setopt($curlHandlers[$i], CURLOPT_POSTFIELDS, $query);	
				break;
			}

			curl_setopt($curlHandlers[$i], CURLOPT_URL, $request->getUrl());
			curl_setopt($curlHandlers[$i], CURLOPT_HEADER, $request->getReturnHeader());
			curl_setopt($curlHandlers[$i], CURLOPT_RETURNTRANSFER, $request->getReturnTransfer());
			curl_setopt($curlHandlers[$i], CURLOPT_ENCODING, $request->getEncoding());
			curl_setopt($curlHandlers[$i], CURLOPT_COOKIE, $request->getCookie());

			curl_multi_add_handle($curlMultipleHandler, $curlHandlers[$i]);
		}

		do {
    		curl_multi_exec($curlMultipleHandler, $running);
    		curl_multi_select($curlMultipleHandler);
		} while ($running > 0);

		foreach ($requestChunk as $i => &$request) {
			$request->setResponse( curl_multi_getcontent($curlHandlers[$i]) );
			$request->setResponseInfo( curl_getinfo($curlHandlers[$i]) );
			curl_multi_remove_handle($curlMultipleHandler, $curlHandlers[$i]);
		}
		curl_multi_close($curlMultipleHandler);
	}

	public function getResponse($index)
	{
		return $this->_requests[$index]->getResponse();
	}

	public function getResponseInfo($index)
	{
		return $this->_requests[$index]->getResponseInfo();
	}

	public function setConcurrency($concurrency)
	{
		$this->_concurrency = (int) $concurrency;
		return $this;
	}
}

/*
	$url - string with optional GET parameters
	$method - GET, POST
	$returnHeader - boolean
	$returnTransfer - boolean
	$encoding - string
	$postParams = associative array of Post parameters
	$cookie - string 'foo=bar; bar=foo'
*/

class Request
{
	private $url;
	private $method = 'GET';
	private $returnHeader = 0;
	private $returnTransfer = 1;
	private $encoding = 'gzip,deflate';
	private $postParams = array();
	private $cookie = '';

	private $response;
	private $responseInfo;

	public function __construct($mixed)
	{
		if(is_string($mixed)) {
			$this->setUrl($mixed);
		} else {
			if(isset($mixed['url']))
				$this->setUrl($mixed['url']);
			if(isset($mixed['method']))
				$this->setMethod($mixed['method']);
			if(isset($mixed['returnHeader']))
				$this->setReturnHeader($mixed['returnHeader']);
			if(isset($mixed['returnTransfer']))
				$this->setReturnTransfer($mixed['returnTransfer']);
			if(isset($mixed['encoding']))
				$this->setEncoding($mixed['encoding']);
			if(isset($mixed['postParams']))
				$this->setPostParams($mixed['postParams']);
			if(isset($mixed['cookie']))
				$this->setCookie($mixed['cookie']);
		}
	}

	public function __call($name, $arguments)
	{
		$prefix = substr($name, 0, 3);
		$property = strtolower($name[3]) . substr($name, 4);
		switch ($prefix) {
	        case 'get':
	            return $this->$property;
	            break;
	        case 'set':
	            if (count($arguments) != 1) {
	                throw new \Exception("Setter for $name requires exactly one parameter.");
	            }
	            if(isset($arguments[0]))
	            	$this->$property = $arguments[0];

	            return $this;
	        default:
	            throw new \Exception("Property $name doesn't exist.");
	            break;
	    }
	}
}
