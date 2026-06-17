<?php


define('DB_HOST', 'localhost');
define('DB_USER', 'root');       
define('DB_PASS', '');            
define('DB_NAME', 'plataforma_servicos');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die(json_encode(['erro' => 'Falha na conexão com o banco de dados: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

function fazerBackupAutomatico(): void {
    $dir = __DIR__ . '/../backups/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $hoje = date('Ymd');
    $arquivosHoje = glob($dir . 'opus_backup_' . $hoje . '*.sql');
    if (!empty($arquivosHoje)) return; 

    try {
        $db  = getDB();
        $sql = "-- OPUS | Backup automático gerado em " . date('d/m/Y H:i:s') . "\n";
        $sql .= "-- ============================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tabelas = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tabelas as $tabela) {
            $sql .= "-- Tabela: $tabela\n";
            $sql .= "DROP TABLE IF EXISTS `$tabela`;\n";
            $create = $db->query("SHOW CREATE TABLE `$tabela`")->fetch();
            $sql .= $create['Create Table'] . ";\n\n";

            $rows = $db->query("SELECT * FROM `$tabela`")->fetchAll(PDO::FETCH_ASSOC);
            if ($rows) {
                $sql .= "INSERT INTO `$tabela` VALUES\n";
                $values = [];
                foreach ($rows as $row) {
                    $escaped = array_map(function($v) use ($db) {
                        return is_null($v) ? 'NULL' : $db->quote($v);
                    }, array_values($row));
                    $values[] = '(' . implode(', ', $escaped) . ')';
                }
                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $arquivo = $dir . 'opus_backup_' . date('Ymd_His') . '.sql';
        file_put_contents($arquivo, $sql);

        $db->exec("CREATE TABLE IF NOT EXISTS backup_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            arquivo VARCHAR(255),
            tamanho INT,
            criado_em DATETIME DEFAULT NOW()
        )");
        $db->prepare("INSERT INTO backup_log (arquivo, tamanho) VALUES (?, ?)")
           ->execute([basename($arquivo), strlen($sql)]);

        
        $todos = glob($dir . 'opus_backup_*.sql');
        foreach ($todos as $f) {
            if (filemtime($f) < strtotime('-7 days')) {
                unlink($f);
            }
        }

    } catch (Exception $e) {
        
    }
}