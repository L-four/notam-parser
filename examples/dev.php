<?php

require  __DIR__ .  '\..\vendor\autoload.php';

use LFour\notam\NotamFunctions;

$notams_handle = fopen(__DIR__ .  '\AUS2022-06-01.csv', 'r');
function read_new_lines(string $notam_str): string {
  return str_replace('\n', "\n", $notam_str);
}
$notams = [];
$count = 0;
while (($line = fgetcsv($notams_handle)) !== FALSE) {
  $count++;
  if ($count === 1) {
    continue;
  }
  $str = read_new_lines($line[1]);

  $notams[] = substr($str, 0, strpos($str, "CREATED:"));
}
fclose($notams_handle);

$contents = file_get_contents(__DIR__ .  '\notams.txt');
$notams += explode('============================================', $contents);


switch(php_sapi_name()) {
  case "cli":
    $start = microtime(TRUE);
    foreach ($notams as $idx => $notam_string) {
      $tokens = NotamFunctions::tokenize_notam($notam_string);
      if (in_array('tokens', $argv)) {
        print_r($tokens);
      }
      if (in_array('tokens-print', $argv)) {
        foreach ($tokens as $token) {
          print $token->type->name . ':' . substr($notam_string, $token->start,
              $token->end - $token->start) . "\n";
        }
      }
      if (in_array('tokens-descriptions', $argv)) {
        foreach ($tokens as $token) {
          print $token->type->name . ':' . NotamFunctions::token_description($token, $notam_string) . "\n";
        }
      }
      if (in_array('check', $argv)) {
        foreach ($tokens as $token) {
          if ($token->type === \LFour\notam\NotamToken::UNKNOWN) {
            print "Notam $idx has unexpected content: " . substr($notam_string, $token->start,
                $token->end - $token->start)  .  var_export($token, TRUE);
          }
        }
      }
      $notam = NotamFunctions::object_from_tokens($tokens, $notam_string);
      if (in_array('class', $argv)) {
        print_r($notam);
      }
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
