<?php

declare(strict_types=1);

namespace LFour\notam;

use NOTAM_LOOKUP_TABLE;

enum NotamToken {
  case WHITE_SPACE;
  case SLASH;

  case IDENT_SERIES;
  case IDENT_NUMBER;
  case IDENT_YEAR;
  case IDENT_TYPE;
  case IDENT_REF_YEAR;
  case IDENT_REF_NUMBER;
  case IDENT_REF_SERIES;

  case UNKNOWN;

  case Q;
  case Q_FIR;
  case Q_NOTAM_CODE;
  case Q_TRAFFIC;
  case Q_PURPOSE;
  case Q_SCOPE;
  case Q_LOWER;
  case Q_UPPER;
  case Q_COORDINATES;
  case Q_RADIUS;

  case A;
  case A_LOCATION;
  case A_LOCATION_2;
  case A_EXTRA;

  case B;
  case B_DATE_TIME;

  case C;
  case C_PERMANENT;
  case C_ESTIMATE;

  case C_DATE_TIME;

  case D;
  case D_SCHEDULE;

  case E;
  case E_TEXT;

  case F;
  case F_LEVEL;
  case F_SFC;

  case G;
  case G_LEVEL;
  case G_UNL;

}

class NotamFunctions {
  /**
   * @param  \LFour\notam\TOKEN[]  $tokens
   * @param  string  $notam_str
   *
   * @return \LFour\notam\NOTAM
   */
  public static function object_from_tokens(array $tokens, string $notam_str): NOTAM {
    $notam = notam_factory("20");
    foreach ($tokens as $token) {
      switch ($token->type) {
        case NotamToken::IDENT_SERIES:
          $notam->ident->series = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::IDENT_NUMBER:
          $notam->ident->number = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::IDENT_YEAR:
          $notam->ident->year = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::IDENT_TYPE:
          $notam->ident->type = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::IDENT_REF_SERIES:
          $notam->ident->ref_series = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::IDENT_REF_NUMBER:
          $notam->ident->ref_number = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::IDENT_REF_YEAR:
          $notam->ident->ref_year = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::WHITE_SPACE:
          break;
        case NotamToken::SLASH:
          break;
        case NotamToken::UNKNOWN:
          break;
        case NotamToken::Q:
          break;
        case NotamToken::Q_FIR:
          $notam->Q->fir = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::Q_NOTAM_CODE:
          $notam->Q->notam_code = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::Q_TRAFFIC:
          $notam->Q->traffic = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::Q_PURPOSE:
          $notam->Q->purpose = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::Q_SCOPE:
          $notam->Q->scope = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::Q_LOWER:
          $notam->Q->lower = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::Q_UPPER:
          $notam->Q->upper = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::Q_COORDINATES:
          $notam->Q->coordinates = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::Q_RADIUS:
          $notam->Q->radius = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::A:
          break;
        case NotamToken::A_LOCATION:
          $notam->A->location = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::A_LOCATION_2:
          $notam->A->location_2 = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::B:
          break;
        case NotamToken::C:
          break;
        case NotamToken::C_PERMANENT:
          $notam->C->permanent = substr($notam_str, $token->start, $token->end - $token->start) === 'PERM';
          break;
        case NotamToken::C_ESTIMATE:
          $notam->C->estimate = substr($notam_str, $token->start, $token->end - $token->start) === 'EST';
          break;
        case NotamToken::D:
          break;
        case NotamToken::D_SCHEDULE:
          $notam->D->schedule = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::E:
          break;
        case NotamToken::E_TEXT:
          $notam->E->body = substr($notam_str, $token->start, $token->end - $token->start);
          break;
        case NotamToken::F:
          break;
        case NotamToken::F_LEVEL:
          $value = substr($notam_str, $token->start, $token->end - $token->start);
          [$feet, $reference] = static::parse_level($value);
          $notam->F->feet = $feet;
          $notam->F->reference = $reference;
          break;
        case NotamToken::F_SFC:
          $notam->F->surface = TRUE;
          break;
        case NotamToken::G:
          break;
        case NotamToken::G_LEVEL:
          $value = substr($notam_str, $token->start, $token->end - $token->start);
          [$feet, $reference] = static::parse_level($value);
          $notam->G->feet = $feet;
          $notam->G->reference = $reference;
          break;
        case NotamToken::G_UNL:
          $notam->G->unlimited = TRUE;
          break;
        case NotamToken::B_DATE_TIME:
          $notam->B->dateTime = static::parse_date($notam->century, substr($notam_str, $token->start, $token->end - $token->start));
          break;
        case NotamToken::C_DATE_TIME:
          $notam->C->dateTime = static::parse_date($notam->century, substr($notam_str, $token->start, $token->end - $token->start));
      }
    }
    return $notam;
  }

