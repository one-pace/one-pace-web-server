<?php
class BencodeModel
{
    function decode_file($filename){
        $string = file_get_contents($filename, FILE_BINARY);
        return $this->decode($string);
    }

    function decode($s, &$pos=0)
    {
        if($pos>=strlen($s)) return null;

        switch($s[$pos]){
            case 'd':
                $pos++;
                $retval = array();
                while ($s[$pos] != 'e'){
                    $key = $this->decode($s, $pos);
                    $val = $this->decode($s, $pos);
                    if ($key === null || $val === null) break;
                    $retval[$key]=$val;
                }
                $retval['isDct'] = True;
                $pos++;
                return $retval;

            case 'l':
                $pos++;
                $retval = array();
                while ($s[$pos] != 'e'){
                    $val = $this->decode($s, $pos);
                    if ($val === null) break;
                    $retval[]=$val;
                }
                $pos++;
                return $retval;

            case 'i':
                $pos++;
                $digits = strpos($s, 'e', $pos)-$pos;
                $val = (int)substr($s, $pos, $digits);
                $pos += $digits+1;
                return $val;

            default:
                $digits = strpos($s, ':', $pos)-$pos;
                if ($digits<0 || $digits >20) return null;
                $len = (int)substr($s, $pos, $digits);
                $pos += $digits+1;
                $str = substr($s, $pos, $len);
                $pos += $len;

                return (string)$str;
        }

        return null;
    }

    function encode(&$d){
        $isDict = 0;
        if(is_array($d)){
            $ret = 'l';
            if(isset($d['isDct']) && $d['isDct']){
                $isDict = 1;
                $ret = 'd';

                // this is required by the specs, and BitTornado actualy chokes on unsorted dictionaries
                ksort($d, SORT_STRING);
            }
            foreach($d as $key=>$value) {
                if($isDict){
                    // skip the isDct element, only if it's set by us
                    if($key == 'isDct' and is_bool($value)) continue;
                    $ret .= strlen($key).':'.$key;
                }
                if (is_string($value)) {
                    $ret .= strlen($value).':'.$value;
                } elseif (is_int($value)){
                    $ret .= "i${value}e";
                } else {
                    $ret .= $this->encode($value);
                }
            }
            return $ret.'e';
        } elseif (is_string($d)) // fallback if we're given a single bencoded string or int
            return strlen($d).':'.$d;
        elseif (is_int($d))
            return "i${d}e";
        else
            return null;
    }
}
?>