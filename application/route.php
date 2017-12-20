<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

Route::pattern([
    'id'    =>  '\d+',
]);

Route::resource('/:version/wms/products','wms/:version.Product');
Route::resource('/:version/wms/purchase','wms/:version.Purchase');
Route::resource('/:version/wms/suppliers','wms/:version.Supplier');
Route::resource('/:version/wms/orders','wms/:version.Order');

return [
    //以ID数组批量删除产品
    '/:version/wms/products/deletes' => ['wms/:version.Product/deletes', ['method' => 'POST']],

    '/:version/wms/products/:id/images/delete' => ['wms/:version.Product/deleteImages', ['method' => 'DELETE']],
    //在存在产品下以产品ID储存图片
    '/:version/wms/products/:id/images' => ['wms/:version.Product/saveImages', ['method' => 'POST']],
    //以ID数组批量删除供应商
    '/:version/wms/suppliers/deletes' => ['wms/:version.Supplier/deletes', ['method' => 'POST']],
    //以ID数组批量删除采购单
    '/:version/wms/purchase/deletes' => ['wms/:version.Purchase/deletes', ['method' => 'POST']],
];

