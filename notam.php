<?php

declare(strict_types=1);

namespace LFour\notam;

enum NotamToken: int {
  case WHITE_SPACE = 0;
  case SLASH = 1;

  case IDENT_SERIES = 2;
  case IDENT_NUMBER = 3;
  case IDENT_YEAR = 4;
  case IDENT_TYPE = 5;
  case IDENT_REF_YEAR = 6;
  case IDENT_REF_NUMBER = 7;
  case IDENT_REF_SERIES = 8;

  case UNKNOWN = 9;

  case Q = 10;
  case Q_FIR = 11;
  case Q_NOTAM_CODE = 12;
  case Q_TRAFFIC = 13;
  case Q_PURPOSE = 14;
  case Q_SCOPE = 15;
  case Q_LOWER = 16;
  case Q_UPPER = 17;
  case Q_COORDINATES = 18;
  case Q_RADIUS = 19;

  case A = 20;
  case A_LOCATION = 21;

  case YEAR = 22;
  case MINUTE = 23;
  case HOUR = 24;
  case DAY = 25;
  case MONTH = 26;

  case B = 27;

  case C = 28;
  case C_PERMANENT = 29;
  case C_ESTIMATE = 30;

  case D = 31;
  case D_SCHEDULE = 32;

  case E = 33;
  case E_TEXT = 34;

  case F = 35;
  case F_LEVEL = 36;

  case G = 37;
  case G_LEVEL = 38;

}


$contents = file_get_contents('notams.txt');
$notams = explode('============================================', $contents);

switch(php_sapi_name()) {
  case "cli":
    foreach ($notams as $notam) {
      $tokens = tokenize_notam($notam);
      foreach ($tokens as $token) {
        print $token->type->name . ':' . substr($notam, $token->start,
            $token->end - $token->start) . "\n";
      }
    }
    break;
  case "cli-server":
    foreach ($notams as $notam) {
      $tokens = tokenize_notam($notam);
      print render_tokens_to_html($tokens, $notam);
    }
    break;
}

print "\n\n";


/**
 * @param  string  $notam_str
 *
 * @return \LFour\notam\TOKEN[]
 */
