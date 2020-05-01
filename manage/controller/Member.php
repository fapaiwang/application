<?php



namespace app\manage\controller;

use app\common\controller\ManageBase;

class Member extends ManageBase

{

    private $mod;

    public function initialize() {

        parent::initialize();

        $this->_name = 'user';

        $this->mod = model('user');

    }

    public function search(){

        $map = [];

        $model = input('get.model',1);

        $status = input('get.status');

        if(is_numeric($status))

        {

            $map['status'] = $status;

        }

        ($keyword = input('get.keyword')) && $map[] = ['user_name','like', '%'.$keyword.'%'];

       // $cate_id = input('get.cate_id');

        $model && $map[] = ['model','in',$model];

        $data = [

            'model'   => $model,

            'status'  => $status,

            'keyword' => $keyword

        ];

        $this->queryData = $data;

        $this->assign('search', $data);

        return $map;

    }

    public function addDo(){

        \app\common\model\User::event('after_insert',function($obj){

                $tags = input('post.tags/a');

                $info_data = [

                    'service_area' => trim(input('post.service_area'),','),

                    'history_complate'  => input('post.history_complate/d',0),

                    'looked'=> input('post.looked/d',0),

                    'tags'  => $tags?implode(',',$tags):'',

                    'description' => input('post.description'),
					
					'online_consulting' => input('post.online_consulting'),
                    
					'company' => input('post.company')

                ];

                $obj->userInfo()->save($info_data);

            $this->uploadAvatar($obj->id);



        });

        parent::addDo();

    }

    public function editDo(){

        \app\common\model\User::event('before_update',function($obj){

            if(empty($obj->password)){

                unset($obj->password);

            }

            return true;

        });

        \app\common\model\User::event('after_update',function(Member $member,$obj){

            if($obj->getData('model') == 4)

            {

                $tags = input('post.tags/a');

                $info_data = [

                    'service_area' => trim(input('post.service_area'),','),

                    'history_complate'  => input('post.history_complate/d',0),

                    'looked'=> input('post.looked/d',0),

                    'tags'  => $tags ? implode(',',$tags):'',

                    'description' => input('post.description'),
					
					'online_consulting' => input('post.online_consulting'),

                    'company' => input('post.company')

                ];

                $obj->userInfo->save($info_data);

            }

            $this->uploadAvatar($obj->id);

            $member->errMsg = '编辑成功';

            return true;

        });

        parent::editDo();

    }

    public function delete(){

        \app\common\model\User::event('after_delete',function($obj){

            //删除用户资料

            $obj->userInfo->delete();

        });

        parent::delete();

    }

    public function ajaxGetBroker()

    {

        $keyword = input('get.keyword');

        $sid = input('param.sid/d',0);

        $where['model']  = 2;

        $where['status'] = 1;

        $keyword && $where[] = ['nick_name','like','%'.$keyword.'%'];

        $lists = $this->mod->where($where)->field('id,nick_name,mobile')->order('id','desc')->paginate(15);

        $this->assign('lists',$lists);

        $this->assign('pages',$lists->render());

        $this->assign('sid',$sid);

        return $this->fetch();

    }

    /**

     * @return \think\response\Json

     * 验证用户是否注册

     */

    public function ajaxCheckUser(){

        $username = input('param.user_name');

        $mobile   = input('param.mobile');

        if($username){

            $map['user_name'] = $username;

        }elseif($mobile){

            if(is_mobile($mobile)){

                $map['mobile'] = $mobile;

            }else{

              return  $this->ajaxReturn(0);

            }

        }else{

           return $this->ajaxReturn(0);

        }

        $total = $this->mod->where($map)->count('id');

        if($total){

           return $this->ajaxReturn(0);

        }else{

           return  $this->ajaxReturn(1);

        }

    }

    /**

     * @return string

     * 头像上传

     */

    private function uploadAvatar($uid)

    {

        $img = '';

        if (isset($_FILES['avatar']) && !empty($_FILES['avatar']['name'])) {

            $file = request()->file('avatar');

            $dir  = config('uploads_path').'avatar/';

            // 移动到框架应用根目录/public/uploads/ 目录下

            $path = $dir.$uid.'/';

            $info = $file->validate(config('upload_img_rule'))->move(env('root_path') . $path,'avatar');

            if ($info) {

                $img = './uploads/' . 'avatar/'.$uid. '/' . str_replace('\\', '/', $info->getSaveName());

                $path = env('root_path').$path;

                $smallpath = $path.'30x30.jpg';

                $smallpath_45 = $path.'45x45.jpg';

                $smallpath_90 = $path.'90x90.jpg';

                $smallpath_180 = $path.'180x180.jpg';

                \think\Image::open($img)->thumb(30,30,\think\Image::THUMB_CENTER)->save($smallpath);

                \think\Image::open($img)->thumb(45,45,\think\Image::THUMB_CENTER)->save($smallpath_45);

                \think\Image::open($img)->thumb(90,90,\think\Image::THUMB_CENTER)->save($smallpath_90);

                \think\Image::open($img)->thumb(180,180,\think\Image::THUMB_CENTER)->save($smallpath_180);

            } else {

                // 上传失败获取错误信息

                $this->error($file->getError());

            }

        }

        return trim($img,'.');

    }

}