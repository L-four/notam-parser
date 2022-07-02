<?php

require '..\src\notam.php';

use function LFour\notam\object_from_tokens;
use function LFour\notam\render_tokens_to_html;
use function LFour\notam\tokenize_notam;

$contents = file_get_contents('notams.txt');
$notams = explode('============================================', $contents);

switch(php_sapi_name()) {
  case "cli":
    $start = microtime(TRUE);
    foreach ($notams as $notam_string) {
      $tokens = tokenize_notam($notam_string);
      foreach ($tokens as $token) {
        print $token->type->name . ':' . substr($notam_string, $token->start,
            $token->end - $token->start) . "\n";
      }
      $notam = object_from_tokens($tokens, $notam_string);
      print_r($notam);
    }
    print "" . microtime(TRUE) - $start;
    break;
  case "cli-server":
    foreach ($notams as $notam_string) {
      $tokens = tokenize_notam($notam_string);
      print render_tokens_to_html($tokens, $notam_string);
    }
    break;
}