function tokenize_notam(string $notam_str): array {
  $char = 0;
  /** @var TOKEN[] $tokens */
  $tokens = [];

  white_space($char, $notam_str, $tokens);

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
    white_space($char, $notam_str, $tokens);

    if ($notam_str[$char + 1] === ")") {
      if ($notam_str[$char] === 'Q') {

        /*
        * Q) YMMM/QPAXX/I/NBO/A/000/999/3740S14451E005
        **/
        read_till_next_section($char, $notam_str, $tokens);


        $tokens[] = new TOKEN(NotamToken::Q, $char, $char + 2);
        $char = $char + 2;

        white_space($char, $notam_str, $tokens);

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

        white_space($char, $notam_str, $tokens);
      }
      else if ($notam_str[$char] === 'A') {
        read_till_next_section($char, $notam_str, $tokens);


        /*
         * A) YMML
         **/
        $tokens[] = new TOKEN(NotamToken::A, $char, $char + 2);
        $char = $char + 2;

        white_space($char, $notam_str, $tokens);

        $tokens[] = new TOKEN(NotamToken::A_LOCATION, $char,
          $char + 4); // ICAO code
        $char = $char + 4;

        white_space($char, $notam_str, $tokens);
      }
      else if ($notam_str[$char] === 'B') {
        read_till_next_section($char, $notam_str, $tokens);

        /*
         * B) 2203090629
         **/
        $tokens[] = new TOKEN(NotamToken::B, $char, $char + 2);
        $char = $char + 2;

        white_space($char, $notam_str, $tokens);

        tokenize_date($char, $tokens);

        white_space($char, $notam_str, $tokens);
      }
      else if ($notam_str[$char] === 'C') {
        read_till_next_section($char, $notam_str, $tokens);

        /*
         * C) PERM
         * C) 2208040600
         **/

        $tokens[] = new TOKEN(NotamToken::C, $char, $char + 2);
        $char = $char + 2;

        white_space($char, $notam_str, $tokens);

        if (substr($notam_str, $char, 4) === 'PERM') {
          $tokens[] = new TOKEN(NotamToken::C_PERMANENT, $char, $char + 4);
          $char = $char + 4;
        }
        else {
          tokenize_date($char, $tokens);

          white_space($char, $notam_str, $tokens);

          if (substr($notam_str, $char, 3) === 'EST') {
            $tokens[] = new TOKEN(NotamToken::C_ESTIMATE, $char, $char + 3);
            $char = $char + 3;
          }
        }

        white_space($char, $notam_str, $tokens);
      }
      else if ($notam_str[$char] === 'D') {
        read_till_next_section($char, $notam_str, $tokens);

        /*
         * D) MON WED THU 0900-1300, TUE FRI SAT 0900-2000
         **/

        $tokens[] = new TOKEN(NotamToken::D, $char, $char + 2);
        $char = $char + 2;

        white_space($char, $notam_str, $tokens);

        $token = new TOKEN(NotamToken::D_SCHEDULE, $char);

        // skip until new line
        while ($notam_str[$char + 1] !== ')') {
          $char++;
        }
        if ($notam_str[$char] === "\n") {
          $char--;
        }
        $token->end = $char;

        $tokens[] = $token;

        white_space($char, $notam_str, $tokens);
      }
      else if ($notam_str[$char] === 'E') {
        read_till_next_section($char, $notam_str, $tokens);

        /*
         * E) AIP DEP AND APCH(DAP) EAST YMML AMD
         * AMD EXISTING LIGHTING CAUTION NOTE ON THE FLW STAR CHARTS TO READ:
         * RWY 34 IS INDICATED BY RUNWAY THRESHOLD IDENTIFICATION LIGHTS,
         * ESSENDON AIRPORT 5NM SE OF ML.
         **/

        $tokens[] = new TOKEN(NotamToken::E, $char, $char + 2);
        $char = $char + 2;

        white_space($char, $notam_str, $tokens);

        $token = new TOKEN(NotamToken::E_TEXT, $char);

        // skip until new line
        while ($char <= strlen($notam_str)) {
          switch (substr($notam_str, $char + 1, 2)) {
            case "F)":
              break 2;
            case "G)":
              break 2;
            default:
              $char++;
              break;
          }
        }

        $token->end = $char;
        $tokens[] = $token;

        white_space($char, $notam_str, $tokens);

        if ($char >= strlen($notam_str)) {
          break;
        }
      }
      else if ($notam_str[$char] === 'F') {
        read_till_next_section($char, $notam_str, $tokens);

        /*
         * F) 1500FT AGL
         **/

        $tokens[] = new TOKEN(NotamToken::F, $char, $char + 2);
        $char = $char + 2;

        //@todo parse this

        $token = new TOKEN(NotamToken::F_LEVEL, $char);

        // skip until new line
        while (!in_array($notam_str[$char], ["\n", "", FALSE])) {
          $char++;
        }
        $token->end = $char;

        $tokens[] = $token;

        white_space($char, $notam_str, $tokens);
      }
      else if ($notam_str[$char] === 'G') {
        read_till_next_section($char, $notam_str, $tokens);

        /*
         * G) 17999FT AMSL
         **/

        $tokens[] = new TOKEN(NotamToken::G, $char, $char + 2);
        $char = $char + 2;

        //@todo parse this

        $token = new TOKEN(NotamToken::G_LEVEL, $char);

        // skip until new line
        while (!in_array($notam_str[$char], ["\n", "", FALSE])) {
          $char++;
        }
        $token->end = $char;

        $tokens[] = $token;

        white_space($char, $notam_str, $tokens);
      }
      else {
        if ($char >= strlen($notam_str)) {
          break;
        }
        read_till_next_section($char, $notam_str, $tokens);
        break;
      }
    }
    else {
      read_till_next_section($char, $notam_str, $tokens);
    }
  }

  return $tokens;
}
/**
 * @param  int  $char
 * @param  array  $tokens
 */
function tokenize_date(int &$char, array &$tokens) {
  $tokens[] = new TOKEN(NotamToken::YEAR, $char, $char + 2);
  $char = $char + 2;

  $tokens[] = new TOKEN(NotamToken::MONTH, $char, $char + 2);
  $char = $char + 2;

  $tokens[] = new TOKEN(NotamToken::DAY, $char, $char + 2);
  $char = $char + 2;

  $tokens[] = new TOKEN(NotamToken::HOUR, $char, $char + 2);;
  $char = $char + 2;

  $tokens[] = new TOKEN(NotamToken::MINUTE, $char, $char + 2);
  $char = $char + 2;
}

