<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;
class Province extends ManageBase
{

    protected $beforeActionList = [
        'beforeIndex' => ['only'=>'index']
    ];
    public function beforeIndex()
    {
        $this->sort = ['ordid'];
        $big_menu = [
            'title' => '添加省份',
            'iframe' => url('Province/add'),
            'id' => 'add',
            'width' => '500',
            'height' => '400'
        ];
        $this->_data = [
            'addcity' => [
                'c' => 'City',
                'a'  => 'add',
                'str' => '<a data-height="480" data-width="500" data-id="add" data-uri="%s" data-title="添加 - %s 城市" class="J_showDialog layui-btn layui-btn-xs layui-btn-normal" href="javascript:;">添加城市</a>',
                'param' => ['province_id' => '@id@'],
                'isajax' => true,
                'replace' => ''
            ],
            'citylist' => [
                'c' => 'City',
                'a'  => 'index',
                'str'    => '<a data-height="" data-width="" data-show_btn="false" data-id="add" data-uri="%s" data-title="%s 城市管理" class="J_showDialog layui-btn layui-btn-xs layui-btn-normal" href="javascript:;">城市管理</a>',
                'param' => ['province_id'=>'@id@','menuid'=>331],
                'isajax' => true,
                'replace'=> ''
            ]
        ];
        $this->_exclude = 'edit';
        $this->assign('big_menu',$big_menu);
    }
}