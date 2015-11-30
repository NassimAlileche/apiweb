<?php

namespace apiweb\Application\Core;

/**
 *
 *
 *
 */
class RestServer {

	/**
	 * Nom du service interrogé
	 * @var string
	 */
	private $service;

	/**
	 * Nom de domaine racine des services
	 * @var string
	 */
	private $serviceBaseNamespace;

	/**
	 * Nom de la méthode HTTP employée
	 * @var string
	 */
	private $httpMethod;
	
	/**
	 * Nom de la méthode du service interrogé
	 * @var string
	 */
	private $serviceClassMethod;
	
	/**
	 * Liste des paramètres de la requête
	 * @var array
	 */
	private $requestParams;
	
	/**
	 * Données de requête réceptionnées par le serveur REST (nom du service + paramètres de requête)
	 * @var array
	 */
	private $data;

	/**
	 * Nom de l'agent client (navigateur) qui envoie la requête au serveur REST
	 * @example 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0'
	 * @var string
	 */
	private $clientUserAgent;
	
	/**
	 * Liste des types de données acceptés par le client
	 * @example 'text/html,application/xhtml+xml,application/xml;q=0.9,*|*;q=0.8'
	 * @var string
	 */
	private $clientHttpAccept;
	
	/**
	 * Résultat renvoyé par le serveur REST
	 * @var object \stdClass
	 */
	private $json;

	/**
	 * Mode employé pour l'utilisation du webservice : ne peut prendre que les valeurs "debug" et "production"
	 * @var string
	 */
	private $mode;

	/**
	 * Chemin racine des ressources
	 * @var string
	 */
	private $rootDir = null;

	/**
	 * Liste des codes d'erreur HTTP possibles. Devrait être une constante.
	 * @var array
	 */
	private $httpResponseCodes = array(
		"100" => "Continue",
		"101" => "Switching protocols",
		"102" => "Processing",
		"200" => "OK",
		"201" => "Created",
		"202" => "Accepted",
		"203" => "Non-Authoritative Information",
		"204" => "No Content",
		"205" => "Reset Content",
		"206" => "Partial Content",
		"207" => "Multi-Status",
		"300" => "Multiple Choices",
		"301" => "Moved Permanently",
		"302" => "Found",
		"303" => "See Other",
		"304" => "Not Modified",
		"305" => "Use Proxy",
		"307" => "Temporary Redirect",
		"308" => "Permanent Redirect",
		"400" => "Bad Request",
		"401" => "Unauthorized",
		"402" => "Payment Required",
		"403" => "Forbidden",
		"404" => "Not Found",
		"405" => "Method Not Allowed",
		"406" => "Not Acceptable",
        "407" => "Proxy Authentication Required",
        "408" => "Request Time-out",
		"409" => "Conflict",
		"410" => "Gone",
		"411" => "Length Required",
		"412" => "Precondition Failed",
		"413" => "Request Entity Too Large",
		"414" => "Request-URI Too Large",
		"415" => "Unsupported Media Type",
		"416" => "Requested Range Not Satisfiable",
		"417" => "Expectation Failed",
		"418" => "I'm a teapot",
		"419" => "Authentication Timeout", 
		"420" => "Enhance Your Calm", 
		"422" => "Unprocessable Entity", 
		"423" => "Locked", 
		"424" => "Failed Dependency Method Failure", 
		"425" => "Unordered Collection", 
		"426" => "Upgrade Required", 
		"428" => "Precondition Required", 
		"429" => "Too Many Requests", 
		"431" => "Request Header Fields Too Large", 
		"444" => "No Response", 
		"449" => "Retry With", 
		"450" => "Blocked by Windows Parental Controls", 
		"451" => "Unavailable For Legal Reasons", 
		"494" => "Request Header Too Large", 
		"495" => "Cert Error", 
		"496" => "No Cert", 
		"497" => "HTTP to HTTPS", 
		"499" => "Client Closed Request",
		"500" => "Internal Server Error",
		"501" => "Not Implemented",
		"502" => "Bad Gateway",
		"503" => "Service Unavailable",
		"504" => "Gateway Time-out",
		"505" => "HTTP Version not supported",
		"506" => "Variant Also Negotiates", 
		"507" => "Insufficient Storage", 
		"508" => "Loop Detected", 
		"509" => "Bandwidth Limit Exceeded", 
		"510" => "Not Extended", 
		"511" => "Network Authentication Required", 
		"598" => "Network read timeout error", 
		"599" => "Network connect timeout error",
		"0"   => "Unknown HTTP Status Code"
	);

	/**
	 *
	 *
	 */
	private function setStatusCode($code) {
		if (function_exists('http_response_code')) {
			http_response_code($code);
		} else {
			$protocol = $_SERVER['SERVER_PROTOCOL'] ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
			$message  = "{$code} " . $this->getHttpResponseCodeMessage($code);
			header("$protocol $message");
		}
	}

