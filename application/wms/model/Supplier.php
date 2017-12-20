<?php

namespace app\wms\model;

use app\common\model\WMSBase as WMSBaseModel;


class Supplier extends WMSBaseModel
{
    /*******************类属性*******************/


    /*******************类方法*******************/

    public function getContentAttr()
    {
        return 'sadasdasdsa';
    }

    /**
     * 描述：获取供应商所提供的产品
     * @date 2017年11月17日下午3:37:32
     * @return \think\model\relation\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany('product', 'gms_supplier_product','product_id','supplier_id');
    }

    /**
     * 描述：获取供应商列表信息
     * @date 2017年11月13日上午9:20:15
     * @param    string $keywords 搜索关键词
     * @param    integer $page 页序数
     * @param    integer $limit 每页数量
     * @return   boolean|string|array              false|错误信息|供应商列表
     */
    public function getDataList($keywords, $page, $limit)
    {
        $map = [];

        if ($keywords) {
            $map['name'] = ['like', '%' . $keywords . '%'];
        }
        try {
            $dataCount = $this->alias('supplier')->where($map)->count('id');

            if ($dataCount === 0) {
                $this->error = '没有数据';
                return false;
            }

            $list = $this
                ->where($map)->alias('gms_supplier');

            // 若有分页
            if ($page && $limit) {
                $list = $list->page($page, $limit);
            }

            $list = $list->select();

            $data['list'] = $list;
            $data['count'] = $dataCount;

            return $data;

        } catch (\Exception $e) {

            $this->error = iconv('', 'utf-8', $e->getMessage());
            return false;
        }

    }

    /**
     * 描述：创建新供应商
     * @date 2017年11月13日上午9:20:29
     * @param  array $param 供应商详情属性数组
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function createData($param)
    {
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
        } catch (\Exception $e) {
            $this->error = '添加失败';
            $this->rollback();
            return false;
        }
    }

    /**
     * 描述：根据ID获取供应商详情
     * @param  array $param 供应商详情属性数组
     * @param  integer $id 供应商ID
     * @param  string $scense 验证场景
     * @return bool|null|\think\Model
     * @throws \think\exception\PDOException
     */
    public function updateDataById($param, $id, $scense = null)
    {
        // 验证
        $validate = validate($this->name);
        if (!$validate->scene($scense)->check($param)) {
            $this->error = $validate->getError();
            return false;
        }
        $this->startTrans();
        try {
            $data = $this->getDataById($id);
            if (!$data) {
                return $data;
            }
            $this->allowField(true)->save($param, [$this->getPk() => $id]);
            if (!empty($param['product_ids'])) {
                $this->products()->saveAll($param['product_ids']);
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = '编辑失败 : ' . $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 描述: 根据供应商ID删除供应商详情
     * @param string $id 供应商ID
     * @param bool $delSon
     * @return   boolean
     * @throws \think\exception\PDOException
     */
    public function delDataById($id = '', $delSon = false)
    {
        $this->startTrans();
        try {
            $data = $this->get($id);
            if (!$data) {
                $this->error = '暂无此数据';
                return false;
            }
            foreach ($data->products()->select() as $product) {
                $product_ids [] = $product->id;
            }

            $this->where($this->getPk(), $id)->delete();
            if (!empty($product_ids)) {
                $data->products()->detach(array_unique($product_ids));
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = '删除失败: ' . $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 描述：根据数值中id值批量删除数据
     * @date 2017年11月2日下午7:51:31
     * @param    array $ids 供应商ID的数组
     * @param    boolean $delSon 是否存在子集
     * @return   boolean                            布尔值
     * @throws \think\exception\PDOException
     */
    public function delDataCollection($ids = [], $delSon = false)
    {
        $this->startTrans();
        try {
            foreach ($ids as $id) {

                $data = $this->get($id);
                if (!$data) {
                    $this->error = '暂无此数据';
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
            $this->error = '操作失败: ' . $e->getMessage();
            $this->rollback();
            return false;
        }
    }
}

