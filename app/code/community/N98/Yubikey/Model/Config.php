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
 * Abstraction for store config to fetch global yubikey settings
 */
class N98_Yubikey_Model_Config extends Mage_Core_Model_Abstract
{
    /**
     * @var int
     */
    const DEFAULT_TIMEOUT = 10;

    /**
     * @var string
     */
    const XML_PATH_YUBIKEY_ENABLED = 'admin/yubikey/enabled';

    /**
     * @var string
     */
    const XML_PATH_YUBIKEY_APIKEY = 'admin/yubikey/api_key';

    /**
     * @var string
     */
    const XML_PATH_YUBIKEY_APIID = 'admin/yubikey/api_id';

    /**
     * @var string
     */
    const XML_PATH_YUBIKEY_USE_HTTPS = 'admin/yubikey/use_https';

    /**
     * @var string
     */
    const XML_PATH_YUBIKEY_TIMEOUT = 'admin/yubikey/timeout';

    /**
     * @var string
     */
    const XML_PATH_YUBIKEY_LOG_ENABLED = 'admin/yubikey/log_enabled';

    /**
     * @var string
     */
    const XML_PATH_YUBIKEY_VALIDATION_SERVER1 = 'admin/yubikey/validation_server_1';

    /**
     * @var string
     */
    const XML_PATH_YUBIKEY_VALIDATION_SERVER2 = 'admin/yubikey/validation_server_2';

    /**
     * @var string
     */
    const XML_PATH_YUBIKEY_VALIDATION_SERVER3 = 'admin/yubikey/validation_server_3';

    /**
     * @var string
     */
    const XML_PATH_YUBIKEY_VALIDATION_SERVER4 = 'admin/yubikey/validation_server_4';

    /**
     * @var string
     */
    const XML_PATH_YUBIKEY_VALIDATION_SERVER5 = 'admin/yubikey/validation_server_5';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_YUBIKEY_ENABLED) == 1;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return Mage::getStoreConfig(self::XML_PATH_YUBIKEY_APIKEY);
    }

    /**
     * @return string
     */
    public function getApiId()
    {
        return Mage::getStoreConfig(self::XML_PATH_YUBIKEY_APIID);
    }

    /**
     * @return bool
     */
    public function useHttps()
    {
        return Mage::getStoreConfig(self::XML_PATH_YUBIKEY_USE_HTTPS) == 1;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        $timeout = intval(Mage::getStoreConfig(self::XML_PATH_YUBIKEY_TIMEOUT));
        if ($timeout < 10) {
            $timeout = self::DEFAULT_TIMEOUT;
        }
        return $timeout;
    }

    /**
     * @return bool
     */
    public function isLogEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_YUBIKEY_LOG_ENABLED) == 1;
    }

    /**
     * @return array
     */
    public function getValidationServers()
    {
        $r = new ReflectionClass(__CLASS__);
        $servers = array();
        for ($i = 1; $i < 6; $i++) {
            $servers[] = Mage::getStoreConfig($r->getConstant('XML_PATH_YUBIKEY_VALIDATION_SERVER' . $i));
        }
        return $servers;
    }
}
