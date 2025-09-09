<?php

namespace App\Controllers\Tickets;

use App\Core\Controller;
use App\Core\Auth;

class TestController extends Controller
{
    public function testAuth()
    {
        echo "<h1>Authentication Test</h1>";
        echo "<p>Is Logged In: " . (Auth::isLoggedIn() ? 'YES' : 'NO') . "</p>";
        echo "<p>User Role: " . (Auth::getUserRole() ?? 'NULL') . "</p>";
        echo "<p>Has Admin Role: " . (Auth::hasRole('admin') ? 'YES' : 'NO') . "</p>";
        echo "<p>Has Developer Role: " . (Auth::hasRole('developer') ? 'YES' : 'NO') . "</p>";
        echo "<p>Has Any Admin/Developer Role: " . (Auth::hasAnyRole(['admin', 'developer']) ? 'YES' : 'NO') . "</p>";
        
        echo "<h2>Session Data:</h2>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
    }
}
