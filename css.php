<?php

$cwd = getcwd();
$cssFiles = array_diff( scandir("{$cwd}\\templates\\src\\stylesheets"), array('.', '..'));

$cssContents = '';
foreach($cssFiles as $file) {
  $cssContents .= file_get_contents("{$cwd}\\templates\\src\\stylesheets\\{$file}");
}

// Enable GZip encoding.
ob_start('ob_gzhandler');
    ob_start();
        // Set the correct MIME type
        header("Content-type: text/css; charset: UTF-8");
        // Enable caching
        header('Cache-Control: public');
        // Expire in three days
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 21600) . ' GMT');
        echo minifyCss($cssContents);
    ob_end_flush();
ob_end_flush();


/*===========================================================================*/
/**
 * This function takes a css-string and compresses it, removing
 * unneccessary whitespace, colons, removing unneccessary px/em
 * declarations etc.
 *
 * @param string $css
 * @return string compressed css content
 * @author Steffen Becker
 */
function minifyCss(string $css) : string
{
  // some of the following functions to minimize the css-output are directly taken
  // from the awesome CSS JS Booster: https://github.com/Schepp/CSS-JS-Booster
  // all credits to Christian Schaefer: http://twitter.com/derSchepp
  // remove comments
  $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
  // backup values within single or double quotes
  preg_match_all('/(\'[^\']*?\'|"[^"]*?")/ims', $css, $hit, PREG_PATTERN_ORDER);
  for ($i=0; $i < count($hit[1]); $i++) {
    $css = str_replace($hit[1][$i], '##########' . $i . '##########', $css);
  }
  // remove traling semicolon of selector's last property
  $css = preg_replace('/;[\s\r\n\t]*?}[\s\r\n\t]*/ims', "}\r\n", $css);
  // remove any whitespace between semicolon and property-name
  $css = preg_replace('/;[\s\r\n\t]*?([\r\n]?[^\s\r\n\t])/ims', ';$1', $css);
  // remove any whitespace surrounding property-colon
  $css = preg_replace('/[\s\r\n\t]*:[\s\r\n\t]*?([^\s\r\n\t])/ims', ':$1', $css);
  // remove any whitespace surrounding selector-comma
  $css = preg_replace('/[\s\r\n\t]*,[\s\r\n\t]*?([^\s\r\n\t])/ims', ',$1', $css);
  // remove any whitespace surrounding opening parenthesis
  $css = preg_replace('/[\s\r\n\t]*{[\s\r\n\t]*?([^\s\r\n\t])/ims', '{$1', $css);
  // remove any whitespace between numbers and units
  $css = preg_replace('/([\d\.]+)[\s\r\n\t]+(px|em|pt|%)/ims', '$1$2', $css);
  // shorten zero-values
  $css = preg_replace('/([^\d\.]0)(px|em|pt|%)/ims', '$1', $css);
  // constrain multiple whitespaces
  $css = preg_replace('/\p{Zs}+/ims',' ', $css);
  // remove newlines
  $css = str_replace(array("\r\n", "\r", "\n"), '', $css);
  // Restore backupped values within single or double quotes
  for ($i=0; $i < count($hit[1]); $i++) {
    $css = str_replace('##########' . $i . '##########', $hit[1][$i], $css);
  }
  return $css;
}
