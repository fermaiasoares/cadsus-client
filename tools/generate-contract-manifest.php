<?php

declare(strict_types=1);

$root = dirname(__DIR__) . '/resources/contracts';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$files = [];

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getFilename() === 'manifest.php') {
        continue;
    }

    $relativePath = str_replace($root . '/', '', $file->getPathname());
    $files[$relativePath] = hash_file('sha256', $file->getPathname());
}

ksort($files);
$manifest = [
    'manual_version' => '2.1',
    'domains_source_sha256' => '79123458147077a7012d51dc74107d9cf2a313221430a5015238c3da9f3903b9',
    'postman_source_sha256' => '3fdcfb5e1674a5d429df96082623f272dce64a58d0ca61a4c9076374a397a253',
    'files' => $files,
];

$contents = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($manifest, true) . ";\n";

if (file_put_contents($root . '/manifest.php', $contents, LOCK_EX) === false) {
    fwrite(STDERR, "Não foi possível gravar o manifesto.\n");
    exit(1);
}

echo sprintf("Manifesto criado com %d artefatos.\n", count($files));
