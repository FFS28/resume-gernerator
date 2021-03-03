<?php

namespace App\Helper;

abstract class StringHelper
{
    /**
     * Crée un slug
     * @param string $text
     * @return string
     */
    public static function slugify(string $text): string
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // trim
        $text = trim($text, '-');
        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        // lowercase
        $text = strtolower($text);

        return $text;
    }

    /**
     * Supprime tout les caractères spéciaux
     * @param string $str
     * @return string
     */
    public static function clean(string $str): string
    {
        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('|[^\w\s/-_\.\+\*\?%!#@$\(\),;"\':]|u', '', $str);

        return $str;
    }

    /**
     * Supprime tout les espaces
     * @param string $str
     * @return string
     */
    public static function removeSpaces(string $str): string
    {
        return preg_replace('#\s#', '', $str);
    }

    /**
     * Supprime tout les accents
     * @param $stripAccents
     * @return string
     */
    public static function stripAccents($stripAccents)
    {
        return strtr(
            utf8_decode($stripAccents),
            utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'),
            'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'
        );
    }

    /**
     * Encode pour les PDF
     * @param string|null $string
     * @return string
     */
    public static function encode(?string $string): string
    {
        if (!$string) return '';

        $fromEncoding = mb_detect_encoding($string);
        $toEncoding = 'UTF-8////IGNORE';

        return $convertedString = @mb_convert_encoding($string, $toEncoding, $fromEncoding) ?:
            @iconv($fromEncoding, $toEncoding, $string);
    }

    public static function extractAmount($string)
    {
        $amount = str_replace(',', '.', str_replace('.', '', $string));
        return floatval($amount);
    }

    public static function contains($string, Array $search, $caseInsensitive = false) {
        $exp = '#'
            . implode('|', $search)
            . ($caseInsensitive ? '#i' : '#');
        return preg_match($exp, $string) ? true : false;
    }
}
