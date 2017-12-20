<?php

namespace app\common\service;

use app\wms\model\Product as ProductModel;
use app\wms\model\Supplier as SupplierModel;
use app\wms\model\Purchase as PurchaseModel;
use think\exception\PDOException;
use think\Log;
use think\Validate;
use think\Request;
use think\Config;
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

    /**
     * WMSService constructor.
     */
    public function __construct()
    {
        $this->imagePath = ROOT_PATH . 'public' . DS . 'uploads';
        $this->imageRule = ['ext' => 'jpg,png,gif', 'size' => '51568', 'type' => 'image/jpeg,image/png'];
        $this->_page = Config::get('pagination')['page'];
        $this->_limit = Config::get('pagination')['limit'];;
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
        ;
    }

    public function deleteOrder($param)
    {
        ;
    }

}