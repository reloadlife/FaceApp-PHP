<?php
require('../FaceApp/faceapp.php');

$FaceApp = new FaceApp('image.jpg');

$photoCode = $FaceApp->getPhotoCode();

$filter = $FaceApp->getFilters()[array_rand($FaceApp->getFilters())];

$FaceApp->applyFilter($photoCode,$filter,false);

if($FaceApp->savePhoto('newImagee.jpg')){
    echo "Done!";
}
