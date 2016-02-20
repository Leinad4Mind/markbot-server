<?php

require_once 'user-map.php';
require_once 'config.php';

header('Content-type: text/html; charset=utf8');

function quit ($code = 400, $message = 'Incomplete or missing arguments') {
  http_response_code($code);

  echo json_encode([
    'code' => $code,
    'message' => $message
  ]);

  exit;
}

function debug ($message) {
  echo '<pre>';
  print_r($message);
  echo '</pre>';
};

$gh_repo = filter_input(INPUT_GET, 'gh_repo', FILTER_SANITIZE_STRING);
$gh_username = filter_input(INPUT_GET, 'gh_username', FILTER_SANITIZE_STRING);
$canvas_course = filter_input(INPUT_GET, 'canvas_course', FILTER_SANITIZE_NUMBER_INT);
$canvas_assignment = filter_input(INPUT_GET, 'canvas_assignment', FILTER_SANITIZE_NUMBER_INT);
$markbot_version = filter_input(INPUT_GET, 'markbot_version', FILTER_SANITIZE_STRING);

if (!$gh_repo || !$gh_username || !$canvas_course || !$canvas_assignment || !$markbot_version) quit();

if (!version_compare($markbot_version, $min_markbot_version, '>=')) {
  quit(400, "Markbot version too old, expecting >= $min_markbot_version");
}

$messages = [
  'BOOYAKASHA',
  'WAY TO GO',
  'SUPER-DUPER',
  'AWESOME',
  'COWABUNGA',
  'RAD',
  'AMAZEBALLS',
  'SWEET',
  'COOL',
  'NICE',
  'FANTASTIC',
  'GERONIMO',
  'WHAMO',
  'SUPERB',
  'STUPENDOUS',
  'MATHMATICAL'
];
$message = $messages[array_rand($messages)];

$canvas_user = $user_map[$gh_username];

$comment = <<<ROBOT
+++++++++++++++++++++++++++++++++++++++++
 └[ ◕ 〜 ◕ ]┘ MARKBOT SAYS, "{$message}!"
+++++++++++++++++++++++++++++++++++++++++

Repository URL:
https://github.com/{$gh_username}/{$gh_repo}

Website URL:
https://{$gh_username}.github.io/{$gh_repo}

+++++++++++++++++++++++++++++++++++++++++
ROBOT;

$data = [
  'comment' => [
    'text_comment' => $comment
  ],
  'submission' => [
    'posted_grade' => 1
  ]
];

$request = [
  'http' => [
    'method' => 'PUT',
    'header' => implode("\r\n", [
      "Authorization: Bearer $canvas_api_key",
      "Content-Type: application/json"
    ]),
    'content' => json_encode($data),
    'verify_peer' => false
  ]
];
$url = "https://{$canvas_base_url}/api/v1/courses/{$canvas_course}/assignments/{$canvas_assignment}/submissions/{$canvas_user}";

if ($DEBUG) debug($data);
if ($DEBUG) debug($url);
if ($DEBUG) debug($request);

$context = stream_context_create($request);
$response = file_get_contents($url, false, $context);

if ($DEBUG) debug(json_decode($response));

quit(200, 'MARKBOT STATUS: SUCCESS');
