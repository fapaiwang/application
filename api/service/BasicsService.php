<?php

namespace app\api\service;

class BasicsService{
    /**
     * 内容过滤
     * @param $info
     * @param mixed
     * @return mixed|null|string|string[]
     * @author: al
     */
    public function filterContent($info){

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
    protected function getHost(){

        return "http://api.".config('url_domain_root');

    }
}