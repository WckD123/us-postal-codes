<?php

require __DIR__ . '/../vendor/autoload.php';

function deleteDirectory(string $directory): void
{
    if (is_dir($directory) === false)
    {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($items as $item)
    {
        $item->isDir() === true ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }

    rmdir($directory);
}
