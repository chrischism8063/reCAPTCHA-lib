<?php

namespace ReCaptcha;

/**
 * CodeIgniter Wrapper Library for reCAPTCHA API
 *
 * @date 2014-06-01 01:30
 *
 * @package Libraries
 * @author  Adriano Rosa (http://adrianorosa.com)
 * @license The MIT License (MIT), http://opensource.org/licenses/MIT
 * @link    https://github.com/adrianorsouza/codeigniter-recaptcha
 * @link    reCAPTCHA docs Reference: {@link https://developers.google.com/recaptcha/}
 * @version 0.1.0
 **/
class CaptchaTheme
{
   /**
    * reCAPTCHA Theme default options
    * RecaptchaOptions Reference: {@link https://developers.google.com/recaptcha/docs/customization}
    *
    * @access protected
    * @var array
    **/
   protected $_recaptchaOptions = array(
               'theme'               => 'red',
               'lang'                => 'en',
               'custom_translations' => null,
               'custom_theme_widget' => null,
               'tabindex'            => 0
               );

   /**
    * List of Standard Theme names available
    * Standard names Reference: {@link https://developers.google.com/recaptcha/docs/customization#Standard_Themes}
    *
    * @access protected
    * @var array
    **/
   protected $_standardThemes = array('red','white','blackglass','clean');

   /**
    * For comparison of built in i18n languages
    *
    * @access protected
    * @var array
    **/
   protected $_builtInlang = array(
               'English'    => 'en',
               'Dutch'      => 'nl',
               'French'     => 'fr',
               'German'     => 'de',
               'Portuguese' => 'pt',
               'Russian'    => 'ru',
               'Spanish'    => 'es',
               'Turkish'    => 'tr',
            );

