<?php

/*
 *
 * PHOTO COUNT ISSUE WITH POSTED PHOTOS ALBUM
 *
 */

//HOST, USER, PASS, NAME
$link = mysqli_connect('host','dbuser','dbpass','dbname') or die("Error " . mysqli_error($link));


$selectFiles = 'SELECT node1 . *
FROM node AS node1
INNER JOIN node AS node2 ON ( node1.parentid = node2.nodeid )
WHERE node2.title LIKE "%PhotoPlog Migration%"';
$selectFilesResult = mysqli_query($link, $selectFiles) or die(mysqli_error($link)." Q=".$selectFiles);


/*
 * LOOP ALL IMAGES
 */
while($file = mysqli_fetch_array($selectFilesResult)){
    $nodeid = $file['nodeid'];
    $publishdate = $file['publishdate'];



    $selectClosure = 'SELECT * FROM closure WHERE parent = 9 AND child = '.$nodeid.'';
    $selectresults = mysqli_query($link, $selectClosure) or die(mysqli_error($link)." Q=".$selectClosure);

    if(mysqli_num_rows($selectresults) == 0){
        $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("9","'.$nodeid.'",2,0,"'.$publishdate.'")';
        $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);

        echo 'INSERT 9 <br/>';
    }


    /***********/


    $selectClosure = 'SELECT * FROM closure WHERE parent = 6 AND child = '.$nodeid.'';
    $selectresults = mysqli_query($link, $selectClosure) or die(mysqli_error($link)." Q=".$selectClosure);

    if(mysqli_num_rows($selectresults) == 0){
        $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("6","'.$nodeid.'",3,0,"'.$publishdate.'")';
        $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);

        echo 'INSERT 6 <br/>';
    }


    /***********/


    $selectClosure = 'SELECT * FROM closure WHERE parent = 1 AND child = '.$nodeid.'';
    $selectresults = mysqli_query($link, $selectClosure) or die(mysqli_error($link)." Q=".$selectClosure);

    if(mysqli_num_rows($selectresults) == 0){
        $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("1","'.$nodeid.'",4,0,"'.$publishdate.'")';
        $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);

        echo 'INSERT 1 <br/>';
    }

    echo '<br/><br/>';
}

echo '<br/>DONE<br/>';