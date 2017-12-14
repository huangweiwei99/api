<?php
// +-----------------------------------------------
// | visoty.com PHP文件
// | ==============================================
// | 描述：公共模型,所有模型都可继承此模型，基于RESTFUL CRUD操作
// | ----------------------------------------------
// | 版权所有 2017-2018 http://www.visoty.com
// | ==============================================
// +-----------------------------------------------
// |  @date: 2017年11月3日上午10:44:24
// |  @author: vson.mail@gmail.com
// +------------------------------------------------

namespace app\common\model;

use think\Model;

class Base extends Model
{
   /*******************类属性*******************/
    // 指定自动写入时间戳的类型为dateTime类型
    public $autoWriteTimestamp = 'datetime';
    
    // 定义时间戳字段名
    public $createTime = 'create_time';
    public $updateTime = 'update_time';
    
    // 定义类型转换
    public $type = [
        'create_time' => 'timestamp:Y/m/d H:i:s',
        'update_time' => 'timestamp:Y/m/d H:i:s',
    ];
    
   /*******************类方法*******************/
    
   /**
   * 描述：根据主键获取详细数据
   * @param  string   $id 主键
   * @return Model|bool 对象|False
   * @date 2017年11月2日下午7:21:21
   */
    public function getDataById($id = '')
    {
        $data = $this->get($id);
        if (!$data) {
            $this->error = '暂无此数据';
            return false;
        }
        return $data;
    }
    
    /**
    * 描述：创建数据对象
    * @param array $param
    * @return bool 布尔值
    * @date 2017年11月2日下午7:23:19
    */
    public function createData($param)
    {
        // 验证
        $validate = validate($this->name);
        if (!$validate->check($param)) {
            $this->error = $validate->getError();
            return false;
        }
        
        try {
            $this->data($param)->allowField(true)->save();
            return true;
        } catch(\Exception $e) {
            $this->error = '添加失败';
            return false;
        }
    }
    
    /**
    * 描述：根据主键更新数据
    * @param array   $param
    * @param integer $id
    * @return bool   布尔值
    * @date 2017年11月2日下午7:30:17
    */
    public function updateDataById($param, $id)
    {
        $checkData = $this->get($id);
        if (!$checkData) {
            $this->error = '暂无此数据';
            return false;
        }
        
        // 验证
        $validate = validate($this->name);
        if (!$validate->scene('update')->check($param)) {
            $this->error = $validate->getError();
            return false;
        }
        
        try {
            $this->allowField(true)->save($param, [$this->getPk() => $id]);
            return true;
        } catch(\Exception $e) {
            $this->error = '编辑失败';
            return false;
        }
    }
    
    /**
    * 描述：根据id删除数据
    * @param   sting   $id
    * @param   bool    $delSon
    * @return  bool    布尔值
    * @date 2017年11月2日下午7:47:52
    */
    public function delDataById($id = '', $delSon = false)
    {
        
        $this->startTrans();
        try {
           
            $this->where($this->getPk(), $id)->delete();
            if ($delSon && is_numeric($id)) {
                // 删除子孙
               
                $childIds = $this->getAllChild($id);
                dump($childIds);die();
                if($childIds){
                   $this->where($this->getPk(), 'in', $childIds)->delete();
                }
            }
            $this->commit();
            return true;
        } catch(\Exception $e) {
            $this->error = '删除失败';
            $this->rollback();
            return false;
        }
    }
    
    /**
    * 描述：根据数值中id值批量删除数据
    * @param    array   $ids
    * @param    bool    $delSon
    * @return   bool    布尔值
    * @date 2017年11月2日下午7:51:31
    */
    public function delDatas($ids = [], $delSon = false)
    {
        if (empty($ids)) {
            $this->error = '删除失败';
            return false;
        }
        
        // 查找所有子元素
        if ($delSon) {
            foreach ($ids as $k => $v) {
                if (!is_numeric($v)) continue;
                $childIds = $this->getAllChild($v);
                $ids = array_merge($ids, $childIds);
            }
            $ids = array_unique($ids);
        }
        
        try {
            $this->where($this->getPk(), 'in', $ids)->delete();
            return true;
        } catch (\Exception $e) {
            $this->error = '操作失败';
            return false;
        }
        
    }
    
    /**
    * 描述：批量启用或者禁用
    * @param    array   $ids
    * @param    integer $status
    * @param    bool    $delSon
    * @return   bool    布尔值
    * @date 2017年11月2日下午8:01:57
    */
    public function enableDatas($ids = [], $status = 1, $delSon = false)
    {
        if (empty($ids)) {
            $this->error = '删除失败';
            return false;
        }
        
        // 查找所有子元素
        if ($delSon && $status === '0') {
            foreach ($ids as $k => $v) {
                $childIds = $this->getAllChild($v);
                $ids = array_merge($ids, $childIds);
            }
            $ids = array_unique($ids);
        }
        try {
            $this->where($this->getPk(),'in',$ids)->setField('status', $status);
            return true;
        } catch (\Exception $e) {
            $this->error = '操作失败';
            return false;
        }
    }
  
    /**
    * 描述：获取所有子孙数据
    * @param    integer   $id  
    * @param    array     $data  
    * @return   array     数组
    * @date 2017年11月2日下午8:04:25
    */
    public function getAllChild($id, &$data = [])
    {
        $map['pid'] = $id;
        $childIds = $this->where($map)->column($this->getPk());

        if (!empty($childIds)) {
            foreach ($childIds as $v) {
                $data[] = $v;
                $this->getAllChild($v, $data);
            }
        }
        return $data;
    }	


    
}
