<?php




namespace app\mobile\controller;





class Dialog extends \think\Controller

{
    private function getUserInfo()

    {

        $info = cookie('userInfo');

        $info = \org\Crypt::decrypt($info);
        return $info;

    }

    public function subscribe()

    {

        $house_id = input('get.house_id/d',0);

        $model    = input('get.model','house');

        $type     = input('get.type/d',0);

        $user     = getSettingCache('user');

        $userInfo          = $this->getUserInfo();

        $this->assign('data',[

            'house_id' => $house_id,

            'model'    => $model,

            'type'     => $type,

            'broker_id' => $this->getBrokerIdByHouseId($model,$house_id)

        ]);

        $where['house_id'] = $house_id; 
        $where['user_id'] = $userInfo['id'];
        $info = db("subscribe")->where($where)->count();

        if($info==0){
            $this->assign('user_setting',$user);
        }else{
            return '您已预约看房！';
            exit;
        }
        return $this->fetch();

    }

    private function getBrokerIdByHouseId($model,$id)

    {

        $allow     = ['house','second_house','rental'];

        $broker_id = 0;

        if(in_array($model,$allow))

        {

            $broker_id = model($model)->where('id',$id)->value('broker_id');

        }

        return $broker_id;

    }

}