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
     public function save()
    {
        $PostData=Mage::app()->getRequest()->getPost();
        foreach($PostData['groups']['ekomi_ekomiIntegration'] as $fields)
        {
           if($fields['server_address'])
            $ServerAddress=$fields['server_address']['value'];
           if($fields['shop_id'])
            $ShopId=$fields['shop_id']['value'];
           if($fields['shop_password'])
            $ShopPassword=$fields['shop_password']['value'];
        }
        if ($ShopId =='' || $ShopPassword=='') {
            Mage::throwException('Shop ID & Password Required.');
        }
        else {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,"http://api.ekomi.de/v3/getSettings?auth=".$ShopId."|".$ShopPassword."&version=cust-1.0.0&type=request&charset=iso");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $server_output = curl_exec ($ch);
                curl_close ($ch);
                if($server_output=='Access denied')
                    Mage::throwException($server_output);
                else
                return parent::save(); 
           } 
    }
}
