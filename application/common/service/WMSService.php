<?php

namespace app\common\service;

use app\wms\model\OrderPaypal;
use app\wms\model\Product as ProductModel;
use app\wms\model\Supplier as SupplierModel;
use app\wms\model\Purchase as PurchaseModel;
use app\wms\model\Order as OrderModel;
use app\wms\model\OrderPaypal as OrderPaypalModel;
use app\wms\model\OrderPaypalItem as OrderPaypalItemModel;
use think\Log;
use think\Validate;
use think\Request;
use think\Config;
use think\Session;
use PayPal\PayPalAPI\TransactionSearchReq;
use PayPal\PayPalAPI\TransactionSearchRequestType;
use PayPal\PayPalAPI\GetTransactionDetailsReq;
use PayPal\PayPalAPI\GetTransactionDetailsRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use com\Upload;

/**
 * 类描述：WMSService类包含了wms所有详细的操作
 * Class WMSService
 * @package app\common\service
 */
class WMSService extends BaseService
{
    /*******************类属性*******************/
    /**
     * @var int 默认当前页
     */
    protected $_page;

    /**
     * @var int 默认一页的记录数目
     */
    protected $_limit;

    /**
     * @var array 上传文件的限制条件
     */
    protected $imageRule;

    /**
     * @var array 上传文件的路径
     */
    protected $imagePath;

    protected $_paypalBaseConfig;

    protected $error;
    /**
     * WMSService constructor.
     */
    public function __construct()
    {
        $this->imagePath = ROOT_PATH . 'public' . DS . 'uploads';
        $this->imageRule = ['ext' => 'jpg,png,gif', 'size' => '51568', 'type' => 'image/jpeg,image/png'];
        $this->_page = Config::get('pagination')['page'];
        $this->_limit = Config::get('pagination')['limit'];
        $this->_paypalBaseConfig = Config::get('paypal_config_base');
    }
    /*******************类方法*******************/


    ////////////////////////Product////////////////////////

    /**
     * 描述：通过ID获取产品详情
     * @param int $id 产品ID
     * @return array string|\think\Model
     */
    public function getProductById($id)
    {

        if (!Validate::is($id, 'number')) {
            return '请输入正确的参数';
        }

        $product = new ProductModel();
        $data = $product->getDataById($id);

        if (!$data) {
            return $product->getError();
        }
        $data = $data->append(['images']);
        return $data;
    }

    /**
     * 描述：获取产品列表信息
     * @date 2017年11月4日上午10:44:31
     * @param    string $keywords 搜索关键词
     * @param    integer $page 页序数
     * @param    integer $limit 每页数量
     * @return   string|array            错误信息|产品列表
     */
    public function getProductList($keywords = null, $page = null, $limit = null)
    {
        $keywords = Validate::is($keywords, 'chsAlphaNum') ? $keywords : '';
        $page = Validate::is($page, 'number') ? $page : $this->_page;
        $limit = Validate::is($limit, 'number') & $limit <= 1000 ? $limit : $this->_limit;

        $product = new ProductModel();
        $data = $product->getDataList($keywords, $page, $limit);

        if ($data === false) {
            return $product->getError();
        }
        return $data;
    }

    /**
     * 描述：创建新产品信息
     * @param Request $request
     * @return array|bool|string   true|错误信息
     */
    public function saveProduct(Request $request)
    {
        $param = $request->param();
        $product = new ProductModel();
        $files = $request->file('images');
        if (!empty($files)) {
            $upload = new Upload($files, $this->imageRule, $this->imagePath);
            $uploadResult = $upload->getFilesPath();
            if (empty($uploadResult)) {
                return $upload->getError();
            }
            $param['images'] = $uploadResult;
        }
        try {
            $data = $product->createData($param);
            if ($data !== true) {
                return $product->getError();
            }
            return $data;
        } catch (\Exception $e) {
            Log::write($e->getMessage(), 'error');
            return $e->getMessage();
        }

    }

