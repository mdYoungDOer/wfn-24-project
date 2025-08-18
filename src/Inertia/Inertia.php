<?php

namespace WFN24\Inertia;

class Inertia
{
    public static function render($component, $props = [])
    {
        // For now, we'll return a simple JSON response
        // In a full implementation, this would render the React component
        header('Content-Type: application/json');
        
        $response = [
            'component' => $component,
            'props' => $props,
            'url' => $_SERVER['REQUEST_URI'] ?? '/',
            'version' => '1.0.0'
        ];
        
        echo json_encode($response);
        exit;
    }
}
