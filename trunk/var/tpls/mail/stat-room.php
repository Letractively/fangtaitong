<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
<body>
    <? if ($tasks): ?>
    亲，您很久没有处理订单了，有<?= $tasks ?>个待办事项未处理，立即<a href="<?= URL_FTT ?>">打开房态通</a>(<?= URL_FTT ?>)处理吧。<br />
    <? endif; ?>
    您昨天的入住率是<?= $livep ?>%，<?= $livep > 90 ? '很棒啊，再接再厉哦！' : ($livep > 70 ? '成绩不错，还可以做得更好哦！' : '加油，功夫不负有心人哦！') ?>及时地对订单处理，能提高入住率和客户满意度。<br />
    本邮件为系统自动发送，请勿回复。
</body>
</html>
