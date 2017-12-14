<?php
// +-----------------------------------------------
// | visoty.com PHP文件
// | ==============================================
// | 描述：Product模型的验证规则
// | ----------------------------------------------
// | 版权所有 2017-2018 http://www.visoty.com
// | ==============================================
// +-----------------------------------------------
// |  @date: 2017年11月3日上午10:44:47
// |  @author: vson.mail@gmail.com
// +------------------------------------------------

namespace app\wms\validate;

use app\common\validate\Base as BaseValidate;


class Product extends BaseValidate
{
    protected $rule = [
        ['name','require|min:2|max:100','产品名称不能为空|产品名称至少两个字符|产品名称最多100个字符'],
        ['sku','require|length:6|alphaNum','SKU不能为空|SKU长度为6位|SKU是字母和数字的组合']
    ];
    
    protected $scene = [
        'update'        =>  ['sku'],
        'create'        => ['name','sku']
    ];
}