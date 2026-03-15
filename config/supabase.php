<?php

class Supabase {
    private static string $url;
    private static string $anonKey;
    private static string $serviceKey;

    public static function init(): void {
        self::$url        = $_ENV['SUPABASE_URL']         ?? '';
        self::$anonKey    = $_ENV['SUPABASE_ANON_KEY']    ?? '';
        self::$serviceKey = $_ENV['SUPABASE_SERVICE_KEY'] ?? '';
    }

    /**
     * Petición a la API REST de Supabase
     * @param string $endpoint  Ej: '/artistas?select=*'
     * @param string $method    GET|POST|PATCH|DELETE
     * @param array  $body      Cuerpo JSON
     * @param bool   $useService Usar service_role (solo backend)
     */
    public static function query(
        string $endpoint,
        string $method = 'GET',
        array $body = [],
        bool $useService = false
    ): array {
        $key = $useService ? self::$serviceKey : self::$anonKey;
        $url = self::$url . '/rest/v1' . $endpoint;

        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $key,
            'Authorization: Bearer ' . $key,
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

    public static function signIn(string $email, string $password): array {
        $url = self::$url . '/auth/v1/token?grant_type=password';
        return self::authRequest($url, compact('email', 'password'));
    }

    public static function signUp(string $email, string $password, array $meta = []): array {
        $url = self::$url . '/auth/v1/signup';
        return self::authRequest($url, [
            'email'    => $email,
            'password' => $password,
            'data'     => $meta,
        ]);
    }

    public static function refreshToken(string $refreshToken): array {
        $url = self::$url . '/auth/v1/token?grant_type=refresh_token';
        return self::authRequest($url, ['refresh_token' => $refreshToken]);
    }

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

    public static function getUrl(): string     { return self::$url; }
    public static function getAnonKey(): string { return self::$anonKey; }
}

Supabase::init();
