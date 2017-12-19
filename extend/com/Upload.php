<?php
namespace com;

use think\File;

class Upload {
    /*******************类属性*******************/
    private $error = [];
    private $count = 0;
    private $files = [];
    private $rule = [];
    private $path = '';
    private $filesPath = [];
    
    /*******************类方法*******************/
    
    /**
    * 描述：获取错误信息
    * @date 2017年11月8日上午9:26:02
    * @param 
    * @return    array    错误信息数组   
    */
    public function getError(){
        return $this->error;
    } 
    
    /**
    * 描述：构造函数
    * @date 2017年11月8日上午9:25:27
    * @param        array       File $files      File类数组 
    * @param        array       $rule            验证规则
    * @param        string      $path            文件储存路径
    * @return return_type
    */
    public function __construct($files = [], $rule = [],$path  = '' ){
        $this->error[]=empty($files)?'请输入有效FILE类的数组':[];
        $this->error[]=empty($rule)?'请输入有效规则的数组':[];
        $this->error[]=empty($path)?'路径不为空':[];
        
        if (!empty($files)&!empty($rule)&$path!=='') {
            $this->files = $files;
            $this->rule  = $rule;
            $this->path  = $path;
            $validate=$this->Validate($this->files,$this->rule);
            if ($validate) {
                $this->getUploaded($this->files,$this->path);
            }
        }
    }
    
    /**
    * 描述：验证要上传的文件
    * @date 2017年11月7日下午9:47:59
    * @param        array       File $files      File类数组 
    * @param        array       $rule            验证规则
    * @return       bool                         布尔值
    */
    public function Validate($files, $rule) {
        $this->files = empty($files)? $this->files:$files;
        $this->rule  = empty(($rule))? $this->rule:$rule;
        $this->error = [];
        foreach ($this->files as $file){
            $this->count++;
            $validate = $file->check($this->rule);
            if ($validate !== true) {
                $this->error[] = '第'.$this->count.'个文件出错,原因:'.$file->getError();
            }
        }
        $this->count = 0;
        return empty($this->error)?true:false;
    }
    
 
    /**
    * 描述：上传文件
    * @date 2017年11月7日下午10:13:15
    * @param        array       File $files      File类数组 
    * @return       string      $path            上传文件要保存的路径
    */
    public function getUploaded($files, $path) {
        $this->files = empty($files)? $this->files:$files;
        $this->path  = $path == ''? $this->path:$path;

        if (!empty($this->error)) {
            return $this->error;
        }
       
        //上传文件
        foreach ($this->files as $file) {
            $this->count++;
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->rule('uniqid')->move($this->path.DS.date('Ymd',time()));
            if($info){
                $this->filesPath [] = ['path' =>str_replace(DIRECTORY_SEPARATOR,'/',date('Ymd',time()).DS.$info->getSaveName())];
            }else{
                // 上传失败获取错误信息
                $this->error[] = '第'.$this->count.'个文件出错,原因:'.$file->getError();
            }
        }
        return empty($this->error)?true:false;
    }
    
    /**
    * 描述：获取文件路径
    * @date 2017年11月10日下午3:00:30
    * @param unknowtype
    * @return       array           文件路径数组
    */
    public function getFilesPath() {
        return $this->filesPath;
    }
    
//     public function uploadImage(Request $request,$path=null)
//     {
//         // 获取表单上传文件
//         $files = $request->file('images');
//         $images = [];
//         $i = 0;
//         $error = '';
//         foreach ($files as $file){
//             $i++;
//             // dump();die();
//             $validate = $file->check(['ext'=>'jpg,png,gif','size'=>'1568','type'=>'image/jpeg,image/png']);
//             if ($validate !== true) {
//                 $error = '上次第'.$i.'张图片出错:'.$file->getError();
//                 return $error;
//             }
//         }
//         //上传文件
//         foreach ($files as $file) {
//             $i++;
//             // 移动到框架应用根目录/public/uploads/ 目录下
//             $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
//             if($info){
//                 $images[]=['path' =>str_replace(DIRECTORY_SEPARATOR,'/',$info->getSaveName())];
//             }else{
//                 // 上传失败获取错误信息
//                 $error = '上次第'.$i.'张图片出错:'. $file->getError();
//                 break;
//             }
//         }
//         return $error===''?  ['images' =>$images] :$error;
//     }
}