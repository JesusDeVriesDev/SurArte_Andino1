<?php
// Capa de abstracción sobre la API REST de Supabase (PostgREST).
// Este archivo se usó en etapas tempranas del proyecto cuando la conexión era
// vía HTTP en lugar de PDO directo. Se conserva por compatibilidad con
// scripts que aún lo puedan referenciar, pero la capa activa es config/db.php.
require_once __DIR__ . '/supabase.php';

class DB {

    // Ejecuta un SELECT sobre una tabla con filtros opcionales de igualdad.
    // Los filtros se convierten en parámetros PostgREST tipo columna=eq.valor.
    public static function select(
        string $table,
        string $select = '*',
        array  $filters = [],
        int    $limit = 100,
        int    $offset = 0
    ): array {
        $params = ['select' => $select, 'limit' => $limit, 'offset' => $offset];
        $query  = '?' . http_build_query($params);

        foreach ($filters as $col => $val) {
            $query .= "&{$col}=eq.{$val}";
        }

        return Supabase::query("/{$table}{$query}");
    }

    // Inserta una fila en la tabla indicada. Los datos del array se serializan a JSON.
    public static function insert(string $table, array $data): array {
        return Supabase::query("/{$table}", 'POST', $data);
    }

    // Actualiza la fila cuyo id coincide con el valor dado.
    // Usa PATCH (actualización parcial) en lugar de PUT para no sobreescribir
    // campos que no se envían en el array $data.
    public static function update(string $table, int|string $id, array $data): array {
        return Supabase::query("/{$table}?id=eq.{$id}", 'PATCH', $data);
    }

    // Elimina la fila identificada por $id. La operación es permanente.
    public static function delete(string $table, int|string $id): array {
        return Supabase::query("/{$table}?id=eq.{$id}", 'DELETE');
    }

    // Convierte el resultado de Supabase en una respuesta JSON estándar.
    // Detecta éxito o error según el código HTTP devuelto por la API REST.
    public static function jsonResponse(array $result, int $successCode = 200): void {
        $isOk = $result['status'] >= 200 && $result['status'] < 300;
        http_response_code($isOk ? $successCode : $result['status']);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $isOk,
            'data'    => $result['data'],
        ]);
    }
}
