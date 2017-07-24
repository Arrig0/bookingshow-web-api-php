<?php

namespace BookingshowWebAPI;

class BookingshowWebAPI
{
	
	protected $accessToken 	= '';
	protected $apiKey 		= '';
    protected $lastResponse = [];
    protected $request 		= null;


	/**
	 * Constructor of class BookingshowWebAPI.
	 *
	 * @return void
	 */
	public function __construct( $request = null )
	{
		 $this->request = $request ?: new Request();
	}

	/**
     * Add authorization headers.
     *
     * @return array Authorization headers.
     */
    protected function authHeaders()
    {
        $headers = [];
        if ($this->accessToken) {
            //$headers['Authorization'] = 'Bearer ' . $this->accessToken;
            $headers['X-Auth-Token'] = $this->accessToken; //Fuck Off BookingShow!!!
        }
        return $headers;
    }
    
    /**
     * Add API Key 
     *
     * @return array Body.
     */
    protected function authBody()
    {
        $body = [];
        if ($this->apiKey) {
            $body['apikey'] = $this->apiKey;
        }
        return $body;
    }


	public function setAccessToken( $token ) {
		$this->accessToken = $token;
	}

	
	public function setApiKey( $key ) {
		$this->apiKey = $key;
	}

	
	/**
	 * Restituisce la  lista di tutti gli  eventi presenti  nel sistema
	 * 
	 * Lista degli argomenti:
	 * $args = [
	 *		'from' 	=> '',	//yyyy-MM-dd Data a partire dalla quale iniziare la ricerca
	 *		'to' 	=> '',	//yyyy-MM-dd Data fino a cui effettuare la ricerca
	 *		'q' 	=> '',	//Ricerca testo generica su nome evento
	 *		'en' 	=> '',	//Ricerca (esatta) per nome dell’evento
	 *		'vn' 	=> '',	//Ricerca (esatta) per nome struttura
	 *		'vl' 	=> '',	//Ricerca per località struttura
	 *		'lat' 	=> '',	//Ricerca per latitudine
	 *		'lng' 	=> '',	//Ricerca per longitudine
	 *		'rad' 	=> '',	//Ricerca per raggio (Km)
	 *		'ps' 	=> '',	//Indica la dimensione della pagina, cioè il numero di risorse che la compone
	 *		'pn' 	=> '',	//Indica il numero di pagina richiesto.
	 *	];
	 *
	 * @param array $args
	 * @return 
	 */
	public function events( $args = [] ) {
		
		$defaults = [
			'from' 	=> '',	
			'to' 	=> '',	
			'q' 	=> '',	
			'en' 	=> '',	
			'vn' 	=> '',	
			'vl' 	=> '',	
			'lat' 	=> '',	
			'lng' 	=> '',	
			'rad' 	=> '',	
			'ps' 	=> '',	
			'pn' 	=> '',	
		];
		
		$args = array_filter( wp_parse_args( $args, $defaults ) );		
		
		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
		$body	 = array_merge($body, $args);
		
        $uri = '/events/';
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);

		if( is_wp_error($this->lastResponse) )
			return $this->lastResponse;