    /**
     * 描述：通过ID更新产品详情
     * @date 2017年11月5日下午7:39:24
     * @param    array $param 产品信息数组
     * @param    integer $id 产品ID
     * @return   bool|string|array       true/错误信息
     */
    public function updateProductById($param, $id)
    {
        if (!Validate::is($id, 'number')) {
            return '请输入正确的参数';
        }
        $product = new ProductModel();
        try {
            $data = $product->updateDataById($param, $id, 'update');
            if ($data !== true) {
                return $product->getError();
            }
            return $data;
        } catch (\Exception $e) {
            Log::write($e->getMessage(), 'error');
            return $e->getMessage();
        }

    }

    /**
     * 描述：根据ID删除产品详细
     * @param integer $id 产品ID
     * @return array|bool|null|string|\think\Model
     */
    public function deleteProduct($id)
    {

        if (!Validate::is($id, 'number')) {
            return '请输入正确的参数';
        }

        $product = new ProductModel();
        try {
            $data = $product->delDataById($id);
            if ($data !== true) {
                return $product->getError();
            }
            return $data;
        } catch (\Exception $e) {
            Log::write($e->getMessage(), 'error');
            return $e->getMessage();
        }

    }

    /**
     * 描述：根据ID批量删除产品详细
     * @param $param
     * @return array|bool|string
     */
    public function deleteProductsByIds($param)
    {

        $product = new ProductModel();
        try {
            $data = $product->delDataCollection($param);
            if ($data !== true) {
                return $product->getError();
            }
            return $data;
        } catch (\Exception $e) {
            Log::write($e->getMessage(), 'error');
            return $e->getMessage();
        }

    }

    /**
     * 描述：在存在产品信息下保存图片
     * @date 2017年11月9日上午9:25:51
     * @param    Request $request 请求信息
     * @param    integer $id 产品ID
     * @return   bool|string|array       true/错误信息
     */
    public function saveImageByProductId(Request $request, $id)
    {
        $product = new ProductModel();

        if (!Validate::is($id, 'number')) {
            return '请输入正确的参数';
        }

        $product = $product->getDataById($id);
        if (!$product) {
            return $product->getError();
        }


        $files = $request->file('images');
        $rule = $this->imageRule;
        $path = $this->imagePath;

        $upload = new Upload($files, $rule, $path);
        $uploadResult = $upload->getFilesPath();
        if (empty($uploadResult)) {
            return $upload->getError();
        }

        try {
            $product->images()->saveAll($uploadResult);
            return true;
        } catch (Exception $e) {
            Log::write($e->getMessage(), 'error');
            return '添加图片失败: ' . $e->getMessage();
        }

    }

    /**
     * 描述：通过产品ID删除产品对应的图片
     * @param $id
     * @return array|bool|null|string|\think\Model
     */
    public function deleteImageByProductId($id)
    {
        if (!Validate::is($id, 'number')) {
            return '请输入正确的参数';
        }

        $product = new ProductModel();
        try {
            $data = $product->delImagesById($id);
            if ($data !== true) {
                return $product->getError();
            }
            return $data;
        } catch (\Exception $e) {
            Log::write($e->getMessage(), 'error');
            return $e->getMessage();
        }
    }

    ////////////////////////Supplier////////////////////////

    /**
     * 描述：通过ID获取供应商详情
     * @param $id
     * @return $this|array|string 布尔值或者供应商详情数组
     */
    public function getSupplierById($id)
    {
        if (!Validate::is($id, 'number')) {
            return '请输入正确的参数';
        }
        $supplier = new SupplierModel();
        $data = $supplier->getDataById($id);

        if (!$data) {
            return $supplier->getError();
        }

        $data['products'] = $data->products;
        $data->hidden(['products.update_time', 'products.create_time', 'products.id']);
        return $data;
    }

    /**
     * 描述：获取供应商列表信息
     * @param    string $keywords 搜索关键词
     * @param    integer $page 页序数
     * @param    integer $limit 每页数量
     * @return array|bool|string
     */
    public function getSupplierList($keywords, $page, $limit)
    {
        $supplier = new SupplierModel();

        $keywords = Validate::is($keywords, 'chsAlphaNum') ? $keywords : '';
        $page = Validate::is($page, 'number') ? $page : $this->_page;
        $limit = Validate::is($limit, 'number') & $limit <= 1000 ? $limit : $this->_limit;

        $data = $supplier->getDataList($keywords, $page, $limit);

        if ($data === false) {
            return $supplier->getError();
        }
        return $data;
    }

