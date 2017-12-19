<?php
/**
 * Created by PhpStorm.
 * User: huangweiwei
 * Date: 2017/12/19
 * Time: 下午4:49
 */

namespace app\wms\validate;

use app\common\validate\Base as BaseValidate;

class Purchase extends BaseValidate
{
    protected $rule =  [
        ['date','require|length:10','日期不能为空|长度为10'],
        ['purchase_transaction_id','require|length:15','采购单号不能为空|采购单长度为15'],
        ['place','require','采购地点不能为空'],
    ];
}