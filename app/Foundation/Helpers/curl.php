<?PHP
if (!function_exists('curl_get')) {
    // 传递数据以易于阅读的样式格式化后输出
    function curl_get($apiUrl = '', $sendData = [], $header = [], $cookie = '')
    {
        if (!empty($sendData)) $apiUrl .= '?' . http_build_query($sendData);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        if(!empty($header)){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_HEADER, 1);
        }
        if (!empty($cookie))  curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        $response = curl_exec($curl);
        if (empty($response)) Throw new Exception(curl_error($curl), 400);
        curl_close($curl);

        return json_decode($response, true);
    }
}

if (!function_exists('curl_post')) {
    /**
     * CURL post请求
     * @param $apiUrl
     * @param $sendData
     * @param array $header
     * @param array $cookIe
     * @param bool $isReturnResponse
     * @return mixed
     * @throws Exception
     */
    function curl_post($apiUrl, $sendData, array $header = [], array $cookIe = [], $isReturnResponse = false)
    {
        $curl = curl_init();
        //判断请求头部，如果为空，默认Json传输
        if (empty($header)){
            $header[] = 'Content-Type:application/json';
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
        } else {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $sendData);
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_URL, $apiUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        if (!empty($cookIe))  curl_setopt($curl,CURLOPT_COOKIE, $cookIe);

        $response = curl_exec($curl);
        if (empty($response)) Throw new Exception(curl_error($curl),\App\Constants\StatusCode::ERR_SERVER);
        curl_close($curl);

        $result =  json_decode($response, true);
        if (is_null($result)) {
            \App\Foundation\Facades\Log::curlLog()->info($response);
            Throw new Exception('接口地址：' . $apiUrl . ' 接口返回结果不是json格式', \App\Constants\StatusCode::ERR_SERVER);
        }

        if ($isReturnResponse) return $response;

        return $result;
    }
}