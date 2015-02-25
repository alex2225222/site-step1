<?php

function create_avatar($new_filename, $files = '') {
  if (empty($files))
    $files = $_FILES['fupload'];
  $path_dir = 'img/avatars/';
  $filename = $files['name'];
  if (preg_match('/[.](JPG)|(jpg)|(JPEG)|(jpeg)|(GIF)|(gif)|(PNG)|(png)$/', $filename, $out)) {
    $source = $files['tmp_name'];
    $size = getimagesize($source);
    if ($size[0] < 50 || $size[1] < 50)
      return false;
    $target = 'img/original/' . $new_filename . '.' . $out[2];
    move_uploaded_file($source, $target);
    if (in_array($out[2], array('JPG', 'jpg', 'jpeg', 'JPEG'))) {
      $im = imagecreatefromjpeg($target);
    }
    elseif (in_array($out[2], array('GIF', 'gif'))) {
      $im = imagecreatefromgif($target);
    }
    elseif (in_array($out[2], array('PNG', 'png'))) {
      $im = imagecreatefrompng($target);
    }
    $w_src = imagesx($im);
    $h_src = imagesy($im);
    $w = 130;

    if ($w_src > $h_src) {
      $dst_x = 0;
      $dst_w = $w;
      $dst_y = 0;
      $dst_h = round($h_src * $w / $w_src);
    }
    elseif ($w_src < $h_src) {
      $dst_y = 0;
      $dst_h = $w;
      $dst_x = 0;
      $dst_w = round($w_src * $w / $h_src);
    }
    else {
      $dst_x = 0;
      $dst_w = $w;
      $dst_y = 0;
      $dst_h = $w;
    }
    $dest = imagecreatetruecolor($dst_w, $dst_h);
    imagecopyresampled($dest, $im, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $w_src, $h_src);
    $avatar = $new_filename . ".jpg";
    imagejpeg($dest, $path_dir . $avatar);
    return $avatar;
  }
  else {
    return '';
  }
}