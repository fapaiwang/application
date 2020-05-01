<?php




namespace app\home\controller;

use app\common\controller\HomeBase;

use app\common\service\Metro;

class Tuiguang extends HomeBase

{

   

    public function index()

    {
        return $this->fetch();

    }
    public function tijiao()

    {
    	
        // $data['house_id']  = input('post.house_id/d',0);

        $data['user_name'] = input('post.user_name');
        $data['mobile'] = input('post.mobile');
        $aaa=input('post.user_name');
        $bbb=input('post.mobile');
$check = '/^(1(([35789][0-9])|(47)))\d{8}$/';
        if(empty($aaa)){
        	echo "<script>alert('请输入您的姓名');</script>";
			return $this->fetch('tuiguang/index');
        }else if(empty($bbb)){
        	echo "<script>alert('请输入您的电话');</script>";
			return $this->fetch('tuiguang/index');
        }else if(!is_mobile($bbb))
        {
            echo "<script>alert('您的电话格式不正确');</script>";
            return $this->fetch('tuiguang/index');
            // $return['msg'] = '手机号码格式不正确！';
        }else if(db('tijiao')->insert($data)){
            echo "<script>alert('提交成功');</script>";
        	return $this->fetch('public/tj');
        }else{
            echo "<script>alert('提交失败');</script>";
            return $this->fetch('public/sb');
        }



        


    }



}