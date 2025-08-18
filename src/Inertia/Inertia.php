<?php

namespace WFN24\Inertia;

class Inertia
{
    public static function render($component, $props = [])
    {
        // For now, we'll render a simple HTML page with the React app
        // In a full implementation, this would render the React component server-side
        
        $html = self::renderHtml($component, $props);
        
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }
    
    private static function renderHtml($component, $props)
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
        /* Fallback styles while React loads */
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
        
        .app-container {
            min-height: 100vh;
            background: #f8fafc;
        }
        
        .header {
            background: linear-gradient(135deg, #e41e5b 0%, #9a0864 100%);
            color: white;
            padding: 1rem 0;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .header p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
        }
        
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .hero-section {
            background: white;
            border-radius: 0.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .hero-section h2 {
            color: #2c2c2c;
            margin-bottom: 1rem;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .feature-card {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 0.5rem;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        
        .feature-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .feature-card h3 {
            color: #2c2c2c;
            margin-bottom: 0.5rem;
        }
        
        .feature-card p {
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .stats-section {
            background: white;
            border-radius: 0.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #e41e5b;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #64748b;
        }
        
        .footer {
            background: #1e293b;
            color: white;
            padding: 2rem 0;
            margin-top: 4rem;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
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
        
        <!-- Fallback content while React loads -->
        <div class="app-container" id="fallback-content" style="display: none;">
            <header class="header">
                <div class="header-content">
                    <h1>WFN24</h1>
                    <p>World Football News 24</p>
                </div>
            </header>
            
            <main class="main-content">
                <section class="hero-section">
                    <h2>Welcome to WFN24</h2>
                    <p>Your comprehensive source for football news, live scores, match updates, and everything football.</p>
                    
                    <div class="features-grid">
                        <div class="feature-card">
                            <div class="feature-icon">📰</div>
                            <h3>Latest News</h3>
                            <p>Breaking football news and updates</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">⚽</div>
                            <h3>Live Matches</h3>
                            <p>Real-time scores and commentary</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">🏆</div>
                            <h3>League Tables</h3>
                            <p>Standings and statistics</p>
                        </div>
                    </div>
                </section>
                
                <section class="stats-section">
                    <h2>WFN24 Stats</h2>
                    <div class="stats-grid">
                        <div class="feature-card">
                            <div class="stat-number">100+</div>
                            <div class="stat-label">News Articles</div>
                        </div>
                        <div class="feature-card">
                            <div class="stat-number">5</div>
                            <div class="stat-label">Live Matches</div>
                        </div>
                        <div class="feature-card">
                            <div class="stat-number">7</div>
                            <div class="stat-label">Leagues Covered</div>
                        </div>
                        <div class="feature-card">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">News Coverage</div>
                        </div>
                    </div>
                </section>
            </main>
            
            <footer class="footer">
                <div class="footer-content">
                    <h3>WFN24</h3>
                    <p>World Football News 24 - Your Ultimate Football Destination</p>
                    <p style="font-size: 0.875rem; opacity: 0.7; margin-top: 1rem;">© 2024 WFN24. All rights reserved.</p>
                </div>
            </footer>
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
    
    <!-- Hide loading and show fallback content after a delay -->
    <script>
        setTimeout(function() {
            const loading = document.getElementById('loading');
            const fallback = document.getElementById('fallback-content');
            
            if (loading) {
                loading.style.opacity = '0';
                loading.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    loading.style.display = 'none';
                    if (fallback) {
                        fallback.style.display = 'block';
                    }
                }, 500);
            }
        }, 2000);
    </script>
</body>
</html>
HTML;
    }
}
