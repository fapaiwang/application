<?php


namespace app\home\controller;


use app\common\controller\HomeBase;

class Recommend extends HomeBase
{
    public function initialize()
    {
        $this->cur_url = 'House';
        parent::initialize();
    }

    /**
     * @return mixed
     * 列表
     */
    public function index()
    {
        $sort  = input('param.sort/d',0);
        $join  = [['house h','h.id = p.house_id']];
        $field = "h.id,h.title,h.img,h.sale_status,h.red_packet,h.city,h.address,h.tags_id,h.price,h.unit,h.pano_url";
        $where = $this->search();
        $lists = model('position')->alias('p')
                                  ->join($join)
                                  ->field($field)
                                  ->where($where)
                                  ->order($this->getSort($sort))
                                  ->group('h.id')
                                  ->paginate(10);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('area',$this->getAreaByCityId());
        $this->assign('special',getLinkMenuCache(3));//特色
        $this->assign('type',getLinkMenuCache(2));//类型
        $this->assign('status',getLinkMenuCache(1));//销售状态
        $this->assign('renovation',getLinkMenuCache(8));//装修情况
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        return $this->fetch();
    }
    private function search()
    {
        $param['area']       = input('param.area/d', $this->cityInfo['id']);
        $param['price']      = input('param.price',0);
        $param['special']    = input('param.special',0);
        $param['type']       = input('param.type',0);//楼盘类型
        $param['status']     = input('param.status',0);//楼盘状态
        $param['renovation'] = input('param.renovation',0);//装修情况
        $param['sort']       = input('param.sort/d',0);//排序
        $data['h.status']    = 1;
        $seo_keys = '';
        $param['area'] == 0 && $param['area'] = $this->cityInfo['id'];
        if(!empty($param['status'])){
            $data['h.sale_status'] = $param['status'];
            $seo_keys .= '_'.getLinkMenuName(1,$param['status']);
        }
        if(!empty($param['renovation']))
        {
            $data['h.renovation'] = $param['renovation'];
            $seo_keys .= '_'.getLinkMenuName(8,$param['renovation']);
        }
        if(!empty($param['area'])){
            $data[] = ['h.city','in',$this->getCityChild($param['area'])];
            $rading = $this->getRadingByAreaId($param['area']);
            //读取商圈
            $param['rading'] = 0;
            if($rading && array_key_exists($param['area'],$rading))
            {
                $param['rading']  = $param['area'];
                $param['area']    = $rading[$param['area']]['pid'];
            }
            $param['area']!=$this->cityInfo['id'] && $seo_keys .= '_'.getCityName($param['area'],'').'推荐楼盘';
            $this->assign('rading',$rading);
        }
        if(!empty($param['price'])){
            $data[] = getHousePrice($param['price']);
            $price  = config('filter.house_price');
            isset($price[$param['price']]) && $seo_keys .= '_'.$price[$param['price']]['name'].'元/平方米';
        }
        if(!empty($param['special'])){
            $data[] = ['','exp',\think\Db::raw("find_in_set({$param['special']},h.tags_id)")];
            $seo_keys .= '_'.getLinkMenuName(3,$param['special']);
        }
        if(!empty($param['type'])){
            $data[] = ['','exp',\think\Db::raw("find_in_set({$param['type']},h.type_id)")];
            $seo_keys .= '_'.getLinkMenuName(2,$param['type']);
        }
        $seo_keys = trim($seo_keys,'_');
        $seo_keys && $this->setSeo(['seo_title'=>$seo_keys,'seo_keys'=>str_replace('_',',',$seo_keys)]);
        $data = array_filter($data);
        $search = $param;
        unset($param['rading']);
        $this->assign('search',$search);
        $this->assign('param',$param);
        return $data;
    }
    private function getSort($sort)
    {
        switch($sort)
        {
            case 0:
                $order = ['h.ordid'=>'asc','h.id'=>'desc'];
                break;
            case 1:
                $order = ['h.price'=>'asc','h.id'=>'desc'];
                break;
            case 2:
                $order = ['h.price'=>'desc','h.id'=>'desc'];
                break;
            case 3:
                $order = ['h.opening_time'=>'desc','h.id'=>'desc'];
                break;
            case 4:
                $order = ['h.opening_time'=>'asc','h.id'=>'desc'];
                break;
            case 5:
                $order = ['h.hits'=>'desc','h.id'=>'desc'];
                break;
            default:
                $order = 'h.ordid asc,h.id desc';
                break;
        }
        return $order;
    }
}