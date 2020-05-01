<?php


namespace app\common\controller;





class MobileBase extends \think\Controller

{

    protected $site;

    private $controller;

    private $seo;

    protected $userInfo;

    protected $city_name;

    protected $cityInfo;

    



    public function initialize(){

        parent::initialize();

        $this->checkUserLogin();

        $site = getSettingCache('site');

        if($site['status'] == 0){

            die($site['reson']);

        }

        $this->site = $site;



        $qq = str_replace('，',',',$site['qq']);

        $qq = explode(',',$qq);

        $city = input('param.city/d',0);

        $domain = cookie('domain');

        //如果存在城市id则获取指定城市信息

        if($domain)

        {
            // echo "12355";exit();
// print_r($domain);exit();
            $this->getDomainByCity($domain['id']);

        }elseif($city){

            $this->getDomainByCity($city);

        }else{

            exit('error');

        }

        $this->seo = [

            'title'     => (isset($this->cityInfo['seo_title']))?$this->cityInfo['name'].$site['title']:$site['title'],

            'seo_title' => (isset($this->cityInfo['seo_title']) && !empty($this->cityInfo['seo_title']))?$this->cityInfo['seo_title']:$site['seo_title'],

            'seo_keys'  => (isset($this->cityInfo['seo_keys']) && !empty($this->cityInfo['seo_title']))?$this->cityInfo['seo_keys']:$site['seo_keys'],

            'seo_desc'  => (isset($this->cityInfo['seo_desc']) && !empty($this->cityInfo['seo_title']))?$this->cityInfo['seo_desc']:$site['seo_desc']

        ];

        $this->getWeixinJsSdk();

        $this->controller = strtolower(request()->controller());

        $this->getMenu();

        $this->setSeo();

        $this->assign('site',$site);

        $this->assign('qq',$qq);

        $this->assign('controller',$this->controller);

        $this->assign('city',getCity());

        $this->assign('cityInfo',$this->cityInfo);

        $this->assign('cityIds',implode(',',$this->getCityChild()));

    }



    //设置站点优化

    protected function setSeo($info='',$field='title',$q=''){

        if(!empty($info)){

            $info['title'] = isset($info['title']) ? $info['title'] : $info[$field];

            $seo['title'] = empty($info['seo_title']) ? $info[$field].'_'.$this->seo['title'] : $info['seo_title'].'_'.$this->seo['title'];

            $seo['keys']  = empty($info['seo_keys']) ? (empty($info['seo_title']) ? $info['title']:$info['seo_title']) : $info['seo_keys'];//$seoarr[$key]['keys'];

            $seo['desc']  = empty($info['seo_desc']) ? '' : $info['seo_desc'];

        }else{

            $site['title'] = empty($q) ? $this->seo['seo_title'].'_'.$this->seo['title'] : $q .'_'.$this->seo['title'];

            $site['keys']  = empty($this->seo['seo_keys']) ? $this->seo['seo_title'] : $this->seo['seo_keys'];

            $site['desc']  = empty($this->seo['seo_desc']) ? '' : $this->seo['seo_desc'];

            $seo = $site;

        }

        $this->assign('seo',$seo);

    }

    private function checkUserLogin(){

        $info = cookie('userInfo');

        $info = \org\Crypt::decrypt($info);

        $this->userInfo = $info;

        $this->assign('userInfo',$info);

    }



    /**

     * @return mixed

     * 空操作 找不到操作方法时执行

     */

    public function _empty(){

        return $this->fetch('public/404');

    }



    /**

     * @param $city_id

     * @return array

     * 根据城市id获取以应的区域

     */

    protected function getAreaByCityId()

    {

        $city = getCity();

        $city_cate = getCity('cate');

        $area      = [];

        $city_id   = $this->cityInfo['id'];

        if(array_key_exists($city_id,$city_cate))

        {

            $pid = $city_cate[$city_id]['pid'];

            if($pid == 0)

            {

                $area = isset($city[$city_id]['_child']) ? $city[$city_id]['_child'] : [];

            }else{

                $area = isset($city[$pid]['_child']) ? $city[$pid]['_child'] : [];

            }

        }

        return $area;

    }

    /**

     * @return bool

     * 获取指定城市id下的所有区域

     */

    protected function getCityChild($city_id = 0)

