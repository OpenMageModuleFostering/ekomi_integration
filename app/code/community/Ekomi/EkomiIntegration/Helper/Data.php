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
    const XML_PATH_DEBUG_RESULT= 'ekomitab/ekomi_ekomiIntegration/debug_result';

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

    public function getDebugResult($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_DEBUG_RESULT, $store);
    }
}
