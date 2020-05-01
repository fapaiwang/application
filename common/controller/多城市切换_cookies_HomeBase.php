<?php



namespace app\common\controller;

use think\image\Exception;



class HomeBase extends \think\Controller

{

    protected $site;

    private $controller;

    private $seo;

    protected $userInfo;

    protected $city_name;

    protected $cityInfo;

    protected $cur_url;

    public function initialize(){

        parent::initialize();

        $this->checkUserLogin();

        $site = getSettingCache('site');

        if($site['status'] == 0){

            die($site['reson']);

        }

        !isset($site['city_domain']) && $site['city_domain'] = 0;

        $this->site = $site;



        $qq = str_replace('，',',',$site['qq']);

        $qq = explode(',',$qq);

        $secondDomain = Request()->panDomain();//获取二级域名前辍

      //echo "domain:".$secondDomain;

      //echo input('server.SERVER_NAME');

      //die();

      $domain=strtolower(input('server.SERVER_NAME'));

        $cityInfo = cookie('cityInfo');//如果不存在cookie或者cookie域名不等于当前请求域名前辍则重新获取当前域名对应的城市信息

       if(is_json($cityInfo))

       {

           $this->cityInfo = json_decode($cityInfo,true);

       }else{

           $this->cityInfo = $cityInfo;

       }

        if(($domain=="www.365fc.com" || $domain=="365fc.com") && $secondDomain=="" && $this->cityInfo['id'] == 0){$this->getCityByIp();die();}



        if(($this->cityInfo['id'] == 0 || $this->cityInfo['domain']!=$secondDomain) && $secondDomain!='www'){

            $this->getDomainByCity($secondDomain);

        }

        $this->cur_url = $this->cur_url?:request()->controller();

        $this->controller = strtolower($this->cur_url);

        $city_all_child   = $this->getCityChild();

        $this->seo = [

            'title'     => (isset($this->cityInfo['seo_title']))?$this->cityInfo['name'].$site['title']:$site['title'],

            'seo_title' => (isset($this->cityInfo['seo_title']) && !empty($this->cityInfo['seo_title']))?$this->cityInfo['seo_title']:$site['seo_title'],

            'seo_keys'  => (isset($this->cityInfo['seo_keys']) && !empty($this->cityInfo['seo_title']))?$this->cityInfo['seo_keys']:$site['seo_keys'],

            'seo_desc'  => (isset($this->cityInfo['seo_desc']) && !empty($this->cityInfo['seo_title']))?$this->cityInfo['seo_desc']:$site['seo_desc']

        ];

        $this->getMenu();

        $this->setSeo();

        $this->assign('site',$site);

        $this->assign('qq',$qq);

        $this->assign('controller',$this->controller);

        $this->assign('cityInfo',$this->cityInfo);

        $this->assign('cur_url',$this->cur_url);

        $this->assign('city_all_child',$city_all_child?implode(',',$city_all_child):'');

        $this->assign('top_nav_city',$this->getCity());

        $this->assign('cityId',$this->cityInfo['id']);

    }



    //设置站点优化

    protected function setSeo($info='',$field='title',$q=''){

        if(!empty($info)){

            $seo['title'] = empty($info['seo_title']) ? $info[$field].'_'.$this->seo['title'] : $info['seo_title'].'_'.$this->seo['title'];

            $seo['keys']  = empty($info['seo_keys']) ? (empty($info['seo_title']) ? $info[$field]:$info['seo_title']) : $info['seo_keys'];//$seoarr[$key]['keys'];

            $seo['desc']  = empty($info['seo_desc']) ? '' : $info['seo_desc'];//$seoarr[$key]['desc'];

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

            $this->assign('menu',$nav['menu']);

        }else{

            throw new \Exception('导航读取错误');

        }

    }

    /**

     * @return mixed

     * 空操作 找不到操作方法时执行

     */

