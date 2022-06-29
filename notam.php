<?php

$contents = file_get_contents('notams.txt');
$notams = explode('============================================', $contents);

foreach ($notams as $notam) {
  $notam_obj = parse_notam($notam);
  print_r($notam_obj);
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

