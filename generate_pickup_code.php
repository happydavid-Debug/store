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

// 生成唯一的六位数字取件码
function generatePickupCode($conn) {
    do {
        $pickupCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $stmt = $conn->prepare("SELECT id FROM file_info WHERE code =?");
        $stmt->bind_param("s", $pickupCode);
        $stmt->execute();
        $result = $stmt->get_result();
        $isDuplicate = $result->num_rows > 0;
        $stmt->close();
    } while ($isDuplicate);

    return $pickupCode;
}

$pickupCode = generatePickupCode($conn);

echo json_encode([
    "status" => "success",
    "pickupCode" => $pickupCode
]);

$conn->close();
?>