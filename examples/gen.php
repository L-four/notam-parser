<?php

$h = fopen('Abbreviations_English.csv', 'r');
$oh = fopen('notam_abbreviations.php', 'w+');


//Tables
//       For convenience, let us give more compact tables in hex and decimal.
//
//         2 3 4 5 6   7       30 40 50 60 70 80 90 100 110 120
//        -------------      ---------------------------------
//       0:   0 @ P ` p     0:    (  2  <  F  P  Z  d   n   x
//       1: ! 1 A Q a q     1:    )  3  =  G  Q  [  e   o   y
//       2: " 2 B R b r     2:    *  4  >  H  R  \  f   p   z
//       3: # 3 C S c s     3: !  +  5  ?  I  S  ]  g   q   {
//       4: $ 4 D T d t     4: "  ,  6  @  J  T  ^  h   r   |
//       5: % 5 E U e u     5: #  -  7  A  K  U  _  i   s   }
//       6: & 6 F V f v     6: $  .  8  B  L  V  `  j   t   ~
//       7: ´ 7 G W g w     7: %  /  9  C  M  W  a  k   u  DEL
//       8: ( 8 H X h x     8: &  0  :  D  N  X  b  l   v
//       9: ) 9 I Y i y     9: ´  1  ;  E  O  Y  c  m   w
//       A: * : J Z j z
//       B: + ; K [ k {
//       C: , < L \ l |
//       D: - = M ] m }
//       E: . > N ^ n ~
//       F: / ? O _ o DEL

function ascii_token_trim($string) {
  $output = '';
  $len = strlen($string);
  for ($i = 0; $i < $len; $i++) {
    $char_code = ord($string[$i]);
    if ($char_code === 47 || $char_code === 45 || $char_code >= 65 && $char_code <= 90) {
      $output .= $string[$i];
    }
  }
  return $output;
}

function ascii_trim($string) {
  $output = '';
  $len = strlen($string);
  for ($i = 0; $i < $len; $i++) {
    $char_code = ord($string[$i]);
    if ($char_code >= 32 && $char_code <= 126) {
      $output .= $string[$i];
    }
  }
  return $output;
}

$tokens = [];
$desc = [];

while (($line = fgetcsv($h)) !== FALSE) {
  $abbr = trim($line[1]);
  $abbr = ascii_token_trim($abbr);

  $token = str_replace("/", "_", $abbr);
  $token = str_replace("-", "__", $token);

  $tokens[$token] = $abbr;
  $desc[$token][] = ascii_trim($line[2]);
}

fputs($oh,  "<?php\n");
fputs($oh, "enum NOTAM_ABBR {\n");

foreach (array_keys($tokens) as $token) {
  fputs($oh, "  case $token;\n");
}

fputs($oh, "}\n");

fputs($oh, "\n");
fputs($oh, "class NOTAM_ABBR_LOOKUP {\n");
fputs($oh, "  static public \$ABBR_LOOKUP = [\n");

foreach ($tokens as $token => $value) {
  fputs($oh, "    '$value' => NOTAM_ABBR::$token,\n");
}

fputs($oh, "  ];\n");
fputs($oh, "  static public \$ABBR_DESC_LOOKUP = [\n");

foreach ($desc as $token => $value) {
  fputs($oh, "    '$token' => [\n");
  foreach ($value as $idx => $val) {
    fputs($oh, "      '$val',\n");
  }
  fputs($oh, "    ],\n");
}

fputs($oh, "  ];\n");
fputs($oh, "}\n");

fclose($oh);
fclose($h);

