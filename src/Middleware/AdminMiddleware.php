<?php

namespace WFN24\Middleware;

class AdminMiddleware
{
    public function handle(): void
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['flash_error'] = 'Access denied. Admin privileges required.';
            header('Location: /');
            exit;
        }
    }
}
