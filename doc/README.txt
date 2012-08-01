=======================================================================================
app 目录存放应用相关的文件

=======================================================================================
var 目录必须可读写，可用于记录日志等

=======================================================================================
lib 目录为依赖的类库目录

=======================================================================================
www 目录为虚拟主机根目录

=======================================================================================
bin 目录为后台脚本目录，以php为后缀的脚本文件均需要php-cli解释器运行，
所有任务均可接受参数-e，指执行环境，同虚拟主机配置的APPLICATION_ENV参数，
可用值一般有 development、production；
所有任务均可接受参数-d，指包含路径，同虚拟主机配置的phpvalue include_path参数，
可用值一般为 /usr/share/php

*sendRoomStatMail_per_day.php 任务需要传递两个参数，
-a为任务分段总数，例如 10，
-i为任务分段编号，例如 0 - 9
执行示例： php sendRoomStatMail_per_day.php -e production -i 1 -a 10

*** 邮件任务依赖 PHPMailer(Version 5.0+)

=======================================================================================
系统启用memcached时，新部署应用必须首先刷新相应的memcached服务器的缓存内容，
可以telnet连接memcached服务器，成功后执行 flush_all 命令

操作示例：
telnet 127.0.0.1 11211
flush_all
=======================================================================================

以下为php.ini推荐的配置，详细选项参考php手册

expose_php = off;

以下为my.cnf推荐的配置，详细选项参考mysql手册
[mysqld]

character_set_server     = utf8
init-connect             = "set names utf8"

=======================================================================================

以下为Apache虚拟主机示例配置：

ServerSignature Off
ServerTokens Prod

<VirtualHost *:80>
	ServerName ftt.net
	ServerAdmin webmaster.ftt@gmail.com

	DocumentRoot "/www/ftt_osv/www"
	php_value include_path "/usr/share/php"
	ErrorLog "/log/ftt_osv_errors.log"
	CustomLog "/log/ftt_osv_common.log" common    

	SetEnv APPLICATION_ENV production

	<Directory "/www/ftt_osv/www">
		Options Indexes MultiViews FollowSymLinks
		AllowOverride None
		Order allow,deny
		Allow from all

        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} -f [OR]
        RewriteCond %{REQUEST_FILENAME} -s [OR]
        RewriteCond %{REQUEST_FILENAME} -l [OR]
        RewriteCond %{REQUEST_FILENAME} -d
        RewriteRule ^.*$ - [NC,L]
        RewriteRule \.ico$ - [NC,L]
        RewriteRule ^.*$ index.php [NC,L]
	</Directory>
</VirtualHost>

=======================================================================================


以下为Ubuntu下PHPUnit2安装：
#!/bin/bash
# sudo
apt-get remove phpunit
pear channel-discover pear.phpunit.de
pear channel-discover components.ez.no
pear update-channels
pear upgrade-all
pear install --alldeps phpunit/PHPUnit
apt-get install phpunit

使用时终端进入 app/tests 目录，直接输phpunit命令
