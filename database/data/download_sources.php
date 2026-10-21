<?php

$dir = __DIR__ . '/sources';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$urls = [
    'divisions.json' => 'https://raw.githubusercontent.com/nuhil/bangladesh-geocode/master/divisions/divisions.json',
    'districts.json' => 'https://raw.githubusercontent.com/nuhil/bangladesh-geocode/master/districts/districts.json',
    'upazilas.json' => 'https://raw.githubusercontent.com/nuhil/bangladesh-geocode/master/upazilas/upazilas.json',
    'unions.json' => 'https://raw.githubusercontent.com/nuhil/bangladesh-geocode/master/unions/unions.json',
    'postcodes.json' => 'https://raw.githubusercontent.com/TanjidulIslamToha/bangladesh-postal-codes-database/main/postcodes-pretty.json',
];

foreach ($urls as $filename => $url) {
    $target = $dir . '/' . $filename;
    echo "Fetching {$filename} from {$url}..." . PHP_EOL;
    $content = file_get_contents($url, false, stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'KrishiHat-Address-Audit/1.0',
        ],
    ]));
    if ($content !== false && strlen($content) > 100) {
        file_put_contents($target, $content);
        echo "  -> Saved {$filename} (" . number_format(strlen($content)) . " bytes)" . PHP_EOL;
    } else {
        echo "  -> FAILED to download {$filename}" . PHP_EOL;
    }
}
echo "Download step complete!" . PHP_EOL;
