<?php

/*
 *
 * INCASE SOMETHING GOES WRONG WHEN YOU RUN 01-PHOTOPLOG-MIGRATE THIS SCRIPT WILL REVERSE THE MIGRATION
 *
 */


//HOST, USER, PASS, NAME
$link = mysqli_connect('host','dbuser','dbpass','dbname') or die("Error " . mysqli_error($link));


/*
 * GET USERS THAT HAVE UPLOADED IMAGES
 */
//USE IF SERVER CAN RUN ALL QUERIES
$userquery = 'SELECT DISTINCT userid, username FROM photoplog_fileuploads';

//USE IF SERVER CAN'T RUN ALL QUERIES AND YOU NEED TO BREAK IT UP BY OFFSET AND RUN SCRIPT MULTIPLE TIMES
//$userquery = 'SELECT DISTINCT userid, username FROM photoplog_fileuploads LIMIT 10 OFFSET 0';

$userresult = mysqli_query($link, $userquery) or die(mysqli_error($link)." Q=".$userquery);


/*
 * LOOP ALL THE USERS
 */

while($user = mysqli_fetch_array($userresult)){
    /*
     * PREP VARIABLES
     */
    $userid = $user['userid'];
    $username = $user['username'];
    $now = strtotime('now');

    echo $userid.'<br/>';


    $querystr = 'SELECT * FROM node WHERE title = "'.mysqli_real_escape_string($link,$username).' PhotoPlog Migration"';
    $query = mysqli_query($link,$querystr) or die(mysqli_error($link)." q=".$querystr);

    $row = mysqli_fetch_array($query);

    if(isset($row) && ! empty($row)){
        $nodeId = $row['nodeid'];


        /*
         * DELETE NODE BY ID
         */
        $deleteNode = 'DELETE FROM node WHERE nodeid = '.$nodeId.'';
        $deleteNodeQuery = mysqli_query($link,$deleteNode) or die(mysqli_error($link)."q= ".$deleteNode);


        echo $deleteNode.'<br/>';


        /*
         * DELETE CLOSURE BY NODE ID
         */
        $deleteClosure = 'DELETE FROM closure WHERE parent = '.$nodeId.' || child = '.$nodeId.'';
        $deleteClosureQuery = mysqli_query($link,$deleteClosure) or die(mysqli_error($link)."q= ".$deleteClosure);
        echo $deleteClosure.'<br/>';

        /*
         * DELETE GALLERY
         */
        $deleteGallery = 'DELETE FROM gallery WHERE nodeid = '.$nodeId.'';
        $deleteGalleryQuery = mysqli_query($link,$deleteGallery) or die(mysqli_error($link)."q= ".$deleteGallery);
        echo $deleteGallery.'<br/>';

        /*
         * DELETE TEXT
         */
        $deleteText = 'DELETE FROM text WHERE nodeid = '.$nodeId.'';
        $deleteTextQuery = mysqli_query($link,$deleteText) or die(mysqli_error($link)."q= ".$deleteText);

        echo $deleteText.'<br/>';

        $getGalleryChildrenNodeIds = 'SELECT * FROM node WHERE parentid = '.$nodeId.'';
        $getGalleryChildrenNodeIdsQuery = mysqli_query($link,$getGalleryChildrenNodeIds) or die(mysqli_error($link)."q=".$getGalleryChildrenNodeIds);



        while($image = mysqli_fetch_array($getGalleryChildrenNodeIdsQuery)){
            $imageNodeId = $image['nodeid'];

            /*
             * GET FILEDATA ID
             */
            $getImageFileDataFromAttach = 'SELECT * FROM attach WHERE nodeid = '.$imageNodeId.'';
            $getImageFileDataFromAttachQuery = mysqli_query($link,$getImageFileDataFromAttach) or die(mysqli_error($link)."q=".$getImageFileDataFromAttach);
            $filedataid = mysqli_fetch_array($getImageFileDataFromAttachQuery);
            $filedataid = $filedataid['filedataid'];

            /*
             * DELETE FROM filedataresize
             */
            $deleteFiledatasize = 'DELETE FROM filedataresize WHERE filedataid = '.$filedataid.'';
            $deleteFiledatasizeQuery = mysqli_query($link,$deleteFiledatasize) or die(mysqli_error($link)."q= ".$deleteFiledatasize);

            /*
             * DELETE FROM filedata
             */
            $deleteFiledata = 'DELETE FROM filedata WHERE filedataid = '.$filedataid.'';
            $deleteFiledataQuery = mysqli_query($link,$deleteFiledata) or die(mysqli_error($link)."q= ".$deleteFiledata);

            /*
             * DELETE FROM ATTACH
             */
            $deleteAttach = 'DELETE FROM attach WHERE nodeid = '.$imageNodeId.'';
            $deleteAttachQuery = mysqli_query($link,$deleteAttach) or die(mysqli_error($link)."q= ".$deleteAttach);

            /*
             * DELETE FROM PHOTO
             */
            $deletePhoto = 'DELETE FROM photo WHERE nodeid = '.$imageNodeId.'';
            $deletePhotoQuery = mysqli_query($link,$deletePhoto) or die(mysqli_error($link)."q= ".$deletePhoto);

            /*
             * DELETE CLOSURE
             */
            $deleteClosure = 'DELETE FROM closure WHERE parent='.$imageNodeId.' || child = '.$imageNodeId.'';
            $deleteClosureQuery = mysqli_query($link,$deleteClosure) or die(mysqli_error($link)."q= ".$deleteClosure);

            /*
             * DELETE TEXT
             */
            $deleteText = 'DELETE FROM text WHERE nodeid = '.$imageNodeId.'';
            $deleteTextQuery = mysqli_query($link,$deleteText) or die(mysqli_error($link)."q= ".$deleteText);
        }
    }

}

echo '<br/><br/> DONE';

echo '<br/><br/>';
