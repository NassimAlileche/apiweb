<?php

namespace apiweb\Application\Core;
use apiweb\Library\Response\ServerResponse as ServerResponse;

/**
 *
 *
 *
 */
class RestServer {

	const NS_SEPARATOR = '\\';

	/**
	 * Instance de la classe du service interrogé 
	 * @var \class
	 */
	private $service = null;

	/**
	 * Nom du service interrogé
	 * @var string
	 */
	private $serviceName;

	/**
	 * Nom de domaine racine des services disponibles
	 * @var string
	 */
	private $serviceBaseNamespace;

	/**
	 * Chemin des fichiers des services
	 * @var string
	 */
	private $serviceFileDir;

	/**
	 * Nom de la méthode du service interrogé
	 * @var string
	 */
	private $serviceClassMethod = null;

	/**
	 * Nom de la méthode HTTP employée
	 * @var string
	 */
	private $httpMethod;

	/**
	 * URL de requête du client
	 * @var string
	 */
	private $queryUrl;
	
	/**
	 * Liste des paramètres en GET de la requête du client
	 * @var array
	 */
	private $queryUrlParams;
	
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
	 * Résultat renvoyé au format JSON par le serveur REST
	 * @var object \stdClass
	 */
	private $json;

	/**
	 * Mode employé pour l'utilisation du webservice : ne peut prendre que les valeurs "debug" et "production"
	 * @var string
	 */
	private $mode;

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
	 * Setter de la classe du service demandé
	 *
	 * @param 	string 		$serviceName 	Nom du service
	 *
	 * @return 	void
	 */
	private function setService($serviceName) {
		$this->setServiceName($serviceName);
	}

	/**
	 *  Getter de la classe du service demandé
	 *
	 *  @return \service_class | null
	 */
	public function getService() {
		return $this->service;
	}

	/**
	 * Setter du nom du service demandé
	 *
	 * @param 	string 		$serviceName 	Nom du service
	 *
	 * @return 	void
	 */
	private function setServiceName($serviceName) {
		$this->serviceName = $serviceName;
	}

	/**
	 *  Getter du nom du service demandé
	 *
	 *  @return string
	 */
	public function getServiceName() {
		return $this->service;
	}

	/**
	 * Setter du namespace de base
	 *
	 * @param 	string 		$serviceBaseNamespace 	Namespace de base
	 *
	 * @return 	void
	 */
	private function setServiceBaseNamespace($serviceBaseNamespace) {
		$this->serviceBaseNamespace = $serviceBaseNamespace;
	}

	/**
	 *  Getter du namespace de base
	 *
	 *  @return string
	 */
	public function getServiceBaseNamespace() {
		return $this->serviceBaseNamespace;
	}

	/**
	 * Setter du chemin des fichiers des services
	 *
	 * @param 	string 		$serviceFileDir 	Chemin
	 *
	 * @return 	void
	 */
	private function setServiceFileDir($serviceFileDir) {
		$this->serviceFileDir = $serviceFileDir;
	}

	/**
	 *  Getter du chemin des fichiers des services
	 *
	 *  @return string
	 */
	public function getServiceFileDir() {
		return $this->serviceFileDir;
	}

	/**
	 * Setter de la méthode de la classe $serviceName
	 *
	 * @param 	string 		$serviceClassMethod 	Chemin
	 *
	 * @return 	void
	 */
	private function setServiceClassMethod($serviceClassMethod) {
		$this->serviceClassMethod = $serviceClassMethod;
	}

