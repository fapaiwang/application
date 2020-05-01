<?php


namespace app\manage\controller;


use app\common\controller\ManageBase;
use QL\QueryList;
class CollectionContent extends ManageBase
{
    private $urls = [];
    private $info;
    public function index()
    {
        $id = input('param.id/d',0);
        $this->assign('id',$id);
        return $this->fetch();
    }
    public function ajaxGetLists()
    {
        $this->getUrlLists();
        $page = input('get.page/d',0);
        $obj = model('CollectionContent');
        $history = model('collection_history');
        $return['code'] = 0;
        $return['url']  = '';
        if($this->urls)
        {
            if(isset($this->urls[$page]))
            {
                try{
                    $domain = $this->info['supplement_url'];
                    $id     = $this->info['id'];
                    $rule = [
                        // 获取当前切片的整个text内容
                        'title'=>array($this->info['url_area'],'text'),
                        // 获取当前切片下的a标签链接
                        'url'=>array($this->info['url_area'],'href')
                    ];
                    if(!empty($this->info['img_area']))
                    {
                        $rule['img'] = [$this->info['img_area'],'src'];
                    }
                    $pathInfo = pathinfo($this->urls[$page]);
                    $path = $pathInfo['dirname'];
                    $ql = QueryList::get($this->urls[$page]);
                    if($this->info['coding'] != 'utf-8')
                    {
                        $ql = $ql->removeHead()->encoding('UTF-8');
                    }
                    $ql = $ql->rules($rule)->range($this->info['list_area'])->query()->getData(function($item) use($domain,$id,$obj,$history,$path){
                        if(strpos($item['url'],"//") === FALSE)
                        {
                            if(strpos($item['url'],"../") !== FALSE)
                            {
                                $item['url'] = $domain.str_replace("../","",$item['url']);
                            }
                            if(strpos($item['url'],"./") !== FALSE)
                            {
                                $item['url'] = $path.str_replace("./","/",$item['url']);
                            }elseif(strpos($item['url'],"//") === FALSE){
                                $item['url'] = $domain.$item['url'];
                            }
                        }

                        $md5_url = md5($item['url']);
                        if($history->where('md5',$md5_url)->count() == 0)
                        {
                            $data['c_id']  = $id;
                            $data['title'] = $item['title'];
                            $data['url']   = $item['url'];
                            if(isset($item['img']))
                            {
                                if(strpos($item['img'],'//') === FALSE)
                                {
                                    $url_arr = parse_url($item['url']);
                                    $domains  = $url_arr['host'];
                                    $img     = $domains.$item['img'];
                                }else{
                                    $img     = $item['img'];
                                }
                                $data['img'] = $this->download($img);
                            }
                            $obj->isUpdate(false)->save($data);
                            $history->isUpdate(false)->save(['md5'=>$md5_url,'c_id'=>$id]);
                            unset($history->id);
                            unset($obj->id);
                        }
                        return $item;
                    });
                    $url_lists = $ql->all();
                    $page++;
                    $return['code'] = 1;
                    $return['data'] = $url_lists;
                    $return['page'] = $page;
                }catch(\Exception $e){
                    $return['msg'] = "<span style='color:#ff0000;'>采集列表网址出错</span>";//$e->getMessage();
                    \think\facade\Log::write('采集列表网址出错：'.$e->getMessage(),'error');
                }
            }else{
                $return['code'] = 2;
                $return['url'] = url('publicGetContent',['c_id'=>$this->info['id']]);
            }
        }else{
            $return['msg'] = '获取列表页网址出 错';
        }
        return json($return);
    }

