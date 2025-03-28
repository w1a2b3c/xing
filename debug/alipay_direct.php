<?php
// 设置错误报告
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 读取配置文件
$configFile = '../admin/config/payment_config.json';
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
    $alipayConfig = $config['alipay'];
} else {
    die("配置文件不存在");
}

// 生成订单号
$outTradeNo = 'TEST' . time();

// 构建biz_content
$bizContent = json_encode([
    'out_trade_no' => $outTradeNo,
    'product_code' => 'FAST_INSTANT_TRADE_PAY',
    'total_amount' => '0.01', // 测试用1分钱
    'subject' => '测试订单 - ' . $outTradeNo,
    'body' => '测试支付接口'
], JSON_UNESCAPED_UNICODE);

// 当前域名
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . $domainName;

// 支付宝网关
$gatewayUrl = 'https://openapi.alipay.com/gateway.do?charset=UTF-8';

// 组装参数
$params = [
    'app_id' => $alipayConfig['appId'],
    'method' => 'alipay.trade.page.pay',
    'format' => 'JSON',
    'return_url' => $baseUrl . '/debug/alipay_return.php',
    'notify_url' => $baseUrl . '/notify_alipay.php',
    'sign_type' => 'RSA2',
    'timestamp' => date('Y-m-d H:i:s'),
    'version' => '1.0',
    'biz_content' => $bizContent
];

// 生成签名前，为签名字符串添加charset参数
$paramsForSign = $params;
$paramsForSign['charset'] = 'UTF-8';

// 按照字典序排序参数
ksort($paramsForSign);

// 构建签名字符串
$stringToBeSigned = '';
foreach ($paramsForSign as $k => $v) {
    if ($k != "sign" && $v !== "" && !is_null($v)) {
        $stringToBeSigned .= $k . "=" . $v . "&";
    }
}
$stringToBeSigned = substr($stringToBeSigned, 0, -1);

// 输出签名字符串，用于调试
file_put_contents('../logs/alipay_sign_debug.log', date('Y-m-d H:i:s') . " 签名字符串: " . $stringToBeSigned . PHP_EOL, FILE_APPEND);

// 处理私钥格式
$privateKey = $alipayConfig['privateKey'];
if (strpos($privateKey, '-----BEGIN') === false) {
    $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" . 
        wordwrap($privateKey, 64, "\n", true) . 
        "\n-----END RSA PRIVATE KEY-----";
}

// 生成签名
$signature = '';
$binary_signature = '';
openssl_sign($stringToBeSigned, $binary_signature, $privateKey, OPENSSL_ALGO_SHA256);
$signature = base64_encode($binary_signature);
$params['sign'] = $signature;

// 生成表单
$formHtml = '<form id="alipayForm" name="alipayForm" action="' . $gatewayUrl . '" method="POST">';
foreach ($params as $key => $value) {
    $formHtml .= '<input type="hidden" name="' . htmlspecialchars($key, ENT_QUOTES) . '" value="' . htmlspecialchars($value, ENT_QUOTES) . '">';
}
$formHtml .= '<input type="hidden" name="sign" value="' . htmlspecialchars($signature, ENT_QUOTES) . '">';
$formHtml .= '<input type="submit" value="测试支付宝支付" style="padding: 10px 20px; background-color: #1890ff; color: white; border: none; border-radius: 4px; cursor: pointer;">';
$formHtml .= '</form>';

// 显示调试信息
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>支付宝直接支付测试</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .debug-info { background: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 20px; }
        pre { background: #eee; padding: 10px; overflow: auto; }
        h2 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>支付宝直接支付测试</h1>
        <p>此页面用于测试支付宝支付接口，点击下方按钮直接发起支付</p>
        
        ' . $formHtml . '
        
        <div class="debug-info">
            <h2>调试信息</h2>
            <h3>订单信息</h3>
            <pre>订单号: ' . $outTradeNo . '
金额: 0.01元
商品名称: 测试订单 - ' . $outTradeNo . '</pre>
            
            <h3>签名字符串</h3>
            <pre>' . htmlspecialchars($stringToBeSigned) . '</pre>
            
            <h3>签名结果</h3>
            <pre>' . htmlspecialchars($signature) . '</pre>
        </div>
    </div>
</body>
</html>';
?> 