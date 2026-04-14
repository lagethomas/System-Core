<?php
declare(strict_types=1);

namespace App\Core;

class Router {
    protected array $routes = [];

    /**
     * Add a route to the system
     * 
     * @param string $method GET, POST, etc.
     * @param string $path Route path (supports {param})
     * @param array $handler ['controller' => '...', 'method' => '...', 'middlewares' => [...]]
     */
    public function add(string $method, string $path, array $handler) {
        // Convert {param} to regex named group
        $pattern = preg_replace('/\{([a-zA-Z0-9_-]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = "#^" . $pattern . "$#";

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if system is in subfolder
        $site_path = parse_url(SITE_URL, PHP_URL_PATH) ?: '';
        $site_path = rtrim($site_path, '/'); // Remove trailing slash for reliable replacement
        
        if (!empty($site_path) && strpos($path, $site_path) === 0) {
            $path = substr($path, strlen($site_path));
        }
        
        $path = '/' . trim($path, '/');

        // --- Custom Domain Detection (Rule 39 / White Label) ---
        global $pdo;
        $host = $_SERVER['HTTP_HOST'];
        $mainHost = parse_url(SITE_URL, PHP_URL_HOST);
        
        if ($host !== $mainHost) {
            // Check if this host is a custom domain for a company
            $stmtD = $pdo->prepare("SELECT slug FROM cp_companies WHERE custom_domain = ? AND active = 1 LIMIT 1");
            $stmtD->execute([$host]);
            $companySlug = $stmtD->fetchColumn();
            
            if ($companySlug) {
                // Prepend the slug virtually so we can use existing routes
                $path = '/' . $companySlug . ($path === '/' ? '' : $path);
            }
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                // Execute Middlewares
                if (isset($route['handler']['middlewares'])) {
                    foreach ($route['handler']['middlewares'] as $middlewareClass) {
                        $middleware = new $middlewareClass();
                        if (!$middleware->handle()) {
                            return; // Middleware stopped execution (e.g. redirect)
                        }
                    }
                }

                // Extract parameters (named groups)
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $controllerInput = $route['handler']['controller'];
                $controllerName = str_starts_with($controllerInput, 'App\\') 
                    ? $controllerInput 
                    : "App\\Controllers\\" . $controllerInput;
                $methodName = $route['handler']['method'];

                if (class_exists($controllerName)) {
                    $controller = new $controllerName();
                    if (method_exists($controller, $methodName)) {
                        return $controller->$methodName(...array_values($params));
                    }
                }
            }
        }

        http_response_code(404);
        include_once __DIR__ . '/../../includes/header.php';
        echo "<div class='card shadow-sm p-5 text-center my-5'>
                <h1 class='display-1 text-primary'>404</h1>
                <h3>Página não encontrada</h3>
                <p class='text-muted'>A rota <code>$path</code> não existe neste sistema.</p>
                <a href='" . SITE_URL . "/dashboard' class='btn btn-primary mt-3'>Voltar ao Início</a>
              </div>";
        include_once __DIR__ . '/../../includes/footer.php';
    }
}
