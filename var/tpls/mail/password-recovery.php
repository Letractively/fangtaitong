<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
    <?= htmlspecialchars($user['u_realname']) ?>，您好！<br />
    您于<?= date('Y-m-d H:i:s', $time) ?>申请了通过电子邮件重置您的房态通密码，请访问以下网页修改你的密码: <br />
    <a href="<?= $goto ?>" target="_blank"><?= $goto ?></a>
    <br />

    如果您从未申请密码重置，请忽略此信息。<br />
    <br />
    此邮件由系统自动生成，请勿回复。
</body>
</html>
