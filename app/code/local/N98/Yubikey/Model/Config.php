<?php

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
