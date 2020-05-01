<?php
namespace app\common\model;

use think\model\concern\SoftDelete;
class Article extends \think\Model
{
    use SoftDelete;  //软删除
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = true;
    protected $createTime = false; //指定时间字段
   // protected $updateTime = 'update_time';
   /* protected $type = [
        'file'    =>  'serialize'
    ];*/
    protected $insert = ['editor','editor_id','ordid'];
    public function articleCate(){
        return $this->hasOne('article_cate','id','cate_id')->joinType('left');
    }
    protected function setCreateTimeAttr($value)
    {
        return strtotime($value);
    }
    protected function setEditorIdAttr()
    {
        $info = \org\Crypt::decrypt(session('adminInfo'));
        return $info['id'];
    }
    protected function setOrdidAttr()
    {
        return 1000;
    }
    protected function setEditorAttr()
    {
        $info = \org\Crypt::decrypt(session('adminInfo'));
        return $info['username'];
    }
    protected function setTagsAttr($value){
        return str_replace('，',',',$value);
    }
    protected function getInfoAttr($value){
        $replace = cache('keyword');
        if($replace){
            $tmp = [];
            foreach($replace as $v){
                $tmp[$v['word']] = $v['url'] ? '<a href="'.$v['url'].'" target="_blank" >'.$v['replace_word'].'</a>' : $v['replace_word'];
            }
            if($tmp){
                return strtr($value,$tmp);
            }
        }
        return $value;
    }
}