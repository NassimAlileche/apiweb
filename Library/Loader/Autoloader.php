<?php

namespace apiweb\Library\Loader;


/**
 *  Classe permettant de charger automatiquement les classes appelées dans l'application 
 *  sans besoin d'utiliser include ou require
 */
class Autoloader {

	/**
	 * 	Chemin de base
	 * 	@var string
	 */
	private static $basePath = null;

	/**
	 *  Domaine du namespace
	 *  @var string
	 */
	private static $namespaceDomain = "apiweb\\";

	/**
	 * 	Instance statique de la classe
	 *
	 * 	@var Object
	 */
	private static $instance = null;

	/**
	 * 	getInstance()
	 *
	 * 	Récupère l'instance de la classe
	 *
	 * 	@return \apiweb\Library\Loader\Autoloader
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  resetInstance()
	 *
	 *  Réinitialise l'instance de la classe
	 *
	 *  @return void
	 */
	public static function resetInstance() {
		self::$instance = null;
	}

	/**
	 * 	__construct()
	 *
	 * 	Constructeur par défaut de la classe
	 *
	 *	@return void
	 */
	private function __construct() {
		spl_autoload_register(array(__CLASS__, 'autoload'));
	}

	/**
	 * setBasePath($path)
	 *
	 * Setter de $basePath
	 *
	 * @param   String  $path  [Chemin du projet (c:\wamp\www\...)]
	 *
	 * @return  void
	 * 
	 * @example 
	 *
	 *     $class::setBasePath("c:\wamp\www\recette")
	 */
	public static function setBasePath($path){
		self::$basePath = $path;
	}

	/**
	 * getBasePath()
	 *
	 * Getter de $basePath
	 *
	 * @return  String  [Retourne $basePath]
	 */
	public function getBasePath() { return self::$basePath; }

	/**
	 * autoload($class)
	 *
	 * Charge la classe
	 * 
	 * @param   String          $class  [Nom de la classe à charger]
	 *
	 * @return  void|Exception  [Retourne une exception si $basePath n'a pas été défini]
	 */
	protected static function autoload($class) {
		if(is_null(self::$basePath)) {
			throw new \Exception("basePath dans la classe " . __CLASS__ . " est Null");
		}

		$name = str_replace( self::$namespaceDomain, "", $class );
		$pathFile = self::$basePath . str_replace('\\', DIRECTORY_SEPARATOR, $name) . ".php";

		//var_dump(array("0" => $class, "1" => self::$namespaceDomain, "2" => $name, "3" => $pathFile));

		if(file_exists($pathFile))
			require_once($pathFile);
		else
			require_once( self::$basePath . "Application/Controllers/DefaultController.php" );
			//throw new \Exception("Exception sur le chemin de fichier : {$pathFile} n'existe pas.");
			//exit;
	}
}