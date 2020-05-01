<?php
namespace app\common\service;

class Poster
{
    public function index($id,$city=0){
        $map['spaceid'] = $id;
        $map[]   = ['startdate','lt',time()];
        $map[]   = ['enddate','gt',time()];
        $map['status']    = 1;
        $map['city_id']      = $city ? $city : $this->getCityInfo();
        $space = $this->getSpaceInfo($id);
        if($space)
        {
            $num  = $space['display_num'];//显示数量从广告位表中读取
            $type = $space->getData('type');
            if($type == 'couplet'){
                return $this->getOne($id);
            }
            $obj = model('poster');
            $count = $obj->where($map)->count();
            if($count == 0){
                $map['city_id'] = 0;
            }
            $list = $obj->where($map)->field('id,name,type,setting')->order(['ordid'=>'asc','id'=>'desc'])->limit($num)->select();
            $temp = [];
            $poster = '';
            if($list){
                if($type == 'slide_pc' || $type == 'slide_mobile')
                {
                    return $this->slides($type,$list,$id);
                }
                foreach($list as $v){
                    switch($v->getData('type')){
                        case 'flash':
                            $str = $this->flash($v['setting'],$space);
                            break;
                        case 'images':
                            $str = $this->images($v,$space);
                            break;
                        case 'text':
                            $str = $this->text($v);
                            break;
                        case 'code':
                            $str = $this->code($v);
                            break;
                        default:
                            $str = $this->images($v,$space);
                            break;
                    }
                    $temp[] = $str;
                }
                $poster = 'document.write(\'<ul class="poster clearfix">'.implode('',$temp).'</ul>\');';
            }
            return $poster;
        }
        return '';
    }
    //对联广告只取一个一次
    public function getOne($id,$city=0){
        $map['spaceid']   = $id;
        $map[]   = ['startdate','lt',time()];
        $map[]   = ['enddate','gt',time()];
        $map['status']    = 1;
        $map['city_id']      = $city ? $city : $this->getCityInfo();
        $obj = model('poster');
        $count = $obj->where($map)->count();
        if($count == 0){
            $map['city_id'] = 0;
        }
        $info = $obj->where($map)->field('id,name,spaceid,type,setting')->order(['ordid'=>'asc','id'=>'desc'])->find();
        $str  = '';
        if($info){
            $space = $this->getSpaceInfo($info['spaceid']);
            switch($space->getData('type')){
                case 'couplet':
                    $str = $this->couplet($info,$space);
                    break;
                case 'text':
                    $str = $this->text($info);
                    break;
                case 'code' :
                    $str = $this->code($info);
                    break;
                case 'imagelist':
                    $str = $this->images($info,$space);
                    break;
                case 'banner':
                    $str = $this->banner($info,$space);
                    break;
                default:
                    $str = $this->images($info,$space);
                    break;
            }
        }
        return $str;
    }
    private function flash($data,$info){
        $flash = '<li><a href="'.$data['linkurl'].'" target="_blank"><object width="'.$info['width'].'px" height="'.$info['height'].'px" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">';
        $flash .= ' <param value="'.$data['fileurl'].'"  name="movie"><param value="high" name="quality">';
        $flash .= '<param value="transparent" name="wmode"><param value="8.0.35.0" name="swfversion">';
        $flash .= '<param value="/static/js/flash/expressInstall.swf" name="expressinstall">';
        $flash .= '<!--[if !IE]>--><object width="'.$info['width'].'px" height="'.$info['height'].'px" data="';
        $flash .= $data['fileurl'].'" type="application/x-shockwave-flash"><!--<![endif]--><param value="high" name="quality">';
        $flash .= '<param value="transparent" name="wmode"><param value="8.0.35.0" name="swfversion">';
        $flash .= '<param value="/static/js//flash/expressInstall.swf" name="expressinstall">';
        $flash .= '<div>您的浏览器<br>需要较新版本的<a href="http://www.adobe.com/go/getflashplayer">Adobe Flash Player</a>才能显示此处内容</div><!--[if !IE]>--></object><!--<![endif]--></object></a>';
        $flash .= '</li>';
        return $flash;
    }
    //对联广告
    private function couplet($data,$info){
        if($data->getData('type') == 'flash'){
            $str_left  = $this->flash($data['setting']['left'],$info);
            $str_right = $this->flash($data['setting']['right'],$info);
        }else{
            $str_left   = '<a href="'.$data['setting']['left']['linkurl'].'" target="_blank"><img alt="'.$data['setting']['left']['alt'].'" src="'.$data['setting']['left']['fileurl'].'" width="'.$info['width'].'" height="'.$info['height'].'px"/></a>';
            $str_right  = '<a href="'.$data['setting']['left']['linkurl'].'" target="_blank"><img alt="'.$data['setting']['right']['alt'].'" src="'.$data['setting']['right']['fileurl'].'" width="'.$info['width'].'" height="'.$info['height'].'px" /></a>';
        }
        $str = '$("body").append(\'<script src="/static/js/flash/couplet.js"></script>\');';
        //左面
        $str .= "theFloaters.addItem('followDiv1',".$info['setting']['paddleft'].",".$info['setting']['paddtop'].",".$info['width'].",".$info['height'].",'left','".$str_left."');";
        //右面
        $str .= "theFloaters.addItem('followDiv2',".$info['setting']['paddleft'].",".$info['setting']['paddtop'].",".$info['width'].",".$info['height'].",'right','".$str_right."');";
        $str .= "theFloaters.play();";
        return $str;
    }
    //文字广告
    private function text($data){
        $str = '<li><a href="'.$data['setting']['linkurl'].'" title="'.$data['setting']['title'].'" target="_blank">'.$data['setting']['title'].'</a></li>';
        return $str;
    }
    //代码广告
    private function code($data){
        return $data['setting']['code'];
    }
    //图片广告
    private function images($data,$info){
        $str = '<li><a href="'.$data['setting']['linkurl'].'" title="'.$data['setting']['alt'].'" target="_blank"><img alt="'.$data['setting']['alt'].'" src="'.$data['setting']['fileurl'].'" width="100%"  /></a></li>';
        return $str;
    }
    //横幅广告包括flash
    private function banner($data,$info){
        if($data->getData('type') == 'flash'){
            $str   = $this->flash($data['setting'],$info);
        }else{
            $str   = $this->images($data,$info);
        }
        return $str;
    }

