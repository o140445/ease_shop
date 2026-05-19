{__NOLAYOUT__}<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>ShopEase</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
{if $url}
    <script type="text/javascript">
        (function () {
            var url = '{$url|htmlentities}';
            if (url.indexOf('javascript:') === 0) {
                history.back();
                return;
            }
            window.location.replace(url);
        })();
    </script>
{/if}
</body>
</html>
