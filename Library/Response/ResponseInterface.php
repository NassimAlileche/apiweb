<?php

namespace apiweb\Library\Response;

interface ResponseInterface {

	private $result;
	private $error;
	private $errorMessage;

	private function setResult($result);
	public function getResult();

	private function setError($error);
	public function getError();

	private function setErrorMessage($errorMessage);
	public function getErrorMessage();
}