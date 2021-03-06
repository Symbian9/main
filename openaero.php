<?php
// openaero.php 1.5.0

// This file is part of OpenAero.

//  OpenAero was originally designed by Ringo Massa and built upon ideas
//  of Jose Luis Aresti, Michael Golan, Alan Cassidy and many others. 

//  OpenAero is Free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.

//  OpenAero is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.

//  You should have received a copy of the GNU General Public License
//  along with OpenAero.  If not, see <http://www.gnu.org/licenses/>.

// This file handles multiple functions that have to be executed in PHP to work
//

// Log request for OpenAero version monitoring
$lines = file ('timestamp.txt');
// Only log 1 per second
if ($lines[0] != time()) {
  file_put_contents ('timestamp.txt', time());
  $line = time() . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . $_REQUEST['version'] . "\n";
  file_put_contents ('../../openaero.log', $line, FILE_APPEND);
}

switch ($_REQUEST['f']) {
  case 'download':
    download();
    break;
  case 'version':
    version();
    break;
  case 'v':
    versionNew();
    break;
  default:
    header( 'Content-Type:' );
}

// download allows saving of files from OpenAero when running on a server (local or online)
// TO DO: add code to prevent unauthorised use
function download() {
  // Check if all data is present
  if(empty($_POST['name']) || empty($_POST['data']) || empty($_POST['format'])) {
    die ("ERROR: Missing data");
  }
  // Prevent sending very large files. Probably not originating from OpenAero
  foreach ($_POST as $postString) {
    if (strlen ($postString > 500000)) die ("ERROR: File too large");
  }
  
  // Sanitize the filename:
  $filename = preg_replace('/[^a-z0-9\-\_\. ]/i','',$_POST['name']);
   
  // Output headers:
  header("Cache-Control: ");
  header("Content-type: ".$_POST['format']);
  header('Content-Disposition: attachment; filename="'.$filename.'"');
  
  // Create file output 
  echo $_POST['data'];
}

// version will check if the provided version number is the latest.
// If not, it will display the latest version in an svg image
function version() {
  $version = '';
  // Get the current stable version from config.js
  ini_set('allow_url_fopen', true);
  $lines = file ('http://openaero.net/config.js');
  foreach ($lines as $line) {
    if (preg_match ('/^var version[^0-9]+([0-9\.]+)/', $line, $matches)) {
      $version = $matches[1];
      break;
    }
  }

  // Build svg image
  header('Content-Type: image/svg+xml');
  if (($_REQUEST['version'] == $version) || ($version == '')) exit;
  // Version provided is different from latest version
  echo "<?xml version=\"1.0\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">";
  echo '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.2" width="200" height="18">';
  echo '<rect width="100%" height="100%" fill="#ff8000"></rect>';
  echo '<text x="195" y="15" text-anchor="end" font-family="Verdana" font-size="13" fill="#4040ff">Download version '.$version.'</text>';
  echo '</svg>';
}

// versionNew will return the stable version number. It is encoded in
// width and height of an SVG image as follows:
// Version aa.bb.cc.dd
// Encoded size aacc x bbdd
function versionNew() {
  $version = '';
  // Get the current stable version from config.js
  ini_set('allow_url_fopen', true);
  $lines = file ('http://openaero.net/config.js');
  foreach ($lines as $line) {
    if (preg_match ('/^var version[^0-9]+([0-9\.]+)/', $line, $matches)) {
      $version = explode('.', $matches[1]);
      break;
    }
  }
  $w = $version[0] * 100 + $version[2];
  $h = $version[1] * 100 + $version[3];
  // Build svg image
  header('Content-Type: image/svg+xml');
  echo "<?xml version=\"1.0\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">";
  echo '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.2" width="'.$w.'" height="'.$h.'">';
  echo '</svg>';
}
?>
