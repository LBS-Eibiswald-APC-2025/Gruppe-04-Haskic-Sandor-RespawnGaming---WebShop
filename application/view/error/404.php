<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Seite nicht gefunden</title>
    <link rel="stylesheet" href="<?= Config::get('URL'); ?>public/css/404.css">
    <style>
        @font-face {
            font-family: 'Ghost';
            src: url('<?= Config::get('URL'); ?>public/fonts/Ghost/Ghost.woff2') format('woff2');
        }

        @font-face {
            font-family: 'Playstation';
            src: url('<?= Config::get('URL'); ?>public/fonts/Playstation/Playstation.woff2') format('woff2');
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            overflow-x: hidden;
            overflow-y: auto !important;
            height: auto !important;
            min-height: 100vh;
        }

        body {
            background: #1B2838;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 70vh;
            color: #fff;
            text-align: center;
            padding: 0.5rem;
        }

        @media (min-width: 576px) {
            body {
                min-height: 75vh;
                padding: 0.75rem;
            }
        }

        @media (min-width: 768px) {
            body {
                min-height: 80vh;
                padding: 1rem;
            }
        }

        .container {
            max-width: 800px;
            width: 100%;
            padding: 1rem;
            background: rgba(0,0,0,0.6);
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }

        @media (min-width: 576px) {
            .container {
                width: 95%;
                padding: 1.5rem;
                border-radius: 10px;
            }
        }

        @media (min-width: 768px) {
            .container {
                width: 90%;
                padding: 2rem;
            }
        }

        @media (min-width: 992px) {
            .container {
                width: 85%;
            }
        }

        .error-image {
            width: auto;
            height: 25vh;
            max-width: 300px;
            margin: 0 auto 1rem;
            display: block;
            animation: pulse 2s infinite;
        }

        @media (min-width: 576px) {
            .error-image {
                height: 30vh;
                margin-bottom: 1.15rem;
            }
        }

        @media (min-width: 768px) {
            .error-image {
                height: 35vh;
                margin-bottom: 1.25rem;
                max-width: 350px;
            }
        }

        @media (min-width: 992px) {
            .error-image {
                height: 40vh;
                max-width: 400px;
            }
        }

        @keyframes pulse {
            0%, 100% {transform: scale(1)}
            50% {transform: scale(1.05)}
        }

        h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-family: 'Ghost', 'Segoe UI', Tahoma, sans-serif;
            text-shadow: 0 0 10px rgba(74, 144, 217, 0.5);
        }

        @media (min-width: 576px) {
            h1 {
                font-size: 2.2rem;
                margin-bottom: 0.55rem;
            }
        }

        @media (min-width: 768px) {
            h1 {
                font-size: 2.6rem;
                margin-bottom: 0.6rem;
            }
        }

        @media (min-width: 992px) {
            h1 {
                font-size: 3rem;
                margin-bottom: 0.625rem;
            }
        }

        p {
            font-size: 0.9rem;
            color: #F15147;
            font-family: 'Playstation', 'Segoe UI', Tahoma, sans-serif !important;
        }

        @media (min-width: 576px) {
            p {
                font-size: 1rem;
            }
        }

        @media (min-width: 768px) {
            p {
                font-size: 1.1rem;
            }
        }

        @media (min-width: 992px) {
            p {
                font-size: 1.2rem;
            }
        }

        .back-home {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #4a90d9, #357ab8);
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-family: 'Playstation', 'Segoe UI', Tahoma, sans-serif !important;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        @media (min-width: 576px) {
            .back-home {
                margin-top: 1.25rem;
                padding: 0.6rem 1.2rem;
                font-size: 1rem;
            }
        }

        @media (min-width: 768px) {
            .back-home {
                margin-top: 1.5rem;
                padding: 0.7rem 1.5rem;
                font-size: 1.1rem;
            }
        }

        .back-home:hover {
            background: linear-gradient(135deg, #8ed529, #5a921e);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
<div class="container">
    <img src="<?= Config::get('URL'); ?>public/image/main/Error_404.png" alt="404 Not Found" class="error-image">
    <h1>404 - Seite nicht gefunden</h1>
    <p>Diese Seite existiert nicht.</p>
    <a href="<?= Config::get('URL'); ?>" class="back-home">Zurück zur Startseite</a>
</div>
</body>
</html>