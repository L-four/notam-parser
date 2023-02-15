<?php
namespace LFour\notam;

require 'notam.php';


class Docs {

  static function render_markdown_token_table() {
    $tokens = NotamToken::cases();
    $table = "| Token | Meaning |\n" .
      "|-------|---------|\n";
    foreach ($tokens as $token) {
      $name = $token->name;
      $desc = self::get_desc($token);
      $table .= "| $name | $desc |\n";
    }

    return $table;
  }

  static function get_desc($token) {
    switch ($token) {
      case NotamToken::WHITE_SPACE:
        return "Whitespace";
        break;
      case NotamToken::SLASH:
        return "Slash";
        break;
      case NotamToken::IDENT_SERIES:
        return "Ident series";
        break;
      case NotamToken::IDENT_NUMBER:
        return "Ident number";
        break;
      case NotamToken::IDENT_YEAR:
        return "Ident year";
        break;
      case NotamToken::IDENT_TYPE:
        return "Ident type";
        break;
      case NotamToken::IDENT_REF_YEAR:
        return "Ident ref year";
        break;
      case NotamToken::IDENT_REF_NUMBER:
        return "Ident ref number";
        break;
      case  NotamToken::IDENT_REF_SERIES:
        return "Ident ref series";
        break;
      case NotamToken::UNKNOWN:
        return "Unknown";
        break;
      case NotamToken::Q:
        return "Qualifier line";
        break;
      case NotamToken::Q_FIR:
        return "Qualifier Flight Information Region";
        break;
      case NotamToken::Q_NOTAM_CODE:
        return "Qualifier NOTAM code";
        break;
      case NotamToken::Q_TRAFFIC:
        return "Qualifier traffic";
        break;
      case NotamToken::Q_PURPOSE:
        return "Qualifier purpose";
        break;
      case NotamToken::Q_SCOPE:
        return "Qualifier scope";
      case NotamToken::Q_LOWER:
        return "Qualifier lower flight level";
        break;
      case NotamToken::Q_UPPER:
        return "Qualifier upper flight level";
      case NotamToken::Q_COORDINATES:
        return "Qualifier coordinates";
        break;
      case NotamToken::Q_RADIUS:
        return "Qualifier coordinates radius";
        break;
      case NotamToken::A:
        return "ICAO line";
        break;
      case NotamToken::A_LOCATION:
        return "ICAO location indicator of the aerodrome or FIR";
        break;
      case NotamToken::A_LOCATION_2:
        return "ICAO location indicator of the aerodrome or FIR";
        break;
      case NotamToken::A_EXTRA:
        return "ICAO extra";
        break;
      case NotamToken::B:
        return "Effective period line";
        break;
      case NotamToken::B_DATE_TIME:
        return "Effective period date time";
        break;
      case NotamToken::C:
        return "End effective period line";
        break;
      case NotamToken::C_PERMANENT:
        return "is permanent";
        break;
      case NotamToken::C_ESTIMATE:
        return "is estimate";
        break;
      case NotamToken::C_DATE_TIME:
        return "Cancellation date time";
        break;
      case NotamToken::D:
        return "Schedule line";
        break;
      case NotamToken::D_SCHEDULE:
        return "Schedule";
        break;
      case NotamToken::E:
        return "Main body line";
        break;
      case NotamToken::E_TEXT:
        return "Main body text";
        break;
      case NotamToken::F:
        return "Lower level line.";
        break;
      case NotamToken::F_LEVEL:
        return "Lower level";
        break;
      case NotamToken::F_SFC:
        return "Lower level is Surface";
        break;
      case NotamToken::G:
        return "Upper level line";
        break;
      case NotamToken::G_LEVEL:
        return "Upper level";
        break;
      case NotamToken::G_UNL:
        return "Upper level is Unlimited";
        break;
    }
    return FALSE;
  }
}

print Docs::render_markdown_token_table();