   /**
    * Standard Theme
    * Display's Theme customization for reCAPTCHA widget
    * by writting a snippet for Standard_Themes and Custom_Theming

    * Custom Theme Template
    * In order to use a custom theme, must set reCAPTCHA options correctly,
    * also provide a custom CSS to display it properly.
    * Fully Custom Reference: {@link: https://developers.google.com/recaptcha/docs/customization#Custom_Theming}
    *
    * @param string $theme_name Optional theme name. NOTE: overwrites the config captcha_standard_theme value
    * @param array $options reCAPTCHA Associative array of available Options. NOTE: overwrites captcha_config
    * @return string Standard_Theme | Custom_Theme | Fallback default reCAPTCHA theme
    **/
   protected function _theme($theme_name = NULL, $options = array())
   {
      $js_snippet = "<script type=\"text/javascript\">var RecaptchaOptions = %s;</script>";

      if ( count($options) > 0 ) {
         $this->_recaptchaOptions = array_merge($this->_recaptchaOptions, $options);
      }

      if ( NULL !== $theme_name ) {
         $this->_recaptchaOptions['theme'] = $theme_name;
      }

      // If lang option value is not built in try to set it from a translation file
      if ( isset($this->_recaptchaOptions['lang'])
            && !in_array($this->_recaptchaOptions['lang'], $this->_builtInlang)
            && !isset($this->_recaptchaOptions['custom_translations']) ) {
         $this->setTranslation($this->_recaptchaOptions['lang']);
      }

      // Skip to default reCAPTCHA theme if it's not set or options is not required
      if ( !isset($this->_recaptchaOptions['theme']) && count($this->_recaptchaOptions) == 0 ) {
         return;
      }

      // Skip to default reCAPTCHA theme if it's set to 'red' and there is no options at all
      if ( $this->_recaptchaOptions['theme'] === 'red' && count($this->_recaptchaOptions) == 0 ) {
         return;
      }

      // If theme name is on of the Standard_Themes assumed we are using correct name
      if ( in_array($this->_recaptchaOptions['theme'], $this->_standardThemes) ) {
         unset($this->_recaptchaOptions['custom_theme_widget']);
         $js_options = json_encode($this->_recaptchaOptions);

         return sprintf($js_snippet, $js_options);

      } elseif ( $this->_recaptchaOptions['theme'] === 'custom' ) {
         // Custom theme MUST have an option [custom_theme_widget: ID_some_widget_name] set for recaptcha
         // If this option is not set, we make it.
         if ( !isset($this->_recaptchaOptions['custom_theme_widget']) ) {
            $this->_recaptchaOptions['custom_theme_widget'] = 'recaptcha_widget';
         }

         $custom_template = '
            <div id="'. $this->_recaptchaOptions['custom_theme_widget'] .'" style="display:none">
               <div id="recaptcha_image"></div>
               <div class="recaptcha_only_if_incorrect_sol" style="color:red">'. $this->i18n("incorrect_try_again") .'</div>

               <span class="recaptcha_only_if_image">'. $this->i18n('instructions_visual') .'</span>
               <span class="recaptcha_only_if_audio">'. $this->i18n('instructions_audio') .'</span>

               <input type="text" id="recaptcha_response_field" name="recaptcha_response_field" />

               <div><a href="javascript:Recaptcha.reload()">'. $this->i18n('refresh_btn') .'</a></div>
               <div class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type(\'audio\')">'. $this->i18n('audio_challenge') .'</a></div>
               <div class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type(\'image\')">'. $this->i18n('visual_challenge') .'</a></div>

               <div><a href="javascript:Recaptcha.showhelp()">'. $this->i18n('help_btn') .'</a></div>
            </div>';

         $js_options = json_encode($this->_recaptchaOptions);
         return sprintf($js_snippet, $js_options) . $custom_template;

      }
      // FALLBACK to red one default theme
      return;
   }

   /**
    * Custom Translations
    *
    * In order to use custom translation (even if it is not built in specially for a custom theme),
    * the translations must be set manually by this method or by passing the lang two letters code to
    * instance constructor It will set translation by a lang code given and overwrites other languages
    * set into captcha_config file.
    *
    * NOTE: If translate file recaptcha.lang[lang_code].php with its respective translation strings
    * within a folder i18n is not found default lang English 'en' will be used instead.
    *
    * @param string $language Two two letter language code e.g: (Italian = 'it')
    * @param string $path Optional alternative path to translate file
    * @return void
    **/
   public function setTranslation($language = 'en', $path = NULL)
   {
      $this->_recaptchaOptions['lang'] = $language;

      $custom_translations = $this->i18n(NULL, $path);

      $this->_recaptchaOptions['custom_translations'] = $custom_translations;

   }

   /**
    * Fetch I18n language line
    *
    * @param string $key The string translated
    * @param string $path Optional path to language file
    * @return array|string
    **/
   protected function i18n($key = NULL, $path = NULL)
   {
      static $RECAPTCHA_LANG;

      if ( $RECAPTCHA_LANG ) {
         return isset($key) ? $RECAPTCHA_LANG[$key] : $RECAPTCHA_LANG;
      }

      if ( !isset($this->_recaptchaOptions['lang']) ) {
         $language = 'en';
      } else {
         $language = $this->_recaptchaOptions['lang'];
      }

      $RECAPTCHA_LANG = array(
         'instructions_visual' => 'Enter the words above:',
         'instructions_audio'  => 'Type what you hear:',
         'play_again'          => 'Play sound again',
         'cant_hear_this'      => 'Download sound as MP3',
         'visual_challenge'    => 'Get an image CAPTCHA',
         'audio_challenge'     => 'Get an audio CAPTCHA',
         'refresh_btn'         => 'Get another CAPTCHA',
         'help_btn'            => 'Help',
         'incorrect_try_again' => 'Incorrect, please try again.'
         );

      // path/to/vendor/lib/I18n/recaptcha.lang.[langcode].php
      $path = ( NULL === $path )
         ? dirname(__DIR__) . DIRECTORY_SEPARATOR . 'I18n' . DIRECTORY_SEPARATOR
         : $path;

      if ( file_exists( $path . 'recaptcha.lang.' . $language . '.php' ) ) {
         include_once $path . 'recaptcha.lang.' . $language . '.php';
      }

      return isset($key) ? $RECAPTCHA_LANG[$key] : $RECAPTCHA_LANG;

   }

   /**
    * Get user's browser language
    *
    * @return string
    **/
   public function clientLang()
   {
      if ( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) {

         $language = explode(',', preg_replace('/(;\s?q=[0-9\.]+)|\s/i', '', strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE']))));
         return strtolower($language[0]);
      }

      return;
   }

}