<?php



namespace app\home\controller;

class Ajax extends \think\Controller

{

    /**

     * 异步获取小区列表

     */


    public function ajaxGetEstate()

    {

        $keyword = input('get.keyword');

        $where['status'] = 1;

        $keyword && $where[] = ['title','like','%'.$keyword.'%'];

        $lists = model('estate')->where($where)->field('id,title,lng,lat,address,city')->paginate(10);

        $this->assign('lists',$lists);

        $this->assign('pages',$lists->render());

        return $this->fetch();

    }



	//在线咨询
	
	public function consults()

    {

		//print_r($_GET['id']);
		//exit();



        $url =$this->getSettingCaches($_GET['id'],$_GET['houseid']);
		
		
		
		

        if(!$url && !is_string($url))

        {

            return $this->fetch('public/404');

        }

        $this->assign('url',$url);

        $this->assign('title','在线咨询');

        return $this->fetch();

    }

	function getSettingCaches($key,$houseid)
	{
   
			//echo 123;
		
			if($key=='0'){
			
				$where['id']     = $houseid;

            
				$setting=model('second_house')->where($where)->field('online_consulting')->select();
				
				
				$info=$setting[0]['online_consulting'];
			
				
			
			}else{
			
				$where['id']     = $key;

            
				$setting=model('user')->where($where)->field('online_consulting')->select();
				
				
				$info=$setting[0]['online_consulting'];
			
			}
	
			
	
			//print_r($info);
	
	
	
	
			return $info;
	
	
    }


    /**

     * @return \think\response\Json

     * 获取城市下级区域

     */

    public function ajaxGetchilds() {

        $id = input('param.id/d',0);

        $where['pid'] = $id;

        $city_id = $this->getcityId();

        (!$id && $city_id) && $where['pid'] = $city_id;

        $result = model('city')->field('id,name,spid')->where($where)->select();

        $return['code'] = 0;

        if (!$result->isEmpty()) {

            $return['code'] = 1;

            $return['data'] = $result;

        }

        return json($return);

    }

    /**

     * @return \think\response\Json

     * 获取城市

     */

    public function ajaxGetCitychilds() {

        $id = input('param.id/d',0);

        $where['pid'] = $id;

        $city_id = $this->getcityId();

        (!$id && $city_id) && $where['pid'] = $city_id;

        $result = model('city')->field('id,name,spid')->where($where)->select();

        $return['code'] = 0;

        if (!$result->isEmpty()) {

            $return['code'] = 1;

            $return['data'] = $result;

        }

        return json($return);

    }

    /**

     * @return \think\response\Json

     * 获取城市坐标

     */

    public function getCityPoint()

    {

        $city = input('get.city/d',0);

        $return['code'] = 0;

        if($city)

        {

            $city_arr = getCity('cate');

            if(isset($city_arr[$city]))

            {

                $lng  = $city_arr[$city]['lng'];

                $lat  = $city_arr[$city]['lat'];

                $name = $city_arr[$city]['name'];

                $return['code'] = 1;

                $data           = ['lng'=>$lng,'lat'=>$lat,'name'=>$name];

                $return['data'] = $data;

            }

        }

        return json($return);

    }



    /**

     * @return \think\response\Json

     * 新房自动检索

     */

    public function searchHouse($city_id = 0)

    {

        $keyword         = input('get.keyword');

        $where['status'] = 1;

        $return['code']  = 0;

        $keyword && $where[] = ['title','like','%'.$keyword.'%'];

        $city = $this->getCityChild($city_id);

        $city && $where[] = ['city','in',$city];

        $lists = model('house')->where($where)->field('id,title,price,address,unit')->limit(10)->select();

        if(!$lists->isEmpty())

        {

            $return['code'] = 1;

            foreach($lists as &$v)

            {

                $v['url'] = url('House/detail',['id'=>$v['id']]);

            }

        }

        $return['data'] = $lists;

        return json($return);

    }

    /**

     * @return \think\response\Json

     * 二手房自动检索

     */

    public function searchSecond($city_id = 0){
        $keyword         = input('param.keyword');
        $types           = input('param.types');
        $where['status'] = 1;
        if(!empty($types)){
            $where['types'] = $types;
        }
        $return['code']  = 0;
        if (!empty($keyword)) {
                $keyword && $where[] = ['title|estate_name','like','%'.$keyword.'%'];
                $city = $this->getCityChild($city_id);
                $city && $where[] = ['city','in',$city];
                $where[] = ['timeout','gt',time()];
                $lists = model('second_house')->where($where)->field('id,title,price,address')->order('create_time','desc')->limit(10)->select();
                if(!$lists->isEmpty()) {
                    $return['code'] = 1;
                    foreach($lists as &$v) {
                        $v['url'] = url('Second/detail',['id'=>$v['id']]);
                    }
                }
        }
        $return['data'] = $lists;
        return json($return);
    }

    /**

     * @return \think\response\Json

     * 写字楼出售自动检索

     */

    public function searchOffice($city_id = 0)

    {

        $keyword         = input('get.keyword');

        $where['status'] = 1;

        $return['code']  = 0;

        $keyword && $where[] = ['title|estate_name','like','%'.$keyword.'%'];



        $city = $this->getCityChild($city_id);

        $city && $where[] = ['city','in',$city];

        $where[] = ['timeout','gt',time()];

        $lists = model('office')->where($where)->field('id,title,price,address')->limit(10)->select();

        if(!$lists->isEmpty())

        {

            $return['code'] = 1;

            foreach($lists as &$v)

            {

                $v['url'] = url('Office/detail',['id'=>$v['id']]);

            }

        }

        $return['data'] = $lists;

        return json($return);

    }

