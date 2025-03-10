<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Seite nicht gefunden</title>
    <style>
        @font-face {
            font-family: 'Ghost';
            src: url('../../../public/fonts/Ghost/Ghost.woff2') format('woff2');
        }
        * {margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Ghost', 'Segoe UI', Tahoma, sans-serif !important;}
        body {background: #1f1f1f; display: flex; align-items: center; justify-content: center; min-height: 80vh; font-family: Arial, sans-serif; color: #fff; text-align: center}
        .container {max-width: 800px; padding: 20px; background: rgba(0,0,0,0.6); border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.5)}
        .error-image {width: auto; height: 40vh; max-width: 400px; margin: 0 auto 20px; display: block; animation: pulse 2s infinite}
        @keyframes pulse {0%, 100% {transform: scale(1)} 50% {transform: scale(1.05)}}
        h1  {font-size: 3rem; margin-bottom: 10px}
        p {font-size: 1.2rem; color: #ff4c4c}
    </style>
</head>
<body>
<div class="container">
    <img src="<?= Config::get('URL'); ?>public/image/main/Error_404.png" alt="404 Not Found" class="error-image">
    <h1>404 - Seite nicht gefunden</h1>
    <p>Diese Seite existiert nicht.</p>
</div>
</body>
</html>