<?php


namespace app\mobile\controller\user;


class Question extends UserBase
{
    public function index()
    {
        $where['q.user_id'] = $this->userInfo['id'];
        $field = 'q.id,q.content,count(q.id) as total,q.reply_num,q.create_time,a.answer';
        $bind_field = "question_id,content as answer";
        $bind_sql   = model('answer')->field($bind_field)->order('id','desc')->buildSql();
        $join = [
            [$bind_sql.' a','a.question_id = q.id','left']
        ];
        $obj   = model('question');
        $lists = $obj->alias('q')->where($where)->field($field)->join($join)->order('q.id','desc')->group('q.id')->paginate(10);

        $this->assign('lists',$lists);
        $this->assign('pages',$lists->render());
        $this->assign('title','我的提问');
        return $this->fetch();
    }
}