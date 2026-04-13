<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Router;

$router = new Router();

require APP_PATH . '/Routes/web.php';
require APP_PATH . '/Routes/api.php';

$router->dispatch();
