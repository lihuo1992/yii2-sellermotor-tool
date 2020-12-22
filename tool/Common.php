<?php
namespace yii2SellermotorTool\tool;
/**
 *   CREATER: 黎获
 *   TIME:2019/11/219:42
 *   NOTES:
 */

use app\modules\user\models\UserInfo;
use Yii;
use yii\db\Expression;

class Common
{
    /**
     * Notes: 生成随机字符串
     * Author: 黎获
     * @param $len
     * @return string
     * Time：2019-03-05
     */
    static public function GetRandStr($len,$lower=false)
    {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9"
        );
        if($lower){
            $chars = array(
                "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
                "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
                "w", "x", "y", "z", "0", "1", "2",
                "3", "4", "5", "6", "7", "8", "9"
            );
        }
        $charsLen = count($chars) - 1;
        shuffle($chars);
        $output = "";
        for ($i=0; $i<$len; $i++)
        {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }

    static public function getDateLine($start_time,$end_time){
        $end_time=min(date('Y-m-d'),$end_time);
        if(strtotime($start_time)> strtotime($end_time)){
            $tem = $start_time;
            $start_time= $end_time;
            $end_time = $tem;

        }
        $days = self::getTimeDiff(strtotime($start_time),strtotime($end_time));
        $days= $days['day']+1;
        $date_arr=array();
        for($i=0;$i<=$days;$i++){
            $date_arr[] = date('Y-m-d',strtotime('+'.$i.' days',strtotime($start_time)));
        }
        return $date_arr;

//        if(empty($date_arr)){
//            $date_arr=[];
//            $date_arr[] = date('Y-m-d',strtotime($start_time));
//        }
//        $time = date('Y-m-d',strtotime('+1 day',strtotime($start_time)));
//        if(strtotime($time)<strtotime($end_time)){
//            $date_arr[]= $time;
//            self::getDateLine($time,$end_time,$date_arr);
//        }
//        else{
//            return $date_arr;
//        }
    }
    /**
     * Notes : 计算时间相差天数
     * Author: 黎获
     * @param $begin_time //开始时间 （时间戳）
     * @param $end_time //结束时间 （时间戳）
     * @param string $type
     * @return array|mixed
     * Time: 2018/10/30 10:26
     */
    static public function getTimeDiff($begin_time,$end_time,$type='ALL'){
        if($begin_time < $end_time){
            $starttime = $begin_time;
            $endtime = $end_time;
        }else{
            $starttime = $end_time;
            $endtime = $begin_time;
        }

//计算天数
        $timediff = $endtime-$starttime;
        $days = intval($timediff/86400);
//计算小时数
        $remain = $timediff%86400;
        $hours = intval($remain/3600);
//计算分钟数
        $remain = $remain%3600;
        $mins = intval($remain/60);
//计算秒数
        $secs = $remain%60;

        $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
        if($type=='DAY'){
//            if(!empty($res['hour']) || !empty($res['min']) || !empty($res['sec'])){
//                $res['day'] = $res['day']+1;
//            }
            return $res['day'];
        }
        return $res;
    }
    public static function  getNickname($username){
        $new_username = '';
        $username_arr = explode('_',$username);

        if(($username_arr[0] == 'sm' && strlen(end($username_arr))==4) || strpos($username,'sm_')===0 ){
            for($x=1;$x<count($username_arr)-1;$x++){
                $new_username.=$username_arr[$x];
            }
            $username = $new_username;
            $pattern = "/^1[3456789]{1}\d{9}$/";
            $mobile_patch = preg_match($pattern, $username);
            if($mobile_patch){
                $username = UserInfo::getSecMobile($username);
            }
        }
        return $username;
    }


    /**
     * Notes: 根据列表数据补充查询
     * Author: 黎获
     * @param $table
     * @param $list
     * @param $whereField
     * @param string $select
     * @return mixed
     */
    public static function  supplyField($table,$list,$whereField,$select='*')
    {


        $field = $whereField;
        if(is_array($select)){
            if(!in_array($field,$select)){
                $select[]=$field;
            }
        }

        if(strpos($field,' as ')){

            $field_arr =  explode(' as ',$field);
            $sqlWhereField = trim($field_arr[0]);
            $field = trim($field_arr[1]);
        }
        else{
            $sqlWhereField=$field;
        }


        $fields = array();
        $index_list = array();
        foreach($list as $key =>$vol)
        {
            $fields[]=$vol[$field];
            $index_list[$vol[$field]][]=$key;
        }
        $where =array();
        $where[]='and';
        $where[]=['in',$sqlWhereField,$fields];
        $res = $table::find()->select($select)->where($where)->asArray()->all();


        foreach($res as $key =>$vol)
        {
            $field_vol = $vol[$field];
            if(isset($index_list[$field_vol]))
            {

                foreach($index_list[$field_vol] as $in_key =>$in_val){
                    foreach ($vol as $vk =>$vv){
                        $list[$in_val][$vk]=$vv;
                    }

                }
            }
        }

        return $list;
    }

    /**
     * Notes :二维数组排序
     * Author: 黎获
     * @param $data
     * @param $field
     * @param $sort  | SORT_ASC , SORT_DESC
     * @return mixed
     * Time: 2018/11/2 13:57
     */
    static public function arraySort($data,$field,$sort,$field2='',$sort2=''){
        $volume = array_column($data,$field);
        if(!empty($field2) && !empty($sort2)){
            foreach ( $data as $key => $row ){
                $num1[$key] = $row [$field];
                $num2[$key] = $row [$field2];
            }
            array_multisort($num1, constant($sort), $num2, constant($sort2), $data);
        }
        else{
            array_multisort($volume, constant($sort), $data);
        }
        return $data;
    }


    /**
     * Notes : 处理图片
     * Author: 黎获
     * @param $image_url
     * @param $array
     * @return string
     * Time: 2018/10/30 11:19
     */
    static public function dealImage($image_url,$array,$host_prefix=null){
        $prefix = substr($image_url,strripos($image_url,'.'));
        if(!is_array($array)){

            //$image_url = substr($image_url,0,strrpos($image_url,'.'));
            //2018/12/21 江世林 修改处理幅图时 需要判断有没有  .
            if(strrpos($image_url,'.')){
                $image_url = substr($image_url,0,strrpos($image_url,'.'));
            }

            //2018/12/21 江世林 修改处理幅图时 需要判断有没有  _SL
            if(strrpos($image_url,'_SL')){
                $image_url=substr($image_url,0,strrpos($image_url,'_SL'));
            }

            if(!strrpos($image_url,'.') && !strrpos($image_url,'_SL')){
                $image_url = $image_url.'.';
            }

            $image_url = $image_url.'_SL'.$array.'_'.$prefix;
        }
        else{
            $image_url = strstr($image_url,'.',true);
            $image_url = $image_url.'._SL500_SR'.$array[0].','.$array[1].'_'.$prefix;
        }
        if(!empty($host_prefix)){
            $image_url= $host_prefix.$image_url;
        }
        return $image_url;
    }

    /**
     * Notes : 处理字符串 0.1 => 0.10
     * Author: 黎获
     * Time: 2018/11/16 17:44
     * @param $value
     * @param int $suffix_point
     * @return string
     */
    static public function dealMoneyNum($value,$suffix_point=2){
        $value = floatval($value);
        $value = abs($value);
        return sprintf("%.".$suffix_point."f",  $value);
    }

}
