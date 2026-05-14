<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$folders = \App\Models\Folder::all(['id', 'name', 'is_root', 'parent_id']);
file_put_contents('folders_dump.json', $folders->toJson(JSON_PRETTY_PRINT));
echo "Dumped " . $folders->count() . " folders to folders_dump.json\n";
