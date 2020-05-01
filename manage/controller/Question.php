<?php


namespace app\manage\controller;


use app\common\controller\ManageBase;

class Question extends ManageBase
{
    protected $beforeActionList = [
        'beforeIndex' => ['only'=>['index']]
    ];
    public function beforeIndex()
    {
        $this->_exclude = 'edit';
        $this->_data = [
            'view'    => [
                'c' => 'Answer',
                'a' => 'index',
                'str'    => '<a data-height="" data-width="" data-show_btn="false" data-id="add" data-uri="%s" data-title="查看回复" class="J_showDialog layui-btn layui-btn-xs layui-btn-normal" href="javascript:;">查看回复</a>',
                'param' => ['question_id'=>'@id@'],
                'isajax' => true,
                'replace'=> ''
            ],
        ];
    }
}