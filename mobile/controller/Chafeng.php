<?php




namespace app\mobile\controller;

use app\common\controller\MobileBase;

class Chafeng extends MobileBase

{

    private $pageSize = 10;

    private $mod      = 'chafeng';



    /**

     * @return mixed

     * 楼盘列表

     */

    public function index()

    {

        return $this->fetch();

    }



}