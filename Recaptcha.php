<?php

/**
 * Classe Recaptcha
 */
class Recaptcha
{
    /**
     * Codice di risposta http
     *
     * @var int
     */
    public $http_code;

    /**
     * Risposta http
     *
     * @var object
     */
    public $response;

    /**
     * Url chiamata API
     *
     * @var string
     */
    public $url_request;

    /**
     * Metodo utiliazzzto nella chiamata API
     *
     * @var string
     */
    public $method_request;

    /**
     * Metodo utiliazzzto nella chiamata API
     *
     * @var array
     */
    public $body_request;

    /**
     * Url base api
     */
    static $httpVerifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Costruttore della risposta http
     *
     * @param array $response
     * @param int $http_code
     * @param string $url_request
     */
    public function __construct($response, $http_code, $url_request, $method_request, $body_request = null)
    {
        $this->response = $response;
        $this->http_code = $http_code;
        $this->url_request = $url_request;
        $this->method_request = $method_request;
        $this->body_request = $body_request;
    }

    /*
    |--------------------------------------------------------------------------
    | Url base api
    |--------------------------------------------------------------------------
    |
    */
    public static function base()
    {
        return self::$httpVerifyUrl;
    }

    /*
    |--------------------------------------------------------------------------
    | Url prepare request
    |--------------------------------------------------------------------------
    |
    */
    public static function url($path = '')
    {
        return self::base().$path;
    }

    /*
    |--------------------------------------------------------------------------
    | Preparazione parametri per la chimata GET
    |--------------------------------------------------------------------------
    |
    */
    public static function prepareParams($params)
    {
        return http_build_query($params);
    }

    /*
    |--------------------------------------------------------------------------
    | Inizio della richiesta GET
    |--------------------------------------------------------------------------
    |
    */
    public static function request($method, $url, $params = null)
    {
        $curl = curl_init();

        if (is_array($url)) {
            $url = implode('/', $url);
        }

        $url = self::url($url.'?').self::prepareParams($params);


        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_POST => $method == 'POST',
            CURLOPT_POSTFIELDS => null,

        ]);

        $response = curl_exec($curl);

        $code = curl_getinfo($curl)['http_code'];

        curl_close($curl);

        return new self(json_decode($response, true), $code, $url, $method, $params);
    }

    /*
    |--------------------------------------------------------------------------
    | Chiamata POST
    |--------------------------------------------------------------------------
    |
    */
    public static function post($url, $params = null)
    {
        return self::request('POST', $url, $params);
    }

    /*
    |--------------------------------------------------------------------------
    | Chiamata GET
    |--------------------------------------------------------------------------
    |
    */
    public static function get($url, $params = [])
    {
        return self::request('GET', $url, $params);
    }

    /*
    |--------------------------------------------------------------------------
    | Verifica se la chiamata http ha avuto esito positivo
    |--------------------------------------------------------------------------
    |
    */
    public function successful()
    {
        return $this->http_code == 200;
    }

    /*
    |--------------------------------------------------------------------------
    | Verifica se la chiamata http ha avuto esito negativo
    |--------------------------------------------------------------------------
    |
    */
    public function failed()
    {
        return ! $this->isSuccess();
    }

    /*
    |--------------------------------------------------------------------------
    | Ritorna il codice di stato http curl
    |--------------------------------------------------------------------------
    |
    */
    public function httpCode()
    {
        return $this->http_code;
    }

    /*
    |--------------------------------------------------------------------------
    | Verifica se la risposta ha ritornato la status in success
    |--------------------------------------------------------------------------
    |
    */
    public function isSuccess()
    {
      return $this->successful() && $this->response && $this->response['success'] === true ;
    }

    /*
    |--------------------------------------------------------------------------
    | Verifica se la risposta ha ritornato errore
    |--------------------------------------------------------------------------
    |
    */
    public function isFailed()
    {
      return ! $this->isSuccess();
    }


    /*
    |--------------------------------------------------------------------------
    | Verifica del Recaptcha
    |--------------------------------------------------------------------------
    |
    */
    static function verify($secretKey, $token) {
        return self::post('', [
          'secret' => $secretKey,
          'response' => $token
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Verifica response del Recaptcha
    |--------------------------------------------------------------------------
    |
    */
    public function isOk() {
        return $this->response && isset($this->response['success']) && $this->response['success'] === true;
    }    

    /*
    |--------------------------------------------------------------------------
    | Verifica del Recaptcha con chiavi di testing
    |--------------------------------------------------------------------------
    |
    */
    static function test($token) {
      return self::verify('6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe', $token);
    }

    public function toArray()
    {
      return [
        'is_success' => $this->isSuccess(),
        'response' => $this->response,
        'http_code' => $this->http_code,
        'url_request' => $this->url_request,
        'method_request' => $this->method_request,
        'body_request' => $this->body_request,
      ];
    }
}