	/**
	 *
	 *
	 */
	private function setRootDir() {
		if($this->rootDir == null) {
			$dir = dirname(str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
			if($dir == '.')
				$dir = '/';
			else {
				// Rajouter un '/' à la fin du nom du répertoire
				if (substr($dir, -1) != '/')
					$dir .= '/';
				// Rajouter un '/' au début du nom du répertoire
				if (substr($dir, 0, 1) != '/')
					$dir = '/' . $dir;
			}
			$this->rootDir = $dir;
		}
	}

	/**
	 *
	 *
	 */
	public function getRootDir() {
		if($this->rootDir == null)
			$this->setRootDir();

		return $this->rootDir;
	}

	/**
	 *
	 *
	 */
	public function getHttpResponseCodeMessage($code) {
		$code = (string)$code;
		return (array_key_exists($code, $this->httpResponseCodes))? $this->httpResponseCodes[$code] : $this->httpResponseCodes['0'];
	}

	/**
	 *
	 *
	 */
	public function __construct($mode = "debug") {

		
		header("Content-type: application/json;application/x-www-form-urlencoded;charset=utf-8");
		header("Content-Length: 4800");
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: 0");

		// Initilialisation de l'objet json représentant la réponse à la requête du client 
		$this->json = new \stdClass();
		$this->json->response = "";
		$this->json->httpResponseCode        = 200;
		$this->json->httpResponseCodeMessage = $this->getHttpResponseCodeMessage(200);
		$this->json->apiError        = false;
		$this->json->apiErrorMessage = "";
		$this->json->serverError 		= false;
		$this->json->serverErrorMessage = "";

		// Nom de domaine racine des services autorisés à être interrogé
		$this->serviceBaseNamespace = "apiweb\\Application\\Controllers";

		// Environnement d'utilisation du webservice
		$this->mode = (strtolower($mode) == "debug" || strtolower($mode) == "production")? strtolower($mode) : "debug";

		// Initialisation du conteneur des données de requête du client
		$this->data = array();

		$this->httpMethod 		= strtoupper($_SERVER["REQUEST_METHOD"]);
		$this->clientUserAgent 	= $_SERVER["HTTP_USER_AGENT"];
		$this->clientHttpAccept = $_SERVER["HTTP_ACCEPT"];

		var_dump("rootdir : {$this->getRootDir()}");

		var_dump($this->httpMethod, $this->clientUserAgent, $this->clientHttpAccept);

		// On réceptionne les données de requête suivant la méthode HTTP employée
		switch($this->httpMethod) {
			case 'GET' : 
				$this->data = $_GET; 
				break;
			
			case 'POST'  :
			case 'PUT'   :
			case 'DELETE':
				$body = file_get_contents("php://input");
				//var_dump($body);
				parse_str($body, $this->data); 
				break;
			
			default :
				$this->showError(405, "La méthode HTTP {$this->httpMethod} n'existe pas ou son emploi n'est pas permis.");
		}

		if(isset($this->data["service"])) {

			$serviceName = $this->data["service"];

			$serviceClassFullName = "\\" . $this->serviceBaseNamespace . "\\" . $serviceName;
			$serviceClassFile     = str_replace("Public/index.php", "Application/Controllers/", $_SERVER["SCRIPT_FILENAME"]) . $serviceName . ".php";

			//var_dump($serviceName, $serviceClassFullName, $serviceClassFile);

			// On vérifie que le service demandé existe ou pas
			if(!class_exists($serviceClassFullName) || !file_exists($serviceClassFile)) { 
				$this->showError( 404, "Le service {$serviceName} n'existe pas : " . $serviceClassFullName . ", " . $serviceClassFile );
			}
			else {
				$this->service = new $serviceClassFullName(); 
			}

			$this->serviceClassMethod = strtolower( $this->httpMethod );

			if(!method_exists($this->service, $this->serviceClassMethod)) {
				$this->showError(404, "La méthode {$this->serviceClassMethod} pour le service {$serviceName} n'existe pas.");
			}

			unset($this->data["service"]);
			$this->requestParams = $this->data;
		}
		else {
			$this->showError(404, "Le paramètre de requête service est manquant.");
		}

	}

	/**
	 * 	Méthode showError($message)
	 *
	 * 	Affiche les messages d'erreur du serveur
	 *
	 * 	@param 		string 		$message 		[Messages d'erreurs du serveur]
	 * 	@return 	void
	 *
	 */
	private function showError($code, $message) {
		$this->setHttpResponseCodeInfo($code);
		$this->json->serverError        = true;
		$this->json->serverErrorMessage = $message;
		exit;
	}

	/**
	 * 	Méthode handle()
	 *
	 * 	Met en forme le résultat renvoyé par l'API
	 *
	 * 	@param
	 * 	@return 	void
	 *
	 */
	/*
	 * 		key=value&key=value
	 * 		array(key->value, key->value)
	 * 		
	 * 		PUT http(s)://url/server.php 	body:method=data&oldWord=toto&newWord=titi
	 */
	public function handle() {
		$result = call_user_func(array($this->service, $this->serviceClassMethod), $this->requestParams);
		$this->json->response = $result->response;
		$this->json->apiError = $result->apiError;
		$this->json->apiErrorMessage = $result->apiErrorMessage;
		//exit;
	}

	/**
	 *
	 *
	 */
	public function setHttpResponseCodeInfo($code) {
		$this->setStatusCode($code);
		$this->json->httpResponseCode        = $code;
		$this->json->httpResponseCodeMessage = $this->getHttpResponseCodeMessage($code);
	}


	/**
	 * 	Méthode __destruct()
	 *
	 * 	Destructeur par défaut. Appelée par exemple lors de l'appel de exit().
	 *
	 * 	@param
	 * 	@return 	void
	 *
	 */
	public function __destruct() {
		/*
		// Caching made easy
		if ($this->mode == 'production' && !$this->cached) {
			if (function_exists('apc_store')) {
				apc_store('urlMap', $this->map);
			} else {
				file_put_contents($this->cacheDir . '/urlMap.cache', serialize($this->map));
			}
		}
		*/
		echo json_encode($this->json, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
	}

}