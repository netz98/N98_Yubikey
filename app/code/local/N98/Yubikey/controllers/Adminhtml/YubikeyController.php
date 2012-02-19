<?php

class N98_Yubikey_Adminhtml_YubikeyController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function loginAction()
    {
        $this->_outTemplate('n98_yubikey/login');
    }

    /**
     * Render specified template
     *
     * @param string $tplName
     * @param array $data parameters required by template
     */
    protected function _outTemplate($tplName, $data = array())
    {
        $this->_initLayoutMessages('adminhtml/session');
        $block = $this->getLayout()->createBlock('adminhtml/template')->setTemplate("$tplName.phtml");
        foreach ($data as $index => $value) {
            $block->assign($index, $value);
        }
        $html = $block->toHtml();
        Mage::getSingleton('core/translate_inline')->processResponseBody($html);
        $this->getResponse()->setBody($html);
    }
}
