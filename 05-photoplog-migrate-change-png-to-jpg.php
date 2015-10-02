<?php

/*
 *
 * FIX MISSING PNG THUMBNAILS BY CHANGING THE EXTENSION IN THE DB TO JPG
 *
 */

//HOST, USER, PASS, NAME
$link = mysqli_connect('host','dbuser','dbpass','dbname') or die("Error " . mysqli_error($link));


$nodequery = 'SELECT * FROM node WHERE title LIKE "%Photoplog Migration%"';
$noderesult = mysqli_query($link, $nodequery) or die(mysqli_error($link)." Q=".$nodequery);


/*
 * LOOP ALL THE USERS
 */
while($node = mysqli_fetch_array($noderesult)){
    $nodeid = $node['nodeid'];
    $userid = $node['userid'];


    $picdata = 'SELECT * FROM node
        INNER JOIN photo ON(
            photo.nodeid = node.nodeid
        )
        WHERE node.parentid = '.$nodeid.' AND node.userid = '.$userid;
    $picdataresults = mysqli_query($link, $picdata) or die(mysqli_error($link)." Q=".$picdata);

    /*
     * LOOP ALL THE PHOTOS
     */
    while($pics = mysqli_fetch_array($picdataresults)){
        $filenodeid = $pics['filedataid'];

        $filedataquery = 'SELECT * FROM filedata WHERE filedataid = '.$filenodeid;
        $filedataresults = mysqli_query($link, $filedataquery) or die(mysqli_error($link)." Q=".$filedataquery);

        $file = mysqli_fetch_array($filedataresults);
        $ext = $file['extension'];

        echo $filenodeid.'<br/>';

        if($ext == 'png'){
            echo 'I am a PNG <br/>';

            $updateFiledata = 'UPDATE filedata SET extension = "jpg" WHERE filedataid = '.$filenodeid;
            $updateFiledataQuery = mysqli_query($link, $updateFiledata) or die(mysqli_error($link)." Q=".$updateFiledata);

            echo 'I am now a JPG <br/>';
        }else{
            echo 'I am not a PNG <br/>';
        }
    }

    echo '<br/><br/>';
}