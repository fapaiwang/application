<?php


namespace app\common\service;


class Metro
{
    /**
     * @param $city_id
     * @return array|\PDOStatement|string|\think\Collection
     *
     */
    public static function index($city_id)
    {
        $where['status'] = 1;
        $where['city']   = $city_id;
        $lists = model('metro')->where($where)->order('ordid asc,id desc')->select();
        return $lists;
    }

    /**
     * @param $metro_id
     * @return array|\PDOStatement|string|\think\Collection
     *
     */
    public static function metroStation($metro_id)
    {
        $where['status'] = 1;
        $where['metro_id'] = $metro_id;
        $lists = model('metro_station')->where($where)->order('ordid asc,id desc')->select();
        return $lists;
    }

    /**
     * @param $id
     * @return mixed
     * 线路名称
     */
    public static function getMetroName($id)
    {
        $where['status'] = 1;
        $where['id']     = $id;
        $name = model('metro')->where($where)->value('name');
        return $name;
    }

    /**
     * @param $id
     * @return mixed
     * 站点名称
     */
    public static function getStationName($id)
    {
        $where['status'] = 1;
        $where['id']     = $id;
        $name = model('metro_station')->where($where)->value('name');
        return $name;
    }
}