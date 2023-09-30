<?php

namespace App\Helpers;

class Helpers
{

      /**
      * @return \Illuminate\Config\Repository|int|mixed
      */
      public static function getVersion()
      {
        switch (config('app.env')) {
            case 'production':
                return config('app.copyright.version');
                break;
            default:
                return time();
                break;
        }
      }

      /**
       * @return false|string
       */
      public static function getCopyrightYear()
      {
          $currentYear = date('Y');
          $copyrightYear = config('app.copyright.year');
          if ($copyrightYear < $currentYear) {
              return $copyrightYear . ' - ' . $currentYear;
          }
          return $currentYear;
      }

      /**
       * @param $email
       * @param int $size
       * @return string
       */
      public static function getGravatarSrc($email,$size = 200)
      {
          $hash = md5(strtolower(trim($email)));
          return "https://www.gravatar.com/avatar/$hash?d=identicon&s=$size";
      }

      /**
       * @param $id
       * @param bool $withGravatar
       * @return string
       */
      public static function renderLinkedUserDisplayName($id, $withGravatar = true)
      {
          $output = '';
          $user = User::find($id);
          if(!is_null($user)) {
              $profileLink = route('users.profile',$user->id);
              if($withGravatar) {
                  $gravatarSrc = self::getGravatarSrc($user->email,14);
                  $output .= "<a href='$profileLink'><img class='display-name-gravatar' src='$gravatarSrc' /></a> ";
              }
              $output .= "<a href='$profileLink'>{$user->name}</a>";
          }
          return $output;
      }

      /**
       * @param $input
       * @return string
       */
      public static function quotesWrap($input) {
          return '"' . str_replace('"','""', $input) . '"';
      }

      /**
      * @param string $name
      * @param array $classNames
      * @param boolean $classOverwrite
      * @return string
      */
      public static function getSvgCodeWithClasses($name = '',$classNames = [],$classOverwrite = false)
      {
        if(empty($name)) {
          return '';
        }
        $name = str_ireplace('.svg','',$name);
        $svg = \file_get_contents(resource_path('svg') . '\\' . $name . '.svg');
        $dom = new \DOMDocument();
        @$dom->loadHTML($svg);
        foreach($dom->getElementsByTagName('svg') as $element) {
          if($classOverwrite) {
            $classes = [];
          } else {
            $classes = explode(' ',$element->getAttribute('class'));
          }
          foreach($classNames as $className) {
              $classes[] = $className;
          }
          $element->setAttribute('class',implode(' ',$classes));
        }
        $dom->saveHTML();
        $code = $dom->saveHTML();
        return $code;
      }
}
