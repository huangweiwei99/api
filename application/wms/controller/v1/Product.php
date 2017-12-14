<?php

namespace app\wms\controller\v1;

use think\Controller;
use think\Request;
use app\common\controller\Api as ApiController;
use app\wms\model\Product as ProductModel;

class Product extends ApiController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {//echo THINK_VERSION;
        $param = $this->param;
        $keywords = isset( $param['keywords']) ? $param['keywords'] : null;
        $page = isset( $param['page'])  ?  $param['page']: null;
        $limit = isset( $param['limit']) ?  $param['limit']: null;
        
        $data = $this->wmsService()->getProductList($keywords,$page,$limit);
        if (!is_array($data)) {
            return resultArray(['error' => $data]);
        }
        return resultArray(['data' => $data]);
        
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save()
    {
        $request = Request::instance();
        $data = $this->wmsService()->saveProduct($request);
        if($data!==true){
            return resultArray(['error' => $data]);
        }
        return resultArray(['data' => '添加成功']);
    }
    
    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read()
    {
        $param = $this->param;
        $product =  new ProductModel();
        $data = $this->wmsService()->getProductById($param['id']);
        if (!$data) {
            return resultArray(['error' => $product->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update()
    {
        $param = $this->param;
        $request = Request::instance();
        $data = $this->wmsService()->updateProductById($param, $param['id']);
        if($data!==true){
            return resultArray(['error' => $data]);
        }
        return resultArray(['data' => '编辑成功']);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete()
    {
        $param = $this->param;
        $product =  new ProductModel();
        $data = $this->wmsService()->deleteProduct($param['id']);
        if ($data!==true) {
            return resultArray(['error' => $data]);
        }
        return resultArray(['data' => '删除成功']); 
    }
    
    /**
     * 批量删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function deletes() {
        $param = $this->param;
        $product = new ProductModel();
        $data = $this->wmsService()->deleteProductsByIds($param['ids']);
        if ($data!==true) {
            return resultArray(['error' => $data]);
        }
        return resultArray(['data' => '批量删除成功']);
    }
    
    public  function saveImages() {
        $request = Request::instance();
        $param = $this->param;
        $data = $this->wmsService()->saveImageByProductId($request,$param['id']);
        if($data!==true){
            return resultArray(['error' => $data]);
        }
        return resultArray(['data' => '添加图片成功']);
    }
}
