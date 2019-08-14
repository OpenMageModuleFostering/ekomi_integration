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
class Ekomi_EkomiIntegration_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ACTIVE = 'ekomitab/ekomi_ekomiIntegration/active';
    const XML_PATH_PRODUCT_REVIEWS = 'ekomitab/ekomi_ekomiIntegration/product_reviews';
    const XML_PATH_SHOP_ID = 'ekomitab/ekomi_ekomiIntegration/shop_id';
    const XML_PATH_SHOP_PASSWORD = 'ekomitab/ekomi_ekomiIntegration/shop_password';
    const XML_PATH_ORDER_STATUS = 'ekomitab/ekomi_ekomiIntegration/order_status';
    const XML_PATH_REVIEW_MOD= 'ekomitab/ekomi_ekomiIntegration/review_mod';

    public function isModuleEnabled($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ACTIVE, $store);
    }

    public function isProductReviewEnabled($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_PRODUCT_REVIEWS, $store);
    }

    public function getShopId($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SHOP_ID, $store);
    }

    public function getShopPassword($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SHOP_PASSWORD, $store);
    }

    public function getOrderStatusForReviewEmail($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ORDER_STATUS, $store);
    }

    public function getReviewMod($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_REVIEW_MOD, $store);
    }

    /**
     * Validates E164 numbers
     * @param $phone
     *
     * @return bool
     */
    function validateE164($phone)
    {
        $pattern = '/^\+?[1-9]\d{1,14}$/';
        preg_match($pattern, $phone, $matches);
        if (!empty($matches)) {
            return true;
        }
        return false;
    }

    /**
     * @param $phone
     * @param $country
     *
     * @return mixed|string
     */
    function addCountryCode($phone, $country)
    {
        $ApiUrl = 'https://plugins-dashboard.ekomiapps.de/api/v1/country-code';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ApiUrl . '?shop_id=' . $this->getShopId() . '&interface_password=' . $this->getShopPassword() . '&country_code=' . $country);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($json, true);
        if($response['status']['type'] == 'success'){
            $phoneCode = $response['data']['phone_code'];

            if(substr($phone, 0 , 1) == '0') {
                $phone = substr_replace($phone, $phoneCode, 0, 1);
            } else if(substr($phone, 0 , 2) != $phoneCode) {
                $phone = $phoneCode . $phone;
            }

            return $phone;
        }
    }
}
