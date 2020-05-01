<?php




namespace app\manage\controller;

use \app\common\controller\ManageBase;

class SecondHouse extends ManageBase

{

    private $model = 'second_house';

    protected $beforeActionList = [

        'beforeEdit' => ['only'=>['edit']],

        'beforeAdd' => ['only'=>['add']],

        'beforeIndex' => ['only'=>['index']]

    ];

    public function initialize(){

        parent::initialize();

        $storage = getSettingCache('storage');

        $this->assign('storage',$storage);

    }

    protected function beforeIndex()

    {
        $list=model('second_house')->select();
        $this->assign('list',$list);
        // $this->sort = ['ordid'=>'asc','id'=>'desc'];


    }

    /**

     * @return array

     * 搜索条件

     */

    public function search()

    {

        $status  = input('get.status');

        $jieduan  = input('get.jieduan');

        $keyword = input('get.keyword');

        $where   = [];

        is_numeric($status) && $where['status'] = $status;
        is_numeric($jieduan) && $where['jieduan'] = $jieduan;

        $keyword && $where[] = ['title','like','%'.$keyword.'%'];

        $data = [

            'status' => $status,

            'keyword'=> $keyword,
            'jieduan'=> $jieduan

        ];

        $this->queryData = $data;
// print_r($data);exit();
        $this->assign('search',$data);

        return $where;

    }

    public function beforeAdd()

    {


// print_r(222);
$fpy = db('user')->where(['model'=>4])->select();
// print_r($fpy);
        $position_lists = \app\manage\service\Position::lists($this->model);
$this->assign('fpy',$fpy);
        $this->assign('position_lists',$position_lists);

    }

    public function beforeEdit()

    {

        $id  = input('param.id/d',0);

        if(!$id)

        {

            $this->error('参数错误');

        }

        $data = model('second_house_data')->where(['house_id'=>$id])->find();

        $position_lists = \app\manage\service\Position::lists($this->model);

        $house_position_cate_id = \app\manage\service\Position::getPositionIdByHouseId($id,$this->model);

        $datas = model('second_house')->where(['id'=>$id])->find();
        $str=$datas['hximg'];
        // print_r($str);
        $var=explode(",",$str);
// print_r($var);

$this->assign('var',$var);
$fpy = db('user')->where(['model'=>4])->select();
$this->assign('fpy',$fpy);

        $this->assign('position_lists',$position_lists);

        $this->assign('position_cate_id',$house_position_cate_id);

        $this->assign('data',$data);

    }

    /**

     * 添加

     */

    public function addDo()

