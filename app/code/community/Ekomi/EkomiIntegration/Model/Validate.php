<?php

/**
 * Ekomi
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 */
class Ekomi_EkomiIntegration_Model_Validate extends Mage_Core_Model_Config_Data
{

    const XML_PATH_SHOP_ID = 'ekomitab/ekomi_ekomiIntegration/shop_id';
    const XML_PATH_SHOP_PASSWORD = 'ekomitab/ekomi_ekomiIntegration/shop_password';

    public function save()
    {
        $ApiUrl = 'http://api.ekomi.de/v3/getSettings';
        $PostData = Mage::app()->getRequest()->getPost();

        foreach ($PostData['groups']['ekomi_ekomiIntegration'] as $fields) {
            if ($fields['shop_id'])
                $ShopId = $fields['shop_id']['value'];
            if ($fields['shop_password'])
                $ShopPassword = $fields['shop_password']['value'];
        }


        if ($ShopId == ''
            && isset($PostData['groups']['ekomi_ekomiIntegration']['fields']['shop_id']['inherit'])
            && $PostData['groups']['ekomi_ekomiIntegration']['fields']['shop_id']['inherit'] == 1
        ) {
            $ShopId = $this->getShopId();
        }

        if ($ShopPassword == ''
            && isset($PostData['groups']['ekomi_ekomiIntegration']['fields']['shop_password']['inherit'])
            && $PostData['groups']['ekomi_ekomiIntegration']['fields']['shop_password']['inherit'] == 1
        ) {
            $ShopPassword = $this->getPassword();
        }

        if ($ShopId == '' || $ShopPassword == '') {
            Mage::throwException('Shop ID & Password Required.');
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $ApiUrl . "?auth=" . $ShopId . "|" . $ShopPassword . "&version=cust-1.0.0&type=request&charset=iso");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close($ch);
            if ($server_output == 'Access denied')
                Mage::throwException($server_output);
            else
                return parent::save();
        }
    }

    /**
     * @return mixed
     */
    protected function getShopId()
    {
        return Mage::getStoreConfig(self::XML_PATH_SHOP_ID);
    }

    /**
     * @return mixed
     */
    protected function getPassword()
    {
        return Mage::getStoreConfig(self::XML_PATH_SHOP_PASSWORD);
    }
}
