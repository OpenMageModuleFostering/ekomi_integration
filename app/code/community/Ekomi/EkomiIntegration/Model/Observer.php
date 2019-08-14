<?php
    /**
     * Inchoo
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
class Ekomi_EkomiIntegration_Model_Observer extends Mage_Core_Helper_Abstract
{
    public function sendOrderToEkomi($observer)
    {
        
        $order = $observer->getEvent()->getOrder();
        $storeId = $order->getStoreId();
        $customer_id = $order->getCustomerId();
        $customerData = Mage::getModel('customer/customer')->load($customer_id);
        $helper = Mage::helper('ekomi_ekomiIntegration');
        $boundary = md5( time() );
        if (!$helper->isModuleEnabled($storeId)) {
            return;
        }
      
        try {
            $schedule_time = date('d-m-Y H:i:s',strtotime($order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));
            $fields = array( 'shop_id'=>$helper->getShopId($storeId), 'password'=>$helper->getShopPassword($storeId), 'salutation'=>'', 'first_name'=>$order->getBillingAddress()->getFirstname(), 'last_name'=>$order->getBillingAddress()->getLastname(), 'email'=>$order->getCustomerEmail(), 'transaction_id'=>$order->getId(),'product_id'=>'','product_name'=>'', 'transaction_time'=>$schedule_time, 'telephone'=>$order->getBillingAddress()->getTelephone());
              $postvars = '';
              $counter=1;
              foreach($fields as $key=>$value) {
                if($counter > 1)$postvars .="&";
                $postvars .= $key . "=" . $value;
                  $counter++;
              }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$helper->getServerAddress($storeId));
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, 'ContentType:multipart/form-data;boundary=' . $boundary);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST , 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
            $server_output = curl_exec ($ch);
            //Mage::logException($server_output);
            curl_close ($ch);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
