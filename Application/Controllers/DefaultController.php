<?php

namespace apiweb\Application\Controllers;

/**
 *
 *
 */
class DefaultController extends \apiweb\Library\Controller\Controller {
    
    /**
     *  Méthode __construct()
     *
     *  Constructeur par défaut appelant le constructeur de Library\Controller\Controller
     *
     *  @return     void
     *
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     *  get($params)
     *
     *  Récupère l'ensemble des assurés avec les paramètres de la requête GET
     *
     *  @param      array       $params         []
     *  @return     array
     *
     */
    public function get($params){


        return $this->setApiResult("get default");
    }

    /**
     *  post($params)
     *
     *  Crée un assuré avec les paramètres de la requête POST
     *
     *  @param      array       $params         []
     *  @return     array
     *
     */
    public function post($params){

            
        return $this->setApiResult("post default");
    }

    /**
     *  put($params)
     *
     *  Mets à jour un assuré avec les paramètres de la requête PUT
     *
     *  @param      array       $params         []
     *  @return     array
     *
     */
    public function put($params){


        return $this->setApiResult("put default");
    }

    /**
     *  delete($params)
     *
     *  Supprime un assuré avec les paramètres de la requête DELETE
     *
     *  @param      array       $params         []
     *  @return     array
     *
     */
    public function delete($params){

        return $this->setApiResult("delete default");
    }

}