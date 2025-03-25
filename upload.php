<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "###";
$password = "###";
$dbname = "###";


// 创建数据库连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接是否成功
if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "数据库连接失败: " . $conn->connect_error
    ]));
}

// 检查请求方法
if ($_SERVER['REQUEST_METHOD']!== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "仅支持POST请求"
    ]);
    exit;
}

// 检查文件是否存在
if (!isset($_FILES['file'])) {
    echo json_encode([
        "status" => "error",
        "message" => "未接收到文件"
    ]);
    exit;
}

// 获取取件码和加密后的取件码以及密码
$pickupCode = $_POST['code']?? null;
$encryptedCodeFromClient = $_POST['encrypted_code']?? null;
$password = $_POST['password']?? null;

if (!$pickupCode) {
    echo json_encode([
        "status" => "error",
        "message" => "缺失取件码参数"
    ]);
    exit;
}

// 获取客户端 IP 地址
$clientIP = $_SERVER['REMOTE_ADDR'];

// 文件保存路径
$targetDir = "uploads/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// 处理文件名（确保文件名安全）
$originalFileName = $_FILES['file']['name'];
$fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
$safeFileName = md5(uniqid()) . '.' . $fileExtension; // 生成唯一文件名
$targetFile = $targetDir. $safeFileName;

// 移动文件
if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
    // 如果前端传递了加密后的取件码，则使用该加密后的取件码
    // 否则，如果有密码，则在后端进行加密
    function encrypt($text, $password) {
        $encrypted = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $charCodeText = ord($text[$i]);
            $charCodePassword = ord($password[$i % strlen($password)]);
            $xorResult = $charCodeText ^ $charCodePassword;
            $encrypted.= chr($xorResult);
        }
        return base64_encode($encrypted);
    }
    $encryptedCode = $encryptedCodeFromClient? $encryptedCodeFromClient : ($password? encrypt($pickupCode, $password) : null);

    // 插入数据到数据库
    $stmt = $conn->prepare("INSERT INTO file_info (code, encrypted_code, file_path, original_file_name, ip_address) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssss", $pickupCode, $encryptedCode, $targetFile, $originalFileName, $clientIP);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "文件上传成功",
            "fileName" => $originalFileName // 返回原始文件名
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "数据库插入失败: " . $stmt->error
        ]);
    }
    $stmt->close();
} else {
    echo json_encode([
        "status" => "error",
        "message" => "文件上传失败"
    ]);
}

$conn->close();
?>