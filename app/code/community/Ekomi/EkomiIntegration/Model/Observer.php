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

        if (!$helper->isModuleEnabled($storeId)) {
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
    protected function getData($order, $storeId )
    {
        $helper = Mage::helper('ekomi_ekomiIntegration');
        $scheduleTime = date('d-m-Y H:i:s',strtotime($order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));
        $fields = array('shop_id'=>$helper->getShopId($storeId), 'password'=>$helper->getShopPassword($storeId), 'salutation'=>'',
            'first_name'=>$order->getBillingAddress()->getFirstname(),
            'last_name'=>$order->getBillingAddress()->getLastname(),
            'email'=>$order->getCustomerEmail(), 'transaction_id'=>$order->getIncrementId(),
             'transaction_time'=>$scheduleTime,
            'telephone'=>$order->getBillingAddress()->getTelephone(),
            'sender_name'=>Mage::getStoreConfig('trans_email/ident_sales/name'),
            'sender_email'=>Mage::getStoreConfig('trans_email/ident_sales/email')
        );
        if ($helper->isProductReviewEnabled($storeId)){
            $fields['has_products'] = 1;
            $fields['products_info'] = $this->getOrderProductsData($order);
        }
        $postvars = '';
        $counter = 1;

        foreach($fields as $key=>$value) {
            if($counter > 1)$postvars .="&";
            $postvars .= $key . "=" . $value;
            $counter++;
        }

        return $postvars;
    }

    protected function getOrderProductsData($order)
    {
        $items = $order->getAllVisibleItems();
        foreach ($items as $item) {
            $products[$item->getId()] = $item->getName();
        }

        return json_encode($products);
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
            curl_setopt($ch, CURLOPT_URL,$this->_apiUrl);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('ContentType:multipart/form-data;boundary=' . $boundary));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST , 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
            curl_exec ($ch);
            curl_close ($ch);
        } catch (Exception $e) {
            Mage::logException($e->getMessage());
        }
    }
}
