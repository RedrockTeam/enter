<?php
    namespace Home\Controller;
    use Think\Controller;

    class IndexController extends BaseController {

        

        private function _ajaxReturn($data, $info, $status) {
            return $this->ajaxReturn(array(
                'data' => $data,
                'info' => $info,
                'status' => $status
            ));
        }


        public function login() {
            if (IS_GET) {
                $this->display();
            } else {
                $manager_name = I('post.manager_name');
                $manager_pass = I('post.manager_pass');
                $manager_pass = sha1(C('secret')).md5($manager_pass);
                $manager_info = M('manager')->where("manager_name = '$manager_name' AND manager_pass = '$manager_pass'")->find();
                if ($manager_info) {
                    session('manager_id', $manager_info['manager_id']);
                    $this->redirect("index");
                } else {
                    $this->error("你输入的密码或用户名有误!");
                }
            }
        }

        public function score_enter() {

            $data = I('post.');
            $flag = true;
            $_data = [];

            $User = M('user');
            $UserDep = M('user_dep');

            foreach ($data['user_dep'] as $dep_id) {
                switch ($dep_id) {
                    case 1:
                        $dep_name = "前端";
                        break;
                    case 2:
                        $dep_name = "后台";
                        break;
                    case 3:
                        $dep_name = "iOS";
                        break;
                    case 4:
                        $dep_name = "Android";
                        break;
                    case 5:
                        $dep_name = "WP";
                        break;
                    case 6:
                        $dep_name = "无";
                        break;
                }
                $map["user_id"] = $data['user_id'];
                $map["dep_name"] = $dep_name;
                $map["dep_id"] = $dep_id;
                if ($UserDep->where($map)->find()) {
                    continue;
                } else {
                    $_add = $UserDep->add($map);
                    if (!$_add) {
                        $flag = false;
                        break;
                    }
                }
            }

            if ($flag) {
                $_save = $User->where("user_id = {$data['user_id']}")->save(array(
                    "user_score" => $data['user_score'],
                    "user_gender" => $data['user_gender']
                ));
                if ($data['user_gender'] == 1) {
                    $_data['user_gender'] = '男';
                } else {
                    $_data['user_gender'] = '女';
                }
                $_data['user_score'] = $data['user_score'];
                $user_dep = $UserDep->where("user_id = {$data['user_id']}")->getField("dep_name", true);
                $_data['user_dep'] = $this->_depDeal($user_dep);
            } else {
                return $this->_ajaxReturn($_data, 'OK', 0);
            }


            if ($_save) {
                return $this->_ajaxReturn($_data, 'OK', 200);
            } else {
                return $this->_ajaxReturn($_data, 'OK', 0);
            }
        }

        private function _depDeal($text) {
            $text = implode("-", $text);
            $text = mb_substr($text, 0, mb_strlen($text), 'utf-8');
            return $text;
        }

        public function index() {

            $User = M('user');
            $UserDep = M('user_dep');

            if (!session('manager_id')) {
                $this->error("请登录", "login", 2);
            }

            /*if ($_FILES['excel']) {
                $result = $this->_excelUpload($_FILES['excel']);
                foreach ($result['data'] as $key => $value) {
                    $_info = [
                        "user_name" => $value['A'],
                        "user_academy" => $value['B'],
                        "user_tel" => $value['C']
                    ];
                    $User->add($_info);
                }
            }*/

            $lists['data'] = $User->limit($Page->firstRow.','.$Page->listRows)->select();
            foreach ($lists['data'] as $index => $info) {
                $lists['data'][$index]['user_dep'] = $this->_depDeal($UserDep->where("user_id = {$info['user_id']}")->getField("dep_name", true));
            }
            $this->assign('lists', $lists);
            $this->display();
            
        }



        private function sort($array, $sort, $field) {
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



        public function message() {
            $dep_id = I('get.id');
            $User = M('user');
            $UserDep = M('user_dep');
            $Message = M('message');
            $users = [];

            $user_ids = $UserDep->where("dep_id = '$dep_id'")->getField("user_id", true);

            foreach ($user_ids as $user_id) {
                $user = $User->where("user_id = '$user_id'")->find();
                if ($dep_id == 1 || $dep_id == 2) {
                    $user['user_no'] = 'W'.$user['user_id'];
                } else {
                    $user['user_no'] = 'M'.$user['user_id'];
                }
                if ($Message->where("user_id = {$user['user_id']}")->find()) {
                    $user['is_message'] = 1;
                } else {
                    $user['is_message'] = 0;
                }
                $user['user_dep'] = $this->_depDeal($UserDep->where("user_id = $user_id")->getField("dep_name", true));
                array_push($users, $user);
            }

            $this->assign('users', array_sort($users, 'user_score'));
            $this->display();
        }

        public function send_message() {

            $Message = M('message');

            $user_no = I('post.user_no');
            $user_id = mb_substr($user_no, 1, mb_strlen($user_no), 'utf-8');
            $user_tel = I('post.user_tel');

            if ($Message->where("user_id = $user_id")->find()) {
                $this->_ajaxReturn(null, '此用户已经发过短信了!', 100);
            }

            $tpl_value = "#no#=".$user_no;
            $response = $this->tpl_send_sms(985713, $tpl_value, $user_tel);
            $response = json_decode($response);
            if ($response->code === 0) {
                $_add = $Message->add(array(
                    'user_id' => $user_id
                ));
                if ($_add) {
                    $this->_ajaxReturn(null, '发送成功!', 200);
                }
            } else if ($response->code === 3) {
                $this->_ajaxReturn(null, '账户余额不足!', 0);
            } else if ($response->code === 7) {
                $this->_ajaxReturn(null, '模板不可用!', 0);
            } else if ($response->code === 8) {
                $this->_ajaxReturn(null, '同一手机号30秒内重复提交相同的内容!', 0);
            } else if ($response->code === 9) {
                $this->_ajaxReturn(null, '同一手机号5分钟内重复提交相同的内容超过3次!', 0);
            } else if ($response->code === -4) {
                $this->_ajaxReturn(null, '访问次数超限!', 0);
            } else if ($response->code === -50) {
                $this->_ajaxReturn(null, '未知异常!', 0);
            }
        }



        public function add_user() {
            if (IS_GET) {
                $this->display("user");
            } else {
                $flag = true;


                $user_dep = I('post.user_dep');
                $UserDep = M('user_dep');
                $data = I('post.');


                $_data = array(
                    "user_name" => $data['user_name'],
                    "user_tel" => $data['user_tel'],
                    "user_academy" => $data['user_academy'],
                    "user_gender" => $data['user_gender'],
                    "user_score" => $data['user_score']
                );
                $user_id = M('user')->add($_data);
                if ($user_id) {
                    foreach ($user_dep as $dep_id) {
                        switch ($dep_id) {
                            case 1:
                                $dep_name = "前端";
                                break;
                            case 2:
                                $dep_name = "后台";
                                break;
                            case 3:
                                $dep_name = "iOS";
                                break;
                            case 4:
                                $dep_name = "Android";
                                break;
                            case 5:
                                $dep_name = "WP";
                                break;
                            case 6:
                                $dep_name = "无";
                                break;
                        }
                        $map["user_id"] = $user_id;
                        $map["dep_name"] = $dep_name;
                        $map["dep_id"] = $dep_id;
                        $_add = $UserDep->add($map);
                        if (!$_add) {
                            $flag = false;
                            break;
                        }
                    }

                    if ($flag) {
                        $this->success("录入成功", 'add_user', 1);
                    } else {
                        $this->error("录入失败", 'add_user', 1);
                    }
                }
            }
        }


        
            	
    }