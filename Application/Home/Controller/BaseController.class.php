<?php
    namespace Home\Controller;
    use Think\Controller;

    class BaseController extends Controller {


    	public function code($code) {
            echo "<pre style=\"color: red\">";
            print_r($code);
            echo "</pre>";
        }

    	/**
         * @param $excelFile
         * @return array
         */
        private function _excelUpload($excelFile){

            // header("Content-Type:text/html;charset=utf-8");
            $config = C('uploadConfig');
            $config['savePath'] = 'upload/admin/device/';
            $config['exts'] = array('xls', 'xlsx'); // 设置附件上传类
            $Upload = new \Think\Upload($config);
            $uploadInfo = $Upload->uploadOne($excelFile);
            $excelFileName = 'Public/'.$uploadInfo['savepath'].$uploadInfo['savename']; // 已上传文件
            $excelFileExts = $uploadInfo['ext']; // 文件后缀类型

            if (!$uploadInfo) {    // 上传错误提示错误信息
                $this->error($Upload->getError());
            } else {   // 上传成功
                return $this->_excelParse($excelFileName, $excelFileExts);
            }
        }

        /**
         * @param $excelFileName
         * @param $excelFileExts
         * @return array
         * @throws \PHPExcel_Exception
         */
        private function _excelParse($excelFileName, $excelFileExts){

            import("Org.Util.PHPExcel");
            import("Org.Util.PHPExcel.Shared.Date");
            $PHPDate = new \PHPExcel_Shared_Date();
            if ($excelFileExts == 'xls') {
                import("Org.Util.PHPExcel.Reader.Excel5");
                $PHPReader = new \PHPExcel_Reader_Excel5();
            } else if ($excelFileExts == 'xlsx') {
                import("Org.Util.PHPExcel.Reader.Excel2007");
                $PHPReader = new \PHPExcel_Reader_Excel2007();
            }

            $PHPExcel = $PHPReader->load($excelFileName);
            // 获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
            $currentSheet = $PHPExcel->getSheet(0);
            // 获取总列数
            $allColumn = $currentSheet->getHighestColumn();
            // 获取总行数
            $allRow = $currentSheet->getHighestRow();
            // 循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
            for($currentRow = 2; $currentRow <= $allRow; $currentRow++){
                // 从哪列开始，A表示第一列
                for($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++){
                    // 数据坐标
                    $address = $currentColumn.$currentRow;
                    $excelData[$currentRow][$currentColumn] = (string)$currentSheet->getCell($address)->getValue();
                    
                }
            }

            return [
                'path' => $excelFileName,
                'data' => $excelData
            ];
        }


        public function tpl_send_sms($tpl_id, $tpl_value, $mobile){
            $apikey = C('apikey');
            $url = "http://yunpian.com/v1/sms/tpl_send.json";
            $encoded_tpl_value = urlencode("$tpl_value");
            $post_string = "apikey=$apikey&tpl_id=$tpl_id&tpl_value=$encoded_tpl_value&mobile=$mobile";
            return $this->sock_post($url, $post_string);
        }

        
        private function sock_post($url,$query){
            $data = "";
            $info=parse_url($url);
            $fp=fsockopen($info["host"],80,$errno,$errstr,30);
            if(!$fp){
                return $data;
            }
            $head="POST ".$info['path']." HTTP/1.0\r\n";
            $head.="Host: ".$info['host']."\r\n";
            $head.="Referer: http://".$info['host'].$info['path']."\r\n";
            $head.="Content-type: application/x-www-form-urlencoded\r\n";
            $head.="Content-Length: ".strlen(trim($query))."\r\n";
            $head.="\r\n";
            $head.=trim($query);
            $write=fputs($fp,$head);
            $header = "";
            while ($str = trim(fgets($fp,4096))) {
                $header.=$str;
            }
            while (!feof($fp)) {
                $data .= fgets($fp,4096);
            }
            return $data;
        }
        	
    }