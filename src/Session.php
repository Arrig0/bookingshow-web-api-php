<?php

namespace BookingshowWebAPI;

class Session
{
    protected $accessToken = '';
    protected $clientKey = '';
    protected $clientUsername = '';
    protected $clientPassword = '';

    protected $request = null;

    /**
     * Constructor
     * Set up client credentials.
     *
     * @param string $clientKey The client ID.
     * @param string $clientUsername The client username.
     * @param string $clientPassword The client password.
     * @param Request $request Optional. The Request object to use.
     */
    public function __construct($clientKey, $clientUsername, $clientPassword = '', $accessToken = null, $request = null)
    {
        $this->setClientKey($clientKey);
        $this->setClientCredentials( $clientUsername, $clientPassword );
        $this->setAccessToken($accessToken);
        
        $this->request = $request ?: new Request();
    }


    /**
     * Get the client key.
     *
     * @return string The client key.
     */
    public function getClientKey()
    {
        return $this->clientKey;
    }

	/**
     * Get the access token.
     *
     * @return string The access token.
     */
    public function getAccessToken() {
		
		if( ! empty($this->accessToken) )
			return $this->accessToken;
			
		$this->accessToken = $this->requestAccessToken();
		
		return $this->accessToken; 
		
	}

    /**
     * Request an access token given an authorization code.
     */
    public function requestAccessToken()
    {
		
		$data = array('apikey' => $this->clientKey,  'user' => $this->clientUsername , 'pwd' => $this->clientPassword);
		
		$response = $this->request->send('GET', '/login', $data);
		
		// if ok, return the access token
		if ( !is_wp_error( $response ) ) {
			return $response['body']->token;
		}

		return $response; //this is a WP_Error instance
		
    }

    /**
     * Set the client ID.
     *
     * @param string $clientId The client ID.
     *
     * @return void
     */
    public function setClientKey($clientKey)
    {
        $this->clientKey = $clientKey;
    }
    
    
    /**
     * Set the client ID.
     *
     * @param string $clientId The client ID.
     *
     * @return void
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }
    

    /**
     * Set the client credentials.
     *
     * @param string $clientUsername The client name.
     * @param string $clientPassword The client password.
     *
     * @return void
     */
    public function setClientCredentials($clientUsername, $clientPassword)
    {
        $this->clientUsername = $clientUsername;
        $this->clientPassword = $clientPassword;
    }

}

