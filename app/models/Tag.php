<?php

class Tag extends Eloquent {
    protected $guarded = array();

    public function item() {
        return $this->belongsToMany('Item');
    }

    public static function getTagIdsByTagNames($tag_names) {
        $tag_ids = array();

        foreach($tag_names as $tag_name) {
            $tag_name = trim(mb_convert_kana( $tag_name, "&quot;s&quot;"));
            $tag_name = str_replace(" ","", $tag_name);
            if (empty($tag_name)) {
                continue;
            }
            $tag_name = mb_strtolower($tag_name);
            $tag = Tag::firstOrCreate(array('name' => $tag_name))->toArray();
            $tag_ids[] = (string)$tag['id'];
        }
        return $tag_ids;
    }
}
