<?php
// 设置错误报告
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 模拟支付宝请求参数
$params = [
    'app_id' => '2021004170655688',
    'method' => 'alipay.trade.page.pay',
    'format' => 'JSON',
    'return_url' => 'http://z.gcbao.cn/pay.html',
    'sign_type' => 'RSA2',
    'timestamp' => '2025-03-27 20:50:18',
    'version' => '1.0',
    'notify_url' => 'http://z.gcbao.cn/notify_alipay.php',
    'biz_content' => '{"out_trade_no":"ORD1743079815115","product_code":"FAST_INSTANT_TRADE_PAY","total_amount":"29.90","subject":"姓名配对 - 我去与我去","body":"姓名配对分析服务"}'
];

// 签名私钥 - 从配置文件中读取
$configFile = '../admin/config/payment_config.json';
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
    $privateKey = $config['alipay']['privateKey'];
} else {
    $privateKey = "MIIEpAIBAAKCAQEAyWAoG2I0iWt1pESi7UbIPkyDCU9wZ9hH7JpWaDcb+KUNwyZM2U6sx6FgYCVD0J1KF6BgMEZHQQQ5sB7RA2ajT9DWyBJz3Z9lCPAEOUZU/NmYoSECQvXpxpUBFiuJIEFGGdP9H2WtfhKGQFJgX3UmW1CouxXu3bmXzGIiZZAFvbjvkIFJzclx6tnEmyRIxlquWXDQHPCMqhhMpJiN/4f1qz7i+AuSDDX/uKwjqlxlnkILevzyVzxtQtmf95EbxdBOis7WEFQSJMSbS4YwUg+5GOgGIYu+HzIWue91UULOjrfScPw0rBpZZL9tjO+qhzPRJbVF2tSxC/f02RPP6jW6DQIDAQABAoIBAQCkj3WcOQjXCAn1zI/cXDJtVn+MlZwVW90Fgy4cjFUPpuFhtAYdO+c2dPwlEhKcrL7uOQ2oYiRwl3z1R2LnJUbZfGM6XWNz7zVYDyQUaMrJCEUP9jKS9XqxKj3jZFyVoSBPeOkNz5mOzULFy2JvdcUfhwuSRuQf/0kA7oTs8a+7M8FKELs28YJL1nbuTNvxyEn8JuYvLnXnT+zxNLpZ/gSEkGDM2++6ZIbRo2+3S5+yQFJbq4fV8CI/SGTJnTv9WmwZl/ooIoVJO51S3KYPoNWrwmJjwuGjAoxOCG9vKxBUcGI13aLLY53KoH9jj3yiwKIaXzJpRYXhsYONHYNE7KQhAoGBAO+lVUV41M/+Q9OGPJ1gILc1/zhvJ2yuRmIcxpEgNztQYh+OGTd98PSSulkn9iQIvjGvkwLXygdAz3HJEto+5a7trTbwm5F5XtUQieJ48FNuDUEU5QPN+A2AWUaI7s1t5QJc6BQDYS+qhygsU4PG3n5xdLWrnfPJAdwa/MlrSCu9AoGBANbhAeYj1ilBmSUbdTXkCSFUCMGZPFa1oKOJL2Q0jOXoUwhC84tJLgfPB3xI//f/b9NKynGvYhxnj51z09gTDQ0EKfegn2kBvlI5FMDRGmM8YKsJMK4TOlBiQzC9TKnYWS6nN1zfK/ry44JVlVQS4OVF6M+n0uURr3lQxoYq3KqxAoGAT5vRLHlSpKhX0rVTXjF+5QlEKKhOSFe9toD2XFrM4T8bY1+6Jzjq+lhL8vXcWn1VJaQXAQMdPSNY59JRcq3/vknULdWjBc64m/obx5S/ST9WFa/89Z8lLRJXMEK1FIh/A2SfOWw8vC28YON2lWGcz/GbwRx+CBtZGmxSJJPL6i0CgYEAijfSsNTTjkR2IbDFOvvwvQdVPHGXehTCEMnMjzLzLM1A+m1W5AS0cLGQ0s4+nZ7Q8YsOBO13MbI67CgdyY1j2XDXY1wp7tXQ5ie1+rnMZnVh0psUZZKVqAkELXnDXHDTOZ/JrI3ceOQQZEIqzKzFgTXBfHQmvGHMu2a2SqyQtIECgYAo8DZkRAzIrqR9gKMNFfSgWbC3vxRz9E1Z7OIsBCjUh4UwcCwSEpH2pWmDJoP8Hy2Wvh8YnQgE4qpKU+6Nko20siRJkp7YpFxOmFYGHxQBpOKIEd2kU9NJw5vIxJJWzGd6/zZZh91WUP12FmOk0+gWLfTGaZFyYGlx/k5JqF1fIw==";
}

