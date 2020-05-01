<?php

namespace app\manage\controller;
use app\common\controller\ManageBase;
use QL\QueryList;
class Collection extends ManageBase
{
    protected $beforeActionList = [
        'beforeIndex' => ['only'=>'index'],
        'beforeEdit'  => ['only'=>'edit']
    ];
    protected function beforeIndex()
    {
        $big_menu = [
            'title' => '添加项目',
            'iframe' => url('Collection/add'),
            'id' => 'add',
            'normal' => true
        ];
        $this->_data = [
            'test' => [
                'c' => 'Collection',
                'a' => 'publicTest',
                'str' => '<a data-height="500" data-width="500" data-id="test" data-show_btn="false" data-uri="%s" data-title="测试 - %s" class="J_showDialog layui-btn layui-btn-xs layui-btn-normal" href="javascript:;">测试</a>',
                'param' => ['id'=>'@id@'],
                'isajax' => 1,
                'replace'=> ''
            ],
            'collection'    => [
                'c' => 'CollectionContent',
                'a' => 'index',
                'str'    => '<a href="%s" class="layui-btn layui-btn-xs">采集</a>',
                'param' => ['id'=>'@id@'],
                'isajax' => 0,
                'replace'=> ''
            ],
            'send'    => [
            'c' => 'CollectionPublish',
            'a' => 'index',
            'str'    => '<a href="%s" class="layui-btn layui-btn-xs">发布</a>',
            'param' => ['id'=>'@id@','status'=>1],
            'isajax' => 0,
            'replace'=> ''
            ]
        ];
        $this->assign('normal',true);
        $this->assign('big_menu', $big_menu);
    }
    protected function beforeEdit(){
        $id = input('param.id/d');
        $cate = model('collection')->field('cate_id')->where(['id'=>$id])->find();
        $spid = model('article_cate')->where(['id'=>$cate['cate_id']])->value('spid');
        if( $spid==0 ){
            $spid = $cate['cate_id'];
        }else{
            $spid .= $cate['cate_id'];
        }
        $this->assign('selected_ids',$spid);
    }
    //测试采集列表网址
    public function publicTest()
    {
        $id     = input('param.id/d',0);
        $info   = model('collection')->where('id',$id)->find();
        $domain = $info['supplement_url'];
        if(empty($info['pageurl_first']))
        {
            $url = str_replace("(*)",2,$info['pageurl']);
        }else{
            $url = $info['pageurl_first'];
        }
        $pathInfo = pathinfo($url);
        $path = $pathInfo['dirname'];
        try{
            $rule = [
                // 获取当前切片的整个text内容
                'title'=>array($info['url_area'],'text'),
                // 获取当前切片下的a标签链接
                'url'=>array($info['url_area'],'href')
            ];
            if(!empty($info['img_area']))
            {
                $rule['img'] = [$info['img_area'],'src'];
            }
            $ql = QueryList::get($url)->rules($rule)->range($info['list_area']);
            if($info['coding']!='utf-8')
            {
                $ql = $ql->encoding('UTF-8')->removeHead();
            }
            $ql = $ql->query()->getData(function($item) use($domain,$path){
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
                return $item;
            });
            $this->assign('url_lists',$ql->all());
            $this->assign('id',$id);
        }catch(\Exception $e){
            echo $e->getMessage();
        }
        return $this->fetch();
    }
    //测试采集内容
    public function publicTestContent()
    {
        $url = input('param.url');
        $url = base64_decode($url);
        $id  = input('param.id/d',0);
        $info = model('collection')->where('id',$id)->find();
        $domain = $info['supplement_img_url'];
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
            $ql = QueryList::get($url);
            if($info['coding'] != 'utf-8')
            {
                $ql = $ql->encoding('UTF-8')->removeHead();
            }
            $ql = $ql->rules($rule)->query()->getData();
            print_r($ql->all());exit;
        }catch(\Exception $e){
            echo $e->getMessage();
        }

    }
    public function ajaxGetTestLists()
    {
        $url = input('get.url');
        $page_start = input('get.start/d',1);
        $page_end   = input('get.end/d',2);
        $url = urldecode($url);
        $dataUrl = [];
        if($page_start && $page_end)
        {
            for($i = $page_start;$i <= $page_end;$i++)
            {
                $dataUrl[] = str_replace("(*)",$i,$url);
            }
        }
        $this->assign('urls',$dataUrl);
        return $this->fetch();
    }
    //删除项目
    public function delete()
    {
        \app\common\model\Collection::event('after_delete',function($obj){
            model('collection_content')->where('c_id',$obj->id)->delete();
            model('collection_history')->where('c_id',$obj->id)->delete();
        });
        parent::delete();
    }

    /**
     * @return \think\response\Json
     * 复制
     */
    public function publicCopy()
    {
        $id = input('param.id/d',0);
        $return['code'] = 0;
        if($id)
        {
            $obj  = model('collection');
            $info = $obj->where('id',$id)->find();
            if($info)
            {
                $data = $info->toArray();
                unset($data['id']);
                $obj->save($data);
                $return['code'] = 1;
                $return['msg']  = '复制成功';
            }
        }else{
            $return['msg'] = '参数错误';
        }
        return json($return);
    }
}