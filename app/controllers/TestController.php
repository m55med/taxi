<?php
namespace App\Controllers;

use App\Core\Controller;

class TestController extends Controller {
    public function routerHit() {
        die('ROUTER HIT');
    }
}