    {

        $data = input('post.');

        $result = $this->validate($data,'SecondHouse');//调用验证器验证

        $code   = 0;

        $msg    = '';

        $obj = model('second_house');







        if(true !== $result)

        {

            // 验证失败 输出错误信息

            $this->error($result);

        }else{

            \think\Db::startTrans();

            try{

                !empty($data['map']) && $location = explode(',',$data['map']);

               // $data['house_type'] = isset($data['house_type']) ? implode(',',$data['house_type']) : 0;

                $data['lng']     = isset($location[0]) ? $location[0] : 0;

                $data['lat']     = isset($location[1]) ? $location[1] : 0;

                $data['tags'] = isset($data['tags']) ? implode(',',$data['tags']) : 0;

                $data['average_price'] = 0;
                
                $data['online_consulting']=$data['online_consulting'];
                
                
                //$data['marketprice']=$data['price'];
                
                
                
            $marketprices=$data['price'];   
            $qipaiprice=$data['qipai'];
            
        // print_r($qipaiprice);    
            $jlzs=round($marketprices/$qipaiprice,1);
            
            //print_r($jlzs);
            if($jlzs<'1.1'){
            
                $data['marketprice']='0';
            
            }
            
            if(($jlzs>='1.1') && ($jlzs<='2')){
            
                $data['marketprice']='1';
            
            }
            if(($jlzs>='1.3') && ($jlzs<='1.4')){
            
                $data['marketprice']='2';
            
            }
            if(($jlzs>='1.5') && ($jlzs<='1.6')){
            
                $data['marketprice']='3';
            
            }
            if(($jlzs>='1.7') && ($jlzs<='1.8')){
            
                $data['marketprice']='4';
            
            }

            if($jlzs>'1.8'){
            
                $data['marketprice']='5';
            
            }
    
  
            
           $suiji=rand(1000,9999);
           // $bianhao=$data['bianhao'];
        if(!empty($data['bianhao'])){
           
             $data['bianhao']=$data['bianhao'].$suiji;
        }else
        {
             $data['bianhao']='T'.$suiji;
        }
               
           
// print_r($_FILES);exit;
         
            // $hximg = $upload->getUploadFileInfo();
             // print_r($_FILES);exit();
              
         // $path=env('root_path'). 'public/uploads/secondhouse';
         //      foreach($_FILES['hximg']['tmp_name'] as $k=>$v){
         //            if(is_uploaded_file($_FILES['hximg']['tmp_name'][$k])){
         //            $save=$path.$_FILES['hximg']['name'][$k];
         //            echo $save."
         //            ";
         //            if(move_uploaded_file($_FILES['hximg']['tmp_name'][$k],$save)){
         //            echo "上传成功！";
         //            }
         //        }
         //     }
        // print_r($data['shu']);
        

        // if(!empty($data['shu'])){

        //     // $bbb=array();
        //     $aaa=$data['shu'];
        //     for ($n=0; $n<=$aaa; $n++) {
        //         $hximg = request()->file('row'.$n);
        //         // print_r($hximg);exit();
        //         if($hximg){
        //                 $info = $hximg->move(env('root_path'). 'public/uploads/secondhouse');
                      
        //                 if($info){      
                       
        //                      $image= $info->getSaveName();
        //                      $bbb='/uploads/secondhouse/'.$image;
        //                 } 

                         
        //             }

        //             $data['hximg']=$bbb.','.$bbb;
        //     }
        // }
         
        // print_r($data['hximg']);
        // exit();

         if(!empty($data['shu'])){

        // $bbb=array();
        $aaa=$data['shu']+1;
            for ($n=0; $n<$aaa; $n++) {
              
         
         
                $hximg[] = request()->file('row'.$n);
                // print_r($file_tmp_name);
                 
                 //print_r(request()->file('row'.$n).'<br>');

                 
                    
            }
            
            
            foreach ($hximg as $key => $hximgs) {

                         $info = $hximgs->move(env('root_path'). 'public/uploads/secondhouse');
                         if($info){      
                       
                              $image= $info->getSaveName();
                              $bbb[]='/uploads/secondhouse/'.$image;
                              
                             
                         } 

                          // $data['hximg']=$bbb.','.$bbb;

                            //$bbb.=','.$bbb;                     
                     }



if($aaa=='1'){

    $bbb=$bbb[0];

}
if($aaa=='2'){

    $bbb=$bbb[0].','.$bbb[1];

}
if($aaa=='3'){

    $bbb=$bbb[0].','.$bbb[1].','.$bbb[2];

}
if($aaa=='4'){

    $bbb=$bbb[0].','.$bbb[1].','.$bbb[2].','.$bbb[3];

}
if($aaa=='5'){

    $bbb=$bbb[0].','.$bbb[1].','.$bbb[2].','.$bbb[3].','.$bbb[4];

}        

$data['hximg']=$bbb;




        }
  
        
               // print_r($bbb);
                  // print_r(11111);             
 
        
           // if (!empty($_FILES['hximg']['name'])) {

           //    $hximg = request()->file('hximg');
    
           //    if($hximg){
           //          $info = $hximg->move(env('root_path'). 'public/uploads/secondhouse');
                  
           //          if($info){      
                   
           //               $image= $info->getSaveName();
           //              $data['hximg']='/uploads/secondhouse/'.$image;
           //          } 
                     
           //      }
           //  } 
              
                        

                if($data['price']>0 && $data['acreage']>0)

                {

                    $data['average_price'] = ceil($data['price'] * 10000 / $data['acreage']);

                }

                if (isset($data['position'])) {

                    $data['rec_position'] = 1;

                }

                $data['file'] = $this->getPic();

              
                (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];
                
                (empty($data['imgs']) && !empty($data['file'])) && $data['imgs'] = $data['file'][0]['url'];
                
                $fpyid=$data['contacts']['contact_name'];
                $fpys = db('user')->where(['id'=>$fpyid])->find();
                $data['contacts']['contact_name']=$fpys['user_name'];
                $data['contacts']['contact_phone']=$fpys['mobile'];
                $data['online_consulting']=$fpys['online_consulting'];
                if($fpyid==16){
                    $data['broker_id']=0;
                }else{
                    $data['broker_id']=$fpyid;
                }
                
                // print_r(222);
                // print_r($data);exit();
                // print_r($data['contacts']['contact_name']);exit();
                $data['qipai']=$data['qipai'];
// print_r($data);exit();
                if($obj->allowField(true)->save($data))

                {

                    $house_id = $obj->id;

                    $this->optionHouseData($house_id,$data);

                    $this->addHousePrice($house_id,$data['average_price']);

                    \app\manage\service\Price::calculationPrice($data['estate_id']);

                    \app\manage\service\Position::option($house_id,$this->model);

                    $msg = '添加房源信息成功';

                    $code = 1;

                    //关联学校

                    \org\Relation::addSchool('second_house',$data['lng'],$data['lat'],$house_id,$data['city']);

                    //关联地铁站

                    \org\Relation::addMetro('second_house',$data['lng'],$data['lat'],$house_id,$data['city']);

                }else{

                    $msg = '添加房源信息失败！';

                }

                \think\Db::commit();

            }catch(\Exception $e){

                \think\facade\Log::record('添加房源信息出错：'.$e->getFile().$e->getLine().$e->getMessage());

                \think\Db::rollback();

                 $msg = $e->getMessage();

            }

        }

        if($code == 1)

        {

            $this->success($msg);

        }else{

            $this->error($msg);

        }

    }