    public function _empty(){

        return $this->fetch('public/404');

    }

    /**

     * @param $area_id

     * @return array

     * 获取区域下的商圈

     */

    protected function getRadingByAreaId($area_id)

    {

        $city        = getCity();

        $city_cate   = getCity('cate');

        $rading      = [];

        $city_id     = $this->cityInfo['id'];

        if(array_key_exists($area_id,$city_cate))

        {

            $pid = $city_cate[$area_id]['pid'];

            if($pid == $city_id)//如果 父级id==城市id  说明当前选择的是区域   否则当前选择的是商圈

            {

                $rading = isset($city[$pid]['_child'][$area_id]['_child']) ? $city[$pid]['_child'][$area_id]['_child'] : [];

            }else{

                $rading = isset($city[$city_id]['_child'][$pid]['_child']) ? $city[$city_id]['_child'][$pid]['_child'] : [];

            }



        }

        return $rading;

    }

    /**

     * @param $city_id

     * @return array

     * 根据城市id获取对应的区域

     */

    protected function getAreaByCityId($city_id = 0)

    {

        $city = getCity();

        $city_cate = getCity('cate');

        $area      = [];

        !$city_id && $city_id = $this->cityInfo['id'];

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

    //如果 不存在cookie 则读取全部城市

    private function getDomainByCity($second){

        if($second && $second != 'www'){

            $info =  model('city')->field('id,name,domain,seo_title,seo_keys,seo_desc')->where(['domain'=>$second,'status'=>1])->find();

            if(!$info){

                $info = $this->getDefaultCity();

            }

        }else{

            $info = $this->getDefaultCity();

        }

        $this->cityInfo = $info;

        cookie('cityInfo',$info);

    }



    /**

     * @return array|null|\PDOStatement|string|\think\Model

     * 读取第一个默认城市

     */

    private function getDefaultCity()

    {

        $info =  model('city')->field('id,name,domain,seo_title,seo_keys,seo_desc')->where(['status'=>1,'pid'=>0])->order(['ordid'=>'desc','id'=>'asc'])->find();

        return $info;

    }

    /**

     * 根据城市Id获取相关城市信息

     */

    private function getCityInfoById($id)

    {

        if($id)

        {

            $info =  model('city')->field('id,name,domain,seo_title,seo_keys,seo_desc')->where(['id'=>$id,'status'=>1])->find();

            if(!$info){

                $info = $this->getDefaultCity();

            }

        }else{

            $info = $this->getDefaultCity();

        }

        $this->cityInfo = $info;

        cookie('cityInfo',$info);

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

     * @return array

     * 按字母顺序排列的全部城市

     */

    private function getCity()

    {

        $city = getCity();

        $hot  = [];

        $city_arr = [];

        foreach($city as $v)

        {

            if($v['is_hot'] == 1)

            {

                $hot[] = $v;

            }

            $first = strtoupper(substr($v['alias'],0,1));

            $city_arr[$first][] = $v;

        }

        ksort($city_arr);

        return ['hot'=>$hot,'city'=>$city_arr];

    }

    

	private function getCityByIp()

    {

        $ip       = request()->ip();

        $city_arr = \util\Ip::find($ip);

        $city     = $city_arr[2];

        $uri      = request()->url();

        $obj      = model('city');

        $info     = $obj->where('name','like',$city.'%')->where('pid',0)->field('id,domain,name,seo_title,seo_keys,seo_desc')->find();

        if(!$info)

        {

            $info = $this->getDefaultCity();

        }

        $domain = $info['domain'];

        cookie('domain',$info->toArray());

      //echo "uri:".$uri;

      //echo "domain:".$domain;

      //exit();

        $domain1=strtolower(input('server.SERVER_NAME'));

        $domain1=str_ireplace("www.","",$domain1);

        $url = "http://".$domain.".".$domain1;

        //$url = $domain.$uri;

        $this->redirect($url);

        return true;

    }

}