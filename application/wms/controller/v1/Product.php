<?php

namespace app\wms\controller\v1;

use think\Request;
use app\common\controller\Api as ApiController;
use app\wms\model\Product as ProductModel;

class Product extends ApiController
{
    /**
     * 显示资源列表
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
     * 描述：保存新建的资源
     * @return array
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

    public function sync()
    {
        
    }

    /**
     * 描述：显示指定的资源
     * @return array
     */
    public function read()
    {
        $param = $this->param;
        $data = $this->wmsService()->getProductById($param['id']);
        if (is_string($data)) {
           return resultArray(['error' => $data]);
        }
        return resultArray(['data' => $data]);
    }


    /**
     * 描述：保存更新的资源
     * @return array
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
     * 描述：删除指定资源
     * @return array
     */

    public function delete()
    {
        $param = $this->param;
        $data = $this->wmsService()->deleteProduct($param['id']);
        if ($data!==true) {
            return resultArray(['error' => $data]);
        }
        return resultArray(['data' => '删除成功']); 
    }


    /**
     * 描述：批量删除指定资源
     * @return array
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

    /**
     * 描述：保存产品对应的图片
     * @return array
     */
    public  function saveImages() {
        $request = Request::instance();
        $param = $this->param;
        $data = $this->wmsService()->saveImageByProductId($request,$param['id']);
        if($data!==true){
            return resultArray(['error' => $data]);
        }
        return resultArray(['data' => '添加图片成功']);
    }

    public function deleteImages()
    {
        //dump('sadada');die();
        $param = $this->param;
        $data = $this->wmsService()->deleteImageByProductId($param['id']);
        if($data!==true){
            return resultArray(['error' => $data]);
        }
        return resultArray(['data' => '删除图片成功']);
    }

}
