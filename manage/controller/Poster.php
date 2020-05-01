<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;

class Poster extends ManageBase
{
    private $mod;
    private $space_id;
    protected $beforeActionList = [
      'beforeIndex'  => ['only' => 'index'],
        'beforeEdit' => ['only' => 'add,edit']
    ];
    public function initialize() {
        parent::initialize();
        $this->mod = model('poster');
        $this->space_id = input('param.space_id/d');
    }
    public function beforeIndex(){
        $big_menu = [
            'title' => '添加广告',
            'iframe' => url('Poster/add',['space_id'=>$this->space_id]),
            'id' => 'add',
            'width' => '500',
            'height' => '380'
        ];
        $this->assign('normal',true);
        $this->assign('big_menu', $big_menu);
    }
    public function search(){
        $city    = input('param.city/d',0);
        $keyword = input('param.keyword');
        $where   = [];
        ($space_id = input('param.space_id/d')) && $where['spaceid'] = $space_id;
        $city && $where[] = ['city_id','eq',$city];
        $keyword && $where[] = ['name','like','%'.$keyword.'%'];
        $data = [
            'city'=>$city,
            'space_id' => $space_id,
            'keyword'  => $keyword
        ];
        $this->queryData = $data;
        $this->assign('search',$data);
        return $where;
    }
    public function beforeEdit()
    {
        $id = input('param.id/d');
        $space_id = $this->space_id;
        if ($id) {
            $space = $this->mod->where('id', $id)->field('city_id,spaceid')->find();
            $space_id = $space['spaceid'];
            $this->save_cache($space_id);
            $city_id  = $space['city_id'];
            $city_spid = \think\Db::name('city')->where(['id'=>$city_id])->value('spid');
            if($city_spid == 0){
                $city_spid = $city_id;
            }else{
                $city_spid .= $city_id;
            }
            $this->assign('city_id',$city_spid);
        }
        $space_info = model('poster_space')->get($space_id);
        switch ($space_info->getData('type')) {
            case 'text':
                $type = [
                    'text' => '文字'
                ];
                break;
            case 'code' :
                $type = [
                    'code' => '代码'
                ];
                break;
            case 'banner':
            case 'couplet':
                $type = [
                    'images' => '图片',
                    'flash' => '动画'
                ];
                break;
            default :
                $type = [
                    'images' => '图片'
                ];
                break;
        }
        $this->assign('space_type', $type);
        $this->assign('space_info', $space_info);
    }
    public function addDo(){
        \app\common\model\Poster::event('after_insert',function($obj){
            //注册后置事件 添加成功后更新广告位统计数量
            $id = $obj->spaceid;
            model('poster_space')->where('id',$id)->setInc('items');
            $this->save_cache($id);
            return true;
        });
        parent::addDo();
    }
    public function editDo(){
        \app\common\model\Poster::event('after_update',function($obj){
            $id = $obj->spaceid;
            $this->save_cache($id);
            return true;
        });
        parent::editDo();
    }
    //删除
    public function delete(){
        //注册后置事件 删除后更新广告位统计数量
        \app\common\model\Poster::event('after_delete',function($obj){
            $id = $obj->spaceid;
            $this->save_cache($id);
            model('poster_space')->where('id',$id)->setDec('items');
            model('attachment')->deleteAttachment('',$obj->setting['fileurl']);//删除图片
            return true;
        });
        parent::delete();
    }
    /**
     * 检查名称是否有重复
     */
    public function ajaxCheckName() {
        $name = input('param.name');
        $id = input('param.id/d');
        if ($this->name_exists($name,$id)) {
            $this->ajaxReturn(0, '广告已存在');
        } else {
            $this->ajaxReturn(1);
        }
    }

    private function name_exists($name,$id=0){
        $pk = $this->mod->getPk();
        $where['name'] = $name;
        $id && $where[$pk]    = ['neq',$id];
        $result=$this->mod->where($where)->count($pk);
        if($result){
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * 更新广告缓存
     * @param $spaceid
     * @param mixed
     * @author: al
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save_cache($spaceid){
        $cache_name = 'poster_img'.$spaceid;
        $poster_space = model('poster')->field('name,setting')->where([['spaceid','=',$spaceid],['startdate','<',time()],['enddate','>',time()],['status','=',1]])->select();
        \think\facade\Cache::set($cache_name,$poster_space);
    }
}