<?php

namespace app\api\service;


class ExcelService
{

    protected static $phpExcelModel;

    public function __construct()
    {
        vendor('PHPExcel.PHPExcel');
        self::$phpExcelModel = new \PHPExcel();


    }

    /**
     * @function   generalExcel 生成想要的Excel
     *
     * @param $field
     * @param $list
     * @param string $title
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     * @author admin
     *
     * @date 2019/5/5 11:00
     */
    public function generalExcel($field, $list, $title='文件')
    {
        $objWriter = new \PHPExcel_Writer_Excel5(self::$phpExcelModel); //设置保存版本格式
        foreach ($list as $key => $value) {
            foreach ($field as $k => $v) {

                if ($key == 0) {
                    if($v['2'] )
                        self::$phpExcelModel ->getActiveSheet()->setCellValue($k . '1', $v[1])->getColumnDimension($k)->setWidth($v['3']);
                    else
                        self::$phpExcelModel ->getActiveSheet()->setCellValue($k . '1', $v[1]);
                }
                $i = $key + 2; //表格是从2开始的]

                if($v['2'] )
                    self::$phpExcelModel ->getActiveSheet()->setCellValue($k . $i, $value[$v[0]])->getColumnDimension($k)->setWidth($v['3']);
                else
                    self::$phpExcelModel ->getActiveSheet()->setCellValue($k . $i, $value[$v[0]]);
            }

        }
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename='.$title.'.xls');
        header("Content-Transfer-Encoding:binary");

        $objWriter->save('php://output');


    }

}
