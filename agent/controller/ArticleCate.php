<?php

namespace app\agent\controller;
use app\common\controller\AgentBase;
class ArticleCate extends AgentBase
{

    public function ajaxGetchilds() {
        $id = input('param.id/d');
        $return = model('article_cate')->field('id,name')->where(['pid'=>$id])->select();
        if (!$return->isEmpty()) {
           return $this->ajaxReturn(1, 'success', $return);
        } else {
            return $this->ajaxReturn(0, 'error');
        }
    }

}