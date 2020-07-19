<?php
namespace app\home\controller\user;

use app\common\controller\UserBase;

use app\common\service\PublishCount;

class Send extends UserBase
{
    private $allow ;
    public function initialize()
    {
        parent::initialize();

        $this->allow = ['second_house','rental'];

    }
    /**
     * 保存二手房
     */
    public function saveSecond()
    {
        if(request()->isPost())
        {
            $data = input('post.');
            $code   = 0;
            $msg    = '';
            $house_array = array();
            $house_array['elevator']         = $data['elevator'];
            $house_array['xiaci']            = $data['xiaci'];
            $house_array['qianfei']          = $data['qianfei'];
            $house_array['enforcement']      = $data['enforcement'];
            $house_array['land_purpose']     = $data['land_purpose'];
            $house_array['land_certificate'] = $data['land_certificate'];
            $house_array['property_no']      = $data['property_no'];
            $house_array['house_purpse']     = $data['house_purpse'];
            $house_array['management']       = $data['management'];
            $house_array['lease']            = $data['lease'];
            $house_array['sequestration']    = $data['sequestration'];
            $house_array['vacate']           = $data['vacate'];
            $house_array['mortgage']         = $data['mortgage'];
            $house_array['elevator_status']  = $data['elevator_status'];
            $house_array['toilet']  = $data['toilet'];
            $house_array['acreage']  = $data['acreage'];
            $house_array['price']  = $data['price'];
            $house_array['ckprice']  = $data['ckprice'];
            $house_array['floor']  = $data['floor'];
            $house_array['total_floor']  = $data['total_floor'];
            $house_array['orientations']  = $data['orientations'];

            if(isset($data['is_free'])){
                $house_array['is_free']         = $data['is_free'];
                unset($data['is_free']);
            }else{
                $house_array['is_free']         = 0;
            }
            if(isset($data['is_commission'])){
                $house_array['is_commission']   = $data['is_commission'];
                unset($data['is_commission']);
            }else{
                $house_array['is_commission']         = 0;
            }
            if(isset($data['is_school'])){
                $house_array['is_school']        = $data['is_school'];
                unset($data['is_school']);
            }else{
                $house_array['is_school']         = 0;
            }
            if(isset($data['is_metro'])){
                $house_array['is_metro']         = $data['is_metro'];
                unset($data['is_metro']);
            }else{
                $house_array['is_metro']         = 0;
            }
            $data['update_time'] = time();
            $house_array['update_time'] = time();
            $house_id = $data['id'];
            $back_url = $data['back_url'];
            if($data['decoration']==0){
                $decoration = '';
            }elseif($data['decoration']==1){
                $decoration = '精装';
            }elseif($data['decoration']==2){
                $decoration = '简装';
            }elseif($data['decoration']==3){
                $decoration = '毛坯';
            }

            $basic_info = array($data['house_property'],$data['years'],$data['orientations'],$decoration,$data['heating_mode'],
                $data['parking_information'],$data['developer'],$data['education'].$data['medical_care'],$data['shangchao'],$data['traffic']
            );
            $house_array['basic_info'] = implode('|',$basic_info);

            unset($data['elevator']);unset($data['xiaci']);unset($data['qianfei']);unset($data['back_url']);
            unset($data['enforcement']);unset($data['land_purpose']);unset($data['land_certificate']);unset($data['hxsimg']);
            unset($data['property_no']);unset($data['house_purpse']);unset($data['management']);unset($data['lease']);
            unset($data['sequestration']);unset($data['vacate']);unset($data['mortgage']);unset($data['id']);unset($data['years']);
            unset($data['toilet']);unset($data['acreage']);unset($data['price']);unset($data['ckprice']);unset($data['floor']);
            unset($data['total_floor']);unset($data['orientations']);unset($data['elevator_status']);unset($data['developer']);
            $broker_id = $this->userInfo['id'];
            $log_arr['operator'] = $broker_id;
            $log_arr['house_id'] = $house_id;
            $log_arr['type'] = 2;
            $log_arr['create_time'] = time();
            \think\Db::startTrans();
            try{
                $a = model('second_house')->where(['id'=>$house_id,'broker_id'=>$broker_id])->update($house_array);
                $b = model('second_house_data')->where(['house_id'=>$house_id])->update($data);
                $c = model('operatio_log')->insert($log_arr);
                if($a&&$b&&$c){
                    $code = 1;
                    $msg = '编辑房源信息成功';
                    \think\Db::commit();
                }else{
                    $code = 1;
                    $msg = '编辑房源信息失败';
                    \think\Db::rollback();
                }
            }catch(\Exception $e){
                    \think\facade\Log::record('编辑房源信息出错：'.$e->getMessage());
                    \think\Db::rollback();
                    $msg = $e->getMessage();
            }
            if($code == 1)
            {
                $this->success($msg,$back_url);
            }else{
                $this->error($msg,$back_url);
            }
        }
    }



