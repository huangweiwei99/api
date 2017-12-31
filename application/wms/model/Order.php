<?php
/**
 * Created by PhpStorm.
 * User: huangweiwei
 * Date: 2017/12/20
 * Time: 下午9:24
 */

namespace app\wms\model;

use app\common\model\WMSBase as WMSBaseModel;

class Order extends WMSBaseModel
{
    public function paypal()
    {
        return $this->hasOne('Orderpaypal');
    }
}