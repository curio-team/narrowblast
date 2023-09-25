<?php
/*
    Based on: https://gist.github.com/jplitza/88d64ce351d38c2f4198
*/
    require __DIR__.'/../vendor/autoload.php';

    $github_cmd = 'npm run update-ci > /dev/null 2>/dev/null &';
    $raw_body = file_get_contents('php://input');

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
    $dotenv->load();

    $webhook_secret = $_ENV['WEBHOOK_SECRET'];

    if (empty($webhook_secret)) {
        http_response_code(403);
        die("Forbidden due to misconfiguration\n");
        exit;
    }

    $webhook_logs = $_ENV['WEBHOOK_LOGFILE'];
    $signature = hash_hmac('sha1', $raw_body, $webhook_secret);

    // required data in headers - doesn't need changing
    $github_required_headers = array(
        'REQUEST_METHOD' => 'POST',
        'HTTP_X_GITHUB_EVENT' => 'push',
        'HTTP_USER_AGENT' => 'GitHub-Hookshot/*',
        'HTTP_X_HUB_SIGNATURE' => 'sha1=' . $signature,
    );
    $timeout_required_headers = array(
        'REQUEST_METHOD' => 'POST',
        'HTTP_X_TIMEOUT_SIGNATURE' => 'sha1=' . $signature,
    );

    error_reporting(0);

    function log_msg($msg) {
        global $webhook_logs;

        if($webhook_logs != '') {
            file_put_contents($webhook_logs, $msg . "\n", FILE_APPEND);
        }
    }

    function array_matches($have, $should, $name = 'array') {
        $hasMatches = true;

        if(is_array($have)) {
            foreach($should as $key => $value) {
                if(!array_key_exists($key, $have)) {
                    log_msg("Missing: $key");
                    $hasMatches = false;
                }
                else if(is_array($value) && is_array($have[$key])) {
                    $hasMatches &= array_matches($have[$key], $value);
                }
                else if(is_array($value) || is_array($have[$key])) {
                    log_msg("Type mismatch: $key");
                    $hasMatches = false;
                }
                else if(!fnmatch($value, $have[$key])) {
                    log_msg("Failed comparison: $key={$have[$key]} (expected $value)");
                    $hasMatches = false;
                }
            }
        } else {
            log_msg("Not an array: $name");
            $hasMatches = false;
        }

        return $hasMatches;
    }

    log_msg("=== Received request from {$_SERVER['REMOTE_ADDR']} ===");
    header("Content-Type: text/plain");

    // First do all checks and then report back in order to avoid timing attacks
    $github_headers_ok = array_matches($_SERVER, $github_required_headers, '$_SERVER');
    $timeout_headers_ok = array_matches($_SERVER, $timeout_required_headers, '$_SERVER');

    // Either staging or production (branches match the environment names)
    $branch = $_ENV['APP_ENV'];

    if($github_headers_ok) {
        $data = json_decode($raw_body, true);

        // Check if the data is okay
        $github_required_data = array(
            'ref' => "refs/heads/$branch",
            'repository' => array(
                'full_name' => 'curio-team/narrowblast',
            ),
        );

        $github_data_ok = array_matches($data, $github_required_data, '$data');

        if(!($github_headers_ok && $github_data_ok)) {
            if($payload->ref === "refs/heads/$branch") {
                http_response_code(403);
                die("Forbidden\n");
            }
            exit;
        }

        // Execute the update command
        passthru($github_cmd);
    } else if($timeout_headers_ok) {
        $maintenance_at = intval($raw_body);
        file_put_contents(__DIR__ . '/next-maintenance.txt', $maintenance_at);
    } else {
        http_response_code(403);
        die("Forbidden\n");
        exit;
    }
?>
