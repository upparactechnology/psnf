<?php

declare(strict_types=1);

/**
 * PSNF ERP — Migration CLI Runner
 * Run from project root: php migrate.php [--fresh] [--seed] [--seed-only]
 */

define('ROOT_PATH',   __DIR__);
define('APP_PATH',    ROOT_PATH . '/app');
define('CORE_PATH',   ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH',  ROOT_PATH . '/resources/views');
define('STORAGE_PATH',ROOT_PATH . '/storage');

// ── Load helpers first (no Application needed) ──────────────────────────────
require CORE_PATH . '/helpers.php';

// ── Load config into globals ─────────────────────────────────────────────────
$GLOBALS['config'] = [
    'app'      => require CONFIG_PATH . '/app.php',
    'database' => require CONFIG_PATH . '/database.php',
    'auth'     => require CONFIG_PATH . '/auth.php',
];

// ── Load core classes ────────────────────────────────────────────────────────
require CORE_PATH . '/Database.php';
require CORE_PATH . '/Model.php';
require CORE_PATH . '/Migration.php';

// ── Minimal Application stub for Model::db() ─────────────────────────────────
namespace Core {
    // Provide the static $app with a db property so Model::db() works
}
namespace {
    $stub = new stdClass();
    $stub->db = new \Core\Database();
    \Core\Application::$app = $stub; // @phpstan-ignore-line

    // Parse CLI arguments
    $args     = $argv ?? [];
    $fresh    = in_array('--fresh',     $args);
    $seed     = in_array('--seed',      $args);
    $seedOnly = in_array('--seed-only', $args);

    echo "\n\033[1;36m╔══════════════════════════════════════════╗\033[0m\n";
    echo "\033[1;36m║        PSNF ERP — Migration Runner       ║\033[0m\n";
    echo "\033[1;36m╚══════════════════════════════════════════╝\033[0m\n\n";

    $runner = new \Core\Migration();

    if (!$seedOnly) {
        echo "\033[33m⟳  Running migrations" . ($fresh ? " (FRESH — drops all tables)" : "") . "...\033[0m\n";
        $runner->run($fresh);
        echo "\n";
    }

    if ($seed || $seedOnly) {
        echo "\033[33m⟳  Running seeders...\033[0m\n";
        $runner->seed();
        echo "\n";
    }

    echo "\033[1;32m✓  All done!\033[0m\n\n";
}
