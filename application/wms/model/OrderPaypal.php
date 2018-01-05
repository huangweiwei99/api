<?php
/**
 * Created by PhpStorm.
 * User: huangweiwei
 * Date: 2017/12/21
 * Time: 下午4:38
 */

namespace app\wms\model;

use PayPal\PayPalAPI\TransactionSearchReq;
use PayPal\PayPalAPI\TransactionSearchRequestType;
use PayPal\PayPalAPI\GetTransactionDetailsReq;
use PayPal\PayPalAPI\GetTransactionDetailsRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;

use app\common\model\WMSBase as WMSBaseModel;


class OrderPaypal extends WMSBaseModel
{
    public function items()
    {
        return $this->hasMany('OrderPaypalItem');
    }

    public function getTransDetails($transId)
    {
        $transactionDetails = new GetTransactionDetailsRequestType();
        $transactionDetails->TransactionID = $transId;

        $request = new GetTransactionDetailsReq();
        $request->GetTransactionDetailsRequest = $transactionDetails;

        $config= array_merge(Config::get('paypal_config_base'),Config::get('paypal_config_vson_mail'));

        $paypalService = new PayPalAPIInterfaceServiceService($config);

        try {
            $transDetailsResponse = $paypalService->GetTransactionDetails($request);
            return $transDetailsResponse;
        } catch (\Expection $e) {
            return $e->getMessage();
        }
    }
}