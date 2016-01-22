<?php

require_once 'user-map.php';
require_once 'config.php';

header('Content-type: text/html; charset=utf8');

function quit () {
  http_response_code(400);
  echo 'HTTP/1.1 400 Bad request';
  exit;
}

function debug ($message) {
  echo '<pre>';
  print_r($message);
  echo '</pre>';
};

$gh_repo = filter_input(INPUT_GET, 'gh_repo', FILTER_SANITIZE_STRING);
$gh_pr = filter_input(INPUT_GET, 'gh_pr', FILTER_SANITIZE_NUMBER_INT);
$canvas_course = filter_input(INPUT_GET, 'canvas_course', FILTER_SANITIZE_NUMBER_INT);
$canvas_assignment = filter_input(INPUT_GET, 'canvas_assignment', FILTER_SANITIZE_NUMBER_INT);

if (!$gh_repo || !$gh_pr || !$canvas_course || !$canvas_assignment) quit();

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

$request = [
  'http' => [
    'method' => 'GET',
    'header' => implode("\r\n", [
      "Authorization: token $github_api_key",
      'Accept: application/vnd.github.v3+json'
    ]),
    'verify_peer' => false
  ]
];
$url = "https://api.github.com/repos/{$gh_repo}/pulls/{$gh_pr}";

if ($DEBUG) debug($url);
if ($DEBUG) debug($request);

$context = stream_context_create($request);
$response = file_get_contents($url, false, $context);

if (!$response) quit();

$gh_details = json_decode($response);

if ($DEBUG) debug($gh_details);
if ($DEBUG) debug($gh_details->user->login);

if (!isset($user_map[$gh_details->user->login])) quit();

$canvas_user = $user_map[$gh_details->user->login];
$repo_bits = explode('/', $gh_repo);

$comment = <<<ROBOT
+++++++++++++++++++++++++++++++++++++++++
 └[ ◕ 〜 ◕ ]┘ MARKBOT SAYS, "{$message}!"
+++++++++++++++++++++++++++++++++++++++++

Pull request:
https://github.com/{$gh_repo}/pull/{$gh_pr}

Website URL:
https://{$gh_details->user->login}.github.io/{$repo_bits[1]}

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

echo 'MARKBOT STATUS: COMPLETE';