    /**
     * 描述：创建新的供应商
     * @date 2017年11月17日上午11:37:14
     * @param    array $param 产品信息数组
     * @return   bool|string|array       true/错误信息
     */
    public function saveSupplier($param)
    {
        $supplier = new SupplierModel();

        try {
            $data = $supplier->createData($param);
            if ($data !== true) {
                return $supplier->getError();
            }
            return $data;
        } catch (\Exception $e) {
            Log::write($e->getMessage(), 'error');
            return $e->getMessage();
        }

    }

    /**
     * 描述：根据ID删除供应商详细
     * @param    integer $id 产品ID
     * @return   bool/array             true或者错误信息
     * @return array|bool|string
     */
    public function deleteSupplier($id)
    {

        if (!Validate::is($id, 'number')) {
            return '请输入正确的参数';
        }
        $supplier = new SupplierModel();

        try {
            $data = $supplier->delDataById($id);
            if ($data !== true) {
                return $supplier->getError();
            }
            return $data;
        } catch (\Exception $e) {
            Log::write($e->getMessage(), 'error');
            return $e->getMessage();
        }

    }

    /**
     * 描述：通过ID更新供应商详情
     * @param    array $param 供应商信息数组
     * @param    integer $id 供应商ID
     * @return array|bool|null|string|\think\Model
     */
    public function updateSupplierById($param, $id)
    {
        if (!Validate::is($id, 'number')) {
            return '请输入正确的参数';
        }
        $supplier = new SupplierModel();
        try {
            $data = $supplier->updateDataById($param, $id);
            if ($data !== true) {
                return $supplier->getError();
            }
            return $data;
        } catch (\Exception $e) {
            Log::write($e->getMessage(), 'error');
            return $e->getMessage();
        }

    }

    /**
     * 描述：根据ID批量删除供应商详细
     * @param $param
     * @return array|bool|string
     */
    public function deleteSuppliersByIds($param)
    {

        if (empty($param)) {
            return '请输入正确的参数';
        }

        $supplier = new SupplierModel();
        try {
            $data = $supplier->delDataCollection($param['ids']);
            if ($data !== true) {
                return $supplier->getError();
            }
            return $data;
        } catch (\Exception $e) {
            return $e->getMessage();
        }


    }

    ////////////////////////Purchase////////////////////////
    public function getPurchaseById($id)
    {
        if (!Validate::is($id, 'number')) {
            return '请输入正确的参数';
        }

        $purchase = new PurchaseModel();
        $data = $purchase->getDataById($id);

        if (!$data) {
            return $purchase->getError();
        }
        $data->contents;
        $data->hidden(['contents.update_time',
            'contents.create_time',
            'contents.pid',
            'contents.supplier_id',
            'contents.product_id',
        ]);
        return $data;
    }

    public function getPurchaseList($keywords, $page, $limit)
    {
        $purchase = new PurchaseModel();

        $keywords = Validate::is($keywords, 'chsAlphaNum') ? $keywords : '';
        $page = Validate::is($page, 'number') ? $page : $this->_page;
        $limit = Validate::is($limit, 'number') & $limit <= 1000 ? $limit : $this->_limit;

        $data = $purchase->getDataList($keywords, $page, $limit);

        if ($data === false) {
            return $purchase->getError();
        }
        return $data;

    }

    public function savePurchase($param)
    {

        $content = [];
        if (
            empty($param['product_id']) &
            empty($param['supplier_id'])
        ) {
            $content = [];
        } elseif (
            empty($param['product_id']) ||
            empty($param['supplier_id'])
        ) {
            return '参数有误，请检查';
        } elseif (
            count($param['product_id']) != count($param['supplier_id'])
        ) {
            return '参数不全，请检查';
        } else {
            for ($i = 0; $i < count($param['product_id']); $i++) {

                $content[] = ['product_id' => $param['product_id'][$i],
                    'supplier_id' => $param['supplier_id'][$i]
                ];
            }
        }
        $param['content'] = $content;
        $purchase = new PurchaseModel();
        try {
            $data = $purchase->createData($param);
            if ($data !== true) {
                return $purchase->getError();
            }
            return $data;
        } catch (\Expection $e) {
            Log::write($e->getMessage(), 'error');
            return $e->getMessage();
        }
    }

