<?php

/**
 * Controller Base — AnimaMarket
 *
 * Provee métodos utilitarios compartidos que todos los controllers heredan.

 */

require_once __DIR__ . '/../Config/Database.php';

class Controller {

    /**
     * Conexión PDO compartida por todos los controllers.
     * Se declara protected para que las clases hijas puedan usarla con $this->db
     */
    protected $db;

    // ══════════════════════════════════════════════════
    // CONSTRUCTOR
    // Establece la conexión a BD una sola vez.
    // Cada controller hijo la hereda automáticamente sin necesidad de repetir new Database()->connect()
    // ══════════════════════════════════════════════════
    public function __construct(){
        $database = new Database();
        $this->db = $database->connect();
    }

    // ══════════════════════════════════════════════════
    // REDIRIGIR
    // Centraliza los header('Location: ...')
    // ══════════════════════════════════════════════════
    protected function redirect($controller, $action = 'index', $params = []){

        // Construir URL base de la redirección
        $url = BASE_URL . 'index.php?controller=' . $controller . '&action=' . $action;

        // Agregar parámetros adicionales si los hay
        foreach($params as $key => $value){
            $url .= '&' . $key . '=' . urlencode($value);
        }

        header('Location: ' . $url);
        exit;
    }

    // ══════════════════════════════════════════════════
    // RENDERIZAR VISTA
    // Carga un archivo .phtml pasándole variables.
    // Las claves del array se convierten en variables
    // disponibles dentro del archivo .phtml: $productos, $categorias
    // ══════════════════════════════════════════════════
    protected function render($view, $data = []){

        // Convertir el array en variables individuales
        // ['productos' => [...]] → $productos = [...]
        extract($data);

        // Construir ruta absoluta al archivo de vista
        $viewPath = __DIR__ . '/../View/' . $view . '.phtml';

        // Verificar que la vista exista antes de cargarla
        if(!file_exists($viewPath)){
            die("❌ Vista <strong>{$view}.phtml</strong> no encontrada.");
        }

        require_once $viewPath;
    }

    // ══════════════════════════════════════════════════
    // RESPUESTA JSON
    // Para peticiones AJAX (carrito, búsqueda live). Envía el header correcto y codifica el array.
    // Uso desde un controller hijo: $this->json(['success' => true, 'total' => 3]);
    // ══════════════════════════════════════════════════
    protected function json($data, $statusCode = 200){
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}