// 验证私钥格式
if (strpos($privateKey, '-----BEGIN RSA PRIVATE KEY-----') === false && 
    strpos($privateKey, '-----BEGIN PRIVATE KEY-----') === false) {
    $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" . 
        wordwrap($privateKey, 64, "\n", true) . 
        "\n-----END RSA PRIVATE KEY-----";
}

// 排序参数
ksort($params);

// 构建签名字符串
$stringToSign = "";
foreach ($params as $k => $v) {
    if ($k != "sign" && $v !== "" && !is_null($v)) {
        $stringToSign .= $k . "=" . $v . "&";
    }
}
$stringToSign = substr($stringToSign, 0, -1);

// 显示签名字符串
echo "签名字符串: <pre>" . htmlspecialchars($stringToSign) . "</pre><br>";

// 参考签名字符串
$referenceSignString = "app_id=2021004170655688&biz_content={\"out_trade_no\":\"ORD1743079815115\",\"product_code\":\"FAST_INSTANT_TRADE_PAY\",\"total_amount\":\"29.90\",\"subject\":\"姓名配对 - 我去与我去\",\"body\":\"姓名配对分析服务\"}&format=JSON&method=alipay.trade.page.pay&notify_url=http://z.gcbao.cn/notify_alipay.php&return_url=http://z.gcbao.cn/pay.html&sign_type=RSA2&timestamp=2025-03-27 20:50:18&version=1.0";
echo "参考签名字符串: <pre>" . htmlspecialchars($referenceSignString) . "</pre><br>";

// 对比两个签名字符串是否相同
if ($stringToSign === $referenceSignString) {
    echo "签名字符串匹配成功!<br>";
} else {
    echo "签名字符串匹配失败!<br>";
    // 进一步分析不匹配原因
    $arr1 = explode('&', $stringToSign);
    $arr2 = explode('&', $referenceSignString);
    $diff1 = array_diff($arr1, $arr2);
    $diff2 = array_diff($arr2, $arr1);
    
    if (!empty($diff1)) {
        echo "我们的字符串中多出的部分: <pre>" . print_r($diff1, true) . "</pre><br>";
    }
    
    if (!empty($diff2)) {
        echo "参考字符串中多出的部分: <pre>" . print_r($diff2, true) . "</pre><br>";
    }
}

// 生成签名
$signature = '';
if (function_exists('openssl_sign')) {
    $binary_signature = '';
    $result = openssl_sign($stringToSign, $binary_signature, $privateKey, OPENSSL_ALGO_SHA256);
    if ($result) {
        $signature = base64_encode($binary_signature);
        echo "生成的签名: <pre>" . $signature . "</pre><br>";
    } else {
        echo "签名生成失败! openssl错误: " . openssl_error_string() . "<br>";
    }
} else {
    echo "openssl_sign 函数不存在, 无法生成签名<br>";
}

// 显示PHP版本和OpenSSL版本信息
echo "PHP版本: " . phpversion() . "<br>";
echo "OpenSSL版本: " . OPENSSL_VERSION_TEXT . "<br>";
?> 