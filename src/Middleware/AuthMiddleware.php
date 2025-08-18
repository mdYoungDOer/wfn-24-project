<?php

namespace WFN24\Middleware;

class AuthMiddleware
{
    public function handle(): void
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash_error'] = 'Please log in to access this page.';
            header('Location: /login');
            exit;
        }
    }
}
