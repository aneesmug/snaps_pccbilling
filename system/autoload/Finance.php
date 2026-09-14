<?php
class Finance
{
    public static function convert_number_to_words($number)
    {
        $hyphen = '-';
        $conjunction = ' and ';
        $separator = ', ';
        $negative = 'negative ';
        $decimal = ' point ';
        $dictionary = [
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'fourty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
            100 => 'hundred',
            1000 => 'thousand',
            1000000 => 'million',
            1000000000 => 'billion',
            1000000000000 => 'trillion',
            1000000000000000 => 'quadrillion',
            1000000000000000000 => 'quintillion',
        ];

        if (!is_numeric($number)) {
            return false;
        }

        if (
            ($number >= 0 && (int) $number < 0) ||
            (int) $number < 0 - PHP_INT_MAX
        ) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' .
                    PHP_INT_MAX .
                    ' and ' .
                    PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . Finance::convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int) ($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units !== 0) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder !== 0) {
                    $string .=
                        $conjunction .
                        Finance::convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string =
                    Finance::convert_number_to_words($numBaseUnits) .
                    ' ' .
                    $dictionary[$baseUnit];
                if ($remainder !== 0) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= Finance::convert_number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = [];
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }

    public static function amount_to_words_bilingual($amount)
    {
        $value = round((float) $amount, 2);
        $is_negative = $value < 0;
        $value = abs($value);

        $whole = (int) floor($value);
        $fraction = (int) round(($value - $whole) * 100);

        if ($fraction === 100) {
            $whole += 1;
            $fraction = 0;
        }

        $en_whole = Finance::convert_number_to_words((string) $whole);
        $en = trim(($is_negative ? 'negative ' : '') . $en_whole . ' riyals');
        if ($fraction > 0) {
            $en .= ' and ' . Finance::convert_number_to_words((string) $fraction) . ' halalas';
        }

        $ar_whole = self::convert_number_to_words_arabic($whole);
        $ar = trim(($is_negative ? 'سالب ' : '') . $ar_whole . ' ريال');
        if ($fraction > 0) {
            $ar .= ' و ' . self::convert_number_to_words_arabic($fraction) . ' هللة';
        }

        return [
            'en' => $en,
            'ar' => $ar,
        ];
    }

    protected static function convert_number_to_words_arabic($number)
    {
        $number = (int) $number;

        if ($number === 0) {
            return 'صفر';
        }

        if ($number < 0) {
            return 'سالب ' . self::convert_number_to_words_arabic(abs($number));
        }

        $ones = [
            0 => 'صفر',
            1 => 'واحد',
            2 => 'اثنان',
            3 => 'ثلاثة',
            4 => 'أربعة',
            5 => 'خمسة',
            6 => 'ستة',
            7 => 'سبعة',
            8 => 'ثمانية',
            9 => 'تسعة',
            10 => 'عشرة',
            11 => 'أحد عشر',
            12 => 'اثنا عشر',
            13 => 'ثلاثة عشر',
            14 => 'أربعة عشر',
            15 => 'خمسة عشر',
            16 => 'ستة عشر',
            17 => 'سبعة عشر',
            18 => 'ثمانية عشر',
            19 => 'تسعة عشر',
        ];

        $tens = [
            20 => 'عشرون',
            30 => 'ثلاثون',
            40 => 'أربعون',
            50 => 'خمسون',
            60 => 'ستون',
            70 => 'سبعون',
            80 => 'ثمانون',
            90 => 'تسعون',
        ];

        $hundreds = [
            100 => 'مائة',
            200 => 'مائتان',
            300 => 'ثلاثمائة',
            400 => 'أربعمائة',
            500 => 'خمسمائة',
            600 => 'ستمائة',
            700 => 'سبعمائة',
            800 => 'ثمانمائة',
            900 => 'تسعمائة',
        ];

        if ($number < 20) {
            return $ones[$number];
        }

        if ($number < 100) {
            $ten_value = ((int) ($number / 10)) * 10;
            $unit = $number % 10;
            if ($unit === 0) {
                return $tens[$ten_value];
            }

            return $ones[$unit] . ' و ' . $tens[$ten_value];
        }

        if ($number < 1000) {
            $hundred_value = ((int) ($number / 100)) * 100;
            $remainder = $number % 100;
            if ($remainder === 0) {
                return $hundreds[$hundred_value];
            }

            return $hundreds[$hundred_value] . ' و ' . self::convert_number_to_words_arabic($remainder);
        }

        $scales = [
            1000000000 => 'مليار',
            1000000 => 'مليون',
            1000 => 'ألف',
        ];

        foreach ($scales as $scale_value => $scale_name) {
            if ($number >= $scale_value) {
                $base = (int) floor($number / $scale_value);
                $remainder = $number % $scale_value;

                $text = self::convert_number_to_words_arabic($base) . ' ' . $scale_name;
                if ($remainder > 0) {
                    $text .= ' و ' . self::convert_number_to_words_arabic($remainder);
                }

                return $text;
            }
        }

        return (string) $number;
    }

    public static function amount_fix($amount, $currency_symbol = false)
    {
        global $config;

        if (!$currency_symbol) {
            $currency_symbol = $config['currency_code'];
        }

        $amount = (string) $amount;

        // Normalize arabic-Indic digits to ASCII digits.
        $amount = strtr($amount, [
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
        ]);

        $amount = str_replace($currency_symbol, '', $amount);
        $amount = str_replace(trim((string) $currency_symbol), '', $amount);
        if (isset($config['currency_code'])) {
            $amount = str_replace((string) $config['currency_code'], '', $amount);
        }
        $amount = preg_replace('/[\x{00A0}\s]+/u', '', $amount);

        $isNegative = strpos($amount, '-') !== false;

        // Keep only numeric chars and common separators.
        $num = preg_replace('/[^0-9,\.\-]/u', '', $amount);
        $num = str_replace('-', '', $num);

        if ($num === '' || $num === null) {
            return 0.0;
        }

        if ($config['currency_decimal_digits'] == 'false') {
            $digits = preg_replace('/[^0-9]/', '', $num);
            if ($digits === '' || $digits === null) {
                return 0.0;
            }
            $value = (float) $digits;
            return $isNegative ? (0 - $value) : $value;
        }

        $decPoint = isset($config['dec_point']) ? (string) $config['dec_point'] : '.';
        $thousandsSep = isset($config['thousands_sep']) ? (string) $config['thousands_sep'] : ',';

        // Respect configured localization first.
        if ($thousandsSep !== '' && $thousandsSep !== $decPoint) {
            $num = str_replace($thousandsSep, '', $num);
        }
        if ($decPoint !== '.') {
            $num = str_replace($decPoint, '.', $num);
        }

        // Remove separator chars that are not between digits.
        $num = preg_replace('/(?<!\d)[\.,]|[\.,](?!\d)/', '', $num);

        // Fallback for mixed user input: keep right-most separator as decimal mark.
        if (strpos($num, ',') !== false && strpos($num, '.') !== false) {
            $lastDot = strrpos($num, '.');
            $lastComma = strrpos($num, ',');
            $sepPos = ($lastDot > $lastComma) ? $lastDot : $lastComma;
            $intPart = preg_replace('/[^0-9]/', '', substr($num, 0, $sepPos));
            $decPart = preg_replace('/[^0-9]/', '', substr($num, $sepPos + 1));
            $num = $intPart . ($decPart !== '' ? '.' . $decPart : '');
        } elseif (strpos($num, ',') !== false) {
            $commaCount = substr_count($num, ',');
            $lastComma = strrpos($num, ',');
            $digitsAfter = strlen($num) - $lastComma - 1;

            // Single comma with exactly 3 digits after is likely thousands separator.
            if ($commaCount > 1 || $digitsAfter === 3) {
                $num = str_replace(',', '', $num);
            } else {
                $num = str_replace(',', '.', $num);
            }
        }

        // If multiple dots exist, keep only the last one as decimal point.
        if (substr_count($num, '.') > 1) {
            $lastDot = strrpos($num, '.');
            $intPart = preg_replace('/[^0-9]/', '', substr($num, 0, $lastDot));
            $decPart = preg_replace('/[^0-9]/', '', substr($num, $lastDot + 1));
            $num = $intPart . ($decPart !== '' ? '.' . $decPart : '');
        }

        $num = preg_replace('/[^0-9\.]/', '', $num);

        if ($num === '' || $num === null) {
            return 0.0;
        }

        $value = (float) $num;
        return $isNegative ? (0 - $value) : $value;
    }
}
