<?php
    namespace App\Models\Options;

    class ThemeCSS 
    {
        public $main_color = '#6E34C5';
        public function __construct($theme_css)
        {
            if($theme_css) 
            {
                $json = json_decode($theme_css, true);  
                $this->main_color = isset($json['main_color']) ? $json['main_color'] : '#6E34C5';    
            }
            else
                $this->main_color = '#6E34C5';
        }
    }