    {

        $city_id = $city_id ? $city_id : $this->cityInfo['id'];

        if($city_id)

        {

            $city_ids = cache('city_all_child_'.$city_id);

            if(!$city_ids)

            {

                $city_ids = model('city')->get_child_ids($city_id,true);

                cache('city_all_child_'.$city_id,$city_ids,7200);

            }

            return $city_ids;

        }

        return false;

    }



    /**

     * 读取站点导航

     */

    private function getMenu(){

        $nav = \app\common\service\NavCache::create();

        if($nav)

        {

            $menu = $nav['menu'];//一级导航

            $menu_child = $nav['child'];//二级导航

            $c = request()->controller();

            if($c !='Index'){

                if(isset($menu[$c]) || isset($menu_child[$c])){

                    (!isset($menu[$c]) && isset($menu_child[$c])) && $menu[$c] = $menu_child[$c];

                    $title = isset($this->cityInfo['seo_title'])?$this->cityInfo['name'].$this->site['title']:$this->site['title'];

                    $seo_title = empty($menu[$c]['seo_title']) ? $menu[$c]['title'] : $menu[$c]['seo_title'];

                    $seo_keys  = $menu[$c]['seo_keys'];

                    $seo_desc  = $menu[$c]['seo_desc'];

                    $this->seo = [

                        'title'     =>$title,

                        'seo_title' => str_replace("{city}",$this->cityInfo['name'],$seo_title),

                        'seo_keys'  => str_replace("{city}",$this->cityInfo['name'],$seo_keys),

                        'seo_desc'  => str_replace("{city}",$this->cityInfo['name'],$seo_desc)

                    ];

                }

            }

            if($c == 'Index' && $this->cityInfo['id'] > 0)

            {

                $this->seo = [

                    'title'     =>$this->cityInfo['name'].$this->site['title'],

                    'seo_title' => $this->cityInfo['seo_title'],

                    'seo_keys'  => $this->cityInfo['seo_keys'],

                    'seo_desc'  => $this->cityInfo['seo_desc'],

                ];

            }

            $this->assign('menu',$menu);

        }else{

            \think\facade\Log::write('导航读取错误','error');

            $this->assign('menu',null);

        }

    }

    private function getWeixinJsSdk()

    {

        $sdk_config = [];

        try{

            $config = getSettingCache('weixin');

            if($config)

            {

                $config['cache_path'] = env('runtime_path');

                $app = new \WeChat\Script($config);

                $sdk_config = $app->getJsSign(request()->url(true));

            }

        }catch(\Exception $e){

            \think\facade\Log::write($e->getMessage(),'error');

        }

        $this->assign('sdk_config',json_encode($sdk_config));

    }

    /**

     * @return array|null|\PDOStatement|string|\think\Model

     * 读取第一个默认城市

     */

    private function getDefaultCity()

    {

        $info =  model('city')->field('id,name,domain,seo_title,seo_keys,seo_desc')->where(['status'=>1,'pid'=>0])->order(['ordid'=>'desc','id'=>'asc'])->find();
        // print_r($info);exit();

        return $info;

    }

    //获取指定城市域名

    private function getDomainByCity($city_id){

        $info =  model('city')->where(['id'=>$city_id,'status'=>1])->field('id,name,domain,seo_title,seo_keys,seo_desc')->find();

        if(!$info){

            $info = $this->getDefaultCity();

        }

        $this->cityInfo = $info;

        cookie('domain',['domain'=>$info['domain'],'id'=>$info['id'],'name'=>$info['name']]);

    }

    /**

     * @return bool 根据ip定位城市

     * 暂未根据ip定位 首次进入 为排序最前的城市

     */

    private function getCityByIp()

    {

        // $ip       = request()->ip();

        // $city_arr = \util\Ip::find($ip);

        // $city     = $city_arr[2];

        // $uri      = request()->url();

        // $obj      = model('city');

        // $info     = $obj->where('name','like',$city.'%')->where('pid',0)->field('id,domain,name,seo_title,seo_keys,seo_desc')->find();
// print_r($info);exit();
        // if(!$info)

        // {

        //     $info = $this->getDefaultCity();

        // }
        $info = $this->getDefaultCity();

        $domain = $info['domain'];

        cookie('domain',$info);

        $url = $domain.$uri;

        $this->redirect($url);

        return true;

    }

}