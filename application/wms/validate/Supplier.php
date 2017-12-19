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


class Supplier extends BaseValidate
{
    protected $rule = [
        ['name','require|min:2','供应商名称不能为空|供应商名称至少两个字符'],
        ['address','require|chsAlphaNum','地址不能为空|地址是中文、字母或数字的组合']
    ];
    
}