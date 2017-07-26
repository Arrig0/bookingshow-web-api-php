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
            $headers['X-Auth-Token'] = $this->accessToken;
        }
        return $headers;
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
		
		$headers = $this->authHeaders();
		$body 	 = [];
		
		// @Todo: Validate args
		$args 	 = array_filter( $this->_parse_args( $args, $defaults ) );
		$body	 = array_merge($body, $args);
		
		$uri = $this->_get_endpoint('/events');		
        $this->lastResponse = $this->request->api('GET', $uri , $body, $headers);

		

        return $this->lastResponse['body'];
	}


	/**
	 * Restituisce  l’evento a  partire  dall’identificati  vo dell’evento  presente nel  sistema
	 *
	 * @param int $event_id
	 * @return 
	 */
	public function event( $event_id = 0 ) {

		if( ! $event_id || ! is_numeric($event_id) ) 
			throw new \Exception('Invalid Parameters');

		$headers = $this->authHeaders();
		$body 	 = [];
		
        $uri = $this->_get_endpoint('/events/' . $event_id);
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        return $this->lastResponse['body'];
	}

	
	/**
	 * Restituisce  tutti gli eventi  per una data  struttura
	 *
	 * @param int $venue_id
	 * @return 
	 */
	public function eventsOfVenue( $venue_id = 0 ) {

		if( ! $venue_id || ! is_numeric($venue_id) ) 
			throw new \Exception('Invalid Parameters');

		$headers = $this->authHeaders();
		$body 	 = [];
		
        $uri = $this->_get_endpoint('/venues/' . $venue_id . '/events');
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        return $this->lastResponse['body'];
	}

	
	/**
	 * Restituisce una  lista di  disponibilità per  ogni  sottosettore di  ciascun settore
	 *
	 * @param int $event_id
	 * @return 
	 */
	public function eventAvailability( $event_id = 0 ) {

		if( ! $event_id || ! is_numeric($event_id) ) 
			throw new \Exception('Invalid Parameters');

		$headers = $this->authHeaders();
		$body 	 = [];
		
        $uri = $this->_get_endpoint('/availevents/' . $event_id . '/sectors');
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        return $this->lastResponse['body'];
	}
	
	
	/**
	 * Restituisce una  lista di settori  per l’evento  specificato
	 *
	 * @param int $event_id
	 * @return 
	 */
	public function eventSectorRates( $event_id = 0 ) {

		if( ! $event_id || ! is_numeric($event_id) ) 
			throw new \Exception('Invalid Parameters');

		$headers = $this->authHeaders();
		$body 	 = [];
		
		$body	 = array_merge($body, array('rates' => 'true'));
	
        $uri = $this->_get_endpoint('/events/' . $event_id . '/sectors');
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        return $this->lastResponse['body'];
	}
	
	
	/**
	 * Genera un nuovo  carrello
	 *
	 * @return 
	 */
	public function cart() {

		$headers = $this->authHeaders();
		$body 	 = [];
		
        $uri = $this->_get_endpoint('/carts');
        $this->lastResponse = $this->request->api('POST', $uri, $body, $headers);
        return $this->lastResponse['body'];
	}
	
	
	/**
	 * Restituisce le  informazioni di  consegna
	 *
	 * @param int $cart_id
	 * @return array Current Delivery Method
	 */
	public function getCartDeliveryType( $cart_id = 0 ) { 
	
		if( ! $cart_id || ! ctype_alnum($cart_id) ) 
			throw new \Exception('Invalid Parameters');
	
		$headers = $this->authHeaders();
		$body 	 = [];
		
        $uri = $this->_get_endpoint('/carts/' . $cart_id . '/deliveryInfo');
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        return $this->lastResponse['body'];
		
	}
	
	public function setCartDeliveryType( $cart_id = 0, $delivery_type = 'Hand' ) {
	
		if( ! $cart_id || ! ctype_alnum($cart_id) )
			throw new \Exception('Invalid Parameters');
		
		// @TODO VALIDATE $payment_type
	
		$headers = $this->authHeaders();
		$body 	 = [];
		
		$body	 = array_merge($body, array('deliveryType' => $delivery_type));
        
        $uri = $this->_get_endpoint('/carts/' . $cart_id . '/deliveryInfo');
        $this->lastResponse = $this->request->api('POST', $uri, $body, $headers);
        return $this->lastResponse['body'];
	
	}
	
	
	/**
	 * Fornisce lista  dei tipi di  consegna validi per il carrello
	 *
	 * @param int $cart_id
	 * @return array Supported Delivery Methods
	 */
	public function getCartValidDeliveryTypes( $cart_id = 0 ) { 
		
		if( ! $cart_id || ! ctype_alnum($cart_id) ) 
			throw new \Exception('Invalid Parameters');
	
		$headers = $this->authHeaders();
		$body 	 = [];
		
        $uri = $this->_get_endpoint('/carts/' . $cart_id . '/getValidDeliveryTypes');
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        return $this->lastResponse['body'];
	
	}
	
	
	/**
	 * Restituisce le  informazioni di  pagamento
	 *
	 * @param int $cart_id
	 * @return array Current Payment Method
	 */
	public function getCartPaymentType( $cart_id = 0 ) { 
		
		if( ! $cart_id || ! ctype_alnum($cart_id) ) 
			throw new \Exception('Invalid Parameters');
	
		$headers = $this->authHeaders();
		$body 	 = [];
		
        $uri = $this->_get_endpoint('/carts/' . $cart_id . '/paymentInfo');
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        return $this->lastResponse['body'];
	}
	
	public function setCartPaymentType( $cart_id = 0, $payment_type = 'Cash' ) {
		
		if( ! $cart_id || ! ctype_alnum($cart_id) ) 
			throw new \Exception('Invalid Parameters');
		
		// @TODO VALIDATE $payment_type
	
		$headers = $this->authHeaders();
		$body 	 = [];
		
		$body	 = array_merge($body, array('paymentType' => $payment_type));
        
        $uri = $this->_get_endpoint('/carts/' . $cart_id . '/paymentInfo');
        $this->lastResponse = $this->request->api('POST', $uri, $body, $headers);
        return $this->lastResponse['body'];
	}
	
	
	/**
	 * Fornisce lista  dei pagamenti  validi
	 *
	 * @param int $cart_id
	 * @return array Supported Payment Methods
	 */
	public function getCartValidPaymentTypes( $cart_id = 0 ) {
		
		if( ! $cart_id || ! ctype_alnum($cart_id) ) 
			throw new \Exception('Invalid Parameters');
	
		$headers = $this->authHeaders();
		$body 	 = [];
		
        $uri = $this->_get_endpoint('/carts/' . $cart_id . '/getValidPaymentTypes');
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
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
		
		if( ! $cart_id || ! ctype_alnum($cart_id) ) 
			throw new \Exception('Invalid Parameters');
		
		$headers = $this->authHeaders();
		$body 	 = [];
		
		$headers = array_merge( $headers, array('Content-Type'  => 'application/json; charset=UTF-8') );
		
		$body = json_encode($tickets);
		
        $uri = $this->_get_endpoint('/carts/' . $cart_id . '/AddTickets');
        $this->lastResponse = $this->request->api('POST', $uri, $body, $headers);
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
		
		if( ! $cart_id || ! ctype_alnum($cart_id) || ! $ticket_id || ! ctype_alnum($ticket_id) ) 
			throw new \Exception('Valid cart or Ticket id pleeeease!');
					
		$headers = $this->authHeaders();
		$body 	 = [];
		
        $uri = $this->_get_endpoint('/carts/' . $cart_id . '/tickets/' . $ticket_id);
        $this->lastResponse = $this->request->api('DELETE', $uri, $body, $headers);
        return $this->lastResponse['body'];
		
	}
	
	/**
	 * Ottiene i tickets del carrello
	 *
	 * @param int $cart_id
	 * @return array Ticjets in the cart
	 */
	public function getTicketsOfCart( $cart_id = 0 ) {
		
		if( ! $cart_id || ! ctype_alnum($cart_id) ) 
			throw new \Exception('Valid cart or Ticket id pleeeease!');
	
		$headers = $this->authHeaders();
		$body 	 = [];
		
        $uri = $this->_get_endpoint('/carts/' . $cart_id . '/tickets');
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers);
        return $this->lastResponse['body'];
		
	}
	
	public function checkoutCart( $cart_id = 0 ) {
	
		if( ! $cart_id || ! ctype_alnum($cart_id) ) 
			throw new \Exception('Valid cart or Ticket id pleeeease!');
	
		$headers = $this->authHeaders();
		$body 	 = [];
		
        $uri = $this->_get_endpoint('/carts/' . $cart_id . '/checkOut');        
        $this->lastResponse = $this->request->api('POST', $uri, $body, $headers);        
        return $this->lastResponse['body'];
		
	}
	
	public function getOrder( $order_id = 0 ) { }
	
	public function getTicketsPrintAtHomePdf( $order_id = 0 ) {
		
		if( ! $order_id || ! ctype_alnum($order_id) ) 
			throw new \Exception('Valid order id pleeeease!');
			
		$headers 		= $this->authHeaders();
		$body 	 		= [];
		$json_response 	= false;
		
        $uri = $this->_get_endpoint('/orders/'.$order_id.'/getTicketsPrintAtHomePdf');        
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers, $json_response);       

        return $this->lastResponse['body'];		
		
	}
	
	
	public function copyTicketsPrintAtHomePdf( $order_id = 0, $filename ) {
		
		if( ! $order_id || ! ctype_alnum($order_id) ) 
			throw new \Exception('Valid order id pleeeease!');
			
		$headers 		= $this->authHeaders();
		$body 	 		= [];
		
		$fp = @fopen($filename, "w");
		if( $fp === false ) 
			throw new \Exception('errore creando il file', 666);

        $uri = $this->_get_endpoint('/orders/'.$order_id.'/getTicketsPrintAtHomePdf'); 
         
        $this->lastResponse = $this->request->apiTransfer('GET', $uri, $body, $headers, $fp); 
        
        fclose($fp);    

        return $this->lastResponse;		
		
	}
	
	
	public function getBoxOfficeReceiptPdf( $order_id = 0 ) {
		
		if( ! $order_id || ! ctype_alnum($order_id) ) 
			throw new \Exception('Valid order id pleeeease!');
			
		$headers 		= $this->authHeaders();
		$body 	 		= [];
		$json_response 	= false;
		
        $uri = $this->_get_endpoint('/orders/'.$order_id.'/getBoxOfficeReceiptPdf');        
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers, $json_response);       

        return $this->lastResponse['body'];
		
	}
	
	public function copyBoxOfficeReceiptPdf( $order_id = 0 ) {
		
		if( ! $order_id || ! ctype_alnum($order_id) ) 
			throw new \Exception('Valid order id pleeeease!');
			
		$headers 		= $this->authHeaders();
		$body 	 		= [];
		
		$fp = @fopen($filename, "w");
		if( $fp === false ) 
			throw new \Exception('errore creando il file', 666);

        $uri = $this->_get_endpoint('/orders/'.$order_id.'/getBoxOfficeReceiptPdf');        
        $this->lastResponse = $this->request->apiTransfer('GET', $uri, $body, $headers, $fp); 
        
        fclose($fp);    

        return $this->lastResponse;		
		
	}
	
	public function getFiscalReceiptPdf( $order_id = 0 ) {
		
		if( ! $order_id || ! ctype_alnum($order_id) ) 
			throw new \Exception('Valid order id pleeeease!');
			
		$headers 		= $this->authHeaders();
		$body 	 		= [];
		$json_response 	= false;
		
        $uri = $this->_get_endpoint('/orders/'.$order_id.'/getFiscalReceiptPdf');        
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers, $json_response);       
        
        return $this->lastResponse['body'];
		
	}
	
	public function copyFiscalReceiptPdf( $order_id = 0 ) {
		
		if( ! $order_id || ! ctype_alnum($order_id) ) 
			throw new \Exception('Valid order id pleeeease!');
			
		$headers 		= $this->authHeaders();
		$body 	 		= [];
		
		$fp = @fopen($filename, "w");
		if( $fp === false ) 
			throw new \Exception('errore creando il file', 666);

        $uri = $this->_get_endpoint('/orders/'.$order_id.'/getFiscalReceiptPdf');        
        $this->lastResponse = $this->request->apiTransfer('GET', $uri, $body, $headers, $fp); 
        
        fclose($fp);    

        return $this->lastResponse;		
		
	}
	
	public function getDigitalTicketsReceiptPdf( $order_id = 0 ) {
		
		if( ! $order_id || ! ctype_alnum($order_id) ) 
			throw new \Exception('Valid order id pleeeease!');
			
		$headers 		= $this->authHeaders();
		$body 	 		= [];
		$json_response 	= false;
		
        $uri = $this->_get_endpoint('/orders/'.$order_id.'/getDigitalTicketsReceiptPdf');        
        $this->lastResponse = $this->request->api('GET', $uri, $body, $headers, $json_response);       

        return $this->lastResponse['body'];
		
	}
	
	
	public function copyDigitalTicketsReceiptPdf( $order_id = 0 ) {
		
		if( ! $order_id || ! ctype_alnum($order_id) ) 
			throw new \Exception('Valid order id pleeeease!');
			
		$headers 		= $this->authHeaders();
		$body 	 		= [];
		
		$fp = @fopen($filename, "w");
		if( $fp === false ) 
			throw new \Exception('errore creando il file', 666);

        $uri = $this->_get_endpoint('/orders/'.$order_id.'/getDigitalTicketsReceiptPdf');        
        $this->lastResponse = $this->request->apiTransfer('GET', $uri, $body, $headers, $fp); 
        
        fclose($fp);    

        return $this->lastResponse;		
		
	}
	
	
	private function _parse_args( $args, $defaults = '' ) {
		if ( is_object( $args ) )
			$r = get_object_vars( $args );
		elseif ( is_array( $args ) )
			$r =& $args;
		else
			wp_parse_str( $args, $r );
	 
		if ( is_array( $defaults ) )
			return array_merge( $defaults, $r );
		return $r;
	}
	
	private function _get_endpoint( $ep ) {

		return $ep . '?apiKey=' . $this->apiKey;
		
	}
		
}