    /**
     * @param $type
     * @param $data
     * @return string
     * 轮播图
     */
    private function slides($type,$data,$space_id = 0)
    {
        if($type == 'slide_pc')
        {
            return $this->getPcSlide($data,$space_id);
        }else{
            return $this->getMobileSlide($data);
        }
    }
    private function getPcSlide($data,$space_id)
    {
        $sid = "slider-".$space_id;
        $str = '$("head").append(\'<link rel="stylesheet" type="text/css" href="/static/css/jquery.slide-packer.css">\');';
        $str .= '$("body").append(\'<script type="text/javascript" src="/static/js/plugins/jquery.slide-packer.js"></script>\');';
        $temp = '<div class="slider '.$sid.'"><div class="switchable-box poster"><ul class="switchable-content">';
        foreach($data as $v)
        {
            $title = empty($v['setting']['alt'])?'':$v['setting']['alt'];
            $title = 'title="'.$title.'"';
            $temp .= '<li><a style="background-image:url('.$v['setting']['fileurl'].');" href="'.$v['setting']['linkurl'].'" title="'.$v['setting']['alt'].'" target="_blank"><img src="'.$v['setting']['fileurl'].'" '.$title.' width="100%"  /></a></li>';
        }
        $temp .='</li></ul><div class="ui-arrow"><a class="ui-prev"></a><a class="ui-next"></a></div></div></div>';
        $temp  = 'document.write(\''.$temp.'\');';
        $slide = "$(function(){
        $('body').find('.".$sid."').slide({
			effect: 'fade', // random/normal/scroll/fade/fold/slice/slide/shutter/grow
			speed: 'slow',
			navCls: 'switchable-nav',
			contentCls: 'switchable-content',
			caption: false,
			prevBtnCls:'ui-prev',
            nextBtnCls:'ui-next',
			evtype: 'click'
		});
        });";
        return $str.$temp.$slide;
    }
    private function getMobileSlide($data)
    {
        $str = '$("head").append(\'<link rel="stylesheet" type="text/css" href="/static/css/swiper.min.css">\');';
        $str .= '$("body").append(\'<script type="text/javascript" src="/static/js/plugins/swiper.min.js"></script>\');';
        $temp = '<div class="swiper-container poster"><div class="swiper-wrapper">';
        foreach($data as $v)
        {
            $temp .= '<div class="swiper-slide"><a href="'.$v['setting']['linkurl'].'" title="'.$v['setting']['alt'].'" target="_blank"><img alt="'.$v['setting']['alt'].'" src="'.$v['setting']['fileurl'].'" width="100%"  /></a></div>';
        }
        $temp .= '</div><div class="swiper-pagination"></div></div>';
        $temp  = 'document.write(\''.$temp.'\');';
        $slide = "$(function(){var mySwiper = new Swiper('.swiper-container', {
        spaceBetween: 30,
        effect: 'slide',
        centeredSlides: true,
        autoplay: {
            delay: 3500,
            disableOnInteraction: false
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true
        },
        loop : true,
        initialSlide :0
    });
    $('.swiper-container').hover(function(){
    mySwiper.autoplay.stop();
    },function(){
    mySwiper.autoplay.start();
    });
    });";
        return $str.$temp.$slide;
    }
    private function getSpaceInfo($id){
        $info = model('poster_space')->where(['id'=>$id,'status'=>1])->field('width,height,setting,type,display_num')->find();
        return $info;
    }
    private function getCityInfo(){
        $city = cookie('cityInfo');
        if(!$city){
            return 0;
        }
        $city = is_json($city)?json_decode($city,true):$city;
        return $city['id'];
    }
}