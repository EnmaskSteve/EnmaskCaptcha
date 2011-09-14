<?php
/**
 * class name: EnmaskCaptcha
 * Author: enmask.com
 */

class EnmaskCaptcha {
    private $protocol;
    private $server;
    private $port;

    public function EnmaskCaptcha()
    {
        $this->protocol = "http://";
        $this->server = "enmask.com/";
        $this->port = "";
    } 

    public function getHtml($name)
    {
        $this->protocol = "http://";
        $url = $this->protocol . $this->server . $this->port . "/Scripts/Enmask.Captcha.js";
        return '<script type="text/javascript" ' . ' src="' . $url . '" data-enmask="true" data-enmask-name="' . $name . '"></script>';
    } 

    public function validate($captchaChallenge, $captchaResponse)
    {
        $this->protocol = "http://";

        $url = $this->protocol . $this->server . $this->port . "CaptchaFont/ValidateCaptcha";
        $encoded = urlencode('response') . '=' . urlencode($captchaResponse) . '&';
        $encoded .= urlencode('challenge') . '=' . urlencode($captchaChallenge);

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $encoded);
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
        $result = json_decode($buffer);
        return array($result->isValid, "Captcha was not valid.");
    } 

   	function &getInstance()
	{
		static $instance;
		if( !isset($instance) ){
			$instance = new EnmaskCaptcha();
		}
		return $instance;
	}
	
   function get($key, $default = '')
    {
        $inst = &EnmaskCaptcha::getInstance();
        return $inst->_get($key, $default);
    } 

    function _get($key, $default = '')
    {
        $key = '_' . $key;
        return isset($this->$key) ? $this->$key : $default;
    } 

    function _set($key, $value)
    {
        $key = '_' . $key;
        $this->$key = $value;
    } 

    function process()
    {
        $inst = &EnmaskCaptcha::getInstance();
        $inst->_process();
    } 

    function _process()
    {
		if ($this->_processed) {
            return;
        } 
		
		if (JRequest::getVar("myCaptcha_challenge")) {
            $this->_submitted = true;
            $enmask_captcha = new EnmaskCaptcha();
            list($isValid, $message) = $enmask_captcha->validate(JRequest::getVar("myCaptcha_challenge"), JRequest::getVar("myCaptcha"));
            $this->_success = $isValid;
            if (!$this->_success) {
                $this->_error = $message;
            } 
        } 
        $this->_processed = true;
    } 
}