    /**

     * @return \think\response\Json

     * 写字楼出租自动检索

     */

    public function searchOfficeRental($city_id = 0)

    {

        $keyword         = input('get.keyword');

        $where['status'] = 1;

        $return['code']  = 0;

        $keyword && $where[] = ['title|estate_name','like','%'.$keyword.'%'];

        $city = $this->getCityChild($city_id);

        $city && $where[] = ['city','in',$city];

        $where[] = ['timeout','gt',time()];

        $lists = model('office_rental')->where($where)->field('id,title,price,address')->limit(10)->select();

        if(!$lists->isEmpty())

        {

            $return['code'] = 1;

            foreach($lists as &$v)

            {

                $v['url'] = url('OfficeRental/detail',['id'=>$v['id']]);

            }

        }

        $return['data'] = $lists;

        return json($return);

    }

    /**

     * @return \think\response\Json

     * 出租房自动检索

     */

    public function searchRental($city_id = 0)

    {

        $keyword         = input('get.keyword');

        $where['status'] = 1;

        $return['code']  = 0;

        $keyword && $where[] = ['title|estate_name','like','%'.$keyword.'%'];

        $city = $this->getCityChild($city_id);

        $city && $where[] = ['city','in',$city];

        $where[] = ['timeout','gt',time()];

        $lists = model('rental')->where($where)->field('id,title,price,address')->limit(10)->select();

        if(!$lists->isEmpty())

        {

            $return['code'] = 1;

            foreach($lists as &$v)

            {

                $v['url'] = url('Rental/detail',['id'=>$v['id']]);

            }

        }

        $return['data'] = $lists;

        return json($return);

    }

    /**

     * @return \think\response\Json

     * 写字楼出售自动检索

     */

    public function searchShops($city_id = 0)

    {

        $keyword         = input('get.keyword');

        $where['status'] = 1;

        $return['code']  = 0;

        $keyword && $where[] = ['title|estate_name','like','%'.$keyword.'%'];

        $city = $this->getCityChild($city_id);

        $city && $where[] = ['city','in',$city];

        $where[] = ['timeout','gt',time()];

        $lists = model('shops')->where($where)->field('id,title,price,address')->limit(10)->select();

        if(!$lists->isEmpty())

        {

            $return['code'] = 1;

            foreach($lists as &$v)

            {

                $v['url'] = url('Shops/detail',['id'=>$v['id']]);

            }

        }

        $return['data'] = $lists;

        return json($return);

    }

    /**

     * @return \think\response\Json

     * 写字楼出售自动检索

     */

    public function searchShopsRental($city_id = 0)

    {

        $keyword         = input('get.keyword');

        $where['status'] = 1;

        $return['code']  = 0;

        $keyword && $where[] = ['title|estate_name','like','%'.$keyword.'%'];

        $city = $this->getCityChild($city_id);

        $city && $where[] = ['city','in',$city];

        $where[] = ['timeout','gt',time()];

        $lists = model('shops_rental')->where($where)->field('id,title,price,address')->limit(10)->select();

        if(!$lists->isEmpty())

        {

            $return['code'] = 1;

            foreach($lists as &$v)

            {

                $v['url'] = url('ShopsRental/detail',['id'=>$v['id']]);

            }

        }

        $return['data'] = $lists;

        return json($return);

    }

    /**

     * @return \think\response\Json

     * 小区自动检索

     */

    public function searchEstate($city_id = 0)

    {

        $keyword         = input('get.keyword');

        $where['status'] = 1;

        $return['code']  = 0;

        $keyword && $where[] = ['title','like','%'.$keyword.'%'];

        $city = $this->getCityChild($city_id);

        $city && $where[] = ['city','in',$city];

        $lists = model('estate')->where($where)->field('id,title,price,address')->limit(10)->select();

        if(!$lists->isEmpty())

        {

            $return['code'] = 1;

            foreach($lists as &$v)

            {

                $v['url'] = url('Estate/detail',['id'=>$v['id']]);

                $v['price'] = '均价'.$v['price'].config('filter.house_price_unit');

            }

        }

        $return['data'] = $lists;

        return json($return);

    }



    /**

     * @param int $city_id

     * @return \think\response\Json

     * 自动检索学校

     */

    public function searchSchool($city_id = 0)

    {

        $keyword         = input('get.keyword');

        $where['status'] = 1;

        $return['code']  = 0;

        $keyword && $where[] = ['title','like','%'.$keyword.'%'];

        $city = $this->getCityChild($city_id);

        $city && $where[] = ['city','in',$city];

        $lists = model('school')->where($where)->field('id,title,address')->limit(10)->select();

        if(!$lists->isEmpty())

        {

            $return['code'] = 1;

            foreach($lists as &$v)

            {

                $v['url'] = url('School/detail',['id'=>$v['id']]);

            }

        }

        $return['data'] = $lists;

        return json($return);

    }

    /**

     * @return bool

     * 获取指定城市id下的所有区域

     */

    private function getCityChild($city_id = 0)

    {

        if(!$city_id)

        {

            $city_id = $this->getcityId();

        }

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

     * 获取城市id

     */

    private function getCityId()

    {

        $cityInfo = cookie('cityInfo');

        if(is_json($cityInfo))

        {

            $cityInfo = json_decode($cityInfo,true);

        }

        $city_id = $cityInfo ? $cityInfo['id'] : 0;

        return $city_id;

    }

}