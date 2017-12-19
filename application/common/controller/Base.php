<?php
namespace app\common\controller;

use think\Controller;
use think\Request;

/**
 * 类描述：基类控制器
 * Class Base
 * @package app\common\controller
 */
class Base extends Controller
{
    /*******************类属性*******************/

    /**
     * @var 获取传入的参数
     */
    public $param;
    
    /*******************类方法*******************/
    /**
     * 描述：构造函数
     */
    public function _initialize()
    {
        parent::_initialize();
        /*防止跨域*/
//         header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
//         header('Access-Control-Allow-Credentials: true');
//         header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
//         header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, authKey, sessionId");
        $param =  Request::instance()->param();
        //$this->param =$this->trimData($param);
        $this->param =$param;
    }
    

    /**
     * 描述：处理参数中的空白
     * @param $data array
     * @return mixed
     */
    public function trimData($data){
        foreach ($data as $k => $v) {
            if(!is_array($v)){
                $data[$k] = trim($v);
            }
        }
        return $data;
    }
    
    

    
//     public function picture(Request $request)
//     {
//         // 获取表单上传文件
//         $file = $request->file('image')
//         if (true !== $this->validate(['image' => $file], ['image' => 'require|image']))
//         {
//             $this->error('请选择图像文件');
//         } else {
//             // 读取图片
//             $image = Image::open($file);
//             // 图片处理
//             switch ($request->param('type')) {
//                 case 1: // 图片裁剪
//                     $image->crop(300, 300);
//                     break;
//                 case 2: // 缩略图
//                     $image->thumb(150, 150, Image::THUMB_CENTER);
//                     break;
//                 case 3: // 垂直翻转
//                     $image->flip();
//                     break;
//                 case 4: // 水平翻转
//                     $image->flip(Image::FLIP_Y);
//                     break;
//                 case 5: // 图片旋转
//                     $image->rotate();
//                     break;
//                 case 6: // 图片水印
//                     $image->water('./logo.png', Image::WATER_NORTHWEST, 50);
//                     break;
//                 case 7: // 文字水印
//                     $image->text('ThinkPHP', VENDOR_PATH . 'topthink/think-captcha/assets/ttfs/1.ttf', 20, '#ffffff');
//                     break;
//             }
//             // 保存图片（以当前时间戳）
//             $saveName = $request->time() . '.png';
//             $image->save(ROOT_PATH . 'public/uploads/' . $saveName);
//             $this->success('图片处理完毕...', '/uploads/' . $saveName, 1);
//         }
//     }
    


}