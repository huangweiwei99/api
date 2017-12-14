<?php
// +-----------------------------------------------
// | visoty.com PHP文件
// | ==============================================
// | 描述：Product模型，属性和方法
// | ----------------------------------------------
// | 版权所有 2017-2018 http://www.visoty.com
// | ==============================================
// +-----------------------------------------------
// |  @date: 2017年11月3日上午10:45:36
// |  @author: vson.mail@gmail.com
// +------------------------------------------------

namespace app\wms\model;

use app\common\model\WMSBase as WMSBaseModel;

class Product extends WMSBaseModel {
    
    
    /*******************类属性*******************/
    
    
    
    /*******************类方法*******************/
  
    public  function suppliers() {
        return $this->belongsToMany('supplier','gms_supplier_product');
    }
    
    /**
    * 描述：产品和图像的一对多关系
    * @date 2017年11月13日上午10:12:01
    * @return 图片对象的集合
    */
    public function images()
    {
        return $this->hasMany('\\app\\system\\model\\Upload','product_id')->field('id,path');
    }
    
    /**
    * 描述：获取产品列表信息 
    * @date 2017年11月3日上午11:08:33
    * @param    string              $keywords     搜索关键词
    * @param    integer             $page         页序数
    * @param    integer             $limit        每页数量
    * @return   boolean|string|array              false|错误信息|产品列表
    */
    public function getDataList($keywords, $page, $limit) {
        $map = [];
        
         if ($keywords) {
             $map['name|sku'] = ['like', '%'.$keywords.'%'];
         }
          try {
              $dataCount = $this->alias('product')->where($map)->count('id');
              
              if ($dataCount===0) {
                  $this->error = '没有数据';
                  return false;
              }
              
              $list=$this
              ->where($map)->alias('gms_product');
              
              // 若有分页
              if ($page && $limit) {
                  $list = $list->page($page, $limit);
              }
              
              $list = $list->select();
              
              $data['list'] =$list;
              $data['count'] =$dataCount;
              
              return $data;
              
          } catch (\Exception $e) {
              $this->error = iconv('','utf-8',$e->getMessage());
              return false;
          }
        
    }
    
    /**
    * 描述：创建新产品  
    * @param    array       $param      产品详情属性数组
    * @return   boolean|string       布尔值| 错误信息    
    * @date 2017年11月3日上午11:16:47
    */
    public function createData($param) {
        // 验证
        $validate = validate($this->name);
        if (!$validate->check($param)) {
            $this->error = $validate->getError();
            return false;
        }
        $this->startTrans();
        try {
            $this->data($param)->allowField(true)->save();
            if ($param['images']) {
                $this->images()->saveAll($param['images']);
            }
            $this->commit();
            return true;
        } catch(\Exception $e) {
            $this->error = '添加失败';
            $this->rollback();
            return false;
        }
    }
    
    /**
    * 描述：根据产品ID更新产品详情  
    * @date 2017年11月3日上午11:17:06
    * @param    array       $param      产品详情属性数组
    * @param    integer     $id         产品ID  
    * @param    string      $scense     验证场景  
    * @return   boolean                 布尔值             
    */
    public function updateDataById($param, $id, $scense = null)
    {
       
        $data = $this->getDataById($id);
        if (!$data) {
            return $data;
        }
        // 验证
        $validate = validate($this->name);
        if (!$validate->scene($scense)->check($param)) {
            $this->error = $validate->getError();
            return false;
         }
        $this->startTrans();
        try {
            $this->allowField(true)->save($param, [$this->getPk() => $id]);
            $this->commit();
            return true;
        } catch(\Exception $e) {
            $this->error = '编辑失败 : '.$e->getMessage();
            $this->rollback();
            return false;
        }
    }
    
    /**
     * 描述：根据id删除数据 
     * @date 2017年11月2日下午7:47:52
     * @param       sting               $id             产品ID
     * @param       boolean             $delSon         是否存在子集
     * @return      boolean                             布尔值
     */
    public function delDataById($id = '', $delSon = false)
    {
        $data = $this->getDataById($id);
        if (!$data) {
            return $data;
        }
        $data = $this->get($id);
        if (!$data) {
            $this->error = '找不到相关数据';
            return false;
        }
        
        $this->startTrans();
        try {
            $data = $this->getDataById($id);
            $images=$data->images();
            $imageList = $images->select();
            if ($data->delete()) {
                $images->delete();
            }
            foreach ($imageList as $image){
                unlink(ROOT_PATH . 'public' . DS . 'uploads'. DS .str_replace('/', DIRECTORY_SEPARATOR, $image->path));  
            }
            $this->commit();
            return true;
        } catch(\Exception $e) {
            $this->error = '删除失败: '.$e->getMessage();
            $this->rollback();
            return false;
        }
    }
    
    /**
     * 描述：根据数值中id值批量删除数据 
     * @date 2017年11月2日下午7:51:31
     * @param    array               $ids           产品ID的数组
     * @param    boolean          $delSon        是否存在子集
     * @return   boolean|string                  布尔值|错误信息
     */
    public function delDatas($ids = [], $delSon = false)
    {
        if (empty($ids)) {
            $this->error = '删除失败';
            return false;
        }
        
        $this->startTrans();
        try {
            foreach ($ids as $id){
                $data = $this->getDataById($id);
                if (!$data) {
                    return $data;
                    break;
                }
               $images=$data->images();
               $imageList = $images->select();
               if ($data->delete()) {
                   $images->delete();
               }
               foreach ($imageList as $image){
                   unlink(ROOT_PATH . 'public' . DS . 'uploads'. DS .str_replace('/', DIRECTORY_SEPARATOR, $image->path));
               }
           }
           $this->commit();
           return true;
        } catch (\Exception $e) {
           $this->error = '操作失败: '.$e->getMessage();
           $this->rollback();
           return false;
        }
        
    }
}

