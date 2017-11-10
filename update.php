#!/usr/bin/php
<?php

require __DIR__.'/vendor/autoload.php';

$autoprefixer = new Autoprefixer();

echo "Current autoprefixer version: {$autoprefixer->getAutoprefixerVersion()}\n";
echo "Checking updates...\n";
$updated = $autoprefixer->update();
if($updated) {
    echo "Updated autoprefixer to version: {$autoprefixer->getAutoprefixerVersion()}\n";
} else {
    echo "No new version available.\n";
}