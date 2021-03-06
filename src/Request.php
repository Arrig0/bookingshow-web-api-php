<?php

namespace BookingshowWebAPI;

class Request
{
    const API_URL = 'http://sede.bookingshow.com:8140/api';
    //const API_URL = 'https://bookingshow.it/api';

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
     * @return array|object The parsed response body. Type is controlled by `Request::setReturnType()`.
     */
    protected function parseBody($body, $status, $json_response)
    {
		
		if( $json_response ) {
		
			$this->lastResponse['body'] = json_decode($body, $this->returnType == self::RETURN_ASSOC);

			if ($status >= 200 && $status <= 299) {
				return $this->lastResponse['body'];
			}
        
		} else {
			
			$this->lastResponse['body'] = $body;
			
			if ($status >= 200 && $status <= 299) {
				return $this->lastResponse['body'];
			}
			
		}

		// Error Handling

        $error = json_decode($body);
        //convert bookingshow response keys to lowercase
        $error = (object)array_combine(array_map('strtolower', array_keys((array)$error)), (array)$error);

        if (isset($error->message) && isset($error->status)) {
            // API call error
            throw new BookingshowWebAPIException($error->message, $error->status);
        } elseif (isset($error->message)) {
            // Auth call error
            throw new BookingshowWebAPIException($error->message, $status);
        } else {
            // Something went really wrong
            throw new BookingshowWebAPIException('An unknown error occurred.', $status);
        }
    }

    /**
     * Parse HTTP response headers.
     *
     * @param string $headers The raw, unparsed response headers.
     *
     * @return array Headers as key–value pairs.
     */
    protected function parseHeaders($headers)
    {
        $headers = str_replace("\r\n", "\n", $headers);
        $headers = explode("\n", $headers);

        array_shift($headers);

        $parsedHeaders = [];
        foreach ($headers as $header) {
            list($key, $value) = explode(':', $header, 2);

            $parsedHeaders[$key] = trim($value);
        }

        return $parsedHeaders;
    }


    /**
     * Make a request to the "api" endpoint.
     *
     * @param string $method The HTTP method to use.
     * @param string $uri The URI to request.
     * @param array $parameters Optional. Query string parameters or HTTP body, depending on $method.
     * @param array $headers Optional. HTTP headers.
     * @param bool $json_response Optional. Wherever the expected response is in json...
     *
     * @return array Response data.
     * - array|object body The response body. Type is controlled by `Request::setReturnType()`.
     * - string headers Response headers.
     * - int status HTTP status code.
     * - string url The requested URL.
     */
    public function api($method, $uri, $parameters = [], $headers = [], $json_response = true)
    {
        return $this->send($method, self::API_URL . $uri, $parameters, $headers, $json_response);
    }
    
    public function apiTransfer($method, $uri, $parameters = [], $headers = [], $file = true)
    {
        return $this->transfer($method, self::API_URL . $uri, $parameters, $headers, $file);
    }

    /**
     * Get the latest full response from the BookingShow API.
     *
     * @return array Response data.
     * - array|object body The response body. Type is controlled by `Request::setReturnType()`.
     * - array headers Response headers.
     * - int status HTTP status code.
     * - string url The requested URL.
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Get a value indicating the response body type.
     *
     * @deprecated Use `Request::getReturnType()` instead.
     *
     * @return bool Whether the body is returned as an associative array or an stdClass.
     */
    public function getReturnAssoc()
    {
        trigger_error(
            'Request::getReturnAssoc() is deprecated. Use Request::getReturnType() instead.',
            E_USER_DEPRECATED
        );

        return $this->returnAssoc;
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
     * Make a request to BookingShow.
     * You'll probably want to use one of the convenience methods instead.
     *
     * @param string $method The HTTP method to use.
     * @param string $url The URL to request.
     * @param array $parameters Optional. Query string parameters or HTTP body, depending on $method.
     * @param array $headers Optional. HTTP headers.
     *
     * @throws BookingshowWebAPIException
     *
     * @return array Response data.
     * - array|object body The response body. Type is controlled by `Request::setReturnType()`.
     * - array headers Response headers.
     * - int status HTTP status code.
     * - string url The requested URL.
     */
    public function send($method, $url, $parameters = [], $headers = [], $json_response = true)
    {
		
        // Reset any old responses
        $this->lastResponse = [];

		// Sometimes a stringified JSON object is passed
        if (is_array($parameters) || is_object($parameters)) {
            $parameters = http_build_query($parameters);
        }

        $mergedHeaders = [];
        foreach ($headers as $key => $val) {
            $mergedHeaders[] = "$key: $val";
        }

		// https://docs.bolt.cm/3.2/howto/curl-ca-certificates
		// curl --remote-name --time-cond cacert.pem https://curl.haxx.se/ca/cacert.pem
        $options = [
            CURLOPT_CAINFO => __DIR__ . '/cacert.pem',
            CURLOPT_ENCODING => '',
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $mergedHeaders,
            CURLOPT_RETURNTRANSFER => true,
        ];

        $url = rtrim($url, '/');
        $method = strtoupper($method);

        switch ($method) {
            case 'DELETE': // No break
            case 'PUT':
                $options[CURLOPT_CUSTOMREQUEST] = $method;
                $options[CURLOPT_POSTFIELDS] = $parameters;

                break;
            case 'POST':
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_POSTFIELDS] = $parameters;

                break;
            default:
                $options[CURLOPT_CUSTOMREQUEST] = $method;

				//echo 'PARAMS OF GET REQUEST: <pre>'; print_r($parameters); echo '</pre>';

                if ($parameters) {
                    //$url .= '/?' . $parameters;
                    //$url .= '&' . $parameters;
                    
                   $parsedUrl = parse_url($url);
				   if ($parsedUrl['path'] == null) {
					  $url .= '/';
				   }
				   
				   $separator = ( !isset($parsedUrl['query']) ) ? '?' : '&';
				
					$url .= $separator . $parameters;
                    
                }

                break;
        }

        $options[CURLOPT_URL] = $url;

        $ch = curl_init();
        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);