  /**
   * @param  string  $notam_str
   *
   * @return \LFour\notam\TOKEN[]
   */
  public static function tokenize_notam(string $notam_str): array {
    $char = 0;
    /** @var TOKEN[] $tokens */
    $tokens = [];
    $notam_len = strlen($notam_str);

    static::white_space($char, $notam_str, $tokens);

    if ($char + 23 > $notam_len) {
      return  $tokens;
    }

    /**
     * F0610/22 NOTAMR F0607/22
     *
     *          number           year                  type                              number                year
     * series      |               |                     |                    series        |                    |
     *   |   +-----+-----+       +-+-+       +-----------+-----------+          |    +------+------+          +--+-+
     *   |   |           |       |   |       |                       |          |    |             |          |    |
     * +---+---+---+---+---+---+---+---+---+---+----+----+----+----+----+----+----+----+----+----+----+----+----+----+
     * | 0 | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10 | 11 | 12 | 13 | 14 | 15 | 16 | 17 | 18 | 19 | 20 | 21 | 22 | 23 |
     * +---+---+---+---+---+---+---+---+---+---+----+----+----+----+----+----+----+----+----+----+----+----+----+----+
     * | F | 0 | 6 | 1 | 0 | / | 2 | 2 |   | N | O  | T  | A  | M  | R  |    | F  |  0 |  6 |  0 |  7 | /  |  2 |  2 |
     * +---+---+---+---+---+---+---+---+---+---+----+----+----+----+----+----+----+----+----+----+----+----+----+----+
     *
     */
    $tokens[] = new TOKEN(NotamToken::IDENT_SERIES, $char, $char+1);
    $char++;

    $tokens[] = new TOKEN(NotamToken::IDENT_NUMBER, $char, $char+4);
    $char = $char + 4;

    $tokens[] = new TOKEN(NotamToken::SLASH, $char, $char+1);
    $char++;

    $tokens[] = new TOKEN(NotamToken::IDENT_YEAR, $char, $char+2);
    $char = $char + 2;

    $tokens[] = new TOKEN(NotamToken::WHITE_SPACE, $char, $char+1);
    $char++;

    $tokens[] = new TOKEN(NotamToken::IDENT_TYPE, $char, $char+6);
    $char = $char + 6;


    /**
     * N - new
     * R - Replace
     * C - Cancel
     */
    if ($notam_str[$char - 1] === "R" || $notam_str[$char - 1] === "C") {
      $tokens[] = new TOKEN(NotamToken::WHITE_SPACE, $char, $char+1);
      $char++;

      $tokens[] = new TOKEN(NotamToken::IDENT_REF_SERIES, $char, $char+1);
      $char++;

      $tokens[] = new TOKEN(NotamToken::IDENT_REF_NUMBER, $char, $char+4);
      $char = $char + 4;

      $tokens[] = new TOKEN(NotamToken::SLASH, $char, $char+1);
      $char++;

      $tokens[] = new TOKEN(NotamToken::IDENT_REF_YEAR, $char, $char+2);
      $char = $char + 2;
    }

    while (TRUE) {
      static::white_space($char, $notam_str, $tokens);
      if ($char + 1 > $notam_len) {
        break;
      }
      if ($notam_str[$char + 1] === ")") {
        if ($notam_str[$char] === 'Q') {

          /*
          * Q) YMMM/QPAXX/I/NBO/A/000/999/3740S14451E005
          **/
          static::read_till_next_section($char, $notam_str, $tokens);


          $tokens[] = new TOKEN(NotamToken::Q, $char, $char + 2);
          $char = $char + 2;

          static::white_space($char, $notam_str, $tokens);

          $tokens[] = new TOKEN(NotamToken::Q_FIR, $char, $char + 4);
          $char = $char + 4;

          $tokens[] = new TOKEN(NotamToken::SLASH, $char, $char + 1);
          $char++;

          $tokens[] = new TOKEN(NotamToken::Q_NOTAM_CODE, $char, $char + 5);
          $char = $char + 5;

          $tokens[] = new TOKEN(NotamToken::SLASH, $char, $char + 1);
          $char++;

          $token = new TOKEN(NotamToken::Q_TRAFFIC, $char);
          while ($notam_str[$char] !== "/") {
            $char++;
          }
          $token->end = $char;
          $tokens[] = $token;

          $tokens[] = new TOKEN(NotamToken::SLASH, $char, $char + 1);
          $char++;

          $token = new TOKEN(NotamToken::Q_PURPOSE, $char);
          while ($notam_str[$char] !== "/") {
            $char++;
          }
          $token->end = $char;
          $tokens[] = $token;

          $tokens[] = new TOKEN(NotamToken::SLASH, $char, $char + 1);
          $char++;

          $token = new TOKEN(NotamToken::Q_SCOPE, $char);
          while ($notam_str[$char] !== "/") {
            $char++;
          }
          $token->end = $char;
          $tokens[] = $token;

          $tokens[] = new TOKEN(NotamToken::SLASH, $char, $char + 1);
          $char++;

          $tokens[] = new TOKEN(NotamToken::Q_LOWER, $char, $char + 3);
          $char = $char + 3;

          $tokens[] = new TOKEN(NotamToken::SLASH, $char, $char + 1);
          $char++;

          $tokens[] = new TOKEN(NotamToken::Q_UPPER, $char, $char + 3);
          $char = $char + 3;

          $tokens[] = new TOKEN(NotamToken::SLASH, $char, $char + 1);
          $char++;

          $tokens[] = new TOKEN(NotamToken::Q_COORDINATES, $char, $char + 11);
          $char = $char + 11;

          $tokens[] = new TOKEN(NotamToken::Q_RADIUS, $char, $char + 3);
          $char = $char + 3;

          static::white_space($char, $notam_str, $tokens);
        }
        else if ($notam_str[$char] === 'A') {
          static::read_till_next_section($char, $notam_str, $tokens);


          /*
           * A) YMML
           * A) YMMM/YBBB
           **/
          $tokens[] = new TOKEN(NotamToken::A, $char, $char + 2);
          $char = $char + 2;

          static::white_space($char, $notam_str, $tokens);

          $tokens[] = new TOKEN(NotamToken::A_LOCATION, $char,
            $char + 4); // ICAO code
          $char = $char + 4;

          if ($notam_str[$char] === '/') {
            $tokens[] = new TOKEN(NotamToken::SLASH, $char, $char + 1);
            $char++;

            $tokens[] = new TOKEN(NotamToken::A_LOCATION_2, $char,
              $char + 4); // ICAO code
            $char = $char + 4;
          }

          $token = new TOKEN(NotamToken::A_EXTRA, $char);

          static::skip_till_next_section($char, $notam_str);

          static::scroll_back_white_space($char, $notam_str);

          $token->end = $char;
          $tokens[] = $token;

          static::white_space($char, $notam_str, $tokens);
        }
        else if ($notam_str[$char] === 'B') {
          static::read_till_next_section($char, $notam_str, $tokens);

          /*
           * B) 2203090629
           **/
          $tokens[] = new TOKEN(NotamToken::B, $char, $char + 2);
          $char = $char + 2;

          static::white_space($char, $notam_str, $tokens);

          $tokens[] = new TOKEN(NotamToken::B_DATE_TIME, $char, $char + 10);
          $char = $char + 10;

          static::white_space($char, $notam_str, $tokens);
        }
        else if ($notam_str[$char] === 'C') {
          static::read_till_next_section($char, $notam_str, $tokens);

          /*
           * C) PERM
           * C) 2208040600
           **/

          $tokens[] = new TOKEN(NotamToken::C, $char, $char + 2);
          $char = $char + 2;

          static::white_space($char, $notam_str, $tokens);

          if (substr($notam_str, $char, 4) === 'PERM') {
            $tokens[] = new TOKEN(NotamToken::C_PERMANENT, $char, $char + 4);
            $char = $char + 4;
          }
          else {
            $tokens[] = new TOKEN(NotamToken::C_DATE_TIME, $char, $char + 10);
            $char = $char + 10;

            static::white_space($char, $notam_str, $tokens);

            if (substr($notam_str, $char, 3) === 'EST') {
              $tokens[] = new TOKEN(NotamToken::C_ESTIMATE, $char, $char + 3);
              $char = $char + 3;
            }
          }

          static::white_space($char, $notam_str, $tokens);
        }
        else if ($notam_str[$char] === 'D') {
          static::read_till_next_section($char, $notam_str, $tokens);

          /*
           * D) MON WED THU 0900-1300, TUE FRI SAT 0900-2000
           **/

          $tokens[] = new TOKEN(NotamToken::D, $char, $char + 2);
          $char = $char + 2;

          static::white_space($char, $notam_str, $tokens);

          $token = new TOKEN(NotamToken::D_SCHEDULE, $char);

          // skip until new line
          while ($notam_str[$char + 1] !== ')') {
            $char++;
          }
          if ($notam_str[$char - 1] === "\n") {
            $char--;
          }
          $token->end = $char;

          $tokens[] = $token;

          static::white_space($char, $notam_str, $tokens);
        }
        else if ($notam_str[$char] === 'E') {
          static::read_till_next_section($char, $notam_str, $tokens);

          /*
           * E) AIP DEP AND APCH(DAP) EAST YMML AMD
           * AMD EXISTING LIGHTING CAUTION NOTE ON THE FLW STAR CHARTS TO READ:
           * RWY 34 IS INDICATED BY RUNWAY THRESHOLD IDENTIFICATION LIGHTS,
           * ESSENDON AIRPORT 5NM SE OF ML.
           **/

          $tokens[] = new TOKEN(NotamToken::E, $char, $char + 2);
          $char = $char + 2;

          static::white_space($char, $notam_str, $tokens);

          $token = new TOKEN(NotamToken::E_TEXT, $char);

          static::skip_till_next_section($char, $notam_str);

          static::scroll_back_white_space($char, $notam_str);

          $token->end = $char;
          $tokens[] = $token;

          static::white_space($char, $notam_str, $tokens);

          if ($char >= strlen($notam_str)) {
            break;
          }
        }
        else if ($notam_str[$char] === 'F') {
          static::read_till_next_section($char, $notam_str, $tokens);

          /*
           * F) 1500FT AGL
           * F) SFC
           **/

          $tokens[] = new TOKEN(NotamToken::F, $char, $char + 2);
          $char = $char + 2;

          static::white_space($char, $notam_str, $tokens);

          /**
           * Surface (SFC), an altitude in feet, or a flight
           * level can be specified.
           **/
          if (substr($notam_str, $char, 3) === 'SFC') {
            $tokens[] = new TOKEN(NotamToken::F_SFC, $char, $char + 3);
            $char = $char + 3;

            static::white_space($char, $notam_str, $tokens);
          }
          else {
            $token = new TOKEN(NotamToken::F_LEVEL, $char);

            static::skip_till_next_section($char, $notam_str);
            static::scroll_back_white_space($char, $notam_str);

            $token->end = $char;

            $tokens[] = $token;

            static::white_space($char, $notam_str, $tokens);
          }
        }
        else if ($notam_str[$char] === 'G') {
          static::read_till_next_section($char, $notam_str, $tokens);

          /*
           * G) 17999FT AMSL
           * G) UNL
           **/

          $tokens[] = new TOKEN(NotamToken::G, $char, $char + 2);
          $char = $char + 2;

          static::white_space($char, $notam_str, $tokens);

          /**
           * Surface (SFC), an altitude in feet, or a flight
           * level can be specified.
           **/
          if (substr($notam_str, $char, 3) === 'UNL') {
            $tokens[] = new TOKEN(NotamToken::G_UNL, $char, $char + 3);
            $char = $char + 3;

            static::white_space($char, $notam_str, $tokens);
          }
          else {
            $token = new TOKEN(NotamToken::G_LEVEL, $char);

            static::skip_till_next_section($char, $notam_str);
            static::scroll_back_white_space($char, $notam_str);

            $token->end = $char;

            $tokens[] = $token;

            static::white_space($char, $notam_str, $tokens);
          }
        }
        else {
          if ($char >= strlen($notam_str)) {
            break;
          }
          static::read_till_next_section($char, $notam_str, $tokens);
          break;
        }
      }
      else {
        if (!static::read_till_next_section($char, $notam_str, $tokens)) {
          break; // we have reached the end or cannot find another section
        }
      }
    }

    return $tokens;
  }

