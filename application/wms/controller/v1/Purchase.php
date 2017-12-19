<?php

namespace app\wms\controller\v1;

use app\common\controller\Api as ApiController;

class Purchase extends ApiController
{
    /**
     * 显示资源列表
     * @return \think\Response
     */
    public function index()
    {
        $param = $this->param;
        $keywords = isset( $param['keywords']) ? $param['keywords'] : null;
        $page = isset( $param['page'])  ?  $param['page']: null;
        $limit = isset( $param['limit']) ?  $param['limit']: null;

        $data = $this->wmsService()->getPurchaseList($keywords,$page,$limit);

        if(!is_array($data)){
            return resultArray(['error' => $data]);
        }
        return resultArray(['data' => $data]);


    }


    /**
     * 保存新建的资源
     * @return \think\Response
     */
    public function save()
    {
        $param = $this->param;
        $data = $this->wmsService()->savePurchase($param);
        if ($data !== true) {
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
        $data = $this->wmsService()->getPurchaseById($param['id']);
        if (is_string($data)) {
            return resultArray(['error' => $data]);
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

        $data = $this->wmsService()->updatePurchaseById($param, $param['id']);
        if($data!==true){
            return resultArray(['error' => $data]);
        }
        return resultArray(['data' => '编辑成功']);
    }

    /**
     * 删除指定资源
     * @return \think\Response
     */
    public function delete()
    {
        $param = $this->param;
        $data = $this->wmsService()->deletePurchase($param['id']);
        if ($data!==true) {
            return resultArray(['error' => $data]);
        }
        return resultArray(['data' => '删除成功']);
    }

    public function deletes()
    {
        $param = $this->param;
        $data = $this->wmsService()->deletePurchaseByIds($param['ids']);
        if ($data!==true) {
            return resultArray(['error' => $data]);
        }
        return resultArray(['data' => '批量删除成功']);
    }
}