    public function publicGetContent()
    {
        $c_id = input('param.c_id/d',0);
        $this->assign('id',$c_id);
        return $this->fetch();
    }
    /**
     * 采集内容
     */
    public function ajaxGetContent()
    {
        $c_id = input('param.c_id/d',0);
        $obj  = model('collection_content');
        $pargram = model('collection');
        $return['code'] = 0;
        if($c_id){
            $info    = $pargram->where('id',$c_id)->find();
            $url_arr = parse_url($info['pageurl']);
            $domain  = $url_arr['host'];
            $content = $obj->where('c_id',$c_id)->where('status',0)->order('id asc')->find();
            if($content)
            {
                $rule = [
                    // 获取当前切片的整个text内容
                    'title'=>array($info['title_area'],'text'),
                    'content' => array($info['content_area'],'html',$info['filter_content'])
                ];
                if(!empty($info['create_time_area']))
                {
                    $rule['create_time'] = [$info['create_time_area'],'text'];
                }
                try{
                    $ql = QueryList::get($content['url']);
                    if($info['coding'] != 'utf-8')
                    {
                        $ql = $ql->encoding('UTF-8')->removeHead();
                    }
                    $ql = $ql->rules($rule)->query()->getData(function($item) use($content,$info,$obj,$domain){
                        if(isset($item['create_time']))
                        {
                            $creat_time = str_replace(['年','月','日'],['-','-',''],$item['create_time']);
                            $data['create_time'] = strtotime($creat_time) ? $creat_time : date("Y-m-d H:i:s");
                        }else{
                            $data['create_time'] = date("Y-m-d H:i:s");
                        }
                        if($item['title'] && $item['content'])
                        {
                            $data['title'] = $item['title'];
                            $data['info']  = $info['download_pic'] == 1 ? $this->downloadContentImg($item['content'],$domain) : $item['content'];
                            $data['img']   = $content['img'];
                            $data['description'] = msubstr(strip_tags($item['content']),0,100);
                            $data['cate_id'] = $info['cate_id'];
                            $data['city']  = $info['city'];
                            $obj->save(['data'=>$data,'status'=>1],['id'=>$content['id']]);
                            unset($obj->id);
                        }
                        return $item['title'];
                    });
                    $return['code'] = 1;
                    $return['data'] = $ql->all();
                }catch(\Exception $e){
                    $obj->save(['status'=>1],['id'=>$content['id']]);
                    $return['code'] = 1;
                    $return['data'] = [$content['url'].'--采集内容出错'];
                    \think\facade\Log::write('采集内容出错：'.$content['id'].'--'.$content['url']);
                }
            }else{
                $pargram->save(['lastdate'=>time()],['id'=>$c_id]);
                $return['code'] = 2;
                $return['msg']  = '<span style="color:#ff0000">内容采集完成</span>';
            }
        }else{
            $return['msg'] = '参数错误';
        }
        return json($return);
    }

    /**
     * 生成列表网址
     */
    private function getUrlLists()
    {
        $id  = input('param.id/d',0);
        $info = model('collection')->where('id',$id)->find();
        $dataUrl = [];
        $this->info = $info;
        try{
            if($info['pagesize_start'] && $info['pagesize_end'])
            {
                for($i = $info['pagesize_start'];$i <= $info['pagesize_end'];$i++)
                {
                    $dataUrl[] = str_replace("(*)",$i,$info['pageurl']);
                }
            }
            if(!empty($info['pageurl_first']))
            {
                $dataUrl[] = $info['pageurl_first'];
            }
            $this->urls = $dataUrl;
        }catch(\Exception $e){
            \think\facade\Log::write('采集列表出错：'.$e->getMessage(),'error');
        }
    }

    /**
     * @param $img
     * @return string
     * 下载图片
     */
    private function download($img)
    {
        $dir = '/uploads/article/'.date('Ymd').'/';
        if(!is_dir($dir))
        {
            @mkdir('.'.$dir,0777,true);
        }
        $img = preg_replace("/\?.*/","",$img);
        $file_name = basename($img);
        $file      = $dir.$file_name;
        if(@copy($img,'.'.$file))
        {
            return $file;
        }
        return '';
    }
    private function downloadContentImg($info,$domain)
    {
        $ext = 'gif|jpg|jpeg|bmp|png';
        $temp = [];
        if(preg_match_all("/(href|src)=([\"|']?)([^ \"'>]+\.($ext))\\2/i", $info, $matches)){
            if(!empty($matches[3])){
                foreach($matches[3] as $v){
                    if(strpos($v,'//')===FALSE)
                    {
                        $img = $domain.$v;
                    }else{
                        $img = $v;
                    }
                    $temp[$v] = $this->download($img);
                }
                $info = strtr($info,$temp);
            }
        }
        return $info;
    }
}