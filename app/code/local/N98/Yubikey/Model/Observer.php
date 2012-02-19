<?php

class N98_Yubikey_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function controllerActionPredispatch($observer)
    {
        $config = Mage::getSingleton('n98_yubikey/config');
        /* @var $config N98_Yubikey_Model_Config */
        if ($config->isEnabled()) {
            $session = Mage::getSingleton('admin/session');
            /** @var $session Mage_Admin_Model_Session */
            if (!$session->getIsYubikeyAuthenticated()) {
                $request = Mage::app()->getRequest();
                /* @var $request Mage_Core_Controller_Request_Http */
                if ($request->getRequestedControllerName() != 'yubikey'
                    && $request->getRequestedActionName() != 'login'
                ){
                    $request->setControllerName('yubikey')
                            ->setActionName('login')
                            ->setDispatched(false);
                } else {
                    if ($request->getPost('otp')) {
                        $yubiAuth = Mage::getModel('n98_yubikey/auth');
                        /* @var $yubiAuth N98_Yubikey_Model_Auth */
                        $yubiAuth->setApiSecret($config->getApiKey());
                        $yubiAuth->setClientId($config->getApiId());
                        $yubiAuth->setUseHttps($config->useHttps());
                        $yubiAuth->setTimeout($config->getTimeout());
                        $yubiAuth->setValidationServers($config->getValidationServers());
                        if ($yubiAuth->verify($request->getPost('otp'))) {
                            $session->setIsYubikeyAuthenticated(true);
                            $request->setControllerName('index')
                                ->setActionName('index')
                                ->setDispatched(false);
                        } else {
                            if ($config->isLogEnabled()) {
                                $this->_log($request->getPost('otp'), $yubiAuth->getStatus());
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $otp
     * @param string $status
     */
    protected function _log($otp, $status)
    {
        $message = date('c') . "\n OTP: " . $otp . ' | Status: ' . $status;
        Mage::log($message, Zend_Log::DEBUG, 'yubikey.log');
    }
}
