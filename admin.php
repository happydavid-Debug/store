<?php
// 数据库连接信息
$servername = "localhost";
$username = "###";
$password = "###";
$dbname = "###";

// 创建数据库连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接是否成功
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 查询上传记录
$sql = "SELECT code, original_file_name, ip_address, timestamp FROM file_info";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员检测网站</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h1>文件上传记录</h1>
    <table>
        <tr>
            <th>取件码</th>
            <th>文件名</th>
            <th>IP 地址</th>
            <th>上传时间</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["code"] . "</td>";
                echo "<td>" . $row["original_file_name"] . "</td>";
                echo "<td>" . $row["ip_address"] . "</td>";
                echo "<td>" . date('Y-m-d H:i:s', strtotime($row["timestamp"])) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>暂无上传记录</td></tr>";
        }
        ?>
    </table>
</body>

</html>
<?php
$conn->close();
?>