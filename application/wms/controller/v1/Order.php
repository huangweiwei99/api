<?php

namespace app\wms\controller\v1;

use app\common\controller\Api as ApiController;
use think\Request;
use think\Session;
use think\View;

class Order extends ApiController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $data = $this->wmsService()->getTestPP2();

        //$data = ['name'=>'api','url'=>'gms.com'];
        return ['data' => $data, 'code' => 1, 'message' => '订单列表操作完成'];
    }


    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = ['name' => 'api', 'url' => 'gms.com'];
        return ['data' => $data, 'code' => 1, 'message' => 'save创建订单操作完成'];
    }

    /**
     * 显示指定的资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function read($id)
    {
        $data = ['name' => 'api', 'url' => 'gms.com'];
        return ['data' => $data, 'code' => 1, 'message' => 'read单个订单详细操作完成'];
    }


    /**
     * 保存更新的资源
     *
     * @param  \think\Request $request
     * @param  int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = ['name' => 'api', 'url' => 'gms.com'];
        return ['data' => $data, 'code' => 1, 'message' => 'update保存已有订单操作完成'];
    }

    /**
     * 删除指定资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $data = ['name' => 'api', 'url' => 'gms.com'];
        return ['data' => $data, 'code' => 1, 'message' => 'delete删除订单操作完成'];
    }

    public function test()
    {


        Session::clear('sync');
        return view();
        // return $this->fetch('view/test.html');
    }

    public function sync()
    {
        //set_time_limit(0);
        //Session::clear();die();
        $param = $this->param;

        $current = $this->wmsService()->syncOrder($param);
        return ['data' => $current, 'code' => 1, 'message' => '订单同步'];
    }

    public function test2()
    {
        $this->wmsService()->sync2();
        echo  'sadsadadada';
    }

}
