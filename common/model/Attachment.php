<?php


namespace app\common\model;


class Attachment extends \think\Model
{
    /**
     * @param $info 富文本中的图片
     * @param $file 上传的多图片
     * 删除附件
     */
    public function deleteAttachment($info,$img = '',$file = '')
    {
        $data  = $this->getFileMd5($info,$file,$img);
        $lists = $this->where('md5','in',$data)->field('hash,url,save_space')->select();
        $tmp = [];
        if($lists)
        {
            if($this->where('md5','in',$data)->delete())
            {
                foreach($lists as $v)
                {
                    if($v['save_space'] == 2)
                    {
                        $tmp[] = $v['url'];
                    }else{
                        //本地删除
                        (is_file('.'.$v['url'])) && @unlink('.'.$v['url']);
                    }
                }
                if(!empty($tmp))
                {
                    //从云存储删除文件
                    $storage = new \org\Storage();
                    $storage->delete($tmp);
                }
            }

        }
    }

    /**
     * @param $data
     * 删除视频
     */
    public function deleteVideo($data)
    {
        $storage = new \org\Storage();
        $storage->delete($data);
    }
    private function getFileMd5($info,$file,$img)
    {
        $info_data = $this->filterContent($info);
        $temp = [];
        if($file)
        {
            if(is_string($file))
            {
                if(strpos($file,'://')===false){
                    $file = '.'.$file;
                }
                $temp[] =  @md5_file($file);
            }else{
                foreach($file as $v)
                {
                    if(strpos($v['url'],'://')===false){
                        $v['url'] = '.'.$v['url'];
                    }
                    $temp[] =  @md5_file($v['url']);
                }
            }
        }
        $data = array_merge($info_data,$temp);
        if(strpos($img,'://') === false)
        {
            $img = '.'.$img;
        }
        $data[] = @md5_file($img);
        return $data;
    }
    protected function filterContent($info){
        $ext = 'gif|jpg|jpeg|bmp|png';
        $temp = [];
        if(preg_match_all("/(href|src)=([\"|']?)([^ \"'>]+\.($ext))\\2/i", $info, $matches)){
            if(!empty($matches[3])){
                foreach($matches[3] as $v){
                    if(strpos($v,'://')===false){
                        $v = '.'.$v;
                    }
                    $temp[] = @md5_file($v);
                }
            }
        }
        return $temp;
    }
}