  /**
   * @param  int  $char
   * @param  string  $notam_str
   * @param  array  $tokens
   */
  public static function white_space(int &$char, string &$notam_str, array &$tokens) {
    $len = strlen($notam_str);
    $token = new TOKEN(NotamToken::WHITE_SPACE);
    $token->start = $char;
    // skip until new line
    while ($char < $len && in_array($notam_str[$char], ["\n", " "])) {
      $char++;
    }
    $token->end = $char;
    if ($token->start != $token->end) {
      $tokens[] = $token;
    }
  }

  /**
   * @param  int  $char
   * @param  string  $notam_str
   */
  public static function scroll_back_white_space(int &$char, string &$notam_str) {
    if ($char <= 1) {
      return;
    }
    // skip until new line
    while (in_array($notam_str[$char - 1], ["\n", " "])) {
      $char--;
    }
  }

  /**
   * @param  int  $char
   * @param  string  $notam_str
   * @param  array  $tokens
   * @return bool returns true if char is advanced
   */
  public static function read_till_next_section(int &$char, string &$notam_str, array &$tokens): bool {
    $len = strlen($notam_str);
    $token = new TOKEN(NotamToken::UNKNOWN);
    $token->start = $char;

    // end of text
    if ($char === ($len-1)) {
      return FALSE;
    }

    if ($char === 0 && $notam_str[$char + 1] !== ')') {
      return FALSE;
    }
    // skip until section start
    // "XXXXXXXXX C) XXXXXX"
    // OR
    // "C) XXXXX"
    while ($char + 1 <= $len) {
      if ($notam_str[$char + 1] === ')' && ($notam_str[$char + -1] === ' ' || $notam_str[$char + -1] === "\n")) {
        break;
      }
      $char++;
    }
    $token->end = $char;
    if ($token->start != $token->end) {
      $tokens[] = $token;
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @param  int  $char
   * @param  string  $notam_str
   */
  public static function skip_till_next_section(int &$char, string &$notam_str): void {
    $last_idx = strlen($notam_str) - 1;

    // skip until section start
    // "XXXXXXXXX C) XXXXXX"
    // OR
    // "C) XXXXX"
    while ($char != $last_idx) {
      if ($notam_str[$char + 1] === ')' && ($notam_str[$char + -1] === ' ' || $notam_str[$char + -1] === "\n")) {
        break;
      }
      $char++;
    }

    if ($char == $last_idx) {
      $char++;
    }
  }

  /**
   * @param \LFour\notam\TOKEN[]  $tokens
   * @param string $notam_str
   *
   * @return string
   */
  public static function render_tokens_to_html(array $tokens, string $notam_str): string {
    $colours = ['#91F9E5', '#76f7bf', '#5fdd9d', '#499167'];
    $num_colours = count($colours);
    $html = '<pre>';
    $i = 0;
    foreach ($tokens as $token) {
      $color = $colours[$i % $num_colours];
      $html .= "<span style='background-color: $color;'><abr title='" . $token->type->name . "'>" . substr($notam_str, $token->start, $token->end - $token->start) . "</abr></span>";
      $i++;
    }
    $html .= '</pre>';
    return  $html;
  }

  /**
   * @param  string  $century The century which the notam was created as a two
   *  letter string "19", "20" etc
   * @param  string  $string Notam DateTime string
   * @param $idx
   *
   * @return \DateTime
   * @throws \Exception
   */
  public static function parse_date(string $century, string $string, $idx = 0): \DateTime {
    $year = substr($string, $idx, 2);
    $idx = $idx + 2;

    $month = substr($string, $idx, 2);
    $idx = $idx + 2;

    $day = substr($string, $idx, 2);
    $idx = $idx + 2;

    $hour = substr($string, $idx, 2);
    $idx = $idx + 2;

    $minute = substr($string, $idx, 2);
    $idx = $idx + 2;

    $date = new \DateTime('now', new \DateTimeZone('UTC'));
    $date->setDate((int) ($century . $year), (int) $month, (int) $day);
    $date->setTime((int) $hour, (int) $minute, 00);
    return $date;
  }

  public static function parse_level(string $string) {
    /// 1500FT AGL
    /// 17999FT AMSL
    /// FL195
    if (str_starts_with($string, "FL")) {
      $feet = substr($string, 2, strlen($string) - 2);
      return [$feet . "00", 'Flight Level'];
    }

    $ftpos = strpos($string, "FT");
    if ($ftpos === FALSE) {
      return ['', ''];
    }
    $feet = substr($string, 0, $ftpos);
    $reference = substr($string, $ftpos + 3, strlen($string) - ($ftpos + 2));
    return [$feet, $reference];
  }

  /**
   * @param TOKEN[] $tokens
   * @param string $notam_string
   * @param bool $ignore_sep
   *
   * @return string[]
   * @throws \Exception
   */
  public static function tokens_descriptions(array &$tokens, string &$notam_string): array {
    $rows = [];
    foreach ($tokens as $i => $token) {
      $description = 'N/A';
      $value = substr($notam_string, $token->start, $token->end - $token->start);
      switch ($token->type) {
        case NotamToken::IDENT_SERIES:
        case NotamToken::IDENT_REF_SERIES:
          if (isset(NOTAM_LOOKUP_TABLE::$NOTAM_SERIES[$value])) {
            $description = NOTAM_LOOKUP_TABLE::$NOTAM_SERIES[$value];
          }
          break;
        case NotamToken::IDENT_NUMBER:
        case NotamToken::IDENT_REF_NUMBER:
          $description = "ID number.";
          break;
        case NotamToken::IDENT_TYPE:
          if (isset(NOTAM_LOOKUP_TABLE::$NOTAM_TYPE[$value])) {
            $description = NOTAM_LOOKUP_TABLE::$NOTAM_TYPE[$value];
          }
          break;
        case NotamToken::IDENT_YEAR:
        case NotamToken::IDENT_REF_YEAR:
          $description = "Year Issued: 20" . $value;
          break;
        case NotamToken::Q:
          $description = 'Qualifier Line';
          break;
        case NotamToken::Q_FIR:
          if (isset(NOTAM_LOOKUP_TABLE::$NOTAM_FIR[$value])) {
            $fir_description = NOTAM_LOOKUP_TABLE::$NOTAM_FIR[$value];
          }
          else {
            $fir_description = 'No extra infomation for ' . $value;
          }
          $description = "Flight Information Region: <b>" . $fir_description . "</b> <a href='https://en.wikipedia.org/wiki/$value'>Wiki</a>";
          break;
        case NotamToken::Q_NOTAM_CODE:
          $description = "<b>" . $value[1] . "</b> " . "Subject: <b>" . NOTAM_LOOKUP_TABLE::$NORAM_Q_CODE_LETTER_2[$value[1]] . "</b><br>";
          $description .= "<b>" . $value[1] . $value[2] . "</b> " . "Signification: <b>" . NOTAM_LOOKUP_TABLE::$NORAM_Q_CODE_LETTER_2_and_3[$value[1] . $value[2]]['signification'] . "</b><br>";

          $description .= "<b>" . $value[3] . "</b> " . "Status of operation: <b>" . NOTAM_LOOKUP_TABLE::$NORAM_Q_CODE_LETTER_4[$value[3]] . "</b><br>";
          $description .= "<b>" . $value[3] . $value[4] . "</b> " . "Signification: <b>" . NOTAM_LOOKUP_TABLE::$NORAM_Q_CODE_LETTER_4_and_5[$value[3] . $value[4]]['signification'] . "</b><br>";
          break;
        case NotamToken::Q_TRAFFIC:
          if (empty($value)) {
            break;
          }
          $description = '';
          foreach (str_split($value) as $char) {
            $description .= "<b>(" . $char . ")</b> " .  NOTAM_LOOKUP_TABLE::$NOTAM_Q_TRAFFIC[$char] . "<br>";
          }
          break;
        case NotamToken::Q_PURPOSE:
          if (empty($value)) {
            break;
          }
          $description = '';
          foreach (str_split($value) as $char) {
            $description .= "<b>(" . $char . ")</b> " .  NOTAM_LOOKUP_TABLE::$NOTAM_Q_PURPOSE[$char] . "<br>";
          }
          break;
        case NotamToken::Q_SCOPE:
          if (empty($value)) {
            break;
          }
          $description = '';
          foreach (str_split($value) as $char) {
            $description .= "<b>(" . $char . ")</b> " .  NOTAM_LOOKUP_TABLE::$NOTAM_Q_SCOPE[$char] . "<br>";
          }
          break;
        case NotamToken::Q_LOWER:
          $feet = ($value === '000')? 0 : ltrim($value, '0') . '00' ;
          $description = 'Flight level ' . $value . ' ( ' . $feet . ' feet )';
          break;
        case NotamToken::Q_UPPER:
          $feet = ltrim($value, '0') . '00';
          if (strlen($feet) > 3) {
            $feet = substr($feet, 0, strlen($feet) - 3) . ',' . substr($feet, strlen($feet) - 3);
          }
          $description = 'Flight level ' . $value . ' ( ' . $feet . ' feet )';
          break;
        case NotamToken::Q_COORDINATES:
          /// 5115N00045W
          //  +---+---+---+---+---+---+---+---+---+---+----+
          //  | 0 | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10 |
          //  +---+---+---+---+---+---+---+---+---+---+----+
          //  | 5 | 1 | 1 | 5 | N | 0 | 0 | 0 | 4 | 5 | W  |
          //  +---+---+---+---+---+---+---+---+---+---+----+

          $degrees_ns   = substr($value,0, 2);
          $minutes_ns   = substr($value,2, 2);
          $north_south  = substr($value,4, 1);

          $degrees_ew = substr($value,5, 3);
          $minutes_ew = substr($value,8, 2);
          $east_west  = substr($value,10, 1);

          $DMS = sprintf('%d°%d\'00.0"%s %d°%d\'00.0"%s', $degrees_ns, $minutes_ns, $north_south, $degrees_ew, $minutes_ew, $east_west);

          $description = '<a href="https://www.google.com/maps/place/' . urlencode($DMS) . '">' . $DMS . '</a>';
          break;
        case NotamToken::Q_RADIUS:
          $NM = ltrim($value, '0');
          $description =  "Within $NM Nautical Miles of the coordinates.";
          break;
        case NotamToken::A:
          $description =  "ICAO location indicator of the aerodrome or FIR";
          break;
        case NotamToken::A_LOCATION:
        case NotamToken::A_LOCATION_2:
          if (isset(NOTAM_LOOKUP_TABLE::$NOTAM_FIR[$value])) {
            $description =  "FIR: " . NOTAM_LOOKUP_TABLE::$NOTAM_FIR[$value];
          }
          else if (isset(NOTAM_LOOKUP_TABLE::$ICAO_AIRPORTS[$value])) {
            $description =  "Aerodrome: " . NOTAM_LOOKUP_TABLE::$ICAO_AIRPORTS[$value];
          }
          break;
        case NotamToken::B:
          $description =  "NOTAM becomes effective date time.";
          break;
        case NotamToken::B_DATE_TIME:
          $century = substr((new \DateTime('now'))->format("Y"), 0, 2);

          $date = NotamFunctions::parse_date($century, $value);
          $datetime_string = $date->format(\DateTime::ATOM);
          $description =  "NOTAM becomes effective at <time datetime='$datetime_string'>" . $datetime_string . " (UTC)</time> <br/>";
          break;
        case NotamToken::C:
          $description =  "NOTAM ending date and time.";
          break;
        case NotamToken::C_DATE_TIME:
          $century = substr((new \DateTime('now'))->format("Y"), 0, 2);

          $date = NotamFunctions::parse_date($century, $value);
          $datetime_string = $date->format(\DateTime::ATOM);
          $description =  "NOTAM in no longer effective after <time datetime='$datetime_string'>" . $datetime_string . " (UTC)</time> <br/>";
          break;
        case NotamToken::C_PERMANENT:
          $description = "NOTAM is permanent.";
          break;
        case NotamToken::C_ESTIMATE:
          $description = "NOTAM ending date and time is an estimate.";
          break;
        case NotamToken::E:
          $description = "Main message.";
          break;
        case NotamToken::E_TEXT:
          $description = "Main message body.";
          break;
        case NotamToken::F:
          $description = "Lower level.";
          break;
        case NotamToken::G:
          $description = "Upper level.";
          break;
        case NotamToken::G_LEVEL:
        case NotamToken::F_LEVEL:
          [$feet, $reference] = static::parse_level($value);
          $description = $feet . " feet";
          if (isset(NOTAM_LOOKUP_TABLE::$NOTAM_lEVEL_ABBR[$reference])) {
            $description .= " " . NOTAM_LOOKUP_TABLE::$NOTAM_lEVEL_ABBR[$reference];
          }
          break;
        case NotamToken::F_SFC:
          $description = "Surface";
          break;
        case NotamToken::G_UNL:
          $description = "Unlimited";
          break;
        default:
          break;
      }
      $rows[] = $description;
    }
    return $rows;
  }

  public static function token_description(TOKEN $token, string &$notam_string): string {
    $tokens = [$token];
    return NotamFunctions::tokens_descriptions($tokens, $notam_string)[0];
  }

  /**
   * @param  string  $notam_str
   *
   * @return \LFour\notam\TOKEN[]
   */
  public static function explain_notam_body(string $notam_str): array {
    $words = explode(" ", $notam_str);
    $tokens = [];
    foreach ($words as $word) {

    }
  }
}


class NOTAM {

  public string $century;

  public NOTAM_IDENT $ident;

  public NOTAM_Q $Q;

  public NOTAM_A $A;

  public NOTAM_B $B;

  public NOTAM_C $C;

  public NOTAM_D $D;

  public NOTAM_E $E;

  public NOTAM_F $F;

  public NOTAM_G $G;
}

class TOKEN {
  public NotamToken $type;
  public ?int $start;
  public ?int $end;
  function __construct(NotamToken $type, $start=NULL, $end=NULL) {
    $this->type = $type;
    $this->start = $start;
    $this->end = $end;
  }
}

class NOTAM_IDENT {
  public string $series;
  public string $number;
  public string $year;
  public string $type;
  public string $ref_series;
  public string $ref_number;
  public string $ref_year;
}

/**
 * Qualifier Line
 * A qualifier line, which contains coded information,
 * coordinates, and radius for area for the automated filtering of
 * NOTAMs
 */
class NOTAM_Q {

  /**
   * Flight information region
   *
   * A FIR is defined as the ICAO location indicator of the FIR in which
   * the subject of the NOTAM is located geographically.
   *
   * @var string $fir
   */
  public string $fir = '';

  /**
   * NOTAM code
   *
   * The first letter is always the letter Q. The second and third letters
   * identify the subject, and the fourth and fifth letters denote the
   * condition of the subject being reported.
   * @var string
   */
  public string $notam_code = '';
  public string $traffic = '';
  public string $purpose = '';
  public string $scope = '';

  /**
   * The lower and upper limit field applies mainly to airspace
   * related NOTAMs but are not limited to them.
   * Default 000
   * @var string
   */
  public string $lower = '000';
  /**
   * The lower and upper limit field applies mainly to airspace
   * related NOTAMs but are not limited to them.
   * Default 999
   * @var string
   */
  public string $upper = '999';

  /**
   * The coordinates represent the point of influence, or the approximate
   * center of a circle whose radius encompasses the whole area of
   * influence. It is specified by an 11-character latitude and longitude
   * accurate to one minute.
   *
   *
   * example 4159N08754W005
   *
   * @var string coordinates
   */
  public string $coordinates;

  /**
   * The radius is a three-digit distance representing the radius of
   * influence in whole nautical miles. A radius that includes a decimal will
   * be rounded to the next higher whole nautical mile. The radius
   * impacts the pilot briefing coverage and number of NOTAMs received
   * in a NOTAM query, so it must be as precise as possible.
   *  @var string $radius
   */
  public string $radius;

}

/**
 * ICAO location indicator of the aerodrome or FIR in which
 * the facility, airspace, or condition being reported is located.
 */
class NOTAM_A {

  /**
   * ICAO Location or FIR
   * @var string
   */
  public string $location;
  /**
   * ICAO Location or FIR
   * @var string
   */
  public string $location_2;
}

/**
 * Item B) From: This entry is the date and time
 * that the NOTAM becomes in force which is
 * equivalent to the date and time at which the
 * activity or condition described in Item E)
 * begins.
 *  Effective date/time (UTC)
 */
class NOTAM_B {
  public \DateTime $dateTime;
}

/**
 * Item C) To: A date-time group must be used to
 * annotate the time that the NOTAM is no longer
 * in effect which is equivalent to the time at
 * which the activity or condition described in Item
 * E) is expected to be no longer valid.
 * Expiration date/time (UTC)
 */
class NOTAM_C {
  public ?\DateTime $dateTime = NULL;
  public bool $permanent = FALSE;
  public bool $estimate = FALSE;
}

/**
 * The D) Schedule is an optional field.
 * If the hazard, status of operation, or condition
 * of facility being reported on will be active in
 * accordance with a specific date and time
 * schedule between the dates times indicated in
 * Items B) and C), insert such information under
 * Item D).
 *
 * The following are approved abbreviations for
 * use in Item D):
 *  • EXC – For designating a full day or a series of full
 *    days when the NOTAM is NOT active.
 *  • SR – If appropriate, to indicate sunrise.
 *  • SS – If appropriate, to indicate sunset.
 *  • H24 – For the whole day/dates concerned. Not to
 *    be used as a single entry.
 *  • DLY – Optional for a ‘daily’ schedule, the
 *    expression ‘nightly’ will not be used.
 *  • EVERY – For a schedule on fixed days.
 *  • AND – If used, will be included in front of the last
 *    date, group, or time period specified in Item D).
 * Schedule (optional)
 */
class NOTAM_D {
  public string $schedule = '';
}

/**
 * The Item E) NOTAM Text field describes is the
 * condition in which the NOTAM is being issued
 * or put into force.
 * The text must be kept brief, yet contain all
 * essential information needed to accurately
 * convey the changes in condition.
 *
 * Plain language text description of information
 */
class NOTAM_E {
  public string $body = '';
}

/**
 * Items F) Lower Limit and G) Upper Limit are
 * required for navigation warnings and airspace
 * restrictions.
 * • Surface (SFC), an altitude in feet, or a flight
 *   level can be specified.
 * • Unlimited (UNL) may be used to describe an
 *   upper limit.
 *
 * Lower altitude limit (Used with Airspace NOTAMs)
 */
class NOTAM_F {
  public string $feet;

  /**
   * AGL  - above ground level
   * AMSL - above mean sea level
   * @var string
   */
  public string $reference;

  public bool $surface = false;
}

/**
 * Items F) Lower Limit and G) Upper Limit are
 * required for navigation warnings and airspace
 * restrictions.
 * • Surface (SFC), an altitude in feet, or a flight
 *   level can be specified.
 * • Unlimited (UNL) may be used to describe an
 *   upper limit.
 *
 * Upper altitude limit (Used with Airspace NOTAMs)
 */
class NOTAM_G {
  public string $feet;

  /**
   * AGL  - above ground level
   * AMSL - above mean sea level
   * @var string
   */
  public string $reference;
  public bool $unlimited = false;
}

function notam_factory(string $century) {
  $notam =  new NOTAM();
  $notam->century = $century;
  $notam->ident = new NOTAM_IDENT();
  $notam->Q = new NOTAM_Q();
  $notam->A = new NOTAM_A();
  $notam->B = new NOTAM_B();
  $notam->C = new NOTAM_C();
  $notam->D = new NOTAM_D();
  $notam->E = new NOTAM_E();
  $notam->F = new NOTAM_F();
  $notam->G = new NOTAM_G();

  return $notam;
}

/**
 * The scope qualifiers are used to categorize NOTAMs
 */
enum NOTAM_SCOPE: string {

  /**
   * Aerodrome
   * @var string
   */
  case A = 'A';

  /**
   * Enroute
   * @var string
   */
  case E = 'E';

  /**
   * Navigation warning
   * @var string
   */
  case W = 'W';

  /**
   * Checklist
   * @var string
   */
  case K = 'K';
}

/**
 * The Purpose qualifier relates a NOTAM to
 * certain purposes (intentions) and thus allows
 * retrieval according to the user’s requirements.
 */
enum NOTAM_PURPOSE: string {

  /**
   * NOTAM selected for the immediate attention of aircraft operators
   * @var string
   */
  case N = 'N';

  /**
   * NOTAM selected for pre-flight information briefing
   * @var string
   */
  case B = 'B';

  /**
   * NOTAM concerning flight operations
   * @var string
   */
  case O = 'O';

  /**
   * Miscellaneous NOTAM; not subject for briefing, but is available on request
   * @var string
   */
  case M = 'M';

  /**
   * NOTAM is a Checklist
   * @var string
   */
  case K = 'K';

}

enum NOTAM_TRAFFIC: string {

  /**
   * Instrument Flight Rules (IFR)
   * @var string
   */
  case I = 'I';

  /**
   * Visual Flight Rules (VFR)
   * @var string
   */
  case V = 'V';

  /**
   * NOTAM is a Checklist
   * @var string
   */
  case K = 'K';
}

enum NOTAM_SERIES: string {
  /**
   * Aerodrome Movement Areas
   */
  case B = 'B';

  /**
   * Published Services
   */
  case C = 'C';

  /**
   * Special Activity Airspace
   */
  case D = 'D';

  /**
   * Airspace Events and Activities
   */
  case E = 'E';

  /**
   * Airways and Air Traffic Routes
   */
  case G = 'G';

  /**
   * Regulatory NOTAMs
   */
  case H = 'H';

  /**
   * Apron/Ramp and Facilities APN
   */
  case I = 'I';

  /**
   * Obstructions
   */
  case J = 'J';

  /**
   * FCC Obstructions ASR assigned
   */
  case K = 'K';

  /**
   * Ground-Based Navigational Aids
   */
  case N = 'N';

  /**
   * Field Condition NOTAM
   */
  case R = 'R';

  /**
   * Published Instrument Procedures
   */
  case V = 'V';

  /**
   * Satellite Based Information
   */
  case Z = 'Z';
}
