<?php
date_default_timezone_set('Europe/London');

function thumb($file, $thumbFile, $thumbWidth) {
    // load image and get image size
    $img = imagecreatefromjpeg( $file );
    $width = imagesx( $img );
    $height = imagesy( $img );

    // calculate thumbnail size
    $new_width = $thumbWidth;
    $new_height = floor( $height * ( $thumbWidth / $width ) );

    // create a new temporary image
    $tmp_img = imagecreatetruecolor( $new_width, $new_height );

    // copy and resize old image into new image 
    imagecopyresampled( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

    // save thumbnail into a file
    imagejpeg( $tmp_img, $thumbFile );
}

function isDirOrDie($dir) {
    if (!is_dir($source)) {
        echo "$source is not a directory\n";
        die;
    }
}

$source = $argv[1];
$target = $argv[2];

isDirOrDie($source);
isDirOrDie($target);

$files = array_merge(glob("$source/*.JPG"), glob("$source/*.jpg"));

$total = count($files);
$output = array();
$i = 0;
foreach ($files as $file) {
    $data = exif_read_data($file);
    $newname = strtotime($data['DateTime']) . ".jpg";
    echo "$i / $total\tEncoding $file -> $newname\n";
    thumb($file, "$target/thumbs/".$newname, 800);
    thumb($file, "$target/medium/".$newname, 1920);
    $output[$newname] = $file;
    $i++;
}

echo "Writing references to output.json\n";
file_put_contents("$target/output.json", json_encode($output, JSON_PRETTY_PRINT));
