<?php

namespace app\manage\controller;

use \app\common\controller\ManageBase;
use app\manage\service\SecondHouseService;
use app\manage\service\Synchronization;
use think\Config;
use think\Db;
use think\facade\Log;


class DealTomorrow extends ManageBase
{

    private $model = 'second_house';

    public function initialize(){

        parent::initialize();

        $storage = getSettingCache('storage');

        $this->assign('storage',$storage);

    }
    /**

     * @return array

     * 搜索条件

     */
    public function Index()
    {
        $list = model('second_house')->field('title,bmrs,bianhao,lianjie,oneetime,jieduan')
              ->where("fcstatus=169 and (TO_DAYS(oneetime)-TO_DAYS(NOW()) = 1 or TO_DAYS(twoetime)-TO_DAYS(NOW()) = 1 or TO_DAYS(bianetime)-TO_DAYS(NOW()) = 1)")
              ->order("jieduan asc,bmrs desc")->select();
        $this->assign('list',$list);
        return $this->fetch();
    }
    function excelFileExport() 
    {
        $data = model('second_house')->field('title,bmrs,bianhao,lianjie,oneetime,jieduan')
            ->where("fcstatus=169 and (TO_DAYS(oneetime)-TO_DAYS(NOW()) = 1 or TO_DAYS(twoetime)-TO_DAYS(NOW()) = 1 or TO_DAYS(bianetime)-TO_DAYS(NOW()) = 1)")
            ->order("jieduan asc,bmrs desc")->select();
        foreach($data as $k=>$v){
            if($v['jieduan']==161){
                $data[$k]['jieduan'] = '一拍';
            }elseif($v['jieduan']==162){
                $data[$k]['jieduan'] = '二拍';
            }elseif($v['jieduan']==163){
                $data[$k]['jieduan'] = '变卖';
            }
        }
        $title='明日成交数据表';
        //文件名
        $fileName = $title. '('.date("YmdHis",time()) .'）'. ".xls";
        //加载第三方类库
        require'../extend/PHPExcel/Classes/PHPExcel.php';
        require'../extend/PHPExcel/Classes/PHPExcel/IOFactory.php';
        //实例化excel类
        $excelObj = new \PHPExcel();
        //构建列数--根据实际需要构建即可
        $letter = array('A','B','C','D','E','F');
        //$excelObj->getActiveSheet()->mergeCells( 'A1:E1');
        //$excelObj->getActiveSheet()->setCellValue("A1",$name);
        //表头数组--需和列数一致
        $tableheader = array('房源名称','报名人数','编号','链接','开拍时间','阶段');
        //填充表头信息
        for ($i = 0; $i < count($tableheader); $i++) {
            $excelObj->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
        }
        //循环填充数据
        foreach ($data as $k => $v) {
            $num = $k + 2;
            //设置每一列的内容
            $excelObj->setActiveSheetIndex(0)
                ->setCellValue('A' . $num, $v['title'])
                ->setCellValue('B' . $num, $v['bmrs'])
                ->setCellValue('C' . $num, $v['bianhao'])
                ->setCellValue('D' . $num, $v['lianjie'])
                ->setCellValue('E' . $num, $v['oneetime'])
                ->setCellValue('F' . $num, $v['jieduan']);
                //设置行高
                $excelObj->getActiveSheet()->getRowDimension($k+3)->setRowHeight(30);
        }
        //以下是设置宽度
        $excelObj->getActiveSheet()->getColumnDimension('A')->setWidth(50);
        $excelObj->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $excelObj->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $excelObj->getActiveSheet()->getColumnDimension('D')->setWidth(40);
        $excelObj->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $excelObj->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        //设置表头行高
        $excelObj->getActiveSheet()->getRowDimension(1)->setRowHeight(28);
        $excelObj->getActiveSheet()->getRowDimension(2)->setRowHeight(28);

        //设置字体样式
        $excelObj->getActiveSheet()->getStyle('A1:D1')->getFont()->setName('黑体');
        $excelObj->getActiveSheet()->getStyle('A1:D1')->getFont()->setSize(20);
        $excelObj->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);
        $excelObj->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);  
        $excelObj->getActiveSheet()->getStyle('A1:D1')->getFont()->setName('宋体');
        $excelObj->getActiveSheet()->getStyle('A1:D1')->getFont()->setSize(16);
        $excelObj->getActiveSheet()->getStyle('A1:D1'.($k+2))->getFont()->setSize(10);

        //设置自动换行
        $excelObj->getActiveSheet()->getStyle('A1:D1'.($k+2))->getAlignment()->setWrapText(true);

        // 重命名表
        $fileName = iconv("utf-8", "gb2312", $fileName); 

        // 设置下载打开为第一个表
        $excelObj->setActiveSheetIndex(0); 

        //设置header头信息
        header('Content-Type: application/vnd.ms-excel;charset=UTF-8');
        header("Content-Disposition: attachment;filename={$fileName}");
        header('Cache-Control: max-age=0');
        $writer = \PHPExcel_IOFactory::createWriter($excelObj, 'Excel2007');
        $writer->save('php://output');
        exit();
    }
}