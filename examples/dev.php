<?php

require  __DIR__ .  '\..\vendor\autoload.php';

use LFour\notam\NotamFunctions;

$contents = file_get_contents(__DIR__ .  '\notams.txt');
$notams = explode('============================================', $contents);

switch(php_sapi_name()) {
  case "cli":
    $start = microtime(TRUE);
    foreach ($notams as $notam_string) {
      $tokens = NotamFunctions::tokenize_notam($notam_string);
      foreach ($tokens as $token) {
        print $token->type->name . ':' . substr($notam_string, $token->start,
            $token->end - $token->start) . "\n";
      }
      $notam = NotamFunctions::object_from_tokens($tokens, $notam_string);
      print_r($notam);
    }
    print "" . microtime(TRUE) - $start;
    break;
  case "cli-server":
    foreach ($notams as $notam_string) {
      $tokens = NotamFunctions::tokenize_notam($notam_string);
      print NotamFunctions::render_tokens_to_html($tokens, $notam_string);
    }
    break;
}
