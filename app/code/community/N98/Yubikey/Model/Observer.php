<?php
/**
 * netz98 magento module
 *
 * LICENSE
 *
 * Copyright © 2012.
 * netz98 new media GmbH. Alle Rechte vorbehalten.
 *
 * Die Nutzung und Weiterverbreitung dieser Software in kompilierter oder nichtkompilierter Form, mit oder ohne Veränderung, ist unter den folgenden Bedingungen zulässig:
 *
 * 1. Weiterverbreitete kompilierte oder nichtkompilierte Exemplare müssen das obere Copyright, die Liste der Bedingungen und den folgenden Verzicht im Sourcecode enthalten.
 * 2. Alle Werbematerialien, die sich auf die Eigenschaften oder die Benutzung der Software beziehen, müssen die folgende Bemerkung enthalten: "Dieses Produkt enthält Software, die von der netz98 new media GmbH entwickelt wurde."
 * 3. Der Name der netz98 new media GmbH darf nicht ohne vorherige ausdrückliche, schriftliche Genehmigung zur Kennzeichnung oder Bewerbung von Produkten, die von dieser Software abgeleitet wurden, verwendet werden.
 * 4. Es ist Lizenznehmern der netz98 new media GmbH nur dann erlaubt die veränderte Software zu verbreiten, wenn jene zu den Bedingungen einer Lizenz, die eine Copyleft-Klausel enthält, lizenziert wird.
 *
 * Diese Software wird von der netz98 new media GmbH ohne jegliche spezielle oder implizierte Garantien zur Verfügung gestellt. So übernimmt die netz98 new media GmbH keine Gewährleistung für die Verwendbarkeit der Software für einen speziellen Zweck oder die generelle Nutzbarkeit. Unter keinen Umständen ist netz98 haftbar für indirekte oder direkte Schäden, die aus der Verwendung der Software resultieren. Jegliche Schadensersatzansprüche sind ausgeschlossen.
 *
 *
 * Copyright © 2012
 * netz98 new media GmbH. All rights reserved.
 *
 * The use and redistribution of this software, either compiled or uncompiled, with or without modifications are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of compiled or uncompiled source must contain the above copyright notice, this list of the conditions and the following disclaimer:
 * 2. All advertising materials mentioning features or use of this software must display the following acknowledgement: “This product includes software developed by the netz98 new media GmbH, Mainz.”
 * 3. The name of the netz98 new media GmbH may not be used to endorse or promote products derived from this software without specific prior written permission.
 * 4. License holders of the netz98 new media GmbH are only permitted to redistribute altered software, if this is licensed under conditions that contain a copyleft-clause.
 * This software is provided by the netz98 new media GmbH without any express or implied warranties. netz98 is under no condition liable for the functional capability of this software for a certain purpose or the general usability. netz98 is under no condition liable for any direct or indirect damages resulting from the use of the software. Liability and Claims for damages of any kind are excluded.
 *
 * @copyright Copyright (c) 2012 netz98 new media GmbH (http://www.netz98.de)
 * @author netz98 new media GmbH <info@netz98.de>
 * @category N98
 * @package N98_Yubikey
 */

/**
 * Hooks into every adminhtml controller and checks if yubikey is enabled.
 * Forwards not authorized yubikey enabled users to yubikey login form.
 */
class N98_Yubikey_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function controllerActionPredispatch(Varien_Event_Observer $observer)
    {
        $session = Mage::getSingleton('admin/session');
        /* @var $session Mage_Admin_Model_Session */
        if ($session->getUser() == null) {
            return;
        }
        $yubikey = $session->getUser()->getYubikey();

        $config = Mage::getSingleton('n98_yubikey/config');
        /* @var $config N98_Yubikey_Model_Config */
        if ($config->isEnabled() && !empty($yubikey)) {
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
                        if ($yubiAuth->verify($request->getPost('otp'), $yubikey)) {
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

    /**
     * @param Varien_Event_Observer $observer
     */
    public function addYubikeyTabToUserPermissionForm(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        /* @var $block Mage_Adminhtml_Block_Permissions_User_Edit_Tabs */

        if ($block instanceof Mage_Adminhtml_Block_Permissions_User_Edit_Tabs) {
            $tabData = array(
                'label'     => Mage::helper('n98_yubikey')->__('Yubikey setup'),
                'title'     => Mage::helper('n98_yubikey')->__('Yubikey setup'),
                'content'   => $block->getLayout()->createBlock('n98_yubikey/adminhtml_permission_user_edit_tab_yubikey')->toHtml(),
                'active'    => true
            );
            if (method_exists($block, 'addTabAfter')) {
                // >= CE 1.6
                $block->addTabAfter('yubikey_section', $tabData, 'roles_section');
            } else {
                $block->addTab('yubikey_section', $tabData);
            }
        }
    }
}
