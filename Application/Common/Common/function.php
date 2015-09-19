<?php 

	function arraySort($nearybyUsers, $sort, $field){
        $sort = array(
            'direction' => $sort, //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field'     => $field,       //排序字段
        );
        $arrSort = array();
        foreach($nearybyUsers as $uniqid => $row){
            foreach($row as $key=>$value){
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if($sort['direction']){
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $arrUsers);
        }
        return $nearybyUsers;
    }


    function array_sort($arr,$keys,$type='desc'){   
	    $keysvalue = $new_array = array();  
	    foreach ($arr as $k => $v){  
	        $keysvalue[$k] = $v[$keys];  
	    }  
	    if($type == 'asc'){  
	        asort($keysvalue);  
	    }else{  
	        arsort($keysvalue);  
	    }  
	    reset($keysvalue);  
	    foreach ($keysvalue as $k=> $v){  
	        $new_array[$k] = $arr[$k];  
	    }  
	    return $new_array;   
	}   
	
	function array2sort($a,$sort,$d) {
	    $num=count($a);
	    if(!$d){
	        for($i=0;$i<$num;$i++){
	            for($j=0;$j<$num-1;$j++){
	                if($a[$j][$sort] > $a[$j+1][$sort]){
	                    foreach ($a[$j] as $key=>$temp){
	                        $t=$a[$j+1][$key];
	                        $a[$j+1][$key]=$a[$j][$key];
	                        $a[$j][$key]=$t;
	                    }
	                }
	            }
	        }
	    } else {
	        for($i=0;$i<$num;$i++){
	            for($j=0;$j<$num-1;$j++){
	                if($a[$j][$sort] < $a[$j+1][$sort]){
	                    foreach ($a[$j] as $key=>$temp){
	                        $t=$a[$j+1][$key];
	                        $a[$j+1][$key]=$a[$j][$key];
	                        $a[$j][$key]=$t;
	                    }
	                }
	            }
	        }
	    }
	    return $a;
	}

?>