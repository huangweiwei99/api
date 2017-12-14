<?php
namespace app\common\service;

use app\common\service\BaseService;
use app\wms\model\Product as ProductModel;
use app\wms\model\Supplier as SupplierModel;
use think\Validate;
use think\Request;
use com\Upload;
/**
* PHP类注释
* 描述：WMSService类包含了wms所有详细的操作
* @date 2017年11月4日上午11:21:14
* @container app\common\service
* @param unknowtype 
* @return return_type 
*/
class WMSService extends BaseService
{
    /*******************类属性*******************/
    protected  $_page   = 1;  
    protected  $_limit  = 10;

    protected $imageRule = ['ext'=>'jpg,png,gif','size'=>'51568','type'=>'image/jpeg,image/png'];
    protected $imagePath = ROOT_PATH . 'public' . DS . 'uploads';
    /*******************类方法*******************/

    
    ////////////////////////Product////////////////////////
    /**
    * 描述：通过ID获取产品详情
    * @date 2017年11月5日上午9:35:56
    * @param    int         $id          产品ID
    * @return   bool/array               布尔值或者产品详情数组
    */
    public function getProductById($id) {
        if (!Validate::is($id,'number') ) {
            return '请输入正确的参数';
        }
        
        $product = new ProductModel();
        $data = $product->getDataById($id);
        if(!$data){
            return ['error' => $product->getError()];
        }
        $data['images']=$data->images;
        return $data;
    }
    
    /**
    * 描述：获取产品列表信息
    * @date 2017年11月4日上午10:44:31
    * @param    string    $keywords     搜索关键词
    * @param    integer   $page         页序数
    * @param    integer   $limit        每页数量
    * @return   string|array            错误信息|产品列表
    */
    public function getProductList($keywords=null, $page=null, $limit=null) {
        $product= new ProductModel();
        
        $keywords = Validate::is($keywords, 'chsAlphaNum') ? $keywords: '';
        $page = Validate::is($page, 'number')? $page: $this->_page;
        $limit = Validate::is($limit, 'number') & $limit<=1000? $limit: $this->_limit;
       
        $data= $product->getDataList($keywords, $page, $limit); 
       
        if ($data===false) {
            return $product->getError();
        }
        return $data;
    }
    
    /**
    * 描述：创建新产品信息
    * @date 2017年11月5日下午6:25:15
    * @param    array       $param      产品信息数组
    * @return   bool|string|array       true/错误信息   
    */
    public function saveProduct(Request $request) {
        $param =$request->param();
        $files = $request->file('images');
        
        $product = new ProductModel();
        
        $upload = new Upload($files, $this->imageRule, $this->imagePath);
        
        $uploadResult= $upload->getFilesPath();
        
        if(empty($uploadResult)){
            return $upload->getError();
        }
        
        $param['images'] = $uploadResult;
        $data = $product->createData($param);
        if ($data!==true) {
            return $product->getError();
        }
        return $data;
    }
    
    /**
    * 描述：通过ID更新产品详情
    * @date 2017年11月5日下午7:39:24
    * @param    array       $param      产品信息数组
    * @param    integer     $id         产品ID
    * @return   bool|string|array       true/错误信息
    */
    public function updateProductById($param, $id) {
        if (!Validate::is($id,'number') ) {
            return '请输入正确的参数';
        }
        $product = new ProductModel();
        $data = $product->updateDataById($param, $id, 'update');
        if ($data!==true) {
            return $product->getError();
        }
        return $data;
    }
    
    /**
    * 描述：根据ID删除产品详细
    * @date 2017年11月5日下午3:00:31
    * @param    integer     $id        产品ID
    * @return   bool/array             true或者错误信息
    */
    public function deleteProduct($id) {
        
        if (!Validate::is($id,'number') ) {
            return '请输入正确的参数';
        }
        
        $product = new ProductModel();
        $data = $product->delDataById($id);
        if($data!==true){
            return  $product->getError();
        }
        return $data;
    }
    
    /**
    * 描述：根据ID批量删除产品详细
    * @date 2017年11月6日上午10:41:49
    * @param    array      $ids     产品ID数组
    * @return   bool                布尔值
    */
    public function deleteProductsByIds($param) {
        $product = new ProductModel();
        $data =$product->delDatas($param);
        if($data!==true){
            return  $product->getError();
        }
        return $data;
    }
    
