<?php

/*
 *
 * CHANGE NODE TABLE SHOWPUBLISHED TO 1 ON IMAGES TO CREATE ALBUM
 *
 */

//HOST, USER, PASS, NAME
$link = mysqli_connect('host','dbuser','dbpass','dbname') or die("Error " . mysqli_error($link));

//USE IF SERVER CAN RUN ALL QUERIES
$selectalbum = 'SELECT * FROM node WHERE title LIKE "%PhotoPlog Migration%"';

//USE IF SERVER CAN'T RUN ALL QUERIES AND YOU NEED TO BREAK IT UP BY OFFSET AND RUN SCRIPT MULTIPLE TIMES
//$selectalbum = 'SELECT * FROM node WHERE title LIKE "%PhotoPlog Migration%" LIMIT 100 OFFSET 0';

$albumResult = mysqli_query($link, $selectalbum) or die(mysqli_error($link)." Q=".$selectalbum);


while($al = mysqli_fetch_array($albumResult)){
    $albumid = $al['nodeid'];

    $filenode = 'SELECT * FROM node WHERE parentid = '.$albumid;
    $filenodeResult = mysqli_query($link, $filenode) or die(mysqli_error($link)." Q=".$filenode);

    echo $filenode.'<br/>';

    while($fn = mysqli_fetch_array($filenodeResult)){
        $filenodeid = $fn['nodeid'];
        $showpublished = $fn['showpublished'];

        if($showpublished != 1){
            $updateShowPublished = "UPDATE node SET showpublished = 1 WHERE nodeid = ".$filenodeid."";
            $updateShowPublishedQuery = mysqli_query($link, $updateShowPublished) or die(mysqli_error($link)." Q=".$updateShowPublished);
        }
    }
}

echo '<br/>DONE<br/>';