    /**

     * 编辑

     */

    public function editDo()

    {

        $data = input('post.');
        //print_r($data);exit();
        
        // $data['online_consulting']=$data['online_consulting'];
        
         
        //$data['marketprice']=$data['price'];
        
        
        $marketprices=$data['price'];   
        $qipaiprice=$data['qipai'];
            
        
        $jlzs=round($marketprices/$qipaiprice,1);
            
                //print_r($jlzs);
                if($jlzs<'1.1'){
            
                    $data['marketprice']='0';
            
                }
            
                if(($jlzs>='1.1') && ($jlzs<='2')){
            
                    $data['marketprice']='1';
            
                }
                if(($jlzs>='1.3') && ($jlzs<='1.4')){
                
                    $data['marketprice']='2';
                
                }
                if(($jlzs>='1.5') && ($jlzs<='1.6')){
                
                    $data['marketprice']='3';
                
                }
                if(($jlzs>='1.7') && ($jlzs<='1.8')){
                
                    $data['marketprice']='4';
                
                }
    
                if($jlzs>'1.8'){
                
                    $data['marketprice']='5';
                
                }
                
        
        
        
        
        
        
        
        
        
        
        
        
        

        $result = $this->validate($data,'SecondHouse');//调用验证器验证

        $code   = 0;

        $msg    = '';

        $obj = model('second_house');

        $url = null;

        isset($data['refer']) && $url = $data['refer'];

        if(true !== $result)

        {

            // 验证失败 输出错误信息

            $this->error($result);

        }elseif(!$data['id']){

            $this->error('参数错误');

        }else{

            \think\Db::startTrans();

            try{

                !empty($data['map']) && $location = explode(',',$data['map']);

                //$data['house_type'] = isset($data['house_type']) ? implode(',',$data['house_type']) : 0;

                $data['lng']     = isset($location[0]) ? $location[0] : 0;

                $data['lat']     = isset($location[1]) ? $location[1] : 0;

                $data['tags'] = isset($data['tags']) ? implode(',',$data['tags']) : 0;

                $data['average_price'] = 0;
          // print_r($_FILES);exit();  
		  
		  
		 // echo $data['shu'];
		//echo 777;
		
		//exit();
		  
		      
        if(!empty($data['shu'])){

        // $bbb=array();
        $aaa=$data['shu'];
		
		// echo $aaa;
//		echo 555;
//		
//		exit();
		
		//if($aaa==1){
//		
//			$hximg = request()->file('row'.$data['shu']);
//			$info = $hximg->move(env('root_path'). 'public/uploads/secondhouse');
//             if($info){      
//                       
//                   $image= $info->getSaveName();
//                   $bbbs='/uploads/secondhouse/'.$image;
//							  
//			  } 
//		
//		}
		
		//echo 666;
//			
//			print_r($hximg);
//			exit();
		
		
		
		
		
            for ($n=0; $n<$aaa; $n++) {
              
    	 
    	 
    	        $hximg[] = request()->file('row'.$n);
                // print_r($file_tmp_name);
    			 
    			 //print_r(request()->file('row'.$n).'<br>');

    			 
                    
            }
			
			
            foreach ($hximg as $key => $hximgs) {

                         $info = $hximgs->move(env('root_path'). 'public/uploads/secondhouse');
                         if($info){      
                       
                              $image= $info->getSaveName();
                              $bbb[]='/uploads/secondhouse/'.$image;
							  
							 
                         } 

                          // $data['hximg']=$bbb.','.$bbb;

                            //$bbb.=','.$bbb;                     
                     }
					 
		

        if($aaa=='1'){

    $bbb=$bbb[0];

}
if($aaa=='2'){

    $bbb=$bbb[0].','.$bbb[1];

}
if($aaa=='3'){

    $bbb=$bbb[0].','.$bbb[1].','.$bbb[2];

}
if($aaa=='4'){

    $bbb=$bbb[0].','.$bbb[1].','.$bbb[2].','.$bbb[3];

}
if($aaa=='5'){

    $bbb=$bbb[0].','.$bbb[1].','.$bbb[2].','.$bbb[3].','.$bbb[4];

}




$data['hximg']=$bbb;

        }
		

		
               // print_r($bbb);
                  // print_r(11111);             
 
// foreach ($bbb as $key => $value) {
//     # code...
// }
    
// print_r($_FILES['row'.$n]['name']);print_r($_FILES['row1']['name']);
        // print_r($bbb);
// print_r($data['hximg']);
 //exit();





                
             // if (!empty($_FILES['hximg']['name'])) {

    //           $hximg = request()->file('hximg');

    //           if($hximg){
    //                 $info = $hximg->move(env('root_path'). 'public/uploads/secondhouse');
                 
    //                 if($info){      
                   
    //                      $image= $info->getSaveName();
    //                     $data['hximg']='/uploads/secondhouse/'.$image;
    //                 } 
                     
    //             }
    //         }
                
                
                
              // $hximg = request()->file('hximg');
              
              // if($hximg){
              //       $info = $hximg->move(env('root_path'). 'public/uploads/secondhouse');
                  
              //       if($info){      
                   
              //            $image= $info->getSaveName();
              //            $data['hximg']='/uploads/secondhouse/'.$image;
              //       } 
              //   }


                if($data['price']>0 && $data['acreage']>0)

                {

                    //计算均价

                    $data['average_price'] = ceil($data['price'] * 10000 / $data['acreage']);

                }

                if (!isset($data['position'])) {

                    $obj->rec_position = 0;

                } else {

                    $obj->rec_position = 1;

                }

                $data['file'] = $this->getPic();

                (empty($data['img']) && !empty($data['file'])) && $data['img'] = $data['file'][0]['url'];
                (empty($data['imgs']) && !empty($data['file'])) && $data['imgs'] = $data['file'][0]['url'];

                $data['ratio'] = $this->addHousePrice($data['id'],$data['average_price']);


                $fpyid=$data['contacts']['contact_name'];
                $fpys = db('user')->where(['id'=>$fpyid])->find();
                $data['contacts']['contact_name']=$fpys['user_name'];
                $data['contacts']['contact_phone']=$fpys['mobile'];
                $data['online_consulting']=$fpys['online_consulting'];
                if($fpyid==16){
                    $data['broker_id']=0;
                }else{
                    $data['broker_id']=$fpyid;
                }




                if($obj->allowField(true)->save($data,['id'=>$data['id']]))

                {

                    $this->optionHouseData($data['id'],$data,true);

                    \app\manage\service\Price::calculationPrice($data['estate_id']);

                    \app\manage\service\Position::option($data['id'],$this->model);

                    $msg = '编辑房源信息成功';

                    $code = 1;

                    \org\Relation::addSchool('second_house',$data['lng'],$data['lat'],$data['id'],$data['city']);

                    \org\Relation::addMetro('second_house',$data['lng'],$data['lat'],$data['id'],$data['city']);

                }else{

                    $msg = '编辑房源信息失败！';

                }



                \think\Db::commit();

            }catch(\Exception $e){

                \think\facade\Log::record('编辑房源信息出错：'.$e->getFile().$e->getLine().$e->getMessage());

                \think\Db::rollback();

                $msg = $e->getMessage();

            }

        }

        if($code == 1)

        {

            $this->success($msg,$url);

        }else{

            $this->error($msg,$url);

        }

    }