    public function deletePurchase($id)
    {
        if (!Validate::is($id, 'number')) {
            return '请输入正确的参数';
        }
        $purchase = new PurchaseModel();

        try {
            $data = $purchase->delDataById($id);
            if ($data !== true) {
                return $purchase->getError();
            }
            return $data;
        } catch (\Exception $e) {
            Log::write($e->getMessage(), 'error');
            return $e->getMessage();
        }
    }

    public function updatePurchaseById($param, $id)
    {
        if (!Validate::is($id, 'number')) {
            return '请输入正确的参数';
        }
        $content = [];
        if (
            empty($param['product_id']) &
            empty($param['supplier_id'])
        ) {
            $content = [];
        } elseif (
            empty($param['product_id']) ||
            empty($param['supplier_id'])
        ) {
            return '参数有误，请检查';
        } elseif (
            count($param['product_id']) != count($param['supplier_id'])
        ) {
            return '参数不全，请检查';
        } else {
            for ($i = 0; $i < count($param['product_id']); $i++) {

                $content[] = ['product_id' => $param['product_id'][$i],
                    'supplier_id' => $param['supplier_id'][$i]
                ];
            }
        }
        $param['content'] = $content;
        $purchase = new PurchaseModel();

        try {
            $data = $purchase->updateDataById($param, $id);
            if ($data !== true) {
                return $purchase->getError();
            }
            return $data;

        } catch (\Expection $e) {
            Log::write($e->getMessage(), 'error');
            return $e->getMessage();
        }
    }

    public function deletePurchaseByIds($param)
    {
        if (empty($param)) {
            return '请输入正确的参数';
        }
        $purchase = new PurchaseModel();
        try {
            $data = $purchase->delDataCollection($param);
            if ($data !== true) {
                return $purchase->getError();
            }
            return $data;
        } catch (\Expection $e) {
            return $e->getMessage();
        }
    }

    ////////////////////////Order////////////////////////
    public function getOrderById($id)
    {
        ;
    }

    public function getOrderList($param)
    {
        ;
    }

    public function saveOrder($param)
    {
        switch ($param['case']) {
            case 'paypal':
                $accountConfig = 'paypal_config_vson_mail';
                $transIds = $this->getPaypalTransSearch('2017-12-20T00:00:00+0100', '2017-12-21T00:00:00+0100', $accountConfig);
                break;
        }
    }

    public function deleteOrder($param)
    {
        ;
    }

    public function syncOrder($param)
    {

        switch ($param['case']) {
            case 'paypal':
                $progress = $this->paypalSync($param['startDate'], $param['endDate'], $param['account']);
                return $progress;
                break;
        }
    }
    /**
     * 描述：
     * @param string $accountConfig
     * @return array
     */
    protected function getPaypalConfig($accountConfig = '')
    {
        return array_merge($this->_paypalBaseConfig, Config::get($accountConfig));
    }

    /**
     * 描述：
     * @param $transId
     * @param $accountConfig
     * @return \PayPal\PayPalAPI\GetTransactionDetailsResponseType|string
     * @throws \Exception
     */
    protected function getPaypalTransDetails($transId, $accountConfig)
    {
        $transactionDetails = new GetTransactionDetailsRequestType();
        $transactionDetails->TransactionID = $transId;

        $request = new GetTransactionDetailsReq();
        $request->GetTransactionDetailsRequest = $transactionDetails;

        $paypalService = new PayPalAPIInterfaceServiceService($this->getPaypalConfig($accountConfig));

        try {
            $transDetailsResponse = $paypalService->GetTransactionDetails($request);
            return $transDetailsResponse;
        } catch (\Expection $e) {
            return $e->getMessage();
        }
    }


    /**
     * 描述：  获取Paypal交易ID集合
     * @param $startDate
     * @param $endDate
     * @param $accountConfig
     * @return array|string
     * @throws \Exception
     */
    protected function getPaypalTransSearch($startDate, $endDate, $accountConfig)
    {
        $transactionSearchRequest = new TransactionSearchRequestType();
        $transactionSearchRequest->StartDate = $startDate;//'2017-12-20T00:00:00+0100';
        $transactionSearchRequest->EndDate = $endDate;//'2017-12-21T00:00:00+0100';

        $tranSearchReq = new TransactionSearchReq();
        $tranSearchReq->TransactionSearchRequest = $transactionSearchRequest;

        $paypalService = new PayPalAPIInterfaceServiceService($this->getPaypalConfig($accountConfig));

        try {
            $transactionSearchResponse = $paypalService->TransactionSearch($tranSearchReq);
            $items = [];
            foreach ($transactionSearchResponse->PaymentTransactions as $transaction) {
                $items[] = $transaction->TransactionID;
            }

            return $items;
        } catch (\Expection $e) {
            return $e->getMessage();
            Log::write($e->getMessage(), 'error');
        }
    }


