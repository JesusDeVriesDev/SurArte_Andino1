<?php
// Cliente HTTP para la API REST y Auth de Supabase.
// Se usó en la fase inicial del proyecto; actualmente la BD se accede
// vía PDO directo (config/db.php). Se mantiene por compatibilidad.
class Supabase {
    private static string $url;
    private static string $anonKey;
    private static string $serviceKey;

    // Carga las credenciales desde las variables de entorno.
    // Debe llamarse al inicio de la app — el final de este archivo lo hace automáticamente.
    public static function init(): void {
        self::$url        = $_ENV['SUPABASE_URL']         ?? '';
        self::$anonKey    = $_ENV['SUPABASE_ANON_KEY']    ?? '';
        self::$serviceKey = $_ENV['SUPABASE_SERVICE_KEY'] ?? '';
    }

    // Hace una petición HTTP a la API REST de Supabase (PostgREST).
    // $endpoint es la ruta relativa, ej: '/artistas?select=*'.
    // $useService=true usa la clave de servicio con permisos elevados — solo para operaciones de backend.
    public static function query(
        string $endpoint,
        string $method = 'GET',
        array  $body = [],
        bool   $useService = false
    ): array {
        $key = $useService ? self::$serviceKey : self::$anonKey;
        $url = self::$url . '/rest/v1' . $endpoint;

        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $key,
            'Authorization: Bearer ' . $key,
            // Prefer: return=representation hace que POST/PATCH devuelvan el registro creado/modificado
            'Prefer: return=representation',
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'data'   => json_decode($response, true),
        ];
    }

    // Autentica un usuario existente con email y contraseña.
    // Devuelve el token JWT de Supabase Auth si las credenciales son válidas.
    public static function signIn(string $email, string $password): array {
        $url = self::$url . '/auth/v1/token?grant_type=password';
        return self::authRequest($url, compact('email', 'password'));
    }

    // Registra un nuevo usuario en Supabase Auth.
    // $meta permite enviar datos adicionales del perfil que Supabase almacena en user_metadata.
    public static function signUp(string $email, string $password, array $meta = []): array {
        $url = self::$url . '/auth/v1/signup';
        return self::authRequest($url, [
            'email'    => $email,
            'password' => $password,
            'data'     => $meta,
        ]);
    }

    // Renueva el access token usando el refresh token guardado en sesión.
    // Necesario porque los tokens JWT de Supabase expiran después de 1 hora.
    public static function refreshToken(string $refreshToken): array {
        $url = self::$url . '/auth/v1/token?grant_type=refresh_token';
        return self::authRequest($url, ['refresh_token' => $refreshToken]);
    }

    // Método interno que ejecuta la petición HTTP a los endpoints de Auth de Supabase.
    // La separación entre query() y authRequest() existe porque Auth usa
    // una URL base distinta (/auth/v1 vs /rest/v1) y no necesita el header Prefer.
    private static function authRequest(string $url, array $body): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'apikey: ' . self::$anonKey,
            ],
        ]);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['status' => $code, 'data' => json_decode($response, true)];
    }

    // Accesores para los casos donde otro módulo necesita la URL o la clave directamente
    public static function getUrl(): string     { return self::$url; }
    public static function getAnonKey(): string { return self::$anonKey; }
}

// Inicializa el cliente al incluir el archivo — así cualquier require_once de supabase.php
// ya tiene las credenciales listas sin necesidad de llamar init() manualmente.
Supabase::init();
