<?php

namespace app\wms\controller\v1;

use think\Controller;
use think\Request;

class Purchase extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $data = ['name'=>'api','url'=>'gms.com'];
        return ['data'=>$data,'code'=>1,'message'=>'采购单列表操作完成'];
    }


    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = ['name'=>'api','url'=>'gms.com'];
        return ['data'=>$data,'code'=>1,'message'=>'save创建采购单操作完成'];
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $data = ['name'=>'api','url'=>'gms.com'];
        return ['data'=>$data,'code'=>1,'message'=>'read单个采购单详细操作完成'.$id];
    }


    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = ['name'=>'api','url'=>'gms.com'];
        return ['data'=>$data,'code'=>1,'message'=>'update保存已有采购单操作完成'];
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $data = ['name'=>'api','url'=>'gms.com'];
        return ['data'=>$data,'code'=>1,'message'=>'delete删除采购单操作完成'];
    }
}
