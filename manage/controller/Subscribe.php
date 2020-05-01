<?php


namespace app\manage\controller;


use app\common\controller\ManageBase;

class Subscribe extends ManageBase
{
    /**
     * @return mixed
     * 预约列表
     */
    public function index()
    {
        $arr   = ['house','second_house','rental','office','office_rental','shops','shops_rental'];
        $where = $this->search();
        $type  = input('param.type','house');
        if(!in_array($type,$arr))
        {
            $type = 'house';
        }
        if($type == 'house')
        {
            $this->_data['broker'] = [
                    'c' => 'Member',
                    'a' => 'ajaxGetBroker',
                    'str'    => '<a data-height="500" data-width="500" data-id="add" data-uri="%s" data-title="选择经纪人" class="J_showDialog layui-btn layui-btn-xs" href="javascript:;">分配经纪人</a>',
                    'param' => ['sid'=>'@id@'],
                    'isajax' => 1,
                    'replace'=> ''
            ];
        }
        $this->_data['follow'] = [
            'c' => 'Subscribe',
            'a' => 'viewSubscribeFollow',
            'str'    => '<a data-height="500" data-width="500" data-id="add" data-uri="%s" data-title="查看跟进" class="J_showDialog layui-btn layui-btn-xs" href="javascript:;">看跟进</a>',
            'param' => ['sid'=>'@id@'],
            'isajax' => 1,
            'replace'=> ''
        ];
        $where['s.model'] = $type;
        $join  = [[$type.' h','h.id = s.house_id','left'],['user u','u.id = s.broker_id','left']];
        $field = 's.*,u.nick_name,h.title';
        $lists = model('subscribe')->alias('s')->join($join)->field($field)->where($where)->order('s.create_time desc')->paginate(20);
        $this->_exclude = 'edit';
        $this->assign('list',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('options',$this->check());
        $this->assign('type',$type);
        return $this->fetch();
    }

    /**
     * @return array
     * 搜索条件
     */
    public function search(){
        $map = [];
        $type = input('get.type');
        is_numeric($type) && $map['s.type'] = $type;
        $data['type'] = $type;
        $this->queryData = $data;
        $this->assign('search', $data);
        return $map;
    }

    /**
     * @return \think\response\Json
     * 分配经纪人
     */
    public function ajaxDistributionBroker()
    {
        $return['code'] = 0;
        $broker_id = input('get.broker_id/d',0);
        $sid       = input('get.sid/d',0);
        if($broker_id && $sid)
        {
            if(model('subscribe')->where('id',$sid)->setField('broker_id',$broker_id))
            {
                $return['code'] = 1;
                $return['msg']  = '分配成功';
            }else{
                $return['msg']  = '系统错误，分配失败！';
            }
        }else{
            $return['msg'] = '参数错误！';
        }
        return json($return);
    }

    /**
     * @return mixed
     * 查看跟进
     */
    public function viewSubscribeFollow()
    {
        $sid = input('param.sid/d',0);
        $lists = [];
        if($sid)
        {
            $lists = model('subscribe_follow')->where('sid',$sid)->field('broker_name,content,create_time')->order('create_time','desc')->select();
        }
        $this->assign('lists',$lists);
        return $this->fetch();
    }
}