<?php
require('../FaceApp/faceapp.php');

$FaceApp = new FaceApp('test/image.jpg');

$photoCode = $FaceApp->getPhotoCode();

$filter = 'old';

$FaceApp->applyFilter($photoCode,$filter,false);

if($FaceApp->savePhoto('newImagee.jpg')){
    echo "Done!";
}