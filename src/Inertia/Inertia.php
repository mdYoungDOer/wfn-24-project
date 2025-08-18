<?php

namespace WFN24\Inertia;

class Inertia
{
    public static function render($component, $props = [])
    {
        // Render the React app properly
        $html = self::renderReactApp($component, $props);
        
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }
    
    private static function renderReactApp($component, $props)
    {
        $propsJson = json_encode($props);
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WFN24 - World Football News 24</title>
    <meta name="description" content="Latest football news, live scores, match updates, and comprehensive coverage of world football">
    <meta name="keywords" content="football, soccer, news, live scores, matches, leagues, teams, players">
    <meta name="author" content="WFN24">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="WFN24 - World Football News 24">
    <meta property="og:description" content="Latest football news, live scores, match updates, and comprehensive coverage of world football">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://wfn24-project-qrml7.ondigitalocean.app">
    <meta property="og:image" content="https://wfn24-project-qrml7.ondigitalocean.app/images/og-image.jpg">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="WFN24 - World Football News 24">
    <meta name="twitter:description" content="Latest football news, live scores, match updates, and comprehensive coverage of world football">
    <meta name="twitter:image" content="https://wfn24-project-qrml7.ondigitalocean.app/images/twitter-image.jpg">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="csrf-token-placeholder">
    
    <!-- Vite Assets -->
    <script type="module" src="/build/assets/app.js"></script>
    <link rel="stylesheet" href="/build/assets/app.css">
    
    <style>
        /* Loading styles */
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background: #f8fafc;
        }
        
        .loading-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #e41e5b 0%, #9a0864 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            color: white;
        }
        
        .loading-content {
            text-align: center;
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255,255,255,0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Loading indicator -->
        <div class="loading-container" id="loading">
            <div class="loading-content">
                <div class="spinner"></div>
                <h1>WFN24</h1>
                <p>World Football News 24</p>
                <p style="font-size: 0.9rem; opacity: 0.6;">Loading...</p>
            </div>
        </div>
    </div>
    
    <!-- Inertia data -->
    <script>
        window.Inertia = {
            page: {
                component: '{$component}',
                props: {$propsJson},
                url: window.location.pathname,
                version: '1.0.0'
            }
        };
    </script>
    
    <!-- Hide loading when React app is ready -->
    <script>
        // Hide loading indicator when React app loads
        window.addEventListener('load', function() {
            setTimeout(function() {
                const loading = document.getElementById('loading');
                if (loading) {
                    loading.style.opacity = '0';
                    loading.style.transition = 'opacity 0.5s ease';
                    setTimeout(function() {
                        loading.style.display = 'none';
                    }, 500);
                }
            }, 1000);
        });
    </script>
</body>
</html>
HTML;
    }
}