    /**

     * 保存出租房

     */

    public function saveRental()

    {

        if(request()->isPost())

        {

            $data = input('post.');

            $code   = 0;

            $msg    = '';

            $status = getSettingCache('user','check_rental');

            $result = $this->validate($data,'app\manage\validate\Rental');//调用验证器验证

            if(true !== $result)

            {

                // 验证失败 输出错误信息

                $msg = $result;

            }else{

                $obj = model('rental');

                \think\Db::startTrans();

                try{

                    !empty($data['map']) && $location = explode(',',$data['map']);

                    $data['lng']     = isset($location[0]) ? $location[0] : 0;

                    $data['lat']     = isset($location[1]) ? $location[1] : 0;

                    $data['broker_id'] = $this->userInfo['id'];

                    $data['user_type'] = $this->userInfo['model'];

                    $data['status']    = is_numeric($status)?$status:1;

                    $img          = $this->uploadImg();

                    $data['file'] = $this->getPic();

                    if (!empty($_FILES['hxsimg']['name'])) {

               $hxsimg = request()->file('hxsimg');

               if($hxsimg){
                     $info = $hxsimg->move(env('root_path'). 'public/wj');
                 
                     if($info){      
                   
                          $image= $info->getSaveName();
                         $data['hxsimg']='/wj/'.$image;
                     } 
                     
                 }
             }
             

                    if(isset($data['id']) && $data['id'])

                    {

                        if(isset($data['timeout']) && $data['timeout']) {

                            $check = PublishCount::check($this->userInfo['id'], $this->userInfo['model']);

                            if($check['code'] == 1)

                            {

                                $img && $data['img'] = $img;

                                (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];

                                $data['ratio'] = $this->addHousePrice($data['id'], $data['price'], 'rental');

                                if ($obj->allowField(true)->save($data, ['id' => $data['id']])) {

                                    $this->optionHouseData($data['id'], $data, 'rental', true);

                                }

                                $code = 1;

                                $msg = isset($check['msg'])?'上架成功！'.$check['msg']:'编辑房源信息成功';

                            }else{

                                $msg = $check['msg'];

                            }

                        }else{

                            $img && $data['img'] = $img;

                            (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];

                            $data['ratio'] = $this->addHousePrice($data['id'], $data['price'], 'rental');

                            if ($obj->allowField(true)->save($data, ['id' => $data['id']])) {

                                $this->optionHouseData($data['id'], $data, 'rental', true);

                            }

                            $code = 1;

                            $msg = '编辑房源信息成功';

                        }

                    }else{

                        $check = PublishCount::check($this->userInfo['id'],$this->userInfo['model']);

                        if($check['code'] == 1)

                        {

                            $data['img'] = $img;

                            !isset($data['timeout']) && $data['timeout'] = 1;

                            (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];

                            if($obj->allowField(true)->save($data))

                            {

                                $house_id = $obj->id;

                                $this->optionHouseData($house_id,$data,'rental');

                                $this->addHousePrice($house_id,$data['price'],'rental');

                            }

                            $code = 1;

                            $msg = isset($check['msg'])?'发布成功！'.$check['msg']:'添加房源信息成功';

                        }else{

                            $msg = $check['msg'];

                        }



                    }

                    \think\Db::commit();

                }catch(\Exception $e){

                    \think\facade\Log::record('添加房源信息出错：'.$e->getMessage());

                    \think\Db::rollback();

                    $msg = $e->getMessage();

                }

            }

            $back_url = isset($data['back_url'])?$data['back_url']:null;

            if($code == 1)

            {

                $this->success($msg,$back_url);

            }else{

                $this->error($msg,$back_url);

            }

        }

    }

    /**

     * 异步删除图片

     */

    public function deleteImg(){

        $path = input('post.path');

        $id   = input('post.id/d',0);

        $field = input('post.field');

        $model = input('post.model','second_house');

        $return['code'] = 0;

        if($path){

            model('attachment')->deleteAttachment('',$path);

            if($id && $field && in_array($model,$this->allow)){

                model($model)->save([$field=>''],['id'=>$id]);

            }

            $return['code'] = 1;

        }else{

            $return['msg'] = '参数错误';

        }

        return json($return);

    }



