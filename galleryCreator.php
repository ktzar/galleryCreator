<?php
require __DIR__ . '/vendor/autoload.php';

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
    if (!is_dir($dir)) {
        echo "$dir is not a directory\n";
        die;
    }
}

$source = $argv[1];
$target = $argv[2];

isDirOrDie($source);
isDirOrDie($target);

$files = array_merge(glob("$source/*.JPG"), glob("$source/*.jpg"));
mkdir("$target/thumbs");
mkdir("$target/medium");
mkdir("$target/static");

$total = count($files);
$output = array(
    'pics' => array()
);
$i = 0;
foreach ($files as $file) {
    $data = exif_read_data($file);
    if (isset($data['DateTime'])) {
        $newname = strtotime($data['DateTime']);
    } else {
        $newname = str_pad($i, 4, '0', STR_PAD_LEFT);
    }
    $newname .= ".jpg";
    echo $i ++ . " / $total\tEncoding $file -> $newname\n";
    thumb($file, "$target/thumbs/".$newname, 800);
    thumb($file, "$target/medium/".$newname, 1920);
    $output['pics'][] = array(
        'thumb' => "./thumbs/$newname",
        'medium' => "./medium/$newname",
        'large' => "./full/" . basename($file),
    );
}

$m = new Mustache_Engine;
$html = $m->render(
    file_get_contents(__DIR__ . '/views/gallery.mustache'),
    $output
);
file_put_contents("$target/index.html", $html);
foreach (glob("./static/*") as $file) {
    copy($file, "$target/$file");
}
