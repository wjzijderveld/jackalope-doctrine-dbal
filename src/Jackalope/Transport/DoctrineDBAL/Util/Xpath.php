<?php

namespace Jackalope\Transport\DoctrineDBAL\Util;

/**
 * Xpath utilities
 *
 */
class Xpath 
{

    /**
     * Escapes a string to be used in an xpath query
     * There is a lot of double escaping here because we use single
     * quote in the EXTRACTVALUE functions
     *
     *
     * @param $query
     * @param string $delimiter
     * @return string
     */
    public static function escape($query, $delimiter = '"', $doubleEscape = null)
    {
        // Escape backslahes that aren't escape characters for quotes
        $query = preg_replace('/\\\\([^"|\\\'])/', '\\\\\\\\\1', $query);

        if ((strpos($query, '\'') !== false) ||
            (strpos($query, '"') !== false))
        {
            $quotechars = array('\'','"');
            $parts = array();
            $current = '';

            foreach (str_split($query) as $character) {

                if (in_array($character, $quotechars)) {
                    if ($current && '\\' !== substr($current, -1)) {
                        $parts[] = $delimiter . $current . $delimiter;
                    }

                    if ($character == '\'') {
                        $parts[] = '"\\' . $character . '"';
                    } else {
                        $parts[] = '\\\'' . $character . '\\\'';
                    }

                    $current = '';
                } else {
                    $current .= $character;
                }

            }

            if ($current) {
                $parts[] =  $delimiter . $current . $delimiter;
            }


            if (count($parts) > 2) {
                $part1 = array_shift($parts);
                $ret = 'concat(' . $part1 . ', ' . self::concatBy2($parts) . ')';
            } else {
                $ret = 'concat(' . join(', ', $parts) . ')';
            }
        } else {
            $ret = $delimiter . $query . $delimiter;
        }

        return $ret;
    }

    /**
     * Because not all concat() implementations support more then 2 arguments,
     * we need this recursive function
     *
     * @param array $parts
     * @return string
     */
    public static function concatBy2(array $parts)
    {
        if (2 === count($parts)) {
            return 'concat(' . join(', ', $parts) . ')';
        }

        $part1 = array_shift($parts);

        $return = 'concat(' . $part1 . ', ';
        foreach (array_chunk($parts, 2) as $twoParts) {
            if (1 === count($twoParts)) {
                $return .= $twoParts[0];
            } else {
                $return .= self::concatBy2($twoParts);
            }
        }

        return $return . ')';
    }

}