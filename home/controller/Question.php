<?php


namespace app\home\controller;


use app\common\controller\HomeBase;

class Question extends HomeBase
{
    public function index()
    {
        $answer = input('param.answer/d',0);
        $where['q.status'] = 1;
        if($answer == 2)
        {
            $where['q.reply_num'] = 0;
        }elseif($answer == 1){
            $where[] = ['q.reply_num','gt',0];
        }
        $join  = [
           ['house h','h.id = q.house_id'],
        ];
        $city = $this->getCityChild();
        $city && $where[] = ['city_id','in',$city];
        $field = 'h.title,q.content,q.house_id,q.id,q.create_time,q.reply_num';
        $lists = model('question')->alias('q')->where($where)->field($field)->join($join)->order(['q.create_time'=>'desc','id'=>'desc'])->paginate(15);
        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('answer',$answer);
        $this->assign('hot_question',$this->getHotQuestionHouse());
        return $this->fetch();
    }

    /**
     * @return mixed
     * 问题详情页
     */
    public function detail()
    {
        $id = input('param.id/d',0);
        if($id)
        {
            $where['q.id'] = $id;
            $where['q.status'] = 1;
            $field = 'h.title,q.*';
            $join = [['house h','h.id = q.house_id']];
            $info = model('question')->alias('q')->where($where)->join($join)->field($field)->find();
            if(!$info)
            {
                return $this->fetch('public/404');
            }
            model('question')->where('id',$id)->setInc('hits');
            $this->assign('info',$info);
            $this->assign('hot_question',$this->getHotQuestionHouse());
            $this->assign('answer',$this->getAnswerById($info['id']));
            $this->assign('relation',$this->relationQuestion($id));
        }else{
            return $this->fetch('public/404');
        }

        return $this->fetch();
    }

    /**
     * @param $id
     * @return \think\Paginator
     * 回答列表
     */
    private function getAnswerById($id)
    {
        $where['question_id'] = $id;
        $where['status']      = 1;
        $lists = model('answer')->where($where)->order('create_time','desc')->paginate(5);
        return $lists;
    }

    /**
     * @param $id
     * @return array|\PDOStatement|string|\think\Collection
     * 相关问答
     */
    private function relationQuestion($id)
    {
        $where['status'] = 1;
        $where[] = ['id','neq',$id];
        $city = $this->getCityChild();
        $city && $where[] = ['city_id','in',$city];
        $lists = model('question')->where($where)->field('id,content')->order('create_time','desc')->limit(5)->select();
        return $lists;
    }
    /**
     * @return array|\PDOStatement|string|\think\Collection
     * 热门楼盘问答
     */
    private function getHotQuestionHouse()
    {
        $where['q.status'] = 1;
        $city = $this->getCityChild();
        $city && $where[] = ['q.city_id','in',$city];
        $join  = [['house h','h.id = q.house_id']];
        $field = 'h.title,h.img,h.id,count(q.id) as total';
        $lists = model('question')->alias('q')->where($where)->join($join)->field($field)->order(['total'=>'desc','q.create_time'=>'desc'])->group('q.house_id')->limit(10)->select();
        return $lists;
    }
}