<?php
namespace App\Work\Common\Service;

use App\Work\Common\Models\WowToolModel;
use App\Utility\Database\Db;

class ToolService
{
    /**
     * @desc       获取工具列表
     * @author     文明<736038880@qq.com>
     * @date       2022-09-29 18:10
     * @return array
     */
    public function getToolList(){
        $fields = 'name,icon_name,page_path';
        $list = WowToolModel::query()->where('status', 1)->select(Db::raw($fields))->orderBy('sort', 'asc')->orderBy('id', 'asc')->get()->toArray();
        return $list;
    }
}