    public function delete()

    {

        \app\common\model\SecondHouse::event('after_delete',function($obj){

            //删除扩展数据

            $mod = model('second_house_data');

            $where = ['house_id'=>$obj->id];

            $info= $mod->where($where)->find();

            if($mod->where($where)->delete())

            {

                model('attachment')->deleteAttachment($info['info'],$obj->img,$info['file']);//删除图片

            }

            model('attachment')->deleteVideo($obj->video);//删除视频

            //删除价格数据

            db('house_price')->where(['house_id'=>$obj->id,'model'=>'second_house'])->delete();

            //删除推荐数据

            action('manage/Position/deleteByHouseId', ['house_id'=>$obj->id,'model'=>$this->model], 'controller');

            //删除地铁关联数据

            \org\Relation::deleteByHouse($obj->id,'second_house');

            //删除学校关联数据

            \org\Relation::deleteByHouse($obj->id,'second_house','school');

        });

        parent::delete();

    }

    /**

     * @param $house_id

     * @param $data

     * 添加扩展数据

     */

    private function optionHouseData($house_id,$data,$update = false)

    {

        if (empty($data['seo']['seo_title'])) {

            $info['seo_title'] = $data['title'];

        }else{

            $info['seo_title'] = $data['seo']['seo_title'];

        }

        $info['supporting'] = isset($data['supporting'])?implode(',',$data['supporting']):0;

        $info['house_id']  = $house_id;

        $info['info']      = isset($data['info']) ? $data['info'] : '';

        $info['seo_keys']  = $data['seo']['seo_keys'];

        $info['seo_desc']  = $data['seo']['seo_desc'];

        $info['file']      = $data['file'];//$this->getPic();

        if($update)

        {

            model('second_house_data')->allowField(true)->save($info,['house_id'=>$house_id]);

        }else{

            model('second_house_data')->allowField(true)->save($info);

        }



    }



    /**

     * @param $house_id

     * @param $price

     * 添加价格

     */

    private function addHousePrice($house_id,$price)

    {

        $priceObj  = model('house_price');

        $rate = 0;

        //读取上一次价格

        $prev_price = $priceObj->where(['house_id'=>$house_id,'model'=>'second_house'])->order('create_time desc')->value('price');

        if($prev_price != $price && intval($price) > 0)

        {

            $data['price'] = $price;

            $data['create_time'] = time();

            $data['house_id'] = $house_id;

            $data['model']    = 'second_house';

            //计算涨幅比

            $prev_price && $rate = number_format((($price - $prev_price) / $prev_price) * 100,1);

            //$obj->removeOption();

            $priceObj->insert($data);

        }

        return $rate;

    }

    /**

     * @param $obj

     * 添加图片

     */

    private function getPic(){

        $insert = [];

        if(isset($_POST['pic']) && !empty($_POST['pic'])) {

            $images = $_POST['pic'];

            foreach ($images as $key => $v) {

                $insert[] = [

                    'url' => $v['pic'],

                    'title' => $v['alt'],

                ];

            }

        }

        return $insert;

    }
   

}