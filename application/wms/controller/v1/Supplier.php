<?php

namespace app\wms\controller\v1;

use think\Controller;
use think\Request;
use app\common\controller\Api as ApiController;
use app\wms\model\Supplier as SupplierModel;

class Supplier extends ApiController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $param = $this->param;
        $keywords = isset( $param['keywords']) ? $param['keywords'] : null;
        $page = isset( $param['page'])  ?  $param['page']: null;
        $limit = isset( $param['limit']) ?  $param['limit']: null;
       
        $data = $this->wmsService()->getSupplierList($keywords,$page,$limit);
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
        $param = $this->param;
        $data = $this->wmsService()->saveSupplier($param);
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
        $supplier =  new SupplierModel();
        $data = $this->wmsService()->getSupplierById($param['id']);
        if (!$data) {
            return resultArray(['error' => $supplier->getError()]);
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
        $supplier = new SupplierModel();
        
        $data = $this->wmsService()->updateSupplierById($param, $param['id']);
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
       $supplier = new SupplierModel();
       $data = $this->wmsService()->deleteSupplier($param['id']);
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
        $supplier = new SupplierModel();
        $data = $this->wmsService()->deleteSuppliersByIds($param);
        if ($data!==true) {
            return resultArray(['error' => $data]);
        }
        return resultArray(['data' => '批量删除成功']);
    }
}
