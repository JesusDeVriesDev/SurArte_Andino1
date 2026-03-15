<?php
require_once __DIR__ . '/supabase.php';

class DB {
    /**
     * SELECT con filtros
     * @param string $table   Nombre de la tabla
     * @param string $select  Columnas (default '*')
     * @param array  $filters ['columna' => 'valor']
     * @param int    $limit
     * @param int    $offset
     */
    public static function select(
        string $table,
        string $select = '*',
        array $filters = [],
        int $limit = 100,
        int $offset = 0
    ): array {
        $params = ['select' => $select, 'limit' => $limit, 'offset' => $offset];
        $query  = '?' . http_build_query($params);

        foreach ($filters as $col => $val) {
            $query .= "&{$col}=eq.{$val}";
        }

        return Supabase::query("/{$table}{$query}");
    }

    public static function insert(string $table, array $data): array {
        return Supabase::query("/{$table}", 'POST', $data);
    }

    public static function update(string $table, int|string $id, array $data): array {
        return Supabase::query("/{$table}?id=eq.{$id}", 'PATCH', $data);
    }

    public static function delete(string $table, int|string $id): array {
        return Supabase::query("/{$table}?id=eq.{$id}", 'DELETE');
    }

    public static function jsonResponse(array $result, int $successCode = 200): void {
        http_response_code($result['status'] >= 200 && $result['status'] < 300 ? $successCode : $result['status']);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result['status'] >= 200 && $result['status'] < 300,
            'data'    => $result['data'],
        ]);
    }
}
