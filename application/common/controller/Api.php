<?php
// +-----------------------------------------------
// | visoty.com PHP文件
// | ==============================================
// | 描述：
// | ----------------------------------------------
// | 版权所有 2017-2018 http://www.visoty.com
// | ==============================================
// +-----------------------------------------------
// |  @date: 2017年11月4日下午4:07:51
// |  @author: vson.mail@gmail.com
// +------------------------------------------------
namespace app\common\controller;

use app\common\service\WMSService;
use app\common\controller\Base as BaseController;

/**
* PHP类注释
* 描述：
* @date 2017年11月4日下午4:08:04
* @container app\common\controller
* @param unknowtype 
* @return return_type 
*/
class Api extends BaseController
{
    public function _initialize()
    {
        parent::_initialize();
    }
    
    /**
    * 描述：在controller中一直引用wmsService方法
    * @param unknowtype
    * @return return_type
    * @date 2017年11月4日下午4:08:55
    */
    public function wmsService() {
        return new WMSService();
    }
}