/**
 * @param  int  $char
 * @param  string  $notam_str
 * @param  array  $tokens
 */
function white_space(int &$char, string &$notam_str, array &$tokens) {
  $token = new TOKEN(NotamToken::WHITE_SPACE);
  $token->start = $char;
  // skip until new line
  while (in_array(substr($notam_str, $char, 1), ["\n", " "])) {
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
 * @param  array  $tokens
 */
function read_till_next_section(int &$char, string &$notam_str, array &$tokens) {
  $token = new TOKEN(NotamToken::UNKNOWN);
  $token->start = $char;
  // skip until new line
  while ($notam_str[$char + 1] !== ')') {
    $char++;
  }
  $token->end = $char;
  if ($token->start != $token->end) {
    $tokens[] = $token;
  }
}

/**
 * @param \LFour\notam\TOKEN[]  $tokens
 * @param string $notam_str
 *
 * @return string
 */
function render_tokens_to_html(array $tokens, string $notam_str) {
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

function parse_date(NOTAM &$notam, string &$notam_str, int &$char) {
  $year = substr($notam_str, $char, 2);
  $char = $char + 2;

  $month = substr($notam_str, $char, 2);
  $char = $char + 2;

  $day = substr($notam_str, $char, 2);
  $char = $char + 2;

  $hour = substr($notam_str, $char, 2);
  $char = $char + 2;

  $minute = substr($notam_str, $char, 2);
  $char = $char + 2;
  $date = new DateTime('now', new DateTimeZone('UTC'));
  $date->setDate((int) $notam->century . $year, (int) $month, (int) $day);
  $date->setTime((int) $hour, (int) $minute, 00);
  return $date;
}

function parse_c(NOTAM &$notam, string &$notam_str, int &$char) {
  // skip white space
  while (substr($notam_str, $char, 1) === " ") {
    $char++;
  }

  if (substr($notam_str, $char, 4) === 'PERM') {
    $char = $char + 4;
    $notam->C->permanent = TRUE;
  }
  else {
    $notam->C->dateTime = parse_date($notam, $notam_str, $char);
    while (substr($notam_str, $char, 1) === " ") {
      $char++;
    }
    if (substr($notam_str, $char, 3) === 'EST') {
      $notam->C->estimate = TRUE;
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
   * The radius is a three-digit distance representing the radius of
   * influence in whole nautical miles. A radius that includes a decimal will
   * be rounded to the next higher whole nautical mile. The radius
   * impacts the pilot briefing coverage and number of NOTAMs received
   * in a NOTAM query, so it must be as precise as possible.
   *
   * example 4159N08754W005
   *
   * @var string coordinates / Radius
   */
  public string $coordinates_radius = '';
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
  public DateTime $dateTime;
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
  public ?DateTime $dateTime = NULL;
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
class NOTAM_SCOPE {

  /**
   * Aerodrome
   * @var string
   */
  public string $A = 'A';

  /**
   * Enroute
   * @var string
   */
  public string $E = 'E';

  /**
   * Navigation warning
   * @var string
   */
  public string $W = 'W';

  /**
   * Checklist
   * @var string
   */
  public string $K = 'K';
  public array $cases = [
    'A',
    'E',
    'W',
    'K',
  ];
}

/**
 * The Purpose qualifier relates a NOTAM to
 * certain purposes (intentions) and thus allows
 * retrieval according to the user’s requirements.
 */
class NOTAM_PURPOSE {

  /**
   * NOTAM selected for the immediate attention of aircraft operators
   * @var string
   */
  public string $N = 'N';

  /**
   * NOTAM selected for pre-flight information briefing
   * @var string
   */
  public string $B = 'B';

  /**
   * NOTAM concerning flight operations
   * @var string
   */
  public string $O = 'O';

  /**
   * Miscellaneous NOTAM; not subject for briefing, but is available on request
   * @var string
   */
  public string $M = 'M';

  /**
   * NOTAM is a Checklist
   * @var string
   */
  public string $K = 'K';

  public array $cases = [
    'N',
    'B',
    'O',
    'M',
    'K',
  ];
}

class NOTAM_TRAFFIC {

  /**
   * Instrument Flight Rules (IFR)
   * @var string
   */
  public string $I = 'I';

  /**
   * Visual Flight Rules (VFR)
   * @var string
   */
  public string $V = 'V';

  /**
   * NOTAM is a Checklist
   * @var string
   */
  public string $K = 'K';

  public array $cases = [
    'I',
    'V',
    'K'
  ];
}

class NOTAM_SERIES {
  /**
   * Aerodrome Movement Areas
   */
  public string $B = 'B';

  /**
   * Published Services
   */
  public string $C = 'C';

  /**
   * Special Activity Airspace
   */
  public string $D = 'D';

  /**
   * Airspace Events and Activities
   */
  public string $E = 'E';

  /**
   * Airways and Air Traffic Routes
   */
  public string $G = 'G';

  /**
   * Regulatory NOTAMs
   */
  public string $H = 'H';

  /**
   * Apron/Ramp and Facilities APN
   */
  public string $I = 'I';

  /**
   * Obstructions
   */
  public string $J = 'J';

  /**
   * FCC Obstructions ASR assigned
   */
  public string $K = 'K';

  /**
   * Ground-Based Navigational Aids
   */
  public string $N = 'N';

  /**
   * Field Condition NOTAM
   */
  public string $R = 'R';

  /**
   * Published Instrument Procedures
   */
  public string $V = 'V';

  /**
   * Satellite Based Information
   */
  public string $Z = 'Z';

  public array $cases = [
    'B',
    'C',
    'D',
    'E',
    'G',
    'H',
    'I',
    'J',
    'K',
    'N',
    'R',
    'V',
    'Z',
  ];
}




function parse_notam(string $notam_str) {
  $notam_str = trim($notam_str, "\n\r");
  $notam = notam_factory("20");
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
  $notam->ident->series = substr( $notam_str, 0, 1);
  $notam->ident->number = substr( $notam_str, 1, 4);
  $notam->ident->year = substr( $notam_str, 6, 2);
  $notam->ident->type = substr( $notam_str, 9, 6);
  /**
   * N - new
   * R - Replace
   * C - Cancel
   * @var string $type_code
   */
  $type_code = substr( $notam_str, 14, 1);
  if ($type_code === "R" || $type_code === "C") {
    $notam->ident->ref_series = substr( $notam_str, 16, 1);
    $notam->ident->ref_number = substr( $notam_str, 17, 4);
    $notam->ident->ref_year = substr( $notam_str, 22, 2);
  }

  $notam_str_len = strlen($notam_str);
  $start_of_line_2 = strpos($notam_str, "\n") + 1;
  $char = $start_of_line_2;

  while ($char < $notam_str_len) {
    if (substr($notam_str, $char, 2) === "Q)") {
      /*
       * Q) YMMM/QPAXX/I/NBO/A/000/999/3740S14451E005
       **/
      // skip past "Q)"
      $char = $char + 2;
      // skip white space
      while (substr($notam_str, $char, 1) === " ") {
        $char++;
      }
      $notam->Q->fir = substr($notam_str, $char, 4);
      $char = $char + 5; // FIR and slash

      $notam->Q->notam_code = substr($notam_str, $char, 5);
      $char = $char + 6; // notam_code and slash

      while (substr($notam_str, $char, 1) !== "/") {
        $notam->Q->traffic .= substr($notam_str, $char, 1);
        $char++;
      }
      $char++; // slash

      while (substr($notam_str, $char, 1) !== "/") {
        $notam->Q->purpose .= substr($notam_str, $char, 1);
        $char++;
      }
      $char++; // slash

      while (substr($notam_str, $char, 1) !== "/") {
        $notam->Q->scope .= substr($notam_str, $char, 1);
        $char++;
      }
      $char++; // slash

      $notam->Q->lower = substr($notam_str, $char, 3);
      $char = $char + 4; // lower and slash

      $notam->Q->upper = substr($notam_str, $char, 3);
      $char = $char + 4; // lower and slash

      $notam->Q->coordinates_radius = substr($notam_str, $char, 14);
      $char = $char + 14; // coordinates, radius.
      // skip until new line
      while (!in_array(substr($notam_str, $char, 1), ["\n", "", FALSE])) {
        $char++;
      }
      $char++;
    }
    else if (substr($notam_str, $char, 2) === "A)") {
      /*
       * A) YMML
       **/
      // skip past "A)"
      $char = $char + 2;
      // skip white space
      while (substr($notam_str, $char, 1) === " ") {
        $char++;
      }
      /**
       * @todo non-icao codes?
       **/
      $notam->A->location = substr($notam_str, $char, 4);
      $char = $char + 4; // ICAO code
      // skip until new line
      while (!in_array(substr($notam_str, $char, 1), ["\n", "", FALSE])) {
        $char++;
      }
      $char++;
    }
    else if (substr($notam_str, $char, 2) === "B)") {
      /*
       * B) 2203090629
       **/
      // skip past "B)"
      $char = $char + 2;
      // skip white space
      while (substr($notam_str, $char, 1) === " ") {
        $char++;
      }

      $notam->B->dateTime = parse_date($notam, $notam_str, $char);

      // skip white space
      while (substr($notam_str, $char, 1) === " ") {
        $char++;
      }
      if (substr($notam_str, $char, 2) === "C)") {
        /*
         *               |
         * B) 2203090629 C) PERM
         * B) 2203090629 C) 2208040600
         **/
        // skip past "C)"
        $char = $char + 2;
        parse_c($notam, $notam_str, $char);
      }
      // skip until new line
      while (!in_array(substr($notam_str, $char, 1), ["\n", "", FALSE])) {
        $char++;
      }
      $char++;
    }
    else if (substr($notam_str, $char, 2) === "C)") {
      /*
       * C) PERM
       * C) 2208040600
       **/
      // skip past "C)"
      $char = $char + 2;

      parse_c($notam, $notam_str, $char);

      // skip until new line
      while (!in_array(substr($notam_str, $char, 1), ["\n", "", FALSE])) {
        $char++;
      }
      $char++;
    }
    else if (substr($notam_str, $char, 2) === "D)") {
      /*
       * D) MON WED THU 0900-1300, TUE FRI SAT 0900-2000
       **/
      // skip past "D)"
      $char = $char + 2;

      // skip white space
      while (substr($notam_str, $char, 1) === " ") {
        $char++;
      }

      // skip until new line
      while (!in_array(substr($notam_str, $char, 1), ["\n", "", FALSE])) {
        /**
         * @todo parse schedule.
         */
        $notam->D->schedule .= substr($notam_str, $char, 1);
        $char++;
      }
      $char++;
    }
    else if (substr($notam_str, $char, 2) === "E)") {
      /*
       * E) AIP DEP AND APCH(DAP) EAST YMML AMD
       * AMD EXISTING LIGHTING CAUTION NOTE ON THE FLW STAR CHARTS TO READ:
       * RWY 34 IS INDICATED BY RUNWAY THRESHOLD IDENTIFICATION LIGHTS,
       * ESSENDON AIRPORT 5NM SE OF ML.
       **/
      // skip past "D)"
      $char = $char + 2;

      // skip white space
      while (substr($notam_str, $char, 1) === " ") {
        $char++;
      }

      // skip until new line
      while (substr($notam_str, $char, 1) !== "") {
        if (substr($notam_str, $char, 1) === "\n") {
          switch (substr($notam_str, $char + 1, 2)) {
            case "F)":
              break 2;
            case "G)":
              break 2;
          }
        }
        /**
         * @todo parse schedule.
         */
        $notam->E->body .= substr($notam_str, $char, 1);
        $char++;
      }
      // skip until new line
      while (!in_array(substr($notam_str, $char, 1), ["\n", "", FALSE])) {
        $char++;
      }
      $char++;
    }
    else if (substr($notam_str, $char, 2) === "F)") {
      /*
       * F) 1500FT AGL
       **/
      // skip past "F)"
      $char = $char + 2;

      //@todo parse this

      // skip until new line
      while (!in_array(substr($notam_str, $char, 1), ["\n", "", FALSE])) {
        $char++;
      }
      $char++;
    }
    else if (substr($notam_str, $char, 2) === "G)") {
      /*
       * G) 17999FT AMSL
       **/
      // skip past "G)"
      $char = $char + 2;

      //@todo parse this

      // skip until new line
      while (!in_array(substr($notam_str, $char, 1), ["\n", "", FALSE])) {
        $char++;
      }
      $char++;
    }
    else {
      // skip until new line
      while (!in_array(substr($notam_str, $char, 1), ["\n", "", FALSE])) {
        $char++;
      }
      $char++;
    }
  }

  return $notam;
}
