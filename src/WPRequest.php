<?php

namespace BookingshowWebAPI;

class WPRequest
{
    const API_URL = 'http://sede.bookingshow.com:8140/api';
    
    const RETURN_ASSOC = 'assoc';
	const RETURN_OBJECT = 'object';

    protected $lastResponse = [];
    protected $returnAssoc = false;
    protected $returnType = self::RETURN_OBJECT;


	/**
     * Parse the response body and handle API errors.
     *
     * @param string $body The raw, unparsed response body.
     * @param int $status The HTTP status code, used to see if additional error handling is needed.
     *
     * @throws BookingshowWebAPIException
     *
     * @return array|object The parsed response body. ////////Type is controlled by `Request::setReturnType()`.
     */
    protected function parseBody( $response, $status = null)
    {
		// php 7
		//$status = $status ?? $this->parseStatusCode($response);
		// php 5
		$status = (!empty($status)) ? $status : $this->parseStatusCode($response);
        
        $this->lastResponse['body'] = json_decode(wp_remote_retrieve_body($response), $this->returnType == self::RETURN_ASSOC);
        
        //echo "<pre>".print_r($this->lastResponse,true)."</pre>";
        
        if ($status >= 200 && $status <= 299) {
            return $this->lastResponse['body'];
        }

        $error = json_decode( wp_remote_retrieve_body($response) );        
        //convert bookingshow errors to lowercase
        $error = (object)array_combine(array_map('strtolower', array_keys((array)$error)), (array)$error);

        if (isset($error->message) && isset($error->message)) {
            // API call error
            throw new BookingshowWebAPIException($error->message, $error->code);
        } else {
            // Something went really wrong
            throw new BookingshowWebAPIException('An unknown error occurred.', $status);
        }
        
        return false;
         
    }


    protected function parseHeaders( $response )
    {
        return wp_remote_retrieve_headers( $response );
    }
    
    protected function parseStatusCode( $response )
    {
        return wp_remote_retrieve_response_code( $response );
    }


    public function api($method, $uri, $parameters = [], $headers = [])
    {
        return $this->send($method, $uri, $parameters, $headers);
    }


    public function getLastResponse()
    {
        return $this->lastResponse;
    }


    public function send($method, $ep, $parameters = [], $headers = [])
    {
		
		// Reset any old responses
        $this->lastResponse = [];
        
        $url = self::API_URL . $ep;
        
        // Sometimes a stringified JSON object is passed
        //if (is_array($parameters) || is_object($parameters)) {
        //    $parameters = http_build_query($parameters);
        //}
		
		$default_headers = array(
			// BS: usa header custom "X-Auth-Token" che viene passato come parametro
			//'Authorization' => 'Bearer ' . $auth,
			// BS: certi EP ritornano errore con Content-Type 'application/json'
			// viene quindi settato dinamicamente 
			//'Content-Type'  => 'application/json; charset=UTF-8',
			'Host'          => 'zero.eu',
			'Accept'        => 'application/json;ver=1.0',
			'User-Agent'	=> 'ZERO-BsConnector/0.10'
		);
		
		$headers = wp_parse_args($headers, $default_headers);
		
		$request = array(
			'headers' => $headers,
			'method'  => $method,
			'body'	  => $parameters
		);
		
		// prior to wp 4.7 class-http does not convert 
		// wp_remote_request body's arguments for GET requests into query string
		// so i do a version check
		$version = get_bloginfo('version');
		if ($version < 4.7) {
			if( $method == 'GET' ) {
				// Sometimes a stringified JSON object is passed
				if (is_array($parameters) || is_object($parameters)) {
				    $url = add_query_arg( $parameters, $url );
				}
				$request['body'] = null;
			} 	
		} 
		
		
		// fuck bookingshow!
		// apikey deve sempre essere in querystring
		if (is_array($parameters) || is_object($parameters)) {				
			if( isset($request['body']['apikey']) ) {
				$url = add_query_arg( array('apiKey' => $request['body']['apikey']), $url );
				unset($request['body']['apikey']);
			}				
		}
		

		echo '<pre>'; 
			echo "{$url}\n\n"; 
			print_r($request); 
			//echo "\n";
			//print_r( json_encode($request['body'])); 
		echo '</pre>';
		//die();

		$response = wp_remote_request( $url, $request );
		
		// Check for error
		if ( is_wp_error( $response ) ) 
			throw new BookingshowWebAPIException('Something went really wrong');
		
		$status = $this->parseStatusCode( $response );
		$headers = $this->parseHeaders( $response );
		$this->lastResponse = [
			'headers' 	=> $headers,
			'status' 	=> $status,
			'url' 		=> $url,
		];
	
		try {
		
			$body = $this->parseBody($response, $status);
		
		} catch(BookingshowWebAPIException $e) {
			
			return new \WP_Error(
				'bs_api_error',
				"BookingShow error: " . $e->getMessage() . "\n" . " Exception code:" . $e->getCode(),
				array(
					'responseObj' 	=> $response
				)
			);
		
		}
		
		return $this->lastResponse;
		
    }
    
    
    /**
     * Get a value indicating the response body type.
     *
     * @return string A value indicating if the response body is an object or associative array.
     */
    public function getReturnType()
    {
        return $this->returnType;
    }
    
    
    /**
     * Set the return type for the response body.
     *
     * @param string $returnType One of the `Request::RETURN_*` constants.
     *
     * @return void
     */
    public function setReturnType($returnType)
    {
        $this->returnAssoc = $returnType == self::RETURN_ASSOC;
        $this->returnType = $returnType;
    }

}