        return $this->lastResponse['body'];
	}


	/**
	 * Restituisce  l’evento a  partire  dall’identificati  vo dell’evento  presente nel  sistema
	 *
	 * @param int $event_id
	 * @return 
	 */
	public function event( $event_id = 0 ) {

		if( ! $event_id || ! is_numeric($event_id) ) {
			return new \WP_Error( 
				'broke', 
				__( "Valid event id pleeeease!", "my_textdomain" ) 
			);
		}

		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
        $uri = '/events/' . $event_id;
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        
        if( is_wp_error($this->lastResponse) )
			return $this->lastResponse;

        return $this->lastResponse['body'];
	}

	
	/**
	 * Restituisce  tutti gli eventi  per una data  struttura
	 *
	 * @param int $venue_id
	 * @return 
	 */
	public function eventsOfVenue( $venue_id = 0 ) {

		if( ! $venue_id || ! is_numeric($venue_id) ) {
			return new \WP_Error( 
				'broke', 
				__( "Valid venue id pleeeease!", "my_textdomain" ) 
			);
		}

		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
        $uri = '/venues/' . $venue_id . '/events';
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        
        if( is_wp_error($this->lastResponse) )
			return $this->lastResponse;

        return $this->lastResponse['body'];
	}

	
	/**
	 * Restituisce una  lista di  disponibilità per  ogni  sottosettore di  ciascun settore
	 *
	 * @param int $event_id
	 * @return 
	 */
	public function eventAvailability( $event_id = 0 ) {

		if( ! $event_id || ! is_numeric($event_id) ) {
			return new \WP_Error( 
				'broke', 
				__( "Valid venue id pleeeease!", "my_textdomain" ) 
			);
		}

		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
        $uri = '/availevents/' . $event_id . '/sectors';
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);

        //if( is_wp_error($this->lastResponse) )
		//	return $this->lastResponse;

        return $this->lastResponse['body'];
	}
	
	
	/**
	 * Restituisce una  lista di settori  per l’evento  specificato
	 *
	 * @param int $event_id
	 * @return 
	 */
	public function eventSectorRates( $event_id = 0 ) {

		if( ! $event_id || ! is_numeric($event_id) ) {
			return new \WP_Error( 
				'broke', 
				__( "Valid venue id pleeeease!", "my_textdomain" ) 
			);
		}

		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
		$body	 = array_merge($body, array('rates' => 'true'));
	
        $uri = '/events/' . $event_id . '/sectors';
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        
        if( is_wp_error($this->lastResponse) )
			return $this->lastResponse;

        return $this->lastResponse['body'];
	}
	
	
	/**
	 * Genera un nuovo  carrello
	 *
	 * @return 
	 */
	public function cart() {

		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
        $uri = '/carts';
        $this->lastResponse = $this->request->api('POST', $uri, $body, $headers);
        
        if( is_wp_error($this->lastResponse) )
			return $this->lastResponse;

        return $this->lastResponse['body'];
	}
	
	
	/**
	 * Restituisce le  informazioni di  consegna
	 *
	 * @param int $cart_id
	 * @return array Current Delivery Method
	 */
	public function getCartDeliveryType( $cart_id = 0 ) { 
	
		if( ! $cart_id || ! ctype_alnum($cart_id) ) {
			return new \WP_Error( 
				'broke', 
				__( "Valid cart id pleeeease!", "my_textdomain" ) 
			);
		}
	
		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
        $uri = '/carts/' . $cart_id . '/deliveryInfo';
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        
        if( is_wp_error($this->lastResponse) )
			return $this->lastResponse;

        return $this->lastResponse['body'];
		
	}
	
	public function setCartDeliveryType( $cart_id = 0, $delivery_type = 'Hand' ) {
	
		if( ! $cart_id || ! ctype_alnum($cart_id) ) {
			return new \WP_Error( 
				'broke', 
				__( "Valid cart id pleeeease!", "my_textdomain" ) 
			);
		}
		
		// @TODO VALIDATE $payment_type
	
		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
		$body	 = array_merge($body, array('deliveryType' => $delivery_type));
        
        $uri = '/carts/' . $cart_id . '/deliveryInfo';
        $this->lastResponse = $this->request->api('POST', $uri, $body, $headers);
        
        if( is_wp_error($this->lastResponse) )
			return $this->lastResponse;

        return $this->lastResponse['body'];
	
	}
	
	
	/**
	 * Fornisce lista  dei tipi di  consegna validi per il carrello
	 *
	 * @param int $cart_id
	 * @return array Supported Delivery Methods
	 */
	public function getCartValidDeliveryTypes( $cart_id = 0 ) { 
		
		if( ! $cart_id || ! ctype_alnum($cart_id) ) {
			return new \WP_Error( 
				'broke', 
				__( "Valid cart id pleeeease!", "my_textdomain" ) 
			);
		}
	
		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
        $uri = '/carts/' . $cart_id . '/getValidDeliveryTypes';
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        
        if( is_wp_error($this->lastResponse) )
			return $this->lastResponse;

        return $this->lastResponse['body'];
	
	}
	
	
	/**
	 * Restituisce le  informazioni di  pagamento
	 *
	 * @param int $cart_id
	 * @return array Current Payment Method
	 */
	public function getCartPaymentType( $cart_id = 0 ) { 
		if( ! $cart_id || ! ctype_alnum($cart_id) ) {
			return new \WP_Error( 
				'broke', 
				__( "Valid cart id pleeeease!", "my_textdomain" ) 
			);
		}
	
		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
        $uri = '/carts/' . $cart_id . '/paymentInfo';
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        
        if( is_wp_error($this->lastResponse) )
			return $this->lastResponse;

        return $this->lastResponse['body'];
	}
	
	public function setCartPaymentType( $cart_id = 0, $payment_type = 'Cash' ) {
		
		if( ! $cart_id || ! ctype_alnum($cart_id) ) {
			return new \WP_Error( 
				'broke', 
				__( "Valid cart id pleeeease!", "my_textdomain" ) 
			);
		}
		
		// @TODO VALIDATE $payment_type
	
		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
		$body	 = array_merge($body, array('paymentType' => $payment_type));
        
        $uri = '/carts/' . $cart_id . '/paymentInfo';
        $this->lastResponse = $this->request->api('POST', $uri, $body, $headers);
        
        if( is_wp_error($this->lastResponse) )
			return $this->lastResponse;

        return $this->lastResponse['body'];
	}
	
	
	/**
	 * Fornisce lista  dei pagamenti  validi
	 *
	 * @param int $cart_id
	 * @return array Supported Payment Methods
	 */
	public function getCartValidPaymentTypes( $cart_id = 0 ) {
		
		if( ! $cart_id || ! ctype_alnum($cart_id) ) {
			return new \WP_Error( 
				'broke', 
				__( "Valid cart id pleeeease!", "my_textdomain" ) 
			);
		}
	
		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
        $uri = '/carts/' . $cart_id . '/getValidPaymentTypes';
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        
        if( is_wp_error($this->lastResponse) )
			return $this->lastResponse;

        return $this->lastResponse['body'];
		
	}
	
	
	/**
	 * Aggiunge uno o più tickets del tipo  specificati dalla ticketRequest la lista dei  tickets aggiunti al carrello
	 *
	 * @param int $cart_id
	 * @param array $tickets
	 * @return array Supported Payment Methods
	 */
	public function addTicketsToCart( $cart_id = 0, $tickets = [] ) {
		
		if( ! $cart_id || ! ctype_alnum($cart_id) ) {
			return new \WP_Error( 
				'broke', 
				__( "Valid cart id pleeeease!", "my_textdomain" ) 
			);
		}
		
		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
		$headers = array_merge( $headers, array('Content-Type'  => 'application/json; charset=UTF-8') );
		
		//FUCK BS!!!
		//$body	 = array_merge($body, $tickets /*array('ticketsRequest' => $tickets)*/);
		$body = json_encode($tickets);
		
        $uri = '/carts/' . $cart_id . '/AddTickets?apiKey=APITest'; //FUCK BS!!!
        $this->lastResponse = $this->request->api('POST', $uri, $body, $headers);
        
        if( is_wp_error($this->lastResponse) )
			return $this->lastResponse;

        return $this->lastResponse['body'];
		
	}
	
	
	/**
	 * Aggiunge uno o più tickets del tipo  specificati dalla ticketRequest la lista dei  tickets aggiunti al carrello
	 *
	 * @param int $cart_id
	 * @param array $tickets
	 * @return array Supported Payment Methods
	 */
	public function removeTicketsFromCart( $cart_id = 0, $ticket_id = 0 ) {
		
		if( ! $cart_id || ! ctype_alnum($cart_id) || ! $ticket_id || ! ctype_alnum($ticket_id) ) {
			return new \WP_Error( 
				'broke', 
				__( "Valid cart or Ticket id pleeeease!", "my_textdomain" ) 
			);
		}
		
		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
        $uri = '/carts/' . $cart_id . '/tickets/' . $ticket_id;
        $this->lastResponse = $this->request->api('DELETE', $uri, $body, $headers);
        
        if( is_wp_error($this->lastResponse) )
			return $this->lastResponse;

        return $this->lastResponse['body'];
		
	}
	
	/**
	 * Ottiene i tickets del carrello
	 *
	 * @param int $cart_id
	 * @return array Ticjets in the cart
	 */
	public function getTicketsOfCart( $cart_id = 0 ) {
		
		if( ! $cart_id || ! ctype_alnum($cart_id) ) {
			return new \WP_Error( 
				'broke', 
				__( "Valid cart or Ticket id pleeeease!", "my_textdomain" ) 
			);
		}
	
		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
        $uri = '/carts/' . $cart_id . '/tickets';
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        
        if( is_wp_error($this->lastResponse) )
			return $this->lastResponse;

        return $this->lastResponse['body'];
		
	}
	
	public function checkoutCart( $cart_id = 0 ) {
	
		if( ! $cart_id || ! ctype_alnum($cart_id) ) {
			return new \WP_Error( 
				'broke', 
				__( "Valid cart or Ticket id pleeeease!", "my_textdomain" ) 
			);
		}
	
		$headers = $this->authHeaders();
		$body 	 = $this->authBody();
		
        $uri = '/carts/' . $cart_id . '/checkOut';
        $this->lastResponse = $this->request->api('POST', $uri, $body, $headers);
        
        if( is_wp_error($this->lastResponse) )
			return $this->lastResponse;

        return $this->lastResponse['body'];
		
	}
	
	public function getOrder( $order_id = 0 ) { }
		
}

