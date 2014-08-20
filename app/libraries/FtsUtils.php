<?php



class FtsUtils{

    /*
     * Convert String into N-Gramed string 
     */
    public static function toNgram($str, $n = 2){
        $res = array();
        $str = self::removeMarkdown($str);
        $str = self::stripCrLf($str);
        $len = mb_strlen($str, 'UTF8');
    
        $index = 0;
        while(true){
            $res[] = mb_substr($str,$index,$n, 'UTF8');
            $index++;
            if($index+$n > $len) break;
        }
        return implode(' ', $res);
    }
    
    
    /**
     *  remove all cr+lf from given string
     */
    private static function stripCrLf($str){
        return preg_replace("/[\r\n]+/", "", $str);
    }

    /**
     *  remove markdown from given string
     */
    private static function removeMarkdown($str){
        $parser = new CustomMarkdown;
        $parser->enableNewlines = true;
        return strip_tags($parser->parse($str));
    }

}
