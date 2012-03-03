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
 * Yubikey service class
 */
class N98_Yubikey_Model_Auth extends Zend_Service_Abstract
{
    /**
     * @var int
     */
    const MIN_NONCE_LENGTH = 16;

    /**
     * @var int
     */
    const MAX_NONCE_LENGTH = 40;

    /**
     * Length of the yubikey
     */
    const YUBKEY_LENGTH = 12;

    /**
     * @var string
     */
    const STATUS_OK = 'OK';

    /**
     * @var string
     */
    const STATUS_BAD_OTP = 'BAD_OTP';

    /**
     * @var string
     */
    const STATUS_REPLAYED_OTP = 'REPLAYED_OTP';

    /**
     * @var string
     */
    const STATUS_BAD_SIGNATURE = 'BAD_SIGNATURE';

    /**
     * @var string
     */
    const STATUS_MISSING_PARAMETER = 'MISSING_PARAMETER';

    /**
     * @var string
     */
    const STATUS_NO_SUCH_CLIENT = 'NO_SUCH_CLIENT';

    /**
     * @var string
     */
    const STATUS_OPERATION_NOT_ALLOWED = 'OPERATION_NOT_ALLOWED';

    /**
     * @var string
     */
    const STATUS_BACKEND_ERROR = 'BACKEND_ERROR';

    /**
     * @var string
     */
    const STATUS_NOT_ENOUGH_ANSWERS = 'NOT_ENOUGH_ANSWERS';

    /**
     * @var string
     */
    const STATUS_REPLAYED_REQUEST = 'REPLAYED_REQUEST';

    /**
     * @var array
     */
    protected $_validationServers = array(
        'api.yubico.com/wsapi/2.0/verify',
        'api2.yubico.com/wsapi/2.0/verify',
        'api3.yubico.com/wsapi/2.0/verify',
        'api4.yubico.com/wsapi/2.0/verify',
        'api5.yubico.com/wsapi/2.0/verify'
    );

    /**
     * @var string
     */
    protected $_clientId = null;

    /**
     * @var string
     */
    protected $_apiSecret = null;

    /**
     * @var bool
     */
    protected $_useHttps = true;

    /**
     * Sync level in percentage between 0 and 100 or "fast" or "secure"
     *
     * @var int
     */
    protected $_syncLevel = 50;

    /**
     * @var int
     */
    protected $_timeout = 10; // seconds

    /**
     * @var bool
     */
    protected $_useTimestamp = true;

    /**
     * @var string
     */
    protected $_status = null;

    /**
     * @param $serverIndex
     */
    protected function _getQueryString($opt)
    {
        $data = array(
            'id'    => $this->_clientId,
            'otp'   => $opt,
            'nonce' => $this->_generateNonce(),
        );
        if ($this->_useTimestamp) {
            $data['timestamp'] = 1;
        }
        $data['sl'] = $this->_syncLevel;
        $data['timeout'] = $this->_timeout;
        ksort($data);
        foreach ($data as $key => &$value) {
            $value = urlencode($value);
        }
        $data['h'] = $this->_generateSignature(http_build_query($data));
        return http_build_query($data);
    }

    /**
     * @return string
     */
    protected function _generateNonce()
    {
        $length = rand(self::MIN_NONCE_LENGTH, self::MAX_NONCE_LENGTH);
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $string = '';
        for ($p = 0; $p < $length; $p++) {
            $string .= substr($characters, rand(0, strlen($characters)), 1);
        }
        return $string;
    }

    /**
     * Generates a signature by given url parameters
     *
     * @param string $data
     * @return string
     */
    protected function _generateSignature($data)
    {
        return base64_encode(hash_hmac('sha1', $data, base64_decode($this->_apiSecret), true));
    }

