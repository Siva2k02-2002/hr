<?php
// TEMPORARY diagnostic. Upload to the live app root, open once, then DELETE it.
// Open as: https://harshahub.hrcloud.in/check_license.php?t=diag2026   (CLI also works: php check_license.php)
if (PHP_SAPI !== 'cli' && ($_GET['t'] ?? '') !== 'diag2026') { http_response_code(404); exit; }
header('Content-Type: text/plain');

$env = [];
foreach (file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#' || ! str_contains($line, '=')) continue;
    [$k, $v] = array_map('trim', explode('=', $line, 2));
    $env[$k] = trim($v, "'\"");
}

$secret = $env['license.signingSecret'] ?? '';
echo "secret length: " . strlen($secret) . " (expect 64), starts: " . substr($secret, 0, 6) . "\n\n";

$db = @new mysqli($env['database.master.hostname'], $env['database.master.username'], $env['database.master.password'], $env['database.master.database'], (int) $env['database.master.port']);
if ($db->connect_error) { exit('PLATFORM DB CONNECT FAILED: ' . $db->connect_error); }

$host = 'harshahub.hrcloud.in';
$cd = $db->query("SELECT * FROM company_domains WHERE domain = '$host'")->fetch_assoc();
echo "company_domains row: " . ($cd ? 'FOUND company_id=' . $cd['company_id'] : 'MISSING') . "\n";
if (! $cd) exit;

$res = $db->query("SELECT * FROM licenses WHERE company_id = {$cd['company_id']} ORDER BY id DESC");
if (! $res || $res->num_rows === 0) exit("No licenses row for company_id {$cd['company_id']} (check would be skipped)\n");

$l = $res->fetch_assoc();
$payload  = implode('|', [$l['license_uuid'], $l['company_id'], $l['plan_id'], $l['domain'], $l['issued_at'], $l['expires_at'], $l['status']]);
$expected = hash_hmac('sha256', $payload, $secret);

echo "latest license id={$l['id']} status={$l['status']} domain={$l['domain']} expires={$l['expires_at']} grace={$l['grace_days']}\n";
echo "payload : $payload\n";
echo "stored  : {$l['signature']}\n";
echo "expected: $expected\n";
echo hash_equals($expected, (string) $l['signature']) ? "RESULT: signature OK\n" : "RESULT: SIGNATURE MISMATCH -> secret differs from the one that signed this row, or a field was edited\n";
