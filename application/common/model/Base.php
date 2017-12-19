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

use think\Log;
use think\Model;

/**
 * 类描述： 模型基类
 * Class Base
 * @package app\common\model
 */
class Base extends Model
{
   /*******************类属性*******************/

    /**
     * @var string 指定自动写入时间戳的类型为dateTime类型
     */
    public $autoWriteTimestamp = 'datetime';

    /**
     * @var string 数据库定义时间戳字段名
     */
    public $createTime = 'create_time';
    public $updateTime = 'update_time';

    /**
     * @var array 定义类型转换
     */
    public $type = [
        'create_time' => 'timestamp:Y/m/d H:i:s',
        'update_time' => 'timestamp:Y/m/d H:i:s',
    ];
    
   /*******************类方法*******************/



    /**
     * 描述：描述：根据主键获取详细数据
     * @param string $id       主键
     * @return bool|null|Model 对象|False
     */

    public function getDataById($id = '')
    {
        try {
            $data = $this->get($id);
            if (!$data) {
                $this->error = '暂无此数据';
                return false;
            }
            return $data;
        } catch (\Exception $e) {
            Log::write($e->getMessage(),'error');
            $this->error = '查询数据失败：'.$e->getMessage();
            return false;
        }

    }

    /**
     * 描述：创建数据对象
     * @param $param array
     * @return bool 布尔值
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
            Log::write($e->getMessage(),'error');
            $this->error = '添加失败：'.$e->getMessage();
            return false;
        }
    }



    /**
     * 描述：根据主键更新数据
     * @param $param array
     * @param $id integer
     * @return bool 布尔值
     */
    public function updateDataById($param, $id)
    {
        
        // 验证
        $validate = validate($this->name);
        if (!$validate->scene('update')->check($param)) {
            $this->error = $validate->getError();
            return false;
        }
        
        try {
            $checkData = $this->get($id);
            if (!$checkData) {
                $this->error = '暂无此数据';
                return false;
            }
            $this->allowField(true)->save($param, [$this->getPk() => $id]);
            return true;
        } catch(\Exception $e) {
            Log::write($e->getMessage(),'error');
            $this->error = '编辑失败：'.$e->getMessage();
            return false;
        }
    }


    /**
     * 描述：根据id删除数据
     * @param string $id    主键值
     * @param bool $delSon  布尔值
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function delDataById($id = '', $delSon = false)
    {

        $this->startTrans();
        try {

            $this->where($this->getPk(), $id)->delete();
            if ($delSon && is_numeric($id)) {
                // 删除子孙

                $childIds = $this->getAllChild($id);
                if($childIds){
                   $this->where($this->getPk(), 'in', $childIds)->delete();
                }
            }
            $this->commit();
            return true;
        } catch(\Exception $e) {
            Log::write($e->getMessage(),'error');
            $this->error = '删除失败';
            $this->rollback();
            return false;
        }
    }

    /**
     * 描述：根据数值中id值批量删除数据
     * @param array $ids 主键数组
     * @param bool $delSon 是否存在子元素
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function delDataCollection($ids = [], $delSon = false)
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
        $this->startTrans();
        try {
            $this->where($this->getPk(), 'in', $ids)->delete();
            $this->commit();
            return true;
        } catch (\Exception $e) {
            Log::write($e->getMessage(),'error');
            $this->error = '操作失败';
            $this->rollback();
            return false;
        }
        
    }

    /**
     * 描述：批量启用或者禁用
     * @param array $ids
     * @param int $status
     * @param bool $delSon
     * @return bool
     */
    public function enableDataCollection($ids = [], $status = 1, $delSon = false)
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
            Log::write($e->getMessage(),'error');
            $this->error = '操作失败';
            return false;
        }
    }

    /**
     * 描述：获取所有子孙数据
     * @param integer $id
     * @param array $data
     * @return array
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