    /**
     * @param string $otp
     * @param string $yubikey
     * @return bool
     */
    public function verify($otp, $yubikey = null)
    {
        if (!$this->_isValidOtp($otp, $yubikey)) {
            return false;
        }
        $queryString = $this->_getQueryString($otp);
        foreach ($this->_validationServers as $apiUrl) {
            try {
                $apiServerUrl = ($this->_useHttps ? 'https://' : 'http://')
                    . $apiUrl
                    . '?'
                    . $queryString;

                $response = $this->getHttpClient()
                                ->setUri($apiServerUrl)
                                ->request();
                if ($response->isSuccessful()) {
                    $parts = $this->_extractParts($response);
                    $this->_status = $parts['status'];
                }

                // Sometimes yubico sends a backend error status
                // try next server.
                if ($parts['status'] == self::STATUS_BACKEND_ERROR) {
                    continue;
                }

                // Check if response contains OTP and nonce
                if (!isset($parts['otp']) || !isset($parts['nonce'])) {
                    return false;
                }

                // Check if send yubikey is same as received yubikey.
                // This must be done to prevent "cut & paste" attacks.
                if ($parts['otp'] != $otp) {
                    return false;
                }

                // Status OK
                if ($parts['status'] == self::STATUS_OK) {
                    return true;
                }
            } catch (Zend_Http_Client_Exception $e) {
                continue; // Take next URL
            }
        }

        return false;
    }

    /**
     * Check if OTP has a valid format
     *
     * @param string $otp
     * @param string $yubikey
     * @return bool
     */
    protected function _isValidOtp($otp, $yubikey = null)
    {
        $yubikeyIsValid = true;
        $formatIsValid = preg_match("/^[cbdefghijklnrtuvCBDEFGHIJKLNRTUV]{44}$/", $otp);
        if ($yubikey != null) {
            // Check if first 12 chars match yubikey of user
            if (substr($otp, 0, self::YUBKEY_LENGTH) !== $yubikey) {
                $yubikeyIsValid = false;
            }
        }
        return $formatIsValid && $yubikeyIsValid;
    }

    /**
     * Extract the status from response string
     *
     * Response looks like this:
     *
     * h=vjhFxZrNHB5CjI6vhuSeF2n46a8=
     * t=2010-09-23T20:34:51Z0678
     * otp=cccccccbchdifctrndncchkftchjlnbhvhtugdljibej
     * nonce=somesendrandomstring
     * sl=75
     * status=OK
     */
    protected function _extractParts(Zend_Http_Response $response)
    {
        $message = $response->getBody();
        $parts = array();
        foreach (explode("\r\n", trim($message)) as $line) {
            list($key, $value) = explode('=', $line);
            $parts[$key] = $value;
        }
        return $parts;
    }

    /**
     * @param string $apiSecret
     */
    public function setApiSecret($apiSecret)
    {
        $this->_apiSecret = $apiSecret;
    }

    /**
     * @return string
     */
    public function getApiSecret()
    {
        return $this->_apiSecret;
    }

    /**
     * @param array $apiUrls
     */
    public function setValidationServers($apiUrls)
    {
        $this->_validationServers = $apiUrls;
    }

    /**
     * @return array
     */
    public function getValidationServers()
    {
        return $this->_validationServers;
    }

    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->_clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->_clientId;
    }

    /**
     * @param int $syncLevel
     */
    public function setSyncLevel($syncLevel)
    {
        $this->_syncLevel = $syncLevel;
    }

    /**
     * @return int
     */
    public function getSyncLevel()
    {
        return $this->_syncLevel;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    /**
     * @param boolean $translateOtp
     */
    public function setTranslateOtp($translateOtp)
    {
        $this->_translateOtp = $translateOtp;
    }

    /**
     * @return boolean
     */
    public function getTranslateOtp()
    {
        return $this->_translateOtp;
    }

    /**
     * @param boolean $useHttps
     */
    public function setUseHttps($useHttps)
    {
        $this->_useHttps = $useHttps;
    }

    /**
     * @return boolean
     */
    public function getUseHttps()
    {
        return $this->_useHttps;
    }

    /**
     * @param boolean $useTimestamp
     */
    public function setUseTimestamp($useTimestamp)
    {
        $this->_useTimestamp = $useTimestamp;
    }

    /**
     * @return boolean
     */
    public function getUseTimestamp()
    {
        return $this->_useTimestamp;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->_status = $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->_status;
    }
}