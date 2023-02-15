<?php
require  __DIR__ .  '\..\..\vendor\autoload.php';

$notam_string = 'J2187/22 NOTAMN' .
                'Q) YBBB/QMXLC/IV/M/A/000/999/2738S15243E005' .
                'A) YAMB' .
                'B) 2207062200 C) 2207070400' .
                'E) TWY Z NOT AVBL DUE PARKED ACFT';

$tokens = \LFour\notam\NotamFunctions::tokenize_notam($notam_string);
print_r($tokens);

$token = $tokens[0];
$value = substr($notam_string, $token->start, $token->end - $token->start);
print_r($value);

$notam = \LFour\notam\NotamFunctions::object_from_tokens($tokens, $notam_string);
print_r($notam->ident->series);


