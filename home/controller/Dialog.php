<?php




namespace app\home\controller;





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
// print_r(1111111111);
        $house_id = input('get.house_id/d',0);

        $model    = input('get.model','house');

        $type     = input('get.type/d',0);

        $user     = getSettingCache('user');

        $create_time = input('create_time');
        print_r($create_time);

        $userInfo          = $this->getUserInfo();

        $this->assign('data',[

            'house_id' => $house_id,

            'model'    => $model,

            'type'     => $type

        ]);
        
        $where['house_id'] = $house_id; 
        $where['user_id'] = $userInfo['id'];
        $info = db("subscribe")->where($where)->count();

        $i = db("subscribe")->where($where)->find();
        $i['create_time'] = date('Y-m',$i['create_time']);
        $tim = date('Y-m');

        if($info==0 || ($info==1 && ($tim !== $i['create_time']))){
            $this->assign('info',$this->getHouseInfo($house_id,$model));
            // print_r($this->getHouseInfo($house_id,$model));
            $this->assign('user_setting',$user);
        }else{
            return '您本月已预约看房！';
            exit;
        }

        return $this->fetch();

    }
    public function fydp()

    {
// print_r(1111111111);
        $house_id = input('get.house_id/d',0);

        $model    = input('get.model','house');

        $type     = input('get.type/d',0);

        $user     = getSettingCache('user');

        $this->assign('data',[

            'house_id' => $house_id,

            'model'    => $model,

            'type'     => $type

        ]);

        $this->assign('info',$this->getHouseInfo($house_id,$model));
// print_r($this->getHouseInfo($house_id,$model));
        $this->assign('user_setting',$user);

        return $this->fetch();

    }
    public function bj()

    {
// print_r(1111111111);

        $infos = cookie('userInfo');
        $infos = \org\Crypt::decrypt($infos);













        $house_id = input('get.house_id/d',0);

        $model    = input('get.model','house');

        $type     = input('get.type/d',0);

        $user     = getSettingCache('user');



        $bjpj=model('fydp')->where('house_id','eq',$house_id)->where('model','eq','second_house')->where('user_id','eq',$infos['id'])->group('user_id')->limit(1)->select();


        $this->assign('data',[

            'house_id' => $house_id,

            'model'    => $model,

            'type'     => $type

        ]);

        $this->assign('info',$this->getHouseInfo($house_id,$model));
// print_r($this->getHouseInfo($house_id,$model));
        $this->assign('user_setting',$user);
        $this->assign('bjpj',$bjpj[0]['house_name']);
         $this->assign('bjid',$bjpj[0]['id']);
        return $this->fetch();

    }

    private function getHouseInfo($house_id,$model)

    {

        $where['id'] = $house_id;

        $where['status'] = 1;

        $field = 'title,img,price,address,broker_id';

        $model == 'house' && $field .= ',unit';

        $info = model($model)->where($where)->field($field)->find();

        if($info)

        {

            $info = $info->toArray();

        }

        switch($model)

        {

            case 'house':

                $info['price'] = "<em class='price'>".$info['price']."</em>".$info['unit'];

                $info['price_txt'] = '均价';

                break;

            case 'second_house':

                $info['price'] = "<em class='price'>".$info['price']."</em>";

                $info['price_txt'] = '售价';

                break;

            case 'rental':

                $info['price'] = "<em class='price'>".$info['price']."</em>";

                $info['price_txt'] = '月租';

                break;

            case 'office':

                $info['price'] = "<em class='price'>".$info['price']."</em>";

                $info['price_txt'] = '售价';

                break;

            case 'office_rental':

                $info['price'] = "<em class='price'>".$info['price']."</em>";

                $info['price_txt'] = '月租';

                break;

            case 'shops':

                $info['price'] = "<em class='price'>".$info['price']."</em>";

                $info['price_txt'] = '售价';

                break;

            case 'shops_rental':

                $info['price'] = "<em class='price'>".$info['price']."</em>";

                $info['price_txt'] = '月租';

                break;

        }

        return $info;

    }

}