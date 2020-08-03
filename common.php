<?php

// 应用公共文件
//随机数
/**
 * @param int    $length 随机数长度
 * @param string $num    是否只包含数字
 *
 * @return null|string 返回随机字符串
 */
function codestr($length = 4, $num = '')
{
    $str = null;
    $strPol = $num == 1 ? '123456789' : "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol) - 1;
    for ($i = 0; $i < $length; $i++) {
        $str .= $strPol[rand(0, $max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }
    return $str;
}

/**
 * @param $obj
 *
 * @return mixed
 * 对象转换为数组
 */
function objToArray($obj)
{
    return json_decode(json_encode($obj), true);
}


/** 
 * 相加，供模板使用 
 * @param <type> $a 
 * @param <type> $b 
 */
function template_add($a,$b){ 
  echo(intval($a)+intval($b)); 
} 
/** 
 * 相减，供模板使用 
 * @param <type> $a 
 * @param <type> $b 
 */
function template_substract($a,$b){ 
  echo(intval($a)-intval($b)); 
}


function template_division($a,$b){ 
 	$aa=($a*10000/$b); 
 	$bb=round($aa);
 	echo ($bb);
}
/**
 * 字符串截取
 *
 * @param           $str
 * @param int       $start
 * @param           $length
 * @param string    $charset
 * @param bool|true $suffix
 *
 * @return string
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{
    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice . '...' : $slice;
}
function mbsubstr($str, $start = 0, $length)
{
    $res = mb_substr($str,$start,$length);
    return $res;
}

/**
 * @param        $list
 * @param string $pk
 * @param string $pid
 * @param string $child
 * @param int    $root
 *
 * @return array
 * 无限分类 数组转 树形结构
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
{
    // 创建Tree
    $tree = [];
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = [];
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[$data[$pk]] =& $list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][$data[$pk]] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * @param        $result
 * @param int    $parentid
 * @param string $format
 *
 * @return array
 * $list=recursion($result,0,'|--');
 */
function recursion($result, $parentid = 0, &$data = [], $format = "|--")
{
    /*记录排序后的类别数组*/
    //static $list=[];
    $icon = ['│', '├', '└'];
    $nbs = '&nbsp;&nbsp;';
    foreach ($result as $k => $v) {
        if ($v['pid'] == $parentid) {
            $v['name'] = isset($v['name']) ? $v['name'] : $v['title'];
            if ($parentid != 0) {
                $v['name'] = $format . $v['name'];
            }
            /*将该类别的数据放入list中*/
            $data[] = $v;
            recursion($result, $v['id'], $data, $format);
        }
    }

    return $data;

}


/**
 * @param $str
 *
 * @return string
 * 密码加密
 */
function passwordEncode($str, $code = '')
{
    return sha1($code . md5($str));
}

/**
 * @param $mobile
 *
 * @return bool
 * 验证手机号码是否正确
 */
function is_mobile($mobile)
{
    $reg = '/^1[3456789][0-9]{9}$/';
    if (!preg_match($reg, $mobile)) {
        return false;
    }
    return true;
}
/**
 * @param $mobile
 * @return int|string
 * 验证手机号码 是否存在
 */
function checkMobileIsExists($mobile)
{
    $where['mobile'] = $mobile;
    $count = model('user')->where($where)->count();
    $flag  = $count ? true : false;
    return $flag;
}
/**
 * @param $string
 * @return bool
 * 判断是否是json字符串
 */
function is_json($string)
{
    if(is_string($string))
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }else{
        return false;
    }

}
function hideMobile($mobile)
{
    $str = substr_replace($mobile,'****',3,4);
    return $str;
}
function hideStr($str)
{
    $start = msubstr($str,0,1,'utf-8',false);
    $last  = msubstr($str,-1,1,'utf-8',false);
    return $start.'***'.$last;
}
//只能获取一级菜单
/**
 * @param $menuId
 * @param string $field
 * @param string $type
 * @param int $selectId
 * @param string $disField
 * @param bool|true $render
 * @return array|string
 */
function getLinkMenu($menuId, $field = 'field', $type = 'select', $selectId = 0, $disField = 'id',$render = true)
{
    if (!$menuId) return [];
    $linkMenuData = cache('linkmenu_' . $menuId);
    if (!$linkMenuData) {
        $result = updateLinkMenuCache();
        if ($result) {
            $linkMenuData = cache('linkmenu_' . $menuId);
        } else {
            return '';
        }
    }

    $lists = $linkMenuData['cate'];
    if (empty($condition)) {
        $condition['pid'] = 0;
    }
    $str = '';
    switch ($type) {
        case 'select':
            $str = '<select name="' . $field . '" id="' . $field . '" lay-verify="' . $field . '">';
            $strs = $menuId != 1 ? '<option value="0">--请选择--</option>' : '';
            $str .= $strs;
            if (!empty($lists)) {
                foreach ($lists as $v) {
                    $s = ($selectId && $selectId == $v[$disField]) ? 'selected' : '';
                    $str .= '<option value="' . $v[$disField] . '" ' . $s . '>' . $v['name'] . '</option>';
                }
            }
            $str .= '</select>';
            break;
        case 'radio':
            if (!empty($lists)) {
                foreach ($lists as $v) {
                    $s = ($selectId && $selectId == $v[$disField]) ? 'checked' : '';
                    if($render)
                    {
                        $str .= '<input type="radio" name="' . $field . '" id="' . $field . $v[$disField] . '" value="' . $v['id'] . '" ' . $s . ' title=' . $v['name'] . '>';
                    }else{
                        $str .= '<label><input type="radio" name="' . $field . '" id="' . $field . $v[$disField] . '" value="' . $v['id'] . '" ' . $s . '>'.$v['name'].'</label>';
                    }
                }
            }
            break;
        case 'checkbox':
            if (!empty($lists)) {
                is_string($selectId) && $selectId = explode(',',$selectId);
                foreach ($lists as $v) {
                    $s = (is_array($selectId) && !empty($selectId)) ? (in_array($v[$disField], $selectId) ? 'checked' : '') : '';
                    if($render)
                    {
                        $str .= '<input type="checkbox" lay-skin="primary" name="' . $field . '[]" id="' . $field . $v[$disField] . '" value="' . $v[$disField] . '" ' . $s . ' title=' . $v['name'] . '>';
                    }else{
                        $str .= '<label><input type="checkbox" lay-skin="primary" name="' . $field . '[]" id="' . $field . $v[$disField] . '" value="' . $v[$disField] . '" ' . $s . '>'.$v['name'].'</label>';
                    }
                }
            }
            break;
        case 'array':
            $str = $lists;
            break;
        default :
            $str = '<select name="' . $field . '" id="' . $field . '"><option value="0">所有</option>';
            if (!empty($lists)) {
                foreach ($lists as $v) {
                    $s = ($selectId && $selectId == $v[$disField]) ? 'selected' : '';
                    $str .= '<option value="' . $v[$disField] . '" ' . $s . '>' . $v['name'] . '</option>';
                }
            }
            $str .= '</select>';
            break;
    }

    return $str;
}

/**
 * 获取拓展菜单名称
 *
 * @param int $linkMenuCateId 扩展菜单分类id
 * @param int $linkMenuId     拓展菜单id
 *
 * @return string
 */
function getLinkMenuName($linkMenuCateId, $linkMenuId)
{
    $linkMenuData = cache('linkmenu_' . $linkMenuCateId);
    if (!$linkMenuData) {
        $result = updateLinkMenuCache();
        if ($result) {
            $linkMenuData = cache('linkmenu_' . $linkMenuCateId);
        } else {
            return '';
        }
    }

    $linkMenu = $linkMenuData['cate'];
    if ($linkMenu && array_key_exists($linkMenuId, $linkMenu)) {
        return $linkMenu[$linkMenuId]['name'];
    } else {
        return '';
    }
}

/**
 * @param string $field
 * @return array|mixed
 * 获取城市列表
 */
function getCity($field = 'tree')
{
    $city = cache('city');
    if(!$city)
    {
        $lists = model('city')->field('id,pid,name,alias,is_hot,img,domain,lat,lng')->where('status',1)->select();
        if($lists){
            $cate = objToArray($lists);//普通列表
            $temp = [];
            foreach($cate as $v){
                $temp[$v['id']] = $v;
            }
            $tree = list_to_tree($temp);//树形列表
            $city = ['cate'=>$temp,'tree'=>$tree];
            cache('city',$city);
        }
    }
    if($city && array_key_exists($field,$city))
    {
        return $city[$field];
    }else{
        return $city;
    }
}
//获取扩展菜单缓存
function getLinkMenuCache($linkMenuCateId,$field = 'cate')
{
    $linkMenuData = cache('linkmenu_' . $linkMenuCateId);
    if (!$linkMenuData) {
        $result = updateLinkMenuCache();
        if ($result) {
            $linkMenuData = cache('linkmenu_' . $linkMenuCateId);
        } else {
            return '';
        }
    }

    return $linkMenuData[$field];
}
// 更新缓存中存放的拓展菜单数据
function updateLinkMenuCache()
{
    $cate = model('linkmenu_cate');
    $cate_list = $cate->where('status', 1)->select();
    if ($cate_list) {
        foreach ($cate_list as $val) {
            $lists = model('linkmenu')->field('id,pid,name,alias')->where(['status' => 1, 'menuid' => $val['id']])->select();
            if ($lists) {
                $cate = objToArray($lists);//普通列表
                $temp = [];
                foreach ($cate as $v) {
                    $temp[$v['id']] = $v;
                }
                $tree = list_to_tree($temp);//树形列表
                cache('linkmenu_' . $val['id'], ['cate' => $temp, 'tree' => $tree]);
            }
        }
        return true;
    }
    return false;
}
/**
 * @param $id
 * @param array $data
 * @return string
 * 获取城市名称
 */
function getCityName($id,$str='',$key = 0,&$data=[]){
    $city = cache('city')['cate'];

    if(!$city){
        $lists = model('city')->field('id,pid,name,alias')->order('ordid asc,id desc')->where('status',1)->select();
        if(!$lists->isEmpty()){
            $temp = [];
            foreach($lists as $v){
                $temp[$v['id']] = $v;
            }
            $tree = list_to_tree($temp);//树形列表
            cache('city',['cate'=>$temp,'tree'=>$tree]);
            $city = $temp;
        }else{
            return '';
        }
    }
    if(isset($city[$id])){
        if($city[$id]['pid']!=0){
            $data[] = $city[$id]['name'];
           
            getCityName($city[$id]['pid'],$str,$key,$data);
        }else{
            $data[] = $city[$id]['name'];
        }
    }
    krsort($data);
    if($key > 0)
    {
        unset($data[$key]);
    }

    // print_r($str);
    // print_r($data);exit();
    return implode($str,$data);
}
if(!function_exists('getCityNameByIds'))
{
    function getCityNameByIds($ids)
    {
        $city = getCity('cate');
        $ids  = array_filter(explode(',',$ids));
        $temp = [];
        if($ids)
        {
            foreach($ids as $v)
            {
                $temp[] = isset($city[$v])?$city[$v]['name']:'';
            }
        }
        return implode(',',$temp);
    }
}
/**
 * @param $id
 * @return mixed
 * 获取指定城市id的spid
 */
function getCitySpidById($id)
{
    $spid = model('city')->get_spid($id);
    return $spid;
}


function getTimesCha($sTime,$type='mohu'){
    $sTime=strtotime($sTime);
    if (!$sTime) return '';
    $cTime = time();
    $dTime = $cTime - $sTime;
    $dDay = intval(date("z", $cTime)) - intval(date("z", $sTime));
    $dYear = intval(date("Y", $cTime)) - intval(date("Y", $sTime));
    if($type == 'mohu'){
        if($dTime >= 0 && $dTime < 3600*24){
            return "2";//正在进行
        }elseif($dTime >= (3600*24)){
            return "3";//已结束
        }else{
            return "1";//即将开始
        }
    }
}

/**
 * @param $key
 * @param string $field
 * @return mixed
 * 获取站点配置
 */
function getSettingCache($key,$field='')
{
    $info = cache($key);
    if(!$info)
    {
        $setting = model('setting')->where(['name'=>$key])->field('data')->find();
        $info    = $setting['data'];
        cache($key,$info);
    }
    if($field && array_key_exists($field,$info))
    {
        return $info[$field];
    }
    return $info;
}



function getSettingCaches($key,$houseid)
{
    //$info = cache($key);
	
	
	//print_r($key);
	
	//print_r($field);
	
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
	//exit();
	
	
	//print_r($info);
	return $info;
	//exit();
	
    }






/**
 * @param string $model
 * @param string $field
 * @return array|mixed
 * 获取指定分类
 */
function getCate($model='articleCate',$field='cate')
{
    $obj = model($model);
    $cate = '';//cache($model);
    if(!$cate)
    {
        $lists = $obj->field('id,pid,name,alias')->where('status',1)->order("ordid asc")->select();
        if($lists){
            $cates = objToArray($lists);//普通列表
            $temp = [];
            foreach($cates as $v){
                $temp[$v['id']] = $v;
            }
            $tree = list_to_tree($temp);//树形列表
            $cate = ['cate'=>$temp,'tree'=>$tree];

            cache($model,$cate,3600);
        }
    }
    if($field && array_key_exists($field,$cate))
    {
        return $cate[$field];
    }
    return $cate;
}
/*
 * 读取分类名称
 */
function getCateName($model='articleCate',$id)
{
    $obj = model($model);
    $cate = cache($model);
    if(!$cate)
    {
        $lists = $obj->field('id,pid,name,alias')->where('status',1)->select();
        if($lists){
            $cates = objToArray($lists);//普通列表
            $temp = [];
            foreach($cates as $v){
                $temp[$v['id']] = $v;
            }
            $tree = list_to_tree($temp);//树形列表
            $cate = ['cate'=>$temp,'tree'=>$tree];
            cache($model,$cate,3600);
        }
    }
    $cate_cate = $cate['cate'];
    if(isset($cate_cate[$id])){
//        if($cate_cate[$id]['pid']!=0){
//            $data[] = $cate_cate[$id]['name'];
//            getCateName($model,$cate_cate[$id]['pid'],$ds,$data);
//        }else{
//            $data[] = $cate_cate[$id]['name'];
//        }
        return $cate_cate[$id]['name'];
    }
    //krsort($data);
   // return implode($ds,$data);
    return '';
}

/**
 * @param $sTime
 * @param string $type
 * @return bool|string
 * 计算时间差
 */
function getTime($sTime,$type='normal'){
    if (!$sTime) return '';
    $cTime = time();
    $dTime = $cTime - $sTime;
    $dDay = intval(date("z", $cTime)) - intval(date("z", $sTime));
    $dYear = intval(date("Y", $cTime)) - intval(date("Y", $sTime));
    if($type == 'normal'){
        if($dTime < 60){
            if($dTime < 10){
                return '刚刚';
            }else{
                return intval(floor($dTime / 10) * 10) . "秒前";
            }
        }elseif($dTime < 3600){
            return intval($dTime / 60) . "分钟前";
        }elseif($dYear == 0 && $dDay == 0){
            return '今天' . date('H:i', $sTime);
        }elseif($dYear == 0){
            return date("Y-m-d", $sTime);
        }else{
            return date("Y-m-d H:i", $sTime);
        }
    }elseif($type == 'mohu'){
        if($dTime < 60){
            return $dTime . "秒前";
        }elseif($dTime < 3600){
            return intval($dTime / 60) . "分钟前";
        }elseif($dTime >= 3600 && $dTime < 3600*24){
            return intval($dTime / 3600) . "小时前";
        }elseif($dTime >= (3600*24) && $dTime < 3600*7*24){
            return intval($dTime / (3600*24)) . "天前";
        }elseif($dTime >= (3600*24*7) && $dTime < 3600*24*30){
            return intval($dTime / (3600*24*7)) . "周前";
        }elseif($dTime >= (3600*24*30) && $dTime < 3600*24*30*12){
            return intval($dTime / (3600*24*30)) . "月前";
        }elseif($dTime >= (3600*24*30*12)){
            return intval($dTime / (3600*24*30*12)) . "年前";
        }/*elseif($dDay > 0 && $dDay <= 7){
            return intval($dDay) . "天前";
        }elseif($dDay > 7 && $dDay <= 30){
            return intval($dDay / 7) . '周前';
        }elseif($dDay > 30){
            return intval($dDay / 30) . '个月前';
        }*/
    }elseif($type == 'full'){
        return date("Y-m-d , H:i:s", $sTime);
    }elseif($type == 'ymd'){
        return date("Y-m-d", $sTime);
    }else{
        if($dTime < 60){
            return $dTime . "秒前";
        }elseif($dTime < 3600){
            return intval($dTime / 60) . "分钟前";
        }elseif($dTime >= 3600 && $dDay == 0){
            return intval($dTime / 3600) . "小时前";
        }elseif($dYear == 0){
            return date("Y-m-d", $sTime);
        }else{
            return date("Y-m-d", $sTime);
        }
    }
}
function getUrl($action,$key,$param,$val=''){
    $param[$key] = $val;
    // print_r($action);
    // print_r($key);
    // print_r($param);
    // print_r($val);
    return url($action,$param);
}

/**
 * @param $img
 * @param int $w
 * @param int $h
 * @param string $default
 * @return string
 * 生成缩略图
 */
function thumb($img,$w=100,$h=100,$default=''){
    $imgurl = parse_url($img);
    $host   = isset($imgurl['host']) ? 'http://'.$imgurl['host'] : '';
    $img    = $imgurl['path'];
    $default = $default ? $default : '/static/images/nopic.jpg';
    try{
        if(empty($img) || !file_exists(ltrim($img,'/'))){
            if($host)
            {
                return \org\Storage::thumb($host.$img,$default,$w, $h);
            }else{
                return $default;
            }
        }
        $img = ltrim($img,'/');
        $picinfo = getimagesize($img);
        if(!$picinfo){
            return $default;
        }
        $type    = image_type_to_extension($picinfo[2],false);
        if($type == 'gif')
        {
            return $host.'/'.$img;
        }
        $fun = "imagecreatefrom{$type}";
        if(!@$fun($img)){
            return $default;
        }
        $newimg_dir = dirname($img).'/thumb/'.$w.'_'.$h;
        if(!is_dir($newimg_dir)){
            mkdir($newimg_dir,0777,true);
        }
        $newimg = $newimg_dir.'/'.basename($img);
        if(file_exists($newimg)) return $host.'/'.$newimg;
        $imgObj = \think\Image::open($img);
        $imgObj->thumb($w, $h,\think\Image::THUMB_CENTER)->save($newimg);
    }catch(\Exception $e){
        \think\facade\Log::write('生成缩略图出错：'.$img.'===='.$e->getMessage());
        return $default;
    }
    return $host.'/'.$newimg;
}
/**
 * @param int $uid 会员id
 * @param int $w 头像规格 30*30 45*45 90*90 180*180
 * @return array|string
 * 获取用户头像 默认返回所有规格
 */
function getAvatar($uid=0,$w=0){
    $avatar = new \org\Avatar();
    $data  = $avatar->getAvatar($uid);
    if(is_array($data)){
        return array_key_exists($w,$data) ? $data[$w] : $data;
    }else{
        return $data;
    }
}
function getUnitData($key = '')
{
    $data = [
        1 => '元/㎡',
        2 => '万/套'
    ];
    if($key && isset($data[$key]))
    {
        return $data[$key];
    }
    return $data;
}

/**
 * @param string $key
 * @return array
 * 预约报名类型
 */
function subscribeType($key=''){
    $data = [
        1 => '预约看房',
        2 => '领取优惠',
        3 => '领取红包',
        4 => '团购报名'
    ];
    if($key && array_key_exists($key,$data)){
        return $data[$key];
    }else{
        return $data;
    }
}

/**
 * @param string $key
 * @return array
 * 商铺出租类别
 */
function getLeaseType($key = '')
{
    $data = [
        1 => '商铺出租',
        2 => '商铺转让'
    ];
    if($key && isset($data['key']))
    {
        return $data[$key];
    }
    return $data;
}
/**
 * @param $id
 * @param $model
 * 更新点击数
 */
function updateHits($id,$model)
{
    model($model)->where('id',$id)->setInc('hits');
}

/**
 * @return array
 * 房源有效期
 */
function getHouseTimeOut()
{
    $data = [
        1 => '1天',
        3 => '3天',
        5 => '5天',
        7 => '7天',
        10 => '10天',
        15 => '15天',
        20 => '20天',
        30 => '30天',
        -1 => '长期'
    ];
    return $data;
}

/**
 * @return array
 * 置顶时间
 */
function getTopTime()
{
    $data = [
        1 => '1天',
        2 => '2天',
        3 => '3天',
        5 => '5天',
        7 => '7天'
    ];
    return $data;
}
/**
 * @return array|PDOStatement|string|\think\Collection
 * 获取省份列表
 */
function getProvinceLists()
{
    return model('province')->getLists();
}

/**
 * @param $area_id
 * @return mixed
 * 根据区域id获取省份id
 */
function getProvinceIdByAreaId($area_id)
{
    return model('city')->getProvinceId($area_id);
}

/**
 * @param $url
 * @return string
 * 返回视频url
 */
function getVideoUrl($url)
{
    $setting = getSettingCache('storage');
    if($setting && $setting['open'] == 1){
        return $setting['domain'].'/'.$url;
    }else{
        return '';
    }

}
/**
 * @param string $key
 * @return mixed
 * 用户分类
 */
function getUserCate($key = '',$field = '')
{
    $data = cache('user_cate');
    if(!$data)
    {
        $lists = model('user_cate')->where('status',1)->order('ordid asc,id desc')->select();
        if(!$lists->isEmpty())
        {
            foreach($lists as $v)
            {
                $data[$v['id']] = $v;
            }
        }
        cache('user_cate',$data);
    }
    if(isset($data[$key]))
    {
        return $field && isset($data[$key][$field])?$data[$key][$field]:$data[$key];
    }
    return $data;
}
/**
 * @param string $key
 * @return array|mixed
 * 新房价格
 */
function  getHousePrice($key = ''){
    $data = config('filter.house_price');
    try{
        if($key){
            $len = count($data);
            if($key == $len){
                $str = ['h.price','egt',$data[$key]['value'][0]];
            }else{
                $str = ['h.price','between',$data[$key]['value']];
            }
        }else{
            $str = $data;
        }
        return $str;
    }catch(\Exception $e){
        \think\facade\Log::write($e->getMessage(),'error');
        return '';
    }

}
/**
 * @param string $key
 * @return array|mixed
 * 二手房价格
 */
function  getSecondPrice($key = '',$field = 'qipai'){
    $data = config('filter.second_qipai');
    try{
        if($key){
            $len = count($data);
            
            if($key == $len){
                $str = [$field,'egt',$data[$key]['value'][0]];
            }else{
                $str = [$field,'between',$data[$key]['value']];
            }
            // print_r($str);
        }else{
            $str = $data;
        }
        return $str;
    }catch(\Exception $e){
        \think\facade\Log::write($e->getMessage(),'error');
        return '';
    }

}

/**
 * @param string $key
 * @return array|mixed
 * 出租房租金
 */
function getRentalPrice($key = '',$field = 'price')
{
    $data = config('filter.rental_price');
    try{
        if($key){
            $len = count($data);
            if($key == $len){
                $str = [$field,'egt',$data[$key]['value'][0]];
            }else{
                $str = [$field,'between',$data[$key]['value']];
            }
        }else{
            $str = $data;
        }
        return $str;
    }catch(\Exception $e){
        return '';
    }

}
/**
 * @param string $key
 * @return array|mixed
 * 小区均价
 */
function getEstatePrice($key = '')
{
    $data = config('filter.estate_price');
    try{
        if($key){
            $len = count($data);
            if($key == $len){
                $str = ['price','egt',$data[$key]['value'][0]];
            }else{
                $str = ['price','between',$data[$key]['value']];
            }
        }else{
            $str = $data;
        }
        return $str;
    }catch(\Exception $e){
        \think\facade\Log::write($e->getMessage(),'error');
        return '';
    }
}

/**
 * @param string $key
 * @return array|mixed
 * 房龄
 */
function getYears($key = '')
{
    $data = config('filter.years');
    try{
        $year = date('Y');
        if($key)
        {
            $len = count($data);
            if($key == $len){
                $str = ['years','lt',$year - $data[$key]['value'][0]];
            }else{
                $str = ['years','between',[$year -$data[$key]['value'][1],$year - $data[$key]['value'][0]]];
            }
        }else{
            $str = $data;
        }
        return $str;
    }catch(\Exception $e){
        \think\facade\Log::write($e->getMessage(),'error');
        return '';
    }
}
/**
 * @param string $key
 * @return array|mixed
 * 户型
 */
function getRoom($key = '',$field = 'room')
{
    $data = config('filter.room');
    try{
        if($key){
            $len = count($data);
            if($key == $len){
                $str = [$field,'egt',$key];
            }else{
                $str = [$field,'eq',$key];
            }
        }else{
            $str = $data;
        }
        return $str;
    }catch(\Exception $e){
        \think\facade\Log::write($e->getMessage(),'error');
        return '';
    }
}

/**
 * @param string $key
 * @return array|mixed
 * 面积
 */
function getAcreage($key = '',$field = 'acreage')
{
    $data = config('filter.acreage');
    try{
        if($key){
            $len = count($data);
            if($key == $len){
                $str = [$field,'egt',$data[$key]['value'][0]];
            }else{
                $str = [$field,'between',$data[$key]['value']];
            }
        }else{
            $str = $data;
        }
        return $str;
    }catch(\Exception $e){
        \think\facade\Log::write($e->getMessage(),'error');
        return '';
    }

}

/**
 * @param string $key
 * @return array|mixed
 * 状态
 */
function getFcstatus($key = '',$field = 'fcstatus')
{
    $data = config('filter.fcstatus');
    try{
        if($key){
            $len = count($data);
            if($key == $len){
                $str = [$field,'egt',$key];
            }else{
                $str = [$field,'eq',$key];
            }
        }else{
            $str = $data;
        }
        return $str;
    }catch(\Exception $e){
        \think\facade\Log::write($e->getMessage(),'error');
        return '';
    }

}

/**
 * @param string $key
 * @return array|mixed
 * 获取条件
 */
function getBussinessCondition($type,$field='',$key = '')
{
    $data = config('filter.'.$type);
    try{
        if($key){
            $len = count($data);
            if($key == $len){
                $str = [$field,'egt',$data[$key]['value'][0]];
            }else{
                $str = [$field,'between',$data[$key]['value']];
            }
        }else{
            $str = $data;
        }
        return $str;
    }catch(\Exception $e){
        \think\facade\Log::write($e->getMessage(),'error');
        return '';
    }
}
function getCityInfoByCityId()
{
    if(request()->isMobile())
    {
        $param  = $_SERVER['REQUEST_URI'];//获取url参数 格式 为 ['/haikou_html'=>'']
        $domain = cookie('domain');//获取cokie存取的域名数据
        $param_a  = trim($param,'//');
        if($param_a){
                //$param = array_keys($param);//取出数组key 格式为 [0=>'/haikou_html']
                preg_match('/\/(.*)[\/|\_|\.]/U',$param,$match);//正则取出城市
            if($match)
            {
                $city = $match[1];//取同城市域名为 haikou
                //如果存在在cookie 并且从url中取得的城市域名不等于cookie保存的城市域名 则重新根据url城市域名获取城市信息
                if($domain)
                {
                    if($city && $city!=$domain['domain'])
                    {
                        $info = model('city')->where('domain',$city)->where('status',1)->field('id,name,domain')->find();
                        if($info)
                        {
                            //保存城市信息 到cookie
                            cookie('domain',$info->toArray());
                            return $info;
                        }
                    }else{
                        return $domain;
                    }
                }elseif($city){
                    $info = model('city')->where('domain',$city)->where('status',1)->field('id,name,domain')->find();
                    if($info)
                    {
                        //保存城市信息 到cookie
                        cookie('domain',$info->toArray());
                        return $info;
                    }
                }else{
                    $info =  model('city')->field('id,name,domain')->where(['status'=>1,'pid'=>0])->order(['ordid'=>'asc','id'=>'asc'])->find();
                    if($info)
                    {
                        cookie('domain',$info->toArray());
                        return $info;
                    }
                    return [];
                }
            }
            return [];
        }elseif($domain){
            return $domain;
        }else{
            $info =  model('city')->field('id,name,domain')->where(['status'=>1,'pid'=>0])->order(['ordid'=>'asc','id'=>'asc'])->find();
            if($info)
            {
                cookie('domain',$info->toArray());
                return $info;
            }
            return [];
        }
    }else{
        return false;
    }
}

/**
 * 等额本息
 * @param $dkm 贷款月数，20年就是240个月
 * @param $dkTotal 贷款总额
 * @param $dknl 贷款年利率
 * @param mixed
 * @author: al
 */
function debx($dkm,$dkTotal,$dknl){
//    $dkm     = 240; //贷款月数，20年就是240个月
//    $dkTotal = 1000000; //贷款总额
//    $dknl    = 0.059;  //贷款年利率
    $emTotal = $dkTotal * $dknl / 12 * pow(1 + $dknl / 12, $dkm) / (pow(1 + $dknl / 12, $dkm) - 1); //每月还款金额
    $lxTotal = 0; //总利息
    $arr[] = [];
    for ($i = 0; $i < $dkm; $i++) {
        $lx      = $dkTotal * $dknl / 12;   //每月还款利息
        $em      = $emTotal - $lx;  //每月还款本金
        $arr[$i]['qi'] = ($i + 1); //第几期
        $arr[$i]['benjing'] = sprintf("%.2f", $em); //本金
        $arr[$i]['lixi'] = sprintf("%.2f", $lx); //利息
        $arr[$i]['benxi'] = sprintf("%.2f", $emTotal);//总额
        $dkTotal = $dkTotal - $em;
        $lxTotal = $lxTotal + $lx;
        $arr[$i]['shengyu'] =sprintf("%.2f", $dkTotal);//剩余金额
    }
    $arr['total_num'] = sprintf("%.2f", $lxTotal);
    return $arr;
}

/**
 * 等额本金
 * @param $dkm
 * @param $dkTotal
 * @param $dknl
 * @param mixed
 * @return string
 * @author: al
 */
 function debj($dkm,$dkTotal,$dknl){
    $em      = $dkTotal / $dkm; //每个月还款本金
    $lxTotal = 0; //总利息
    $arr[] = [];
    for ($i = 0; $i < $dkm; $i++) {
        $lx      = $dkTotal * $dknl / 12; //每月还款利息
        $arr[$i]['qi'] = ($i + 1); //第几期
        $arr[$i]['benjing'] = sprintf("%.2f", $em); //本金
        $arr[$i]['lixi'] = sprintf("%.2f", $lx); //利息
        $arr[$i]['benxi'] =sprintf("%.2f", ($em + $lx));//总额
        $dkTotal -= $em;
        $arr[$i]['shengyu'] = sprintf("%.2f", $dkTotal);//剩余金额
        $lxTotal = $lxTotal + $lx;
    }
    $arr['total_num'] =sprintf("%.2f", $lxTotal);
    return $arr;
}

/**
 * 获取登录信息(用户)
 * @param mixed
 * @return mixed|string
 * @author: al
 */
function login_user(){
    $infos = cookie('userInfo');
    $infos = \org\Crypt::decrypt($infos);
    return $infos;
 }
function loginUser($user_id){
    $cache = "user_info_".$user_id;
    $infos = cache($cache);
    if (!$infos){
        return "";
    }
    $infos = \org\Crypt::decrypt($infos);
    return $infos;
}
/**
 * 创建数组
 * @param $from
 * @param $to
 * @param int $step
 * @param mixed
 * @return array
 * @author: al
 */
function array_range($from, $to, $step=1){
    $array = array();
    for ($x=$from; $x <= $to; $x += $step){
        $array[] = $x;
    }
    return $array;
}

/**
 * 根据id 获取法拍网类型
 * @param $id
 * @param mixed
 * @return mixed
 * @author: al
 */
function fa_option_type($id){
    if (empty($id)){
        return "";
    }
    $result = cache("fa_option_type");
    if (!$result){
        $list  = \think\Db::connect('db2')->field("id,title")->name('options')->select();
        foreach ($list as $k => $v) {
            $result[$v['id']]= $v["title"];
        }
        cache("fa_option_type",$result);
    }
    return $result[$id];
}
/**
 * @return bool
 * 获取指定城市id下的所有区域
 */
function getCityChild($city_id = 0){
    $city_id = $city_id ? $city_id : $this->cityInfo['id'];
    if($city_id) {
        $city_ids = cache('city_all_child_'.$city_id);
        if(!$city_ids){
            $city_ids = model('city')->get_child_ids($city_id,true);
            cache('city_all_child_'.$city_id,$city_ids,7200);
        }
        return $city_ids;
    }
    return false;
}

/**
 * 获取区域下的商圈
 * @param $area_id
 * @param mixed
 * @return array
 * @author: al
 */
function getRadingByAreaId($area_id){
    $city        = getCity();
    $city_cate   = getCity('cate');
    $rading      = [];
    $city_id     = 39;
    if(array_key_exists($area_id,$city_cate)) {
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
function mobileEncryption($mobile){
    $xing = substr($mobile,3,4);  //获取手机号中间四位
    $info = str_replace($xing,'****',$mobile);
    return $info;
}