<?php
/**
 * Created by PhpStorm.
 * User: huangweiwei
 * Date: 2017/12/18
 * Time: 上午9:34
 */

namespace app\wms\model;

use app\common\model\WMSBase as WMSBaseModel;

class PurchaseContent extends WMSBaseModel
{

    public function initialize(){
        $this->append(['supplier_info']);
        $this->append(['product_info']);

    }

    public function supplier()
    {
       return  $this->hasOne('Supplier','id','supplier_id');
    }

    public function product()
    {
        return $this->hasOne('Product','id','product_id');
    }



    protected function getProductInfoAttr()
    {
        $this->product->images;
        return $this->product->hidden(['create_time','update_time']);
    }

    protected function getSupplierInfoAttr(){
        return $this->supplier->hidden(['create_time','update_time']);
    }



}