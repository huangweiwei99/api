<?php
namespace app\wms\model;
use app\common\model\WMSBase as WMSBaseModel;


class Supplier extends WMSBaseModel{
    /*******************类属性*******************/

    
    
    
    /*******************类方法*******************/
    
    /**
     * 描述：获取供应商所提供的产品
     * @date 2017年11月17日下午3:37:32
     * @return return_type
     */
    public  function products() {
        
        return $this->belongsToMany('product','gms_supplier_product');
        
    }
    
    /**
     * 描述：获取供应商列表信息
     * @date 2017年11月13日上午9:20:15
     * @param    string              $keywords     搜索关键词
     * @param    integer             $page         页序数
     * @param    integer             $limit        每页数量
     * @return   boolean|string|array              false|错误信息|供应商列表
     */
    public function getDataList($keywords, $page, $limit) {
        $map = [];
        
        if ($keywords) {
            $map['name'] = ['like', '%'.$keywords.'%'];
        }
        try {
            $dataCount = $this->alias('supplier')->where($map)->count('id');
            
            if ($dataCount===0) {
                $this->error = '没有数据';
                return false;
            }
            
            $list=$this
            ->where($map)->alias('gms_supplier');
            
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
     * 描述：创建新供应商
     * @date 2017年11月13日上午9:20:29
     * @param    array       $param      供应商详情属性数组
     * @return   boolean|string       布尔值| 错误信息
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
            if (!empty($param['product_ids'])) {
                $this->products()->saveAll($param['product_ids']);
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
     * 描述：根据供应商ID更新供应商详情
     * @date 2017年11月13日上午9:21:30
     * @param    array       $param      供应商详情属性数组
     * @param    integer     $id         供应商ID
     * @param    string      $scense     验证场景
     * @return   boolean|string       布尔值| 错误信息
     */
    public function updateDataById($param, $id, $scense = null){
       
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
            if (!empty($param['product_ids'])) {
                $this->products()->saveAll($param['product_ids']);
            }
            $this->commit();
            return true;
        } catch(\Exception $e) {
            $this->error = '编辑失败 : '.$e->getMessage();
            $this->rollback();
            return false;
        }
    }
    
    /**
    * 描述: 根据供应商ID删除供应商详情
    * @date: Dec 14, 2017 10:32:05 AM
    * @param    integer     $id         供应商ID
    * @return   boolean                 布尔值
    */
    public function delDataById($id = '', $delSon = false){
       
        $data = $this->getDataById($id);
        if (!$data) {
            return $data;
        }
        
        $this->startTrans();
        try {
            
            foreach ($data->products()->select() as $product) {
                $product_ids [] = $product->id;
            }
         
            $this->where($this->getPk(), $id)->delete();
            if (!empty($product_ids)) {
                $data->products()->detach(array_unique($product_ids));
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
     * @param    array               $ids           供应商ID的数组
     * @param    boolean             $delSon        是否存在子集
     * @return   boolean                            布尔值
     */
    public function delDatas($ids = [], $delSon = false){
        
        
        $this->startTrans();
        try {
            foreach ($ids as $id) {
                
                $data = $this->getDataById($id);
                if (!$data) {
                    return $data;
                    break;
                }
                foreach ($data->products()->select() as $product) {
                    $product_ids [] = $product->id;
                }
                
                $this->where($this->getPk(), $id)->delete();
                if (!empty($product_ids)) {
                    $data->products()->detach(array_unique($product_ids));
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

