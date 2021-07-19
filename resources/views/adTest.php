<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="utf-8">
    <title>تست پروژه چی بخونم</title>
    <style>
        body {
            background: #f2f3f8;
        }
    </style>
</head>
<body>
<div class="AlaaAdDom" alaa-ad-size="size-width-full"></div>
<script type="text/javascript">
    (function (w, d, i) {
        var fp = 'https://ads.alaatv.com/js/engine.js',
            l = 'AlaaAdEngine',
            s = 'script',
            da = new Date(),
            v = ''.concat(da.getFullYear(),(da.getMonth()+1),da.getDate(),da.getHours()),
            f = d.getElementsByTagName(s)[0],
            j = d.createElement(s);
        w[l] = w[l] || {};
        w[l].UUID=i;
        j.async = true;
        j.src = fp + '?uuid=' + i + '&v=' + v;
        f.parentNode.insertBefore(j, f);
    })(window, document, '<?php if(isset($uuid)) echo $uuid; else echo '' ; ?>');
</script>
</body>
</html>
