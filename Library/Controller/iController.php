<?php

namespace apiweb\Library\Controller;

/**
 *
 *
 */
interface iController {

    public function get($param);
    
    public function post($param);
    
    public function put($param);
    
    public function delete($param);

}