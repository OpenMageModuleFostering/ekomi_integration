<?php

/**
 * Ekomi Integration
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
class Ekomi_EkomiIntegration_Model_Observer
{
    protected $_apiUrl = 'https://apps.ekomi.com/srr/add-recipient';

    /**
     * @param $observer
     */
    public function sendOrderToEkomi($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $storeId = $order->getStoreId();
        $helper = Mage::helper('ekomi_ekomiIntegration');
        $statuses = explode(',', $helper->getOrderStatusForReviewEmail($storeId));

        if (!$helper->isModuleEnabled($storeId) || (is_array($statuses) &&
                !empty($statuses) && !in_array($order->getStatus(), $statuses))) {
            return;
        }

        try {
            $postvars = $this->getData($order, $storeId);
            if ($postvars != '') {
                $this->sendOrderData($postvars);
            }

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * @param $order
     * @param $storeId
     * @return string
     */
    protected function getData($order, $storeId)
    {
        $helper = Mage::helper('ekomi_ekomiIntegration');
        $scheduleTime = date('d-m-Y H:i:s', strtotime($order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));

        $fields = array('shop_id' => $helper->getShopId($storeId), 'password' => $helper->getShopPassword($storeId), 'salutation' => '',
            'first_name' => $order->getBillingAddress()->getFirstname(),
            'last_name' => $order->getBillingAddress()->getLastname(),
            'email' => $order->getCustomerEmail(), 'transaction_id' => $order->getIncrementId(),
            'transaction_time' => $scheduleTime,
            'telephone' => $order->getBillingAddress()->getTelephone(),
            'sender_name' => Mage::getStoreConfig('trans_email/ident_sales/name'),
            'sender_email' => Mage::getStoreConfig('trans_email/ident_sales/email')
        );

        if ($order->getCustomerId()) {
            $fields['client_id'] = $order->getCustomerId();
            $fields['screen_name'] = $this->getCustomerScreenName($order->getCustomerId());
        } else {
            $fields['client_id'] = 'guest_oId_' . $order->getIncrementId();
            $lname =  $order->getBillingAddress()->getLastname();
            $fields['screen_name'] = $order->getBillingAddress()->getFirstname() . $lname[0];
        }

        if ($helper->isProductReviewEnabled($storeId)) {
            $fields['has_products'] = 1;
            $productsData = $this->getOrderProductsData($order, $storeId);
            $fields['products_info'] = json_encode($productsData['product_info']);
            $fields['products_other'] = json_encode($productsData['other']);
        }

        $postvars = '';
        $counter = 1;

        foreach ($fields as $key => $value) {
            if ($counter > 1) $postvars .= "&";
            $postvars .= $key . "=" . $value;
            $counter++;
        }

        return $postvars;
    }

    /**
     * @param $customerId
     * @return string
     */
    protected function getCustomerScreenName($customerId)
    {
        $customerData = Mage::getModel('customer/customer')->load($customerId);
        $screenName = $this->appendName($customerData->getPrefix(), false);
        $screenName .= $this->appendName($customerData->getFirstname(), ($screenName != '') ? true : false);
        $screenName .= $this->appendName($customerData->getMiddlename(), ($screenName != '') ? true : false);
        $screenName .= $this->appendName($customerData->getLastname(), ($screenName != '') ? true : false);
        $screenName .= $this->appendName($customerData->getSuffix(), ($screenName != '') ? true : false);
        return $screenName;
    }

    /**
     * @param $param
     * @param bool $space
     * @return string
     */
    protected function appendName($param, $space = true)
    {

        if ($param != '' && $space === true) {
            return ' ' . $param;
        }

        return $param;
    }

    /**
     * @param $order
     * @param $storeId
     * @return mixed
     */
    protected function  getOrderProductsData($order, $storeId)
    {
        $items = $order->getAllVisibleItems();
        foreach ($items as $item) {
            $product = $item->getProduct();
            $products['product_info'][$product->getId()] = urlencode($item->getName());
            $product->setStoreId($storeId);
            $canonicalUrl = $product->getUrlModel()->getUrl($product, array('_ignore_category' => true));
            $canonicalUrl = strstr($canonicalUrl, "?", true);
            if ($product->getThumbnail() != 'no_selection') {
                $productOther['image_url'] = utf8_decode(Mage::helper('catalog/image')->init($product, 'thumbnail'));
            }
            $productOther = array(
                'product_ids' => array(
                    'gbase' => utf8_decode($product->getSku())
                ), // product IDs
                'links' => array(
                    array('rel' => 'canonical', 'type' => 'text/html',
                        'href' => utf8_decode($canonicalUrl))
                )
            );
            $products['other'][$product->getId()]['product_other'] = $productOther;
        }

        return $products;
    }

    /**
     * @param $postvars
     * @param $boundary
     */
    protected function sendOrderData($postvars)
    {
        $boundary = md5(time());
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->_apiUrl);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('ContentType:multipart/form-data;boundary=' . $boundary));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
            curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            Mage::logException($e->getMessage());
        }
    }
}
