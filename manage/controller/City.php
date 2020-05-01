<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;
class City extends ManageBase
{
//前置操作定义
    protected $beforeActionList = [
        // 'second' =>  ['except'=>'hello'],
        'beforeEdit'  =>  ['only'=>'edit,add'],
    ];
    private $mod;
    public function initialize(){
        parent::initialize();
        $this->mod = model('city');
    }
    public function index() {
        $province_id = input('param.province_id/d',0);
        $pid   = input('param.pid/d',0);
        $where[] = ['pid','eq',$pid];
        $province_id && $where[] = ['province_id','eq',$province_id];
        $lists = $this->mod->where($where)->order('ordid asc,id asc')->select();
        //$lists = recursion(objToArray($lists));
        $big_menu = [
            'title' => '添加城市',
            'iframe' => url('City/add',['pid'=>$pid,'province_id'=>$province_id]),
            'id' => 'add',
            'width' => '500',
            'height' => '450'
        ];
        $this->_data = [
            'addchild' => [
                'c' => 'City',
                'a'  => 'add',
                'str' => '<a data-height="480" data-width="500" data-id="add" data-uri="%s" data-title="添加 - %s 区域" class="J_showDialog layui-btn layui-btn-xs layui-btn-normal" href="javascript:;">添加区域</a>',
                'param' => ['pid' => '@id@','province_id'=>$province_id],
                'isajax' => true,
                'replace' => ''
            ],
            'listschild' => [
                'c' => 'City',
                'a'  => 'index',
                'str' => '<a href="%s" class="layui-btn layui-btn-xs layui-btn-normal">查看区域</a>',
                'param' => ['pid' => '@id@','province_id'=>$province_id],
                'isajax' => false,
                'replace' => ''
            ]
        ];
        $parent_id = $this->mod->removeOption()->where('id',$pid)->value('pid');
        $this->_ajaxedit = true;
        $this->assign('big_menu', $big_menu);
        $this->assign('list',$lists);
        $this->assign('options',$this->check());
        $this->assign('pid',$parent_id);
        $this->assign('id',$pid);
        $this->assign('province_id',$province_id);
        return $this->fetch();
    }
    public function ajaxSelect()
    {
        $onlycity = input('param.onlycity/d',0);
        $province = input('param.province/d',1);
        $where['province_id'] = $province;
        $onlycity && $where['pid'] = 0;
        $lists = $this->mod->where($where)->order('ordid asc,id desc')->select();
        $lists = recursion(objToArray($lists));
        $this->assign('list',$lists);
        $this->assign('province',$province);
        return $this->fetch();
    }
    protected function beforeEdit(){
        $pid = input('param.pid/d');
        $province_id = input('param.province_id/d',0);
        $spid = 0;
        if ($pid) {
            $spid = $this->mod->where(['id'=>$pid])->value('spid');
            $spid = $spid ? $spid.$pid : $pid;
        }
        $this->assign('spid', $spid);
        $this->assign('province_id',$province_id);
    }
    public function addDo(){
        $data = input('post.');
        $name_arr = explode("\n",str_replace('&',"\n",trim($data['name'])));
        $map = explode(',',$data['map']);
        $lat = isset($map[1])?$map[1]:0;
        $lng = $map[0];
        $py = new \org\Pinyin();
        $adddata = [];
        foreach($name_arr as $v){
            //检测分类是否存在
            if($this->mod->name_exists($data['name'], $data['pid'])){
                unset($v);
            }else{
                //生成spid
                $data['lat']  = $lat;
                $data['lng']  = $lng;
                $data['name'] = $v;
                $data['spid'] = $this->mod->get_spid($data['pid']);
                $data['alias']= trim($py->getAllPY($data['name']));		//全部拼音
                !$data['alias'] && $data['domain'] = $data['alias'];
                $adddata[] = $data;
            }
        }
        if($this->mod->saveAll($adddata)){
          $this->success('添加成功');
        }else{
           $this->error('添加失败');
        }

    }
    public function editDo()
    {
        \app\common\model\City::event('before_update',function($obj){
            $map  = input('post.map');
            $map_arr  = explode(',',$map);
            $obj->lat = isset($map_arr[1]) ? $map_arr[1] : 0;
            $obj->lng = $map_arr[0];
        });
        parent::editDo();
    }
    public function delete(){
        //判断是否有下级栏目存在  存在则不能删除
        \app\common\model\City::event('before_delete',function($obj,City $that){
            $id = $obj->id;
            //是否有下级栏目
            $child = $obj->get_child_ids($id);

            if(!empty($child)){
                $that->errMsg = '存在下级区域，不允许删除！';
                return false;
            }
            return true;
        });
        parent::delete();
    }
    public function ajaxGetchilds() {
        $id = input('param.id/d');
        $province_id = input('param.province_id/d',0);
        $where['pid'] = $id;
        $province_id && $where['province_id'] = $province_id;
        $return = $this->mod->field('id,name,spid')->where($where)->select();
        if (!$return->isEmpty($return)) {
            return $this->ajaxReturn(1, 'success', $return);
        } else {
            return $this->ajaxReturn(0, 'error');
        }
    }
    /**
     * 更新城市分类缓存
     */
    public function cache(){
        if($this->doCache()){
            $this->success('城市缓存更新成功');
        }else{
            $this->error('城市缓存更新失败');
        }
    }
    public function doCache(){
        $lists = $this->mod->field('id,pid,name,alias,is_hot,domain,lat,lng')->where('status',1)->select();
        if($lists){
            $cate = objToArray($lists);//普通列表
            $temp = [];
            foreach($cate as $v){
                $temp[$v['id']] = $v;
            }
            $tree = list_to_tree($temp);//树形列表
            cache('city',['cate'=>$temp,'tree'=>$tree]);
            return true;
        }
        return false;

    }
}