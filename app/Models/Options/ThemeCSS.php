<?php
    namespace App\Models\Options;

    class ThemeCSS 
    {
        public $main_color = '#6E34C5';
        public function __construct(string $theme_css)
        {
            $json = json_decode($theme_css, true);  
            $this->main_color = isset($json['main_color']) ? $json['main_color'] : '#6E34C5';
        }
    }
