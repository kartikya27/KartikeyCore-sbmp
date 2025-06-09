<?php

namespace Kartikey\Core\Helpers;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Session;

class Translator
{
    protected static $translations = [];

    public static function translate($text)
    {
        // Check if the translation already exists
        $language = Session::get('app_locale', config('app.locale'));

        if($language == 'de')
        {
            return $text;
        }

        if (isset(self::$translations[$text])) {
            return self::$translations[$text];
        }

        // Perform the translation
        $translatedText = GoogleTranslate::trans($text, $language);
        // Store the translation in the static variable
        self::$translations[$text] = $translatedText;

        return $translatedText;
    }
}
