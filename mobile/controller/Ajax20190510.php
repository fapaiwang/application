<?php




namespace app\mobile\controller;

use app\common\controller\MobileBase;

class Ajax extends MobileBase

{

    /**

     * @return mixed

     * 在线咨询

     */

    public function consult()

    {


		

        $url = getSettingCache('site','online_consulting');

        if(!$url && !is_string($url))

        {

            return $this->fetch('public/404');

        }

        $this->assign('url',$url);

        $this->assign('title','在线咨询');

        return $this->fetch();

    }


	public function consults()

    {

		//print_r($_GET['id']);
		//exit();



        $url = getSettingCaches($_GET['id'],'online_consulting');
		
		//print_r($url);
		//exit();
		

        if(!$url && !is_string($url))

        {

            return $this->fetch('public/404');

        }

        $this->assign('url',$url);

        $this->assign('title','在线咨询');

        return $this->fetch();

    }




    /**

     * @return mixed

     * 全景相册

     */

    public function pano()

    {

        $url = input('get.pano_url');

        $this->assign('title','全景相册');

        $this->assign('url',base64_decode($url));

        return $this->fetch();

    }

    /**

     * 异步获取小区列表

     */

    public function ajaxGetEstate()

    {

        $return['code'] = 0;

        $keyword = input('get.keyword');

        $where['status'] = 1;

        $keyword && $where[] = ['title','like','%'.$keyword.'%'];

        $city = $this->getCityChild();

        $city && $where[] = ['city','in',$city];

        $lists = model('estate')->where($where)->field('id,title,lng,lat,address')->limit(10)->select();

        if($lists)

        {

            $return['code'] = 1;

            $return['data'] = $lists;

        }

       return json($return);

    }



    /**

     * @return \think\response\Json

     * 获取城市下级区域

     */

    public function ajaxGetchilds() {

        return action('home/Ajax/ajaxGetchilds');

    }

    /**

     * @return \think\response\Json

     * 获取城市

     */

    public function ajaxGetCitychilds() {

        return action('home/Ajax/ajaxGetCitychilds');

    }

    /**

     * @return \think\response\Json

     * 新房自动检索

     */

    public function searchHouse()

    {

        return action('home/Ajax/searchHouse',['city_id'=>$this->cityInfo['id']]);

    }

    /**

     * @return \think\response\Json

     * 二手房自动检索

     */

    public function searchSecond()

    {

        return action('home/Ajax/searchSecond',['city_id'=>$this->cityInfo['id']]);

    }

    /**

     * @return \think\response\Json

     * 出租房自动检索

     */

    public function searchRental()

    {

        return action('home/Ajax/searchRental',['city_id'=>$this->cityInfo['id']]);

    }

    /**

     * @return \think\response\Json

     * 写字楼出售自动检索

     */

    public function searchOffice()

    {

        return action('home/Ajax/searchOffice',['city_id'=>$this->cityInfo['id']]);

    }

    /**

     * @return \think\response\Json

     * 写字楼出租自动检索

     */

    public function searchOfficeRental()

    {

        return action('home/Ajax/searchOfficeRental',['city_id'=>$this->cityInfo['id']]);

    }

    /**

     * @return \think\response\Json

     * 商铺出租自动检索

     */

    public function searchShopsRental()

    {

        return action('home/Ajax/searchShopsRental',['city_id'=>$this->cityInfo['id']]);

    }

    /**

     * @return \think\response\Json

     * 商铺出售自动检索

     */

    public function searchShops()

    {

        return action('home/Ajax/searchShops',['city_id'=>$this->cityInfo['id']]);

    }

    /**

     * @return \think\response\Json

     * 小区自动检索

     */

    public function searchEstate()

    {

        return action('home/Ajax/searchEstate',['city_id'=>$this->cityInfo['id']]);

    }

}