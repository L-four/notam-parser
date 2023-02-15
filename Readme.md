# Notam Parser

## Demo
https://notam-reader.kyahrindlisbacher.com/

## Install

```shell
composer require l-four/notam
```
## Usage

First we tokenize the notam string into an array of tokens.
Then it's possible to parse the tokens into a Notam object.

This is the basic usage:
```php
require  __DIR__ .  '\vendor\autoload.php';

$notam_string = 'J2187/22 NOTAMN' .
                'Q) YBBB/QMXLC/IV/M/A/000/999/2738S15243E005' . 
                'A) YAMB' .
                'B) 2207062200 C) 2207070400' .
                'E) TWY Z NOT AVBL DUE PARKED ACFT';

$tokens = \LFour\notam\NotamFunctions::tokenize_notam($notam_string);
print_r($tokens);
```
This will output an array of tokens:
```php
Array
(
    [0] => LFour\notam\TOKEN Object
        (
            [type] => LFour\notam\NotamToken Enum
                (
                    [name] => IDENT_SERIES
                )

            [start] => 0
            [end] => 1
        )
        ... etc
)
```
Each token represents a part of the notam string. 

The type enum is used to identify the token type.

The value of a token can be retrieved by using the `substr()` function.

```php
  $token = $tokens[0];
  $value = substr($notam_string, $token->start, $token->end - $token->start);
  print_r($value);
```
Output: `J`

For more ergonomic usage. The tokens can be parsed into a Notam object.
```php
$notam = \LFour\notam\NotamFunctions::object_from_tokens($tokens, $notam_string);
print_r($notam->ident->series);
```
Output: `J`

# Token Type Reference Table
| Token            | Meaning                                         |
|------------------|-------------------------------------------------|
| WHITE_SPACE      | Whitespace                                      |
| SLASH            | Slash                                           |
| IDENT_SERIES     | Ident series                                    |
| IDENT_NUMBER     | Ident number                                    |
| IDENT_YEAR       | Ident year                                      |
| IDENT_TYPE       | Ident type                                      |
| IDENT_REF_YEAR   | Ident ref year                                  |
| IDENT_REF_NUMBER | Ident ref number                                |
| IDENT_REF_SERIES | Ident ref series                                |
| UNKNOWN          | Unknown                                         |
| Q                | Qualifier line                                  |
| Q_FIR            | Qualifier Flight Information Region             |
| Q_NOTAM_CODE     | Qualifier NOTAM code                            |
| Q_TRAFFIC        | Qualifier traffic                               |
| Q_PURPOSE        | Qualifier purpose                               |
| Q_SCOPE          | Qualifier scope                                 |
| Q_LOWER          | Qualifier lower flight level                    |
| Q_UPPER          | Qualifier upper flight level                    |
| Q_COORDINATES    | Qualifier coordinates                           |
| Q_RADIUS         | Qualifier coordinates radius                    |
| A                | ICAO line                                       |
| A_LOCATION       | ICAO location indicator of the aerodrome or FIR |
| A_LOCATION_2     | ICAO location indicator of the aerodrome or FIR |
| A_EXTRA          | ICAO extra                                      |
| B                | Effective period line                           |
| B_DATE_TIME      | Effective period date time                      |
| C                | End effective period line                       |
| C_PERMANENT      | is permanent                                    |
| C_ESTIMATE       | is estimate                                     |
| C_DATE_TIME      | Cancellation date time                          |
| D                | Schedule line                                   |
| D_SCHEDULE       | Schedule                                        |
| E                | Main body line                                  |
| E_TEXT           | Main body text                                  |
| F                | Lower level line.                               |
| F_LEVEL          | Lower level                                     |
| F_SFC            | Lower level is Surface                          |
| G                | Upper level line                                |
| G_LEVEL          | Upper level                                     |
| G_UNL            | Upper level is Unlimited                        |


## Full Example
```php
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
```