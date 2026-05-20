<?php
$cdnurl = function_exists('config') ? config('view_replace_str.__CDN__') : '';
$publicurl = function_exists('config') ? (config('view_replace_str.__PUBLIC__') ?: '/') : '/';
$debug = function_exists('config') ? config('app_debug') : false;
$rawMessage = isset($message) ? (string)$message : '';
$isMissingRoute = (bool)preg_match('/(module|controller|method|action) not exists|模块不存在|控制器不存在|方法不存在/i', $rawMessage);
$title = $isMissingRoute ? 'Page not found' : 'Page unavailable';
$headline = $isMissingRoute ? '404' : 'Oops';
$summary = $isMissingRoute ? 'The page you are looking for does not exist.' : 'The page is temporarily unavailable.';
$detail = 'Please return to the homepage or go back and try again.';
$homeUrl = $publicurl ?: '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?> - Shop Ease</title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?php echo $cdnurl; ?>/assets/img/favicon.ico">
    <style>
        * { box-sizing: border-box; }
        html, body { min-height: 100%; margin: 0; }
        body {
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            color: #111827;
            background: #f5f7fb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 18px;
        }
        .shop-error-card {
            width: min(680px, 100%);
            background: #fff;
            border: 1px solid #eef1f5;
            border-radius: 18px;
            box-shadow: 0 22px 60px rgba(15, 23, 42, .08);
            padding: 54px 46px;
            text-align: center;
        }
        .shop-error-mark {
            width: 104px;
            height: 104px;
            margin: 0 auto 24px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #ef0d1a, #ff5d65);
            color: #fff;
            font-size: 44px;
            font-weight: 800;
            line-height: 1;
        }
        .shop-error-card h1 {
            margin: 0;
            font-size: 42px;
            line-height: 1.15;
            letter-spacing: 0;
        }
        .shop-error-card p {
            margin: 16px auto 0;
            max-width: 460px;
            color: #64748b;
            font-size: 17px;
            line-height: 1.7;
        }
        .shop-error-actions {
            margin-top: 34px;
            display: flex;
            justify-content: center;
            gap: 16px;
        }
        .shop-error-actions a {
            min-width: 150px;
            height: 48px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
        }
        .shop-error-actions a:first-child {
            background: #ef0d1a;
            color: #fff;
            box-shadow: 0 12px 28px rgba(239, 13, 26, .18);
        }
        .shop-error-actions a:last-child {
            background: #fff;
            color: #111827;
            border: 1px solid #dbe3ee;
        }
        .shop-error-debug {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #eef1f5;
            color: #94a3b8;
            font-size: 13px;
            word-break: break-word;
        }
        @media (max-width: 560px) {
            body { align-items: stretch; padding: 0; background: #fff; }
            .shop-error-card {
                min-height: 100vh;
                border: 0;
                border-radius: 0;
                box-shadow: none;
                padding: 72px 24px 34px;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            .shop-error-card h1 { font-size: 34px; }
            .shop-error-card p { font-size: 15px; }
            .shop-error-actions { flex-direction: column; gap: 12px; }
            .shop-error-actions a { width: 100%; }
        }
    </style>
</head>
<body>
<main class="shop-error-card">
    <div class="shop-error-mark"><?php echo htmlspecialchars($headline, ENT_QUOTES, 'UTF-8'); ?></div>
    <h1><?php echo htmlspecialchars($summary, ENT_QUOTES, 'UTF-8'); ?></h1>
    <p><?php echo htmlspecialchars($detail, ENT_QUOTES, 'UTF-8'); ?></p>
    <div class="shop-error-actions">
        <a href="<?php echo htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8'); ?>">Back Home</a>
        <a href="javascript:history.back()">Go Back</a>
    </div>
    <?php if ($debug && !$isMissingRoute && $rawMessage !== ''): ?>
    <div class="shop-error-debug"><?php echo htmlspecialchars($rawMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
</main>
</body>
</html>
