// Vercel's filesystem is read-only except /tmp, so create the
// writable folders Laravel needs before the app boots.
$dirs = [
    '/tmp/storage/app/public',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
    '/tmp/views',
    '/tmp/bootstrap-cache',
];

foreach ($dirs as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Forward everything to Laravel's normal entry point.
require __DIR__ . '/../public/index.php';