    /**
     * 描述：
     * @return string
     * @throws \Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sync2()
    {

        $transResult=$this->getPaypalTransDetails('0U1708071H170820F','paypal_config_vson_mail')->PaymentTransactionDetails;

        $order = new  OrderModel();
        $order->platform = 1;
        $order->internal_transaction_id = $order->count()==0?date("Ymd").'0001':($order->max('internal_transaction_id')+1);

        $orderPaypal= new OrderPaypalModel();

        $orderPaypal->receiver = $transResult->ReceiverInfo->Receiver;
        $orderPaypal->payer_id = $transResult->PayerInfo->PayerID;
        $orderPaypal->payer = $transResult->PayerInfo->Payer;
        $orderPaypal->payer_firstname = $transResult->PayerInfo->PayerName->FirstName;
        $orderPaypal->payer_middlename = $transResult->PayerInfo->PayerName->MiddleName;
        $orderPaypal->payer_lastname = $transResult->PayerInfo->PayerName->LastName;
        $orderPaypal->payer_business = $transResult->PayerInfo->PayerID;
        $orderPaypal->payer_address_owner = $transResult->PayerInfo->Address->AddressOwner;
        $orderPaypal->payer_address_status = $transResult->PayerInfo->PayerStatus;
        $orderPaypal->payer_address_name = $transResult->PayerInfo->Address->Name;
        $orderPaypal->payer_address_street1 = $transResult->PayerInfo->Address->Street1;
        $orderPaypal->payer_address_street2 = $transResult->PayerInfo->Address->Street2;//dump($transResult->PayerInfo->Address);die();
        $orderPaypal->payer_city_name = $transResult->PayerInfo->Address->CityName;
        $orderPaypal->payer_state_or_province = $transResult->PayerInfo->Address->StateOrProvince;
        $orderPaypal->payer_postal_code = $transResult->PayerInfo->Address->PostalCode;
        $orderPaypal->payer_country = $transResult->PayerInfo->Address->Country;
        $orderPaypal->payer_country_name = $transResult->PayerInfo->Address->CountryName;
        $orderPaypal->payer_phone = $transResult->PayerInfo->Address->Phone;
        $orderPaypal->transaction_id = $transResult->PaymentInfo->TransactionID;
        $orderPaypal->ebay_transaction_id = $transResult->PaymentInfo->EbayTransactionID;
        $orderPaypal->parent_transaction_id= $transResult->PayerInfo->PayerID;
        $orderPaypal->payment_type = $transResult->PaymentInfo->PaymentType;
        $orderPaypal->payment_date = $transResult->PaymentInfo->PaymentDate;
        $orderPaypal->currency_code = $transResult->PaymentInfo->GrossAmount->currencyID;
        $orderPaypal->gross_amount = $transResult->PaymentInfo->GrossAmount->value;
        $orderPaypal->fee_amount = $transResult->PaymentInfo->FeeAmount->value;
        $orderPaypal->settle_amount = $transResult->PaymentInfo->SettleAmount->value;
        $orderPaypal->tax_amount = $transResult->PaymentInfo->TaxAmount->value;
        $orderPaypal->exchange_rate = $transResult->PaymentInfo->ExchangeRate;
        $orderPaypal->payment_status = $transResult->PaymentInfo->PaymentStatus;
        $orderPaypal->pending_reason = $transResult->PaymentInfo->PendingReason;
        $orderPaypal->invoice_id = $transResult->PaymentItemInfo->InvoiceID;
        $orderPaypal->memo = $transResult->PaymentItemInfo->Memo;
        $orderPaypal->sales_tax = $transResult->PaymentItemInfo->SalesTax;
        $orderPaypal->payer_status = $transResult->PayerInfo->PayerStatus;
        $orderPaypal->subject = $transResult->PaymentInfo->Subject;
        $orderPaypal->buyer_id = $transResult->PaymentItemInfo->Auction->BuyerID;




        $items= [];
        if (!empty($transResult->PaymentItemInfo->PaymentItem)) {

            foreach ( $transResult->PaymentItemInfo->PaymentItem as $item ) {
                $paymentItem = new OrderPaypalItemModel();
                $paymentItem->ebay_item_txn_id = $item->EbayItemTxnId;
                $paymentItem->item_name = $item->Name;
                $paymentItem->item_number= $item->Number;
                $paymentItem->item_quantity= $item->Quantity;
                $paymentItem->item_amount= is_null($item->Amount)?null:$item->Amount->value;
                $items[] = $paymentItem;
            }
        }

        try {
            $order->paypal=$orderPaypal;
            $order->together('paypal')->save();
            if (!empty($items)) {
                $orderId = $order->getLastInsID();
                $orderPaypal=$order->find($orderId)->paypal;
                $orderPaypal->items()->saveAll($items);
            }

        } catch (\Expection $e) {
            return $e->getMessage();
            Log::write($e->getMessage(), 'error');
        }


    }
    protected function paypalSync($startDate,$endDate,$account)
    {
        //http://localhost:8888/api/public/v1/wms/orders/sync?timed=1514348292&startDate=2017-12-20T00%3A00%3A00%2B0100&endDate=2017-12-21T00%3A00%3A00%2B0100&account=paypal_config_vson_mail&case=paypal
        $transIds = Session::get('trans_ids', 'sync');
        if (empty($transIds)) {
            //sleep(6);
//            $transArray = [
//                '9G356899PY8893312',
//                '88R31562230229506',
//                '5X514845MB447372M',
//                '85U45239KY935590H',
//                '8KU17751863042457',
//                '0S256976PS410634N',
//                '6D165084W0069710T',
//                '6GY347953G255323T',
//                '0U1708071H170820F',
//                '0AX79827RK7016431',
//                '4M333090PG496345H'
//            ];
            $transResult = $this->getPaypalTransSearch($startDate, $endDate, $account);
            if (is_string($transResult)) {
              return $transResult;
            }
            Session::set('trans_ids', $transResult, 'sync');
            $transIds = Session::get('trans_ids', 'sync');
            Session::set('total', count($transIds), 'sync');
            $current = 0;
        }else{
            //dump($transIds);die();
            $transResult=$this->getPaypalTransDetails($transIds[0],$account)->PaymentTransactionDetails;
            if (is_string($transResult)) {
                return $transResult;
            }
            $order = new  OrderModel();
            $order->platform = 1;
            $order->internal_transaction_id = $order->count()==0?date("Ymd").'0001':($order->max('internal_transaction_id')+1);

            $orderPaypal= new OrderPaypalModel();
            $orderPaypal->receiver = $transResult->ReceiverInfo->Receiver;
            $orderPaypal->payer_id = $transResult->PayerInfo->PayerID;
            $orderPaypal->payer = $transResult->PayerInfo->Payer;
            $orderPaypal->payer_firstname = $transResult->PayerInfo->PayerName->FirstName;
            $orderPaypal->payer_middlename = $transResult->PayerInfo->PayerName->MiddleName;
            $orderPaypal->payer_lastname = $transResult->PayerInfo->PayerName->LastName;
            $orderPaypal->payer_business = $transResult->PayerInfo->PayerID;
            $orderPaypal->payer_address_owner = $transResult->PayerInfo->Address->AddressOwner;
            $orderPaypal->payer_address_status = $transResult->PayerInfo->PayerStatus;
            $orderPaypal->payer_address_name = $transResult->PayerInfo->Address->Name;
            $orderPaypal->payer_address_street1 = $transResult->PayerInfo->Address->Street1;
            $orderPaypal->payer_address_street2 = $transResult->PayerInfo->Address->Street2;//dump($transResult->PayerInfo->Address);die();
            $orderPaypal->payer_city_name = $transResult->PayerInfo->Address->CityName;
            $orderPaypal->payer_state_or_province = $transResult->PayerInfo->Address->StateOrProvince;
            $orderPaypal->payer_postal_code = $transResult->PayerInfo->Address->PostalCode;
            $orderPaypal->payer_country = $transResult->PayerInfo->Address->Country;
            $orderPaypal->payer_country_name = $transResult->PayerInfo->Address->CountryName;
            $orderPaypal->payer_phone = $transResult->PayerInfo->Address->Phone;
            $orderPaypal->transaction_id = $transResult->PaymentInfo->TransactionID;
            $orderPaypal->ebay_transaction_id = $transResult->PaymentInfo->EbayTransactionID;
            $orderPaypal->parent_transaction_id= $transResult->PayerInfo->PayerID;
            $orderPaypal->payment_type = $transResult->PaymentInfo->PaymentType;
            $orderPaypal->payment_date = $transResult->PaymentInfo->PaymentDate;
            $orderPaypal->currency_code = $transResult->PaymentInfo->GrossAmount->currencyID;
            $orderPaypal->gross_amount = is_null($transResult->PaymentInfo->GrossAmount)?null:$transResult->PaymentInfo->GrossAmount->value;
            $orderPaypal->fee_amount = is_null($transResult->PaymentInfo->FeeAmount)?null:$transResult->PaymentInfo->FeeAmount->value;
            $orderPaypal->settle_amount = is_null($transResult->PaymentInfo->SettleAmount)?null:$transResult->PaymentInfo->SettleAmount->value;
            $orderPaypal->tax_amount = is_null($transResult->PaymentInfo->TaxAmount)?null:$transResult->PaymentInfo->TaxAmount->value;
            $orderPaypal->exchange_rate = is_null($transResult->PaymentInfo->ExchangeRate)?null:$transResult->PaymentInfo->ExchangeRate;
            $orderPaypal->payment_status = is_null($transResult->PaymentInfo->PaymentStatus)?null:$transResult->PaymentInfo->PaymentStatus;
            $orderPaypal->pending_reason = is_null($transResult->PaymentInfo->PendingReason)?null:$transResult->PaymentInfo->PendingReason;
            $orderPaypal->invoice_id = is_null($transResult->PaymentItemInfo->InvoiceID)?null:$transResult->PaymentItemInfo->InvoiceID;
            $orderPaypal->memo = is_null($transResult->PaymentItemInfo->Memo)?null:$transResult->PaymentItemInfo->Memo;
            $orderPaypal->sales_tax = is_null($transResult->PaymentItemInfo->SalesTax)?null:$transResult->PaymentItemInfo->SalesTax;
            $orderPaypal->payer_status = is_null($transResult->PayerInfo->PayerStatus)?null:$transResult->PayerInfo->PayerStatus;
            $orderPaypal->subject = is_null($transResult->PaymentInfo->Subject)?null:$transResult->PaymentInfo->Subject;
            $orderPaypal->buyer_id = is_null($transResult->PaymentItemInfo->Auction)?null:$transResult->PaymentItemInfo->Auction->BuyerID;

            $items= [];
            if (!empty($transResult->PaymentItemInfo->PaymentItem)) {

                foreach ( $transResult->PaymentItemInfo->PaymentItem as $item ) {
                    $paymentItem = new OrderPaypalItemModel();
                    $paymentItem->ebay_item_txn_id = $item->EbayItemTxnId;
                    $paymentItem->item_name = $item->Name;
                    $paymentItem->item_number= $item->Number;
                    $paymentItem->item_quantity= $item->Quantity;
                    $paymentItem->item_amount= is_null($item->Amount)?null:$item->Amount->value;
                    $items[] = $paymentItem;
                }
            }


            try {
                $order->paypal=$orderPaypal;
                $order->together('paypal')->save();
                if (!empty($items)) {
                    $orderId = $order->getLastInsID();
                    $orderPaypal=$order->find($orderId)->paypal;
                    $orderPaypal->items()->saveAll($items);
                }

            } catch (\Expection $e) {
                return $e->getMessage();
                Log::write($e->getTraceAsString(), 'error');
            }
            unset($transIds[0]);
            $transIds = array_merge($transIds);
            Session::set('trans_ids', array_merge($transIds), 'sync');
            $transIds = Session::get('trans_ids', 'sync');
            $total = Session::get('total', 'sync');
            $current = ceil((($total - count($transIds)) / $total) * 100);
        }
        return $current;
    }

}