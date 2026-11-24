#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Onda 0.2 — checagem de ambiente de homologação (sem depender de Artisan boot completo).
 *
 * Uso (na raiz do i-Educar):
 *   php packages/buriti/i-educar-exportacao-unificada-package/docs/onda-0/check-ambiente.php
 */

$root = dirname(__DIR__, 5);
if (! is_file($root.'/artisan')) {
    $root = getcwd() ?: $root;
}

$ok = 0;
$fail = 0;
$warn = 0;

$line = static function (string $status, string $msg) use (&$ok, &$fail, &$warn): void {
    echo sprintf("[%s] %s\n", $status, $msg);
    if ($status === 'OK') {
        $ok++;
    } elseif ($status === 'WARN') {
        $warn++;
    } else {
        $fail++;
    }
};

$line('OK', 'PHP '.PHP_VERSION);
$line(version_compare(PHP_VERSION, '8.3.0', '>=') ? 'OK' : 'FAIL', 'PHP ≥ 8.3');

foreach (['pdo_pgsql', 'pgsql', 'gd', 'zip', 'xml', 'mbstring'] as $ext) {
    $line(extension_loaded($ext) ? 'OK' : 'FAIL', "extensão {$ext}");
}

$envFile = $root.'/.env';
if (! is_file($envFile)) {
    $line('FAIL', '.env ausente em '.$root);
} else {
    $line('OK', '.env presente');
    $env = [];
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $row) {
        if ($row[0] === '#' || ! str_contains($row, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $row, 2);
        $env[trim($k)] = trim($v, " \t\"'");
    }

    $host = $env['DB_HOST'] ?? '127.0.0.1';
    $port = (int) ($env['DB_PORT'] ?? 5432);
    $db = $env['DB_DATABASE'] ?? '';
    $user = $env['DB_USERNAME'] ?? '';
    $pass = $env['DB_PASSWORD'] ?? '';

    $line($db !== '' ? 'OK' : 'FAIL', 'DB_DATABASE configurado');
    $line(($env['APP_KEY'] ?? '') !== '' ? 'OK' : 'FAIL', 'APP_KEY configurado');

    try {
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $port, $db);
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
        ]);
        $line('OK', "Postgres conectado ({$host}:{$port}/{$db})");

        $tables = ['pmieducar.escola', 'pmieducar.turma', 'pmieducar.aluno', 'cadastro.pessoa'];
        foreach ($tables as $table) {
            try {
                $n = (int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
                $line($n > 0 ? 'OK' : 'WARN', "{$table}: {$n} registro(s)");
            } catch (Throwable $e) {
                $line('WARN', "{$table}: ".$e->getMessage());
            }
        }
    } catch (Throwable $e) {
        $line('FAIL', 'Postgres: '.$e->getMessage());
    }

    $redisHost = $env['REDIS_HOST'] ?? '127.0.0.1';
    $redisPort = (int) ($env['REDIS_PORT'] ?? 6379);
    $fp = @fsockopen($redisHost, $redisPort, $errno, $errstr, 1.5);
    if ($fp) {
        fclose($fp);
        $line('OK', "Redis alcançável ({$redisHost}:{$redisPort})");
    } else {
        $line('WARN', "Redis não alcançável ({$redisHost}:{$redisPort}) — cache/queue podem falhar");
    }
}

$storage = $root.'/storage/app/exportacao-onda0';
$formats = ['pdf', 'odt', 'docx', 'ods', 'xlsx', 'html', 'xml', 'csv', 'txt', 'jpg'];
$present = 0;
foreach ($formats as $ext) {
    if (is_file($storage.'/prova-onda0-laguna.'.$ext)) {
        $present++;
    }
}
$line($present >= 3 ? 'OK' : 'FAIL', "artefatos exportação Onda 0: {$present}/10 em storage/app/exportacao-onda0");

echo "\nResumo: {$ok} ok, {$warn} avisos, {$fail} falhas\n";
exit($fail > 0 ? 1 : 0);
