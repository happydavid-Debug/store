文件上传项目部署指南​
项目概述​
本项目实现了一个具备文件上传和管理功能的系统。用户能够通过前端页面上传文件，并获取唯一的取件码。管理员则可通过特定页面查看所有文件的上传记录，包括取件码、文件名、上传者 IP 地址及上传时间。系统采用前后端分离架构，前端使用 HTML、CSS 和 JavaScript 构建用户界面，后端基于 PHP 语言与 MySQL 数据库进行数据交互和存储。​
环境要求​
服务器环境​
操作系统：推荐使用 Linux 系统，如 Ubuntu、CentOS 等，也支持 Windows Server 系统。​
Web 服务器：安装 Apache 或 Nginx。​
PHP 环境：PHP 7.2 及以上版本，并确保安装了以下扩展：​
mysqli：用于连接和操作 MySQL 数据库。​
fileinfo：用于处理文件相关信息。​
openssl：若涉及加密功能（如取件码加密），需此扩展支持。​
数据库​
MySQL 数据库：8.0 及以上版本。需提前创建好数据库及相关表结构。数据库表结构如下：​
​
CREATE TABLE uploads (​
    id INT AUTO_INCREMENT PRIMARY KEY,​
    pickup_code VARCHAR(255) NOT NULL,​
    encrypted_code VARCHAR(255),​
    file_path VARCHAR(255) NOT NULL,​
    original_file_name VARCHAR(255) NOT NULL,​
    upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,​
    ip_address VARCHAR(45) NOT NULL​
);​
​
客户端​
浏览器：支持主流浏览器，如 Chrome、Firefox、Edge 等，建议使用最新版本以获得最佳体验。​
部署步骤​
后端部署​
克隆项目代码：从代码仓库将后端代码克隆到服务器指定目录，例如/var/www/html/file-upload-backend。若没有代码仓库，可直接将代码文件上传至服务器对应目录。​
配置数据库连接：打开后端代码中的upload.php和admin.php文件，找到数据库连接配置部分，修改以下信息：​
​
// 数据库连接信息​
$servername = "localhost";​
$username = "你的数据库用户名";​
$password = "你的数据库密码";​
$dbname = "你的数据库名";​
​
设置文件上传目录权限：确保项目中的文件上传目录（如uploads目录）具有可写权限，可通过以下命令设置：​
​
chmod -R 777 /var/www/html/file-upload-backend/uploads​
​
前端部署​
由于 CSS 和 JavaScript 代码均直接写在 HTML 文件内，前端部署时，仅需将包含完整代码的 HTML 文件上传至 Web 服务器的文档根目录，例如/var/www/html/file-upload-frontend。确保 HTML 文件命名规范，且无路径引用错误。​
配置 Web 服务器​
Apache 服务器​
创建虚拟主机配置文件：在 Apache 的虚拟主机配置目录（通常为/etc/apache2/sites-available）下创建一个新的配置文件，例如file-upload.conf。​
配置虚拟主机：在file-upload.conf文件中添加如下内容，根据实际情况修改DocumentRoot路径：​
​
<VirtualHost *:80>​
    ServerName your_domain.com​
    DocumentRoot /var/www/html/file-upload-frontend​
    <Directory /var/www/html/file-upload-frontend>​
        Options Indexes FollowSymLinks​
        AllowOverride All​
        Require all granted​
    </Directory>​
</VirtualHost>​
​
启用虚拟主机：运行以下命令启用新创建的虚拟主机：​
​
a2ensite file-upload.conf​
​
重启 Apache 服务：使配置生效：​
​
systemctl restart apache2​
​
Nginx 服务器​
创建 Nginx 配置文件：在 Nginx 的配置目录（通常为/etc/nginx/conf.d）下创建一个新的配置文件，例如file-upload.conf。​
配置 Nginx：在file-upload.conf文件中添加如下内容，根据实际情况修改root路径：​
​
server {​
    listen 80;​
    server_name your_domain.com;​
    root /var/www/html/file-upload-frontend;​
    index index.html;​
    location / {​
        try_files $uri $uri/ =404;​
    }​
}​
​
检查 Nginx 配置语法：运行以下命令检查配置文件语法是否正确：​
​
nginx -t​
​
重启 Nginx 服务：使配置生效：​
​
systemctl restart nginx​
​
常见问题及解决办法​
文件上传失败：​
原因：可能是文件上传目录权限不足，或者 PHP 配置中的上传文件大小限制过小。​
解决办法：确保文件上传目录具有可写权限（如上述设置chmod -R 777）。同时，检查 PHP 配置文件（php.ini）中的upload_max_filesize和post_max_size参数，适当调大数值，例如：​
​
upload_max_filesize = 50M​
post_max_size = 50M​
​
修改后需重启 Web 服务器和 PHP-FPM 服务（若使用 PHP-FPM）。​
2. 数据库连接失败：​
原因：数据库连接配置错误，或者数据库服务未正常运行。​
解决办法：仔细检查upload.php和admin.php中的数据库连接配置信息是否正确。同时，通过命令行工具（如mysql -u用户名 -p）尝试连接数据库，检查数据库服务是否正常。若数据库服务未运行，启动数据库服务：​
​
systemctl start mysql​
​
前端页面无法加载资源：​
原因：资源文件路径错误，或者 Web 服务器配置问题。虽然 CSS 和 JavaScript 代码内联，但仍可能存在 HTML 文件路径引用错误。​
解决办法：仔细检查 HTML 文件内涉及的相对路径引用，确保无误。同时，检查 Web 服务器的配置，确保正确配置了文档根目录和静态资源的访问规则。​
注意事项​
安全方面：在生产环境中，应避免将数据库用户名和密码明文写在代码中，建议使用环境变量或配置文件加密等方式进行安全配置。同时，对用户上传的文件进行严格的安全检查，防止上传恶意文件。​
性能优化：随着文件上传数量的增加，数据库和服务器性能可能受到影响。可以考虑对数据库进行索引优化，对文件存储进行分布式部署等方式提升性能。​
备份与恢复：定期对数据库和上传的文件进行备份，以防数据丢失。制定数据恢复策略，确保在出现问题时能够快速恢复数据。