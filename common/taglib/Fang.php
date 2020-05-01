<?php
namespace app\common\taglib;
use think\template\TagLib;

class Fang extends TagLib
{
    /**
     * 定义标签列表
     */
    protected $tags = [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'lists' => ['attr' => 'limit,alias,join,field,table,order,where,id,offset,length,key,page,mod,empty', 'close' => 1],
        'position' => ['attr' => 'limit,posid,field,table,order,id,id,offset,length,key,page,mod,empty','close'=>1],
        'read'  => ['attr' => 'field,table,order,where,id,empty','close' => 1],
    ];
    //获取数据列表
    public function tagLists($tag, $content)
    {
        $tag['limit'] = (isset($tag['limit']) && is_numeric($tag['limit'])) ? $tag['limit'] : 10;
        $id     = $tag['id'];
        $empty  = isset($tag['empty']) ? $tag['empty'] : '暂无数据';
        $key    = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod    = isset($tag['mod']) ? $tag['mod'] : '2';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $length = !empty($tag['length']) && is_numeric($tag['length']) ? intval($tag['length']) : 'null';

        $parseStr = '<?php ';

        $parseStr .= '$obj = model("'.$tag["table"].'");';
        if(isset($tag['alias']))
        {
            $parseStr .= '$obj = $obj->alias("'.$tag['alias'].'");';
        }
        if(isset($tag['join']))
        {
            $parseStr.= '$join = '.$tag['join'].';';
            $parseStr .= '$obj = $obj->join($join);';
        }
        if(isset($tag['where']))
        {
            $where  = $tag['where'];
            $parseStr .= '$obj = $obj->where("'.$where.'");';
        }
        if(isset($tag['field'])){
            $parseStr .= '$obj = $obj->field("'.$tag["field"].'");';
        }

        if(isset($tag['order'])){
            $parseStr .= '$obj = $obj->order("'.$tag["order"].'");';
        }
        if(isset($tag['group'])){
            $parseStr .= '$obj = $obj->group("'.$tag["group"].'");';
        }
        if(isset($tag['limit']) && isset($tag['page'])){
            $parseStr .= '$name = $obj->paginate('.$tag['limit'].');';
            $parseStr .= '$pages=$name->render();';
        }else{
            $parseStr .= '$name = $obj->limit('.$tag['limit'].')->select();';
        }

        $parseStr .= 'if(is_array( $name ) || $name  instanceof \think\Collection || $name instanceof \think\Paginator ): $' . $key . ' = 0;';
        // 设置了输出数组长度
        if (0 != $offset || 'null' != $length) {
            $parseStr .= '$__LIST__ = is_array( $name ) ? array_slice($name ,' . $offset . ',' . $length . ', true) :  $name ->slice(' . $offset . ',' . $length . ', true); ';
        } else {
            $parseStr .= ' $__LIST__ = $name;';
        }
        $parseStr .= 'if( count($__LIST__)==0 ) : echo "' . $empty . '" ;';
        $parseStr .= 'else: ';
        $parseStr .= 'foreach($__LIST__ as $key=>$' . $id . '): ';
        $parseStr .= '$mod = ($' . $key . ' % ' . $mod . ' );';
        $parseStr .= '++$' . $key . ';?>';
        $parseStr .= $content;
        $parseStr .= '<?php endforeach; endif; else: echo "' . $empty . '" ;endif; ?>';

        return $parseStr;
    }
    //读取单条记录
    public function tagRead($tag, $content)
    {
        $id     = $tag['id'];
        $empty  = isset($tag['empty']) ? $tag['empty'] : '暂无数据';

        $parseStr = '<?php ';
        $parseStr .= '$obj = model("'.$tag["table"].'");';
        if(isset($tag['where'])){
            $where  = $tag['where'];
            //$parseStr .= "if (is_array(".$where.")):";
            //$parseStr .= '$obj = $obj->where('.$where.');else:';
            $parseStr .= '$obj = $obj->where("'.$where.'");';
            //$parseStr .= 'endif;';

        }
        if(isset($tag['field'])){
            $parseStr .= '$obj = $obj->field("'.$tag["field"].'");';
        }

        if(isset($tag['order'])){
            $parseStr .= '$obj->order("'.$tag["order"].'");';
        }

        $parseStr .= '$name = $obj->find();';

        $parseStr .= 'if( $name ):';
        $parseStr .= '$'.$id.' = $name; ?>';
        $parseStr .= $content;
        $parseStr .= '<?php else: echo "' . $empty . '" ;endif; ?>';
        return $parseStr;
    }

    /**
     * @param $tag
     * @param $content
     * 推荐位标签
     */
    public function tagPosition($tag,$content)
    {
        $tag['limit'] = (isset($tag['limit']) && is_numeric($tag['limit'])) ? $tag['limit'] : 10;
        $id     = $tag['id'];
        $empty  = isset($tag['empty']) ? $tag['empty'] : '暂无数据';
        $key    = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod    = isset($tag['mod']) ? $tag['mod'] : '2';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $length = !empty($tag['length']) && is_numeric($tag['length']) ? intval($tag['length']) : 'null';

        $posid  = $tag['posid'];
        $parseStr = '<?php ';
        $parseStr.= '$join   = [["'.$tag["table"].' t","t.id = p.house_id"]];';
        $parseStr .= '$obj = model("Position")->alias("p")->join($join);';
        $where = "p.status = 1 and p.cate_id = ".$posid." and t.status = 1 and p.model = '".$tag['table']."'";
        if(isset($tag['where'])){
            $where  .= " and ".$tag['where'];
        }
        $parseStr .= '$obj = $obj->where("'.$where.'");';
        if(isset($tag['field'])){
            $field = $tag['field'];
            $default_price_txt = $tag["table"] == 'house' ? '待定' : '面议';
            if(strpos($field,'t.price')!==FALSE)
            {
                $replace = "(case t.price when 0 then '{$default_price_txt}' else t.price end) as price";
                $field = str_replace('t.price',$replace,$field);
            }
            $parseStr .= '$obj = $obj->field("'.$field.'");';
        }
        if(isset($tag['order'])){
            $parseStr .= '$obj = $obj->order("'.$tag["order"].'");';
        }else{
            $parseStr .= '$obj = $obj->order("p.ordid asc,t.id desc");';
        }
        if(isset($tag['limit']) && isset($tag['page'])){
            $parseStr .= '$name = $obj->paginate('.$tag['limit'].');';
            $parseStr .= '$position_pages=$name->render();';
        }else{
            $parseStr .= '$name = $obj->limit('.$tag['limit'].')->select();';
        }
        $parseStr .= 'if(is_array( $name ) || $name  instanceof \think\Collection || $name instanceof \think\Paginator ): $' . $key . ' = 0;';
        // 设置了输出数组长度
        if (0 != $offset || 'null' != $length) {
            $parseStr .= '$__LIST__ = is_array( $name ) ? array_slice($name ,' . $offset . ',' . $length . ', true) :  $name ->slice(' . $offset . ',' . $length . ', true); ';
        } else {
            $parseStr .= ' $__LIST__ = $name;';
        }
        $parseStr .= 'if( count($__LIST__)==0 ) : echo "' . $empty . '" ;';
        $parseStr .= 'else: ';
        $parseStr .= 'foreach($__LIST__ as $key=>$' . $id . '): ';
        $parseStr .= '$mod = ($' . $key . ' % ' . $mod . ' );';
        $parseStr .= '++$' . $key . ';?>';
        $parseStr .= $content;
        $parseStr .= '<?php endforeach; endif; else: echo "' . $empty . '" ;endif; ?>';

        return $parseStr;
    }
}