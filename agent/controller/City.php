<?php

namespace app\agent\controller;
use app\common\controller\AgentBase;
class City extends AgentBase
{
//前置操作定义
    private $mod;
    public function initialize(){
        parent::initialize();
        $this->mod = model('city');
    }
    public function ajaxGetchilds() {
        $id = input('param.id/d');
        $return['code'] = 0;
        $where['pid'] = $id;
        if($id == 0)
        {
            $where[] = ['id','in',$this->getAgentCity()];
        }
        $result = $this->mod->field('id,name,spid')->where($where)->select();
        if (!$result->isEmpty()) {
            $return['code'] = 1;
            $return['data'] = $result;
        }
        return json($return);
    }

    /**
     *获取代理城市
     */
    public function getAgentCity()
    {
        $agent_id = $this->agentInfo['company_id'];
        $city = model('agent_company')->where(['id'=>$agent_id])->value('city');
        return $city;
    }
}