    /**
    * 描述：在存在产品信息下保存图片
    * @date 2017年11月9日上午9:25:51
    * @param    Request     $request    请求信息
    * @param    integer     $id         产品ID
    * @return   bool|string|array       true/错误信息
    */
    public function saveImageByProductId(Request $request, $id) {
        $product = new ProductModel();
        
        if (!Validate::is($id,'number') ) {
            return '请输入正确的参数';
        }
        
        $product= $product->getDataById($id);
        if(!$product){
            return  $product->getError();
        }
        
        
        $files = $request->file('images');
        $rule  = $this->imageRule;
        $path  = $this->imagePath;
      
        $upload = new Upload($files, $rule, $path);
        $uploadResult= $upload->getFilesPath();
        if(empty($uploadResult)){
            return $upload->getError();
        }
      
        try {
            $product->images()->saveAll($uploadResult);
            return true;
        } catch(\Exception $e) {
            return '添加图片失败: '.$e->getMessage();
        }
       
    }
    
    
    ////////////////////////Supplier////////////////////////
    /**
    * 描述：通过ID获取供应商详情
    * @date 2017年11月17日下午4:53:56
    * @param    int         $id          产品ID
    * @return   bool/array               布尔值或者供应商详情数组
    */
    public function getSupplierById($id) {
        if (!Validate::is($id, 'number')) {
            return '请输入正确的参数';
        }
        $supplier = new SupplierModel();
        $data = $supplier->getDataById($id)->hidden(['products'=>['pivot']]);
        if(!$data){
            return ['error' => $supplier->getError()];
        }
        $data['products']=$data->products;
        return $data;
    }

    /**
     * 描述：获取供应商列表信息
     * @date 2017年11月4日上午10:44:31
     * @param    string    $keywords     搜索关键词
     * @param    integer   $page         页序数
     * @param    integer   $limit        每页数量
     * @return   string|array            错误信息|供应商列表
     */
    public function getSupplierList($keywords,$page,$limit) {
        $supplier =new SupplierModel();
        
        $keywords = Validate::is($keywords, 'chsAlphaNum') ? $keywords: '';
        $page = Validate::is($page, 'number')? $page: $this->_page;
        $limit = Validate::is($limit, 'number') & $limit<=1000? $limit: $this->_limit;
        
        $data= $supplier->getDataList($keywords, $page, $limit);
        
        if ($data===false) {
            return $supplier->getError();
        }
        return $data;
    }
    
    /**
    * 描述：创建新的供应商
    * @date 2017年11月17日上午11:37:14
    * @param    array       $param      产品信息数组
    * @return   bool|string|array       true/错误信息   
    */
    public function saveSupplier($param) {
        $supplier = new SupplierModel();
        
        $data = $supplier->createData($param);
        if($data !== true) {
            return $supplier->getError();
        }
        return $data;
    }
    
    /**
    * 描述：根据ID删除供应商详细
    * @date 2017年11月20日上午10:06:39
    * @param    integer     $id        产品ID
    * @return   bool/array             true或者错误信息 
    */
    public function deleteSupplier($id) {
        
        if (!Validate::is($id, 'number')) {
               return '请输入正确的参数';
        }
        $supplier =new SupplierModel();
        
        $data = $supplier->delDataById($id);
        if($data!==true){
            return  $supplier->getError();
        }
        return $data;
    }
    
    /**
    * 描述：通过ID更新供应商详情
    * @date 2017年11月5日下午7:39:24
    * @param    array       $param      供应商信息数组
    * @param    integer     $id         供应商ID
    * @return   bool                    true|false
    */
    public function updateSupplierById($param, $id) {
        if (!Validate::is($id, 'number')) {
            return '请输入正确的参数';
        }
        $supplier =new SupplierModel();
        $data = $supplier->updateDataById($param,$id);
        if($data!==true){
            return  $supplier->getError();
        }
        return $data;
   }
   
   /**
    * 描述：根据ID批量删除供应商详细
    * @date 2017年11月6日上午10:41:49
    * @param    array      $ids     供应商ID数组
    * @return   bool                布尔值
    */
   public function deleteSuppliersByIds($param) {
       
       if (empty($param['ids'])) {
           return '请输入正确的参数';
       }
       
        $supplier = new SupplierModel();
        $data = $supplier->delDatas($param['ids']);
        
        if($data !== true){
            return  $supplier->getError();
        }
        return $data;
        
    }
    ////////////////////////Purchase////////////////////////
    public function getPurchaseById($id) {
        ;
    }
    
    public function getPurchaseList($param) {
        ;
    }
    
    public function savePurchase($param) {
        ;
    }
    
    public function deletePurchase($param) {
        ;
    }
    
    
    ////////////////////////Order////////////////////////
    public function getOrderById($id) {
        ;
    }
    
    public function getOrderList($param) {
        ;
    }
    
    public function saveOrder($param) {
        ;
    }
    
    public function deleteOrder($param) {
        ;
    }
}