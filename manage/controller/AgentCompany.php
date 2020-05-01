<?php


namespace app\manage\controller;
use app\common\controller\ManageBase;
class AgentCompany extends ManageBase
{
    protected $beforeActionList = [
        'beforeAdd'=>['only'=>['add','edit']],
    ];
    public function beforeAdd()
    {
        $cate = model('agent_role')->order('id')->select();
        $this->assign('cate',$cate);
    }
    public function search()
    {
        $map = [];
        ($keyword = input('get.keyword')) && $map[] = ['company_name|contact_name','like', '%'.$keyword.'%'];
        $status = input('get.status');
        if(is_numeric($status))
        {
            $map['status'] = $status;
        }
        $this->assign('search', [
            'status'  => $status,
            'keyword' => $keyword
        ]);
        return $map;
    }
    public function addDo()
    {
        \app\common\model\AgentCompany::event('before_insert',function($obj){
            $obj->city = trim($obj->city,',');
            $obj->service_time_end = isset($obj->service_time_end)?strtotime($obj->service_time_end)+86399:strtotime('+1 year');
        });
        \app\common\model\AgentCompany::event('after_insert',function($obj){
            $data['company_id']  = $obj->id;
            $data['user_name']   = $data['true_name'] = $obj->contact_name;
            $data['mobile']      = $obj->mobile;
            $data['create_time'] = time();
            $data['surper_manager'] = 1;
            $data['password']       = $obj->password;
            model('agent')->save($data);
        });
        parent::addDo();
    }
    public function editDo()
    {
        \app\common\model\AgentCompany::event('before_update',function($obj){
            if(empty($obj->password)){
                unset($obj->password);
            }
            $obj->city = trim($obj->city,',');
            $obj->service_time_end = isset($obj->service_time_end)?strtotime($obj->service_time_end)+86399:strtotime('+1 year');
        });
        \app\common\model\AgentCompany::event('after_update',function($obj){
            $data['user_name']   = $data['true_name'] = $obj->contact_name;
            $data['mobile']      = $obj->mobile;
            isset($obj->password) && $data['password']       = $obj->password;
            model('agent')->save($data,['company_id'=>$obj->id,'surper_manager'=>1]);
        });
        parent::editDo();
    }
    public function ajaxEdit()
    {
        $mod = model('agent_company');
        $pk  = $mod->getPk();
        $id  = input($pk);
        $field = input('param.field');
        $val   = input('param.val');
        $mod->where([$pk => $id])->setField($field, $val);
        model('agent')->where(['company_id'=>$id])->setField($field, $val);
        return $this->ajaxReturn(1);
    }

}