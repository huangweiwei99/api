<?php
/**
* PHP类注释
* 描述：来处理异常，并且直接接管系统的所有异常信息输出json错误信息
* @date 2017年11月9日上午11:54:46
* @container class_container
* @param unknowtype 
* @return return_type 
*/

namespace com;

use think\exception\Handle;
use think\exception\HttpException;

class Http extends Handle
{
    public function render(\Exception $e)
    {
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
        }
        if (!isset($statusCode)) {
            $statusCode = 500;
        }
        $result = [
            'code' => $statusCode,
            'error' => $e->getMessage().$e->getTraceAsString(),
            'type'=>'system',
            'time' => $_SERVER['REQUEST_TIME']
        ];
        return json($result, $statusCode);
    }
}

