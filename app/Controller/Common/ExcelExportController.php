<?php

namespace App\Controller\Common;

use App\Middleware\RequestMiddleware;
use App\Controller\AbstractController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;


/**
 * Excel导出控制器
 * @Controller(prefix="excel")
 */
class ExcelExportController extends AbstractController
{
    /**
     * 获取Excel内容数据
     * @RequestMapping(path="excel_content", methods="post")
     * @Middleware(RequestMiddleware::class)
     */
    public function getExcelContent()
    {
        $requestParams = $this->request->all();
        $data = $requestParams['data'] ?? []; //数据模块
        $percentArr = $requestParams['percent_arr'] ?? []; //是否百分比的字段
        $tableHeader =  $requestParams['table_header'] ?? []; //表头字段
        $tableHeaderMean =  $requestParams['table_header_mean'] ?? []; //表头字段解释
        $stringArr = $requestParams['string_arr'] ?? []; // 需要以字符形式导出的字段（防止科学计数法格式）
        $time =  $requestParams['time'] ?? 'time'; //时间字段

        $tableContent = '';
        $tableContent .= '<table id="tables"><tbody><tr>';
        foreach ($tableHeader as $k => $v) {
            $tableContent .= '<th>' . $tableHeaderMean[$v] . '</th>';
        }
        $tableContent .= '</tr>';
        foreach($data as $k => $v) {
            $tableContent .= '<tr>';
            foreach ($tableHeader as $k1 => $v1) {
                //判断是否为时间戳格式
                if (($v1 == $time || preg_match('/_time/', $v1)) && is_numeric($v[$v1])) {
                    if (in_array($v1, $stringArr)) {
                        $tableContent .= '<td style="mso-number-format:\'\@\';" data-tableexport-msonumberformat="\@">' . date('Y-m-d H:i:s', $v[$v1]) . '</td>';
                    } else {
                        $tableContent .= '<td>' . date('Y-m-d H:i:s', $v[$v1]) . '</td>';
                    }
                }else {
                    if (in_array($v1, $stringArr)) {
                        $tableContent .= '<td style="mso-number-format:\'\@\';" data-tableexport-msonumberformat="\@">' . $v[$v1] . ((in_array($v1, $percentArr)) ? '%' : '') . '</td>';
                    } else {
                        $tableContent .= '<td>' . $v[$v1] . ((in_array($v1, $percentArr)) ? '%' : '') . '</td>';
                    }
                }
            }
            $tableContent .= '</tr>';
        }
        $tableContent .= '</tbody></table>';
        return $this->success([
            'excel_content' => $tableContent
        ]);
    }
}
