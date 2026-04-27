<?php

/**
 * Clase Database: lee credenciales del .env y entregar una conexión.
 */
class Database {

    private $host;
    private $port;
    private $db;
    private $user;
    private $pass;

    /**
     * Al instanciarse, carga el .env y asigna
     * cada credencial a su propiedad correspondiente.
     */
    public function __construct(){
        $this->loadEnv();

        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->port = $_ENV['DB_PORT'] ?? '3306';
        $this->db   = $_ENV['DB_NAME'] ?? 'tienda_galindez';
        $this->user = $_ENV['DB_USER'] ?? 'root';
        $this->pass = $_ENV['DB_PASS'] ?? '';
    }

    /**
     * Lee el archivo .env línea por línea y carga
     * cada variable en $_ENV y en el entorno del proceso (putenv).
     * Ignora líneas vacías y comentarios (#).
     */
    private function loadEnv(){

        // Ruta absoluta al .env — sube un nivel desde Config/ hasta la raíz
        $envFile = __DIR__ . '/../.env';

        // Si no existe el .env, detenemos todo con un mensaje claro
        if(!file_exists($envFile)){
            die('❌ Archivo .env no encontrado. Copia .env.example y configura tus credenciales.');
        }

        // Leer todas las líneas ignorando vacías
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach($lines as $line){

            // Saltar líneas que son comentarios
            if(str_starts_with(trim($line), '#')){
                continue;
            }

            // Saltar líneas que no tienen el formato CLAVE=VALOR
            if(strpos($line, '=') === false){
                continue;
            }

            // Separar en exactamente dos partes: clave y valor
            // El límite 2 evita romper valores que contengan '='
            [$key, $value] = explode('=', $line, 2);

            $key   = trim($key);
            $value = trim($value);

            // Eliminar comillas si el valor las tiene ("valor" o 'valor')
            $value = trim($value, '"\'');

            // Disponible tanto en $_ENV como en getenv()
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }

    /**
     * Crea y retorna una conexión PDO configurada.
     * Lanza un mensaje según el entorno:
     *   - development: muestra el error técnico completo
     *   - production:  muestra solo un mensaje genérico (sin exponer datos)
     *
     * PDO::FETCH_ASSOC como modo por defecto evita repetirlo
     * en cada consulta de los modelos.
     */
    public function connect(){

        try {

            // DSN: cadena de conexión con host, puerto, BD y charset
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset=utf8mb4";

            $conn = new PDO($dsn, $this->user, $this->pass);

            // Activar excepciones para capturar errores SQL fácilmente
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Retornar arrays asociativos por defecto en todos los modelos
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $conn;

        } catch(PDOException $e){

            // En desarrollo mostramos el error real para depurar rápido
            if(($_ENV['APP_ENV'] ?? 'development') === 'development'){
                die('❌ Error de conexión: ' . $e->getMessage());
            }

            // En producción ocultamos detalles técnicos al usuario final
            die('❌ Error de conexión. Contacta al administrador.');
        }
    }
}