	/**
	 *  Getter de la méthode de la classe $serviceName
	 *
	 *  @return string
	 */
	public function getServiceClassMethod() {
		return $this->serviceClassMethod;
	}
	/**
	 *
	 *
	 */
	public function __construct($mode = "debug") {

		
		header("Content-type: application/json;application/x-www-form-urlencoded;");
		header("Content-Length: 4800");
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: 0");

		// Initilialisation de l'objet json représentant la réponse à la requête du client 
		$this->json = new \stdClass();
		$this->json->response                = "";
		$this->json->httpResponseCode        = 200;
		$this->json->httpResponseCodeMessage = $this->getHttpResponseCodeMessage(200);
		$this->json->apiError                = false;
		$this->json->apiErrorMessage         = "";
		$this->json->serverError 		     = false;
		$this->json->serverErrorMessage      = "";

		// Nom de domaine racine des services autorisés à être interrogé
		$this->setServiceBaseNamespace("apiweb\Application\Controllers");
		// Répertoire des services
		$this->setServiceFileDir(APIWEB_FS_ROOT . "Application/Controllers/"); 
		// Environnement d'utilisation du webservice
		$this->mode = (strtolower($mode) == "debug" || strtolower($mode) == "production")? strtolower($mode) : "debug";
		// Initialisation du conteneur des données de requête du client
		$this->data = array();
		// Chemin URI normalisé, obtenu en supprimant les slashs aux extrémités
		$this->queryUrl         = $this->normalizeUrlPath( trim($_SERVER["REQUEST_URI"]) );
		// Partie de l'URI correspondant à des paramères supplémentaires de filtrage transmis dans l'URI
		$this->queryUrlParams   = preg_replace("/^.*\?/i", "", $this->queryUrl);
		// Verbe HTTP
		$this->httpMethod 		= strtoupper($_SERVER["REQUEST_METHOD"]);
		$this->clientUserAgent 	= $_SERVER["HTTP_USER_AGENT"];
		$this->clientHttpAccept = $_SERVER["HTTP_ACCEPT"];

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

	/**
	 *
	 *
	 */
	public function handle() {

		//var_dump($this->queryUrl, $this->queryUrlParams);

		// On réceptionne les données de requête suivant la méthode HTTP employée
		switch($this->httpMethod) {
			case 'GET' : 
				$this->data = $_GET; 
				break;
			case 'POST'  :
			case 'PUT'   :
			case 'DELETE':
				$body = file_get_contents("php://input");
				var_dump("{$this->httpMethod} body :", $body);
				parse_str($body, $this->data); 
				break;
			default :
				$this->setServerErrorMessage(405, "La méthode HTTP {$this->httpMethod} n'existe pas ou son utilisation est interdite.");
		}

		$this->handleByGETParameters();
		//$this->handleByURIParsing();

		$this->setResponse();
	}

	/**
	 *
	 *
	 */
	private function handleByGETParameters() {

		// Requête du type /url/rest/api/endpoint?service=nomservice&param1=value1&param2=value2
		if(isset($this->data["service"])) {
			$serviceName = $this->data["service"];

			$serviceClassFullName = self::NS_SEPARATOR . $this->serviceBaseNamespace . self::NS_SEPARATOR . $serviceName;
			$serviceClassFile     = $this->serviceFileDir . $serviceName . ".php";

			// On vérifie que le service demandé existe ou pas
			if(!class_exists($serviceClassFullName) || !file_exists($serviceClassFile)) { 
				//var_dump(class_exists($serviceClassFullName), file_exists($serviceClassFile));
				$this->setServerErrorMessage( 404, "Le service {$serviceName} n'existe pas." );
			}
			else {
				$this->service = new $serviceClassFullName(); 
			}

			$this->serviceClassMethod = strtolower( $this->httpMethod );

			if(!method_exists($this->service, $this->serviceClassMethod)) {
				$this->setServerErrorMessage(404, "La méthode {$this->serviceClassMethod} pour le service {$serviceName} n'existe pas.");
			}

			unset($this->data["service"]);
			$this->queryUrlParams = $this->data;
		}
		else {
			$this->setServerErrorMessage(404, "Le paramètre de requête service est manquant.");
		}
	}

	/**
	 *
	 *
	 */
	private function handleByURIParsing() {

		$parts = explode('/', $this->queryUrl);
		if($parts[0] == "apiweb") {
			array_splice($parts, 0, 1);
		}

		// Schema
		$data_scheme = array(
			"services" => "",
			"operation" => "", 
			"id" => "",
			"other" => ""
		);

		if(isset($parts[0])) {
			$data_scheme["services"] = $parts[0];
			array_splice($parts, 0, 1);
		}
		else {
			$this->setServerErrorMessage(404, "Le premier paramètre doit correspondre à un service à interroger");
		}
		
		if(isset($parts[0])) {
			$data_scheme["operation"] = $parts[0];
			array_splice($parts, 0, 1);
		}
		else {
			$this->setServerErrorMessage(404, "Le deuxième paramètre doit correspondre à une opération à effectuer sur le service '{$data_scheme["services"]}'");
		}

		if(isset($parts[0])) {
			$data_scheme["id"] = $parts[0];
			array_splice($parts, 0, 1);
		}

		if(!empty($parts)) {
			$data_scheme["other"] = $parts;
		}

		$serviceName = $data_scheme["services"];

		$serviceClassFullName = self::NS_SEPARATOR . $this->serviceBaseNamespace . self::NS_SEPARATOR . $serviceName;
		$serviceClassFile     = $this->serviceFileDir . $serviceName . ".php";

		//var_dump($serviceName, $serviceClassFullName, $serviceClassFile);

		// On vérifie que le service demandé existe ou pas
		if(!class_exists($serviceClassFullName) || !file_exists($serviceClassFile)) { 
			//var_dump(class_exists($serviceClassFullName), file_exists($serviceClassFile));
			$this->setServerErrorMessage( 404, "Le service {$serviceName} n'existe pas." );
		}
		else {
			$this->service = new $serviceClassFullName(); 
		}

		$this->serviceClassMethod = strtolower( $this->httpMethod );

		var_dump($data_scheme);
	}

	/**
	 * 	Méthode setResponse()
	 *
	 * 	Met en forme le résultat renvoyé par l'API
	 *
	 * 	@param
	 * 	@return 	void
	 *
	 */
	public function setResponse() {

		if($this->service !== null && $this->serviceClassMethod !== null) {
			$result = call_user_func( array($this->service, $this->serviceClassMethod), $this->queryUrlParams );
			$this->json->response = $result->response;
			$this->json->apiError = $result->apiError;
			$this->json->apiErrorMessage = $result->apiErrorMessage;
			//exit;
		}
		else {
			$this->setServerErrorMessage(500, "La requête ne peut pas être traitée.");
		}
	}


	/**
	 * 	Méthode setServerErrorMessage($message)
	 *
	 * 	Affiche les messages d'erreur du serveur
	 *
	 * 	@param 		string 		$message 		[Messages d'erreurs du serveur]
	 * 	@return 	void
	 *
	 */
	private function setServerErrorMessage($code, $message) {
		$this->setHttpResponseCodeInfo($code);
		$this->json->serverError        = true;
		$this->json->serverErrorMessage = $message;
		exit;
	}

	/**
	 *
	 *
	 */
	private function setHttpResponseHeader($code) {
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
	public function normalizeUrlPath($url) {
		// Supprime les caractère invisibles en début et fin de chaîne
		$result = $url;
		// Supprime le premier caractère de la chaîne si c'est un slash '/'
		if(substr($result, 0, 1) == '/')
			$result = substr($result, 1, strlen($result)-1);
		// Supprime le dernier caractère de la chaîne si c'est un slash '/'
		if(substr($result, -1) == '/')
			$result = substr($result, 0, strlen($result)-1);

		return $result;
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
	public function setHttpResponseCodeInfo($code) {
		$this->setHttpResponseHeader($code);
		$this->json->httpResponseCode        = $code;
		$this->json->httpResponseCodeMessage = $this->getHttpResponseCodeMessage($code);
	}
}