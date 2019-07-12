<?php
function size_beautify($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } else if ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } else if ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } else if ($bytes > 1) {
        $bytes = "$bytes bytes";
    } else if ($bytes == 1) {
        $bytes = "$bytes  byte";
    } else {
        $bytes = '0 bytes';
    }

    return $bytes;
}
function starts_with($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function ends_with($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}
function parts_to_ints($parts)
{
    $values = array_map('trim', explode(',', $parts));
    $ints   = [];

    foreach($values as $value)
    {
		if(preg_match("/^\d+-\d+$/", $value))
        {
			preg_match_all("/\d+/", $value, $matches);
			$first  = $matches[0][0];
            $second = $matches[0][1];
			
            $from = $first <= $second ? $first  : $second;
            $to   = $first <= $second ? $second : $first;

            for($i = $from; $i <= $to; $i++)
            {
                $ints[] = $i;
            }
        }
        else if(preg_match("/^\d+$/", $value, $match))
        {
            $ints[] = $match[0];
        }
    }

    $ints = array_unique($ints);
    sort($ints);

    return $ints;
}
?>