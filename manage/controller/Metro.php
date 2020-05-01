<?php


namespace app\manage\controller;


use app\common\controller\ManageBase;

class Metro extends ManageBase
{

    protected $beforeActionList = [
        'beforeIndex'  => ['only' => 'index'],
    ];
    public function initialize()
    {
        parent::initialize();
        $this->sort = 'city asc,ordid asc,id desc';
    }
    public function beforeIndex()
    {
        $big_menu = [
            'title' => '添加地铁线',
            'iframe' => url('Metro/add'),
            'id' => 'add',
            'width' => '500',
            'height' => '500'
        ];
        $this->_ajaxedit = 1;
        $this->_data = [
            'addstatiion' => [
                'c' => 'MetroStation',
                'a' => 'add',
                'str'    => '<a data-height="500" data-width="500" data-id="add" data-uri="%s" data-title="添加地铁站" class="J_showDialog layui-btn layui-btn-xs" href="javascript:;">添加地铁站</a>',
                'param' => ['metro_id' => '@id@'],
                'isajax' => 1,
                'replace' => ''
            ],
            'addlist' => [
                'c' => 'MetroStation',
                'a' => 'index',
                'str' => '<a href="%s" class="layui-btn layui-btn-xs">站点列表</a>',
                'param' => ['metro_id' => '@id@'],
                'isajax' => 0,
                'replace' => ''
            ],
        ];
        $this->assign('big_menu', $big_menu);
    }
    public function search()
    {
        $city    = input('param.city/d',0);
        $keyword = input('param.keyword');
        $where   = [];
        $city && $where[] = ['city','eq',$city];
        $keyword && $where[] = ['name','like','%'.$keyword.'%'];
        $data = [
          'city' => $city,
            'keyword' => $keyword
        ];
        $this->queryData = $data;
        $this->assign('search',$data);
        return $where;
    }
    /**
     * @return \think\response\Json
     * 异步获取地铁线
     */
    public function ajaxGetMetro()
    {
        $city = input('get.city/d',0);
        $return['code'] = 0;
        $lists = model('metro')->where('city',$city)->where('status',1)->order('ordid asc,id desc')->select();
        if(!$lists->isEmpty())
        {
            $return['code'] = 1;
            $return['data'] = $lists;
        }
        return json($return);
    }

    /**
     * 删除
     */
    public function delete()
    {
        \app\common\model\Metro::event('after_delete',function($obj){
            model('metro_station')->where('metro_id',$obj->id)->delete();//删除站点
            \org\Relation::deleteByMetro($obj->id);//删除所有关联数据
        });
        parent::delete();
    }
}