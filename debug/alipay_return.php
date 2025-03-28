<?php
// 设置错误报告
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 输出所有GET参数
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>支付宝回调测试页面</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .debug-info { background: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 20px; }
        pre { background: #eee; padding: 10px; overflow: auto; }
        h2 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>支付宝回调测试页面</h1>';

// 检查是否有返回参数
if (!empty($_GET)) {
    echo '<div class="debug-info">
            <h2>支付宝回调参数</h2>
            <pre>';
    print_r($_GET);
    echo '</pre>';
    
    // 检查是否支付成功
    if (isset($_GET['trade_status']) && $_GET['trade_status'] == 'TRADE_SUCCESS') {
        echo '<p class="success">支付成功！订单号：'.htmlspecialchars($_GET['out_trade_no']).'</p>';
    } else {
        echo '<p class="error">支付未完成或失败</p>';
    }
    
    echo '</div>';
} else {
    echo '<p class="error">未收到任何支付宝回调参数！</p>';
}

echo '<p><a href="../index.html">返回首页</a></p>
    </div>
</body>
</html>';
?> 