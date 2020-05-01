<?php

namespace app\common\controller;


class ApiBase extends \think\Controller
{
    protected $city = 0;
    protected $defaultNoDomainImg = '/static/images/nopic.jpg';
    protected $cityInfo = [];
    public function __construct()
    {
        parent::__construct();
        $city = input('get.city/d',0);
        if(!$city)
        {
            $this->getDefaultCity();
        }else{
            $this->city = $city;
            $this->cityInfo = ['id'=>$city,'name'=>getCityName($city)];
        }
    }
    protected function getHost(){
        return "http://api.".config('url_domain_root');
    }
    protected function getImgUrl($img)
    {
        if($img && strpos($img,'http://') !== FALSE)
        {
            return $img;
        }else if(!$img || !file_exists('.'.$img)){
            return $this->getHost().$this->defaultNoDomainImg;
        }
        return $this->getHost().$img;
    }
    /**
     * @return bool
     * 获取指定城市id下的所有区域
     */
    protected function getCityChild($city_id = 0)
    {
        $city_id = $city_id ? $city_id : $this->city;
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
     * @param $info
     * @return mixed|string
     * 内容过滤
     */
    protected function filterContent($info){
        $ext = 'gif|jpg|jpeg|bmp|png';
        $temp = array();
        if(preg_match_all("/(href|src)=([\"|']?)([^ \"'>]+\.($ext))\\2/i", $info, $matches)){
            if(!empty($matches[3])){
                foreach($matches[3] as $v){
                    if(strpos($v,'://')===false){
                        $temp[$v] = $this->getImgUrl($v);
                    }
                }
            }
        }
        $parenter = array(
            "/\s(?!src)[a-zA-Z]+=[\'\"]{1}[^\'\"]+[\'\"]{1}/iu",
            "/<\/span>/i",
            "/<\/strong>/i",
            "/<\/font>/i",
            "/<br\/>/i",
            "/<\/a>/i"

        );
        $replace = array("$1","","","","","");
        $content = preg_replace($parenter,$replace,$info); ;
        $p = array(
            "<a>",
            "<span>",
            "<font>",
            "<strong>",
            "&nbsp;"
        );
        $r = array("","","","","");
        $content = str_replace($p,$r,$content);
        $content = strtr($content,$temp);
        return $content;
    }
    /**
     * @param $id
     * @param $tags
     * @param int $num
     * @return array
     * 楼盘标签
     */
    protected function getTags($id,$tags,$num = 4)
    {
        $data = [];
        if(is_array($tags))
        {
            foreach($tags as $k=>$v)
            {
                if($k>$num - 1)
                {
                    break;
                }
                $data[] = getLinkMenuName($id,$v);

            }
        }
        return $data;
    }

    /**
     * @param $data
     * @return mixed
     * 转换图片链接
     */
    protected function turnFileUrl($data)
    {
        if(is_array($data))
        {
            foreach($data as &$v)
            {
                $v['url'] = $this->getImgUrl($v['url']);
            }
        }else{
            $data = [];
        }
        return $data;
    }
    /**
     * @param $location
     * @return bool
     * 百度地图坐标转腾讯地图坐标
     */
    protected function turnLocation($location)
    {
        $url = "https://apis.map.qq.com/ws/coord/v1/translate?type=3&key=5HEBZ-OEA62-QOKU2-CUAOZ-D4IE6-JCFZV&locations=".$location;
        $ch = curl_init(); // 启动一个CURL会话
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $tmpInfo = curl_exec($ch);     //返回api的json对象
        //关闭URL请求
        curl_close($ch);
        $result = json_decode($tmpInfo,true);
        if($result['status'] == 0)
        {
            return $result['locations'];
        }else{
            \think\facade\Log::write($result['message'],'error');
            return false;
        }
    }

    /**
     * @return array|null|\PDOStatement|string|\think\Model
     * 首次进入获取默认城市
     */
    private function getDefaultCity()
    {
        $info =  model('city')->field('id,name')->where(['status'=>1,'pid'=>0])->order(['ordid'=>'asc','id'=>'asc'])->find();
        $this->city = $info['id'];
        $this->cityInfo = $info;
        return $info;
    }
}