    /**

     * @return \think\response\Json

     * 删除

     */

    public function delete()

    {

        $id    = input('param.id',0);

        $model = input('param.model','second_house');

        $return['code'] = 0;

        if(!$id)

        {

            $return['msg'] = '参数错误';

        }elseif(!in_array($model,$this->allow)){

            $return['msg'] = '不允许的数据模型';

        }else{

            $where[]       = ['id','in',$id];

            $where[]       = ['broker_id','eq',$this->userInfo['id']];

            $obj   = model($model);

            $lists = $obj->field('id,img')->where($where)->select();

            if($obj->where($where)->delete())

            {

                $this->deleteExtra($lists,$model);

                $return['code'] = 1;

                $return['msg'] = '删除成功';

            }else{

                $return['msg'] = '删除失败';

            }

        }

        return json($return);

    }



    /**

     * @param $data

     * @param $model

     * 删除扩展数据

     */

    private function deleteExtra($data,$model)

    {

        if(!$data->isEmpty())

        {

            foreach($data as $info)

            {

                $extra = model($model.'_data');

                $where = ['house_id'=>$info['id']];

                $detail = $extra::get($where);

                //删除房源图片

                if($extra->where($where)->delete())

                {

                    model('attachment')->deleteAttachment($detail['info'],$info['img'],$detail['file']);

                }

                //model('attachment')->deleteVideo($info['video']);//删除视频

                //删除价格数据

                db('house_price')->where(['house_id'=>$info['id'],'model'=>$model])->delete();

                //删除地铁关联数据

                \org\Relation::deleteByHouse($info['id'],$model);

                //删除学校关联数据

                \org\Relation::deleteByHouse($info['id'],$model,'school');

            }

        }

    }

    /**

     * @return string

     * 图片上传

     */

    private function uploadImg()

    {

        $img = '';

        if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {

            try{

                $dir  = "user/".$this->userInfo['id'];

                $file = request()->file('file');

                $upload = new \org\Storage();

                $upload->thumbUploadFile($file,$dir);

                $img = $upload->getFullName();

            }catch(\Exception $e){

                $this->error($e->getMessage());

            }

        }

        return $img;

    }

    /**

     * @param $house_id

     * @param $data

     * 添加扩展数据

     */

    private function optionHouseData($house_id,$data,$model = 'second_house',$update = false)

    {

        if (empty($data['seo']['seo_title'])) {

            $info['seo_title'] = $data['title'];

        }else{

            $info['seo_title'] = $data['seo']['seo_title'];

        }

        $info['house_id']  = $house_id;

        $info['info']      = isset($data['info']) ? $data['info'] : '';

        $info['file']      = $data['file'];//$this->getPic();

        isset($data['supporting']) &&  $info['supporting'] = implode(',',$data['supporting']);

        if($update)

        {

            model($model.'_data')->allowField(true)->save($info,['house_id'=>$house_id]);

        }else{

            model($model.'_data')->allowField(true)->save($info);

        }

        //关联学校

        \org\Relation::addSchool($model,$data['lng'],$data['lat'],$house_id,$data['city']);

        //关联地铁站

        \org\Relation::addMetro($model,$data['lng'],$data['lat'],$house_id,$data['city']);

    }



    /**

     * @param $house_id

     * @param $price

     * 添加价格

     */

    private function addHousePrice($house_id,$price,$model='second_house')

    {

        $priceObj  = model('house_price');

        $rate = 0;

        //读取上一次价格

        $prev_price = $priceObj->where(['house_id'=>$house_id,'model'=>$model])->order('create_time desc')->value('price');

        if($prev_price != $price && intval($price) > 0)

        {

            $data['price'] = $price;

            $data['create_time'] = time();

            $data['house_id'] = $house_id;

            $data['model']    = $model;

            //计算涨幅比

            $prev_price && $rate = number_format((($price - $prev_price) / $prev_price) * 100,1);

            $priceObj->removeOption();

            $priceObj->save($data);

        }

        return $rate;

    }

    /**

     * @param $obj

     * 添加图片

     */

    private function getPic(){

        $insert = [];

        if(isset($_POST['pic']) && !empty($_POST['pic'])) {

            $images = $_POST['pic'];

            foreach ($images as $key => $v) {

                $insert[] = [

                    'url' => $v['pic'],

                    'title' => $v['alt'],

                ];

            }

        }

        return $insert;

    }

}