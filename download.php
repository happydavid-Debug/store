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

// 封装加密函数
function encrypt($text, $password) {
    $encrypted = '';
    $passwordLength = 0;
    while (isset($password[$passwordLength])) {
        $passwordLength++;
    }
    $textLength = 0;
    while (isset($text[$textLength])) {
        $textLength++;
    }
    for ($i = 0; $i < $textLength; $i++) {
        $encrypted .= chr(ord($text[$i]) ^ ord($password[$i % $passwordLength]));
    }
    return base64_encode($encrypted);
}

// 封装解密函数
function decrypt($encrypted, $password) {
    $encrypted = base64_decode($encrypted);
    if ($encrypted === false) {
        throw new Exception("Base64 解码失败");
    }
    $decrypted = '';
    $passwordLength = 0;
    while (isset($password[$passwordLength])) {
        $passwordLength++;
    }
    $encryptedLength = 0;
    while (isset($encrypted[$encryptedLength])) {
        $encryptedLength++;
    }
    for ($i = 0; $i < $encryptedLength; $i++) {
        $decrypted .= chr(ord($encrypted[$i]) ^ ord($password[$i % $passwordLength]));
    }
    return $decrypted;
}

// 检查请求方法
if ($_SERVER['REQUEST_METHOD']!== 'POST') {
    try {
        throw new Exception("仅支持POST请求，下载失败");
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
        exit;
    }
}

// 获取参数并过滤
$pickupCode = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

if (!$pickupCode) {
    try {
        throw new Exception("取件码不能为空，下载失败");
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
        exit;
    }
}

$encryptedCode = null;
if ($password) {
    $encryptedCode = encrypt($pickupCode, $password);
}

$fileFound = false;

// 查询数据库
if (!$password) {
    $stmt = $conn->prepare("SELECT file_path, original_file_name FROM file_info WHERE code =?");
    $stmt->bind_param("s", $pickupCode);
} else {
    $stmt = $conn->prepare("SELECT file_path, original_file_name, encrypted_code FROM file_info WHERE code =?");
    $stmt->bind_param("s", $pickupCode);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!$password) {
            echo json_encode([
                "status" => "success",
                "filePath" => $row['file_path'],
                "fileName" => $row['original_file_name']
            ]);
            $fileFound = true;
            break;
        } else {
            if (isset($row['encrypted_code'])) {
                $decrypted = decrypt($row['encrypted_code'], $password);
                if ($decrypted === $pickupCode) {
                    echo json_encode([
                        "status" => "success",
                        "filePath" => $row['file_path'],
                        "fileName" => $row['original_file_name']
                    ]);
                    $fileFound = true;
                    break;
                }
            }
        }
    }
}

$stmt->close();

if (!$fileFound) {
    try {
        throw new Exception("未找到对应的文件，下载失败");
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }
}

$conn->close();
?>