<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'WFN24') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Meta tags -->
    <meta name="description" content="World Football News 24 - Your ultimate destination for football news, live scores, and comprehensive match coverage.">
    <meta name="keywords" content="football, soccer, news, live scores, matches, teams, players, leagues">
    <meta name="author" content="WFN24">
    
    <!-- Open Graph -->
    <meta property="og:title" content="WFN24 - World Football News 24">
    <meta property="og:description" content="Your ultimate destination for football news, live scores, and comprehensive match coverage.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/wfn24-logo.png') }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="WFN24 - World Football News 24">
    <meta name="twitter:description" content="Your ultimate destination for football news, live scores, and comprehensive match coverage.">
    <meta name="twitter:image" content="{{ asset('images/wfn24-logo.png') }}">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia

    <!-- Loading indicator -->
    <div id="loading-indicator" class="fixed inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50" style="display: none;">
        <div class="text-center">
            <div class="spinner mx-auto mb-4"></div>
            <p class="text-gray-600">Loading...</p>
        </div>
    </div>

    <script>
        // Show loading indicator on navigation
        document.addEventListener('inertia:start', () => {
            document.getElementById('loading-indicator').style.display = 'flex';
        });

        document.addEventListener('inertia:finish', () => {
            document.getElementById('loading-indicator').style.display = 'none';
        });

        // WebSocket connection for real-time updates
        let ws = null;
        
        function connectWebSocket() {
            try {
                ws = new WebSocket('ws://localhost:8080');
                
                ws.onopen = function() {
                    console.log('WebSocket connected');
                };
                
                ws.onmessage = function(event) {
                    const data = JSON.parse(event.data);
                    handleWebSocketMessage(data);
                };
                
                ws.onclose = function() {
                    console.log('WebSocket disconnected');
                    // Reconnect after 5 seconds
                    setTimeout(connectWebSocket, 5000);
                };
                
                ws.onerror = function(error) {
                    console.error('WebSocket error:', error);
                };
            } catch (error) {
                console.error('Failed to connect to WebSocket:', error);
            }
        }

        function handleWebSocketMessage(data) {
            switch (data.type) {
                case 'match_update':
                    // Handle match score updates
                    if (window.matchUpdateHandler) {
                        window.matchUpdateHandler(data);
                    }
                    break;
                    
                case 'live_match':
                    // Handle new live matches
                    if (window.liveMatchHandler) {
                        window.liveMatchHandler(data);
                    }
                    break;
                    
                case 'news_update':
                    // Handle news updates
                    if (window.newsUpdateHandler) {
                        window.newsUpdateHandler(data);
                    }
                    break;
            }
        }

        // Connect to WebSocket when page loads
        document.addEventListener('DOMContentLoaded', connectWebSocket);
    </script>
</body>
</html>