        if (curl_error($ch)) {
            throw new BookingshowWebAPIException('cURL transport error: ' . curl_errno($ch) . ' ' .  curl_error($ch));
        }

        list($headers, $body) = explode("\r\n\r\n", $response, 2);

        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headers = $this->parseHeaders($headers);

        $this->lastResponse = [
            'headers' => $headers,
            'status' => $status,
            'url' => $url,
        ];

        // Run this here since we might throw
        $body = $this->parseBody($body, $status, $json_response);

        curl_close($ch);

        return $this->lastResponse;
    }
    
    
    /**
     * Make a transfer request to BookingShow.
     * You'll probably want to use one of the convenience methods instead.
     *
     * @param string $method The HTTP method to use.
     * @param string $url The URL to request.
     * @param array $parameters Optional. Query string parameters or HTTP body, depending on $method.
     * @param array $headers Optional. HTTP headers.
     *
     * @throws BookingshowWebAPIException
     *
     * @return array Response data.
     * - array|object body The response body. Type is controlled by `Request::setReturnType()`.
     * - array headers Response headers.
     * - int status HTTP status code.
     * - string url The requested URL.
     */
    public function transfer($method, $url, $parameters = [], $headers = [], $file = '')
    {
		
        // Reset any old responses
        $this->lastResponse = [];

		// Sometimes a stringified JSON object is passed
        if (is_array($parameters) || is_object($parameters)) {
            $parameters = http_build_query($parameters);
        }

        $mergedHeaders = [];
        foreach ($headers as $key => $val) {
            $mergedHeaders[] = "$key: $val";
        }

		// https://docs.bolt.cm/3.2/howto/curl-ca-certificates
		// curl --remote-name --time-cond cacert.pem https://curl.haxx.se/ca/cacert.pem
        $options = [
            CURLOPT_CAINFO => __DIR__ . '/cacert.pem',
            CURLOPT_ENCODING => '',
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $mergedHeaders,
            CURLOPT_RETURNTRANSFER => true,            
            CURLOPT_FILE => $file,
			CURLOPT_BINARYTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true
        ];

        $url = rtrim($url, '/');
        $method = strtoupper($method);

        switch ($method) {
            case 'POST':
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_POSTFIELDS] = $parameters;

                break;
            default:
                $options[CURLOPT_CUSTOMREQUEST] = $method;

                if ($parameters) {
                    
                   $parsedUrl = parse_url($url);
				   if ($parsedUrl['path'] == null) {
					  $url .= '/';
				   }
				   
				   $separator = ( !isset($parsedUrl['query']) ) ? '?' : '&';
				
					$url .= $separator . $parameters;
                    
                }

                break;
        }

        $options[CURLOPT_URL] = $url;

        $ch = curl_init();
        curl_setopt_array($ch, $options);

        curl_exec($ch);

        if (curl_error($ch)) {
            throw new BookingshowWebAPIException('cURL transport error: ' . curl_errno($ch) . ' ' .  curl_error($ch));
        }

        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($status == 200){
			$response = 'Downloaded!';
		} else{
			$response = "Error getting pdf - Status Code: " . $status ;
		}

        curl_close($ch);

        return $response;
    }

    

    /**
     * Set the return type for the response body.
     *
     * @deprecated Use `Request::setReturnType()` instead.
     *
     * @param bool $returnAssoc Whether to return an associative array or an stdClass.
     *
     * @return void
     */
    public function setReturnAssoc($returnAssoc)
    {
        trigger_error(
            'Request::setReturnType() is deprecated. Use Request::setReturnType() instead.',
            E_USER_DEPRECATED
        );

        $this->returnAssoc = $returnAssoc;
        $this->returnType = $returnAssoc ? self::RETURN_ASSOC : self::RETURN_OBJECT;
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

