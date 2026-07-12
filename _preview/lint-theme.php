<?php
$root = '/theme';
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$fails = 0;
foreach ($rii as $file) {
	if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
		continue;
	}
	$path = $file->getPathname();
	try {
		token_get_all(file_get_contents($path), TOKEN_PARSE);
		echo "OK " . str_replace($root, '', $path) . PHP_EOL;
	} catch (Throwable $e) {
		$fails++;
		echo "FAIL " . str_replace($root, '', $path) . " :: " . $e->getMessage() . PHP_EOL;
	}
}
echo $fails ? "RESULT=FAIL\n" : "RESULT=PASS\n";
