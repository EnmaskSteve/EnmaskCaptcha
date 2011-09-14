<?php
/**
 * Enmask Captcha Plugin.
 * 
 * @version 1.0
 * @package Enmask Captcha Plugin 13.05.2011
 * @author Enmask
 * @copyright (C) 2010 by Enmask.com
 */

require_once(dirname(__FILE__) . '/Enmask_Captcha/enmask.captcha.php');

defined('_JEXEC') or ('_VALID_MOS') or die('Direct Access to this location is not allowed.');

jimport('joomla.plugin.plugin');

class plgSystemEnmask_Captcha extends JPlugin {
    function plgSystemEnmask_Captcha(&$subject, $config)
    {
        parent::__construct($subject, $config);
    } 

    function processPage()
	{
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$task = JRequest::getCmd('task');

		if( $this->params->get('addToContact',1) == 1 && $option == 'com_contact' && $task == 'contact.submit'){
			return true;
		}
		if( $this->params->get('addToUserRegistration',1) == 1 && $option == 'com_users' && $task == 'registration.register'){
			return true;
		}
		if( $this->params->get('addToFUser',1) == 1 && $task == 'remind.remind'){
			return true;
		}
		if( $this->params->get('addToFPass',1) == 1 && $task == 'reset.request'){
			return true;
		}
		if( $this->params->get('addToLogin',1) == 1 && $task == 'user.login'){
			return true;
		}
		return false;
	}
	


    function addFormToBuffer()
	{
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$task = JRequest::getCmd('task');
		if( $this->params->get('addToContact',1) == 1 && $option == 'com_contact' && $view == 'contact' ){
			return true;
		}
		if( $this->params->get('addToUserRegistration',1) == 1 && $option == 'com_users' && $view == 'registration'){
			return true;
		}
		if( $this->params->get('addToFUser',1) == 1 && $option == 'com_users' && $view == 'remind'){
			return true;
		}
		if( $this->params->get('addToFPass',1) == 1 && $option == 'com_users' && $view == 'reset' && !JRequest::getCmd('layout')){
			return true;
		}
		if( $this->params->get('addToLogin',1) == 1 && $option == 'com_users' && $view == 'login'){
			return true;
		}
		return false;
	}

    function onAfterInitialise()
    {
        if (!$this->processPage()){
            return;
        } 
        EnmaskCaptcha::process();
    } 

    function onAfterRoute()
    {
		$mainframe = JFactory::getApplication();
		if( !$this->processPage() ){
			return;
		}
		$submited = EnmaskCaptcha::get('submitted');
		$success = EnmaskCaptcha::get('success');

		if( !$success ){
			$mainframe->enqueueMessage('Captcha text error', 'error');
			$option = JRequest::getCmd('option');
			$view = JRequest::getCmd('view');
			$task = JRequest::getCmd('task');
			if ($option == 'com_contact' && $task == 'contact.submit')
			{
				JRequest::setVar('task','0');
				JRequest::setVar('view','contact');
				unset($_GET['task'], $_POST['task']);
			}
			if ($option == 'com_users' && $task == 'registration.register')
			{
				JRequest::setVar('task','0');
				JRequest::setVar('view','registration');
				$_GET['task'] = '0';
				$_POST['task'] = '0';
				unset($_GET['task'], $_POST['task']);
			}
			if ($option == 'com_users' && $task == 'remind.remind')
			{
				JRequest::setVar('view','remind');
				JRequest::setVar('task','0');
				unset($_GET['task'], $_POST['task']);
			}
			if ($option == 'com_users' && $task == 'reset.request')
			{
				JRequest::setVar('view','reset');
				JRequest::setVar('task','0');
				unset($_GET['task'], $_POST['task']);
			}
			if ($option == 'com_users' && $task == 'user.login')
			{
				JRequest::setVar('view','login');
				JRequest::setVar('task','0');
				unset($_GET['task'], $_POST['task']);
			}
		}
	} 

    function onAfterDispatch()
    {
        if (!$this->addFormToBuffer()) {
            return;
        } 

        $document = &JFactory::getDocument();
        $buffer = $document->getBuffer('component');
		
        // add recaptcha before the submit button
        $re = "/<(button|input)(.*type=['\"](submit|button)['\"].*)(.*name=['\"](validate)['\"].*)?>/i";

        $buffer = preg_replace_callback($re, array(&$this, '_addenmaskscript'), $buffer);

        $document->setBuffer($buffer, 'component');
    } 

    function _addEnmaskScript($matches)
    {
		$enmask_captcha = new EnmaskCaptcha();
        return $enmask_captcha->getHtml('myCaptcha') . '<button class="button validate1" type="submit" name="validate">'.strip_tags($matches[0]).'</button>';
    } 
}