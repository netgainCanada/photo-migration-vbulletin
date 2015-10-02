<?php

/*
 *
 * MIGRATING THE PHOTOPLOG SCRIPT
 *
 */

//HOST, USER, PASS, NAME
$link = mysqli_connect('host','dbuser','dbpass','dbname') or die("Error " . mysqli_error($link));


/*
 * GET ALL USERS THAT HAVE UPLOADED IMAGES TO PHOTOPLOG
 */
//USE IF SERVER CAN RUN ALL QUERIES
$userquery = 'SELECT DISTINCT userid, username FROM photoplog_fileuploads';

//USE IF SERVER CAN'T RUN ALL QUERIES AND YOU NEED TO BREAK IT UP BY OFFSET AND RUN SCRIPT MULTIPLE TIMES
//$userquery = 'SELECT DISTINCT userid, username FROM photoplog_fileuploads LIMIT 50 OFFSET 0';
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

    /*
     * INSERT ALBUM NODE
     */
    $insertnode = "INSERT INTO node(routeid, contenttypeid, userid, authorname, title, htmltitle, parentid, urlident, showpublished, open, showopen, approved, showapproved, publishdate, created, lastcontent) VALUES('52', '22', '".$userid."', '".mysqli_real_escape_string($link,$username)."', '".mysqli_real_escape_string($link,$username)." PhotoPlog Migration', '".mysqli_real_escape_string($link,$username)." PhotoPlog Migration', '9', 'photoplog-migration-".$userid."', '1', '1', '1', '1', '1', '".$now."', '".$now."', '".$now."')";
    $insertnodeQuery = mysqli_query($link, $insertnode) or die(mysqli_error($link)." Q=".$insertnode);

    $nodelast = 'SELECT nodeid FROM node ORDER BY nodeid DESC LIMIT 1';
    $nodelastresult = mysqli_query($link, $nodelast) or die(mysqli_error($link)." Q=".$nodelast);
    $album_id = mysqli_fetch_array($nodelastresult);
    $album_id = $album_id[0];

    /*
     * SET STARTER... USED TO SHOW ALBUM IN special/albums
     */
    $updateStarter = "UPDATE node SET starter = ".$album_id." WHERE nodeid = ".$album_id."";
    $updateStarterQuery = mysqli_query($link, $updateStarter) or die(mysqli_error($link)." Q=".$updateStarter);


    echo 'Album node inserted with ID #'.$album_id.'<br/>';


    /*
     * INSERT ALBUM INTO CLOSURE
     */
    $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("'.$album_id.'","'.$album_id.'",0,0,"'.$now.'")';
    $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);
    echo 'Insert Closure '.$album_id.' - '.$album_id.'<br/>';



    $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("9","'.$album_id.'",1,0,"'.$now.'")';
    $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);
 
    $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("6","'.$album_id.'",2,0,"'.$now.'")';
    $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);

    $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("1","'.$album_id.'",3,0,"'.$now.'")';
    $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);




    /*
     * INSERT GALLERY
     */
    $insertGallery = 'INSERT INTO gallery(nodeid, caption) VALUES('.$album_id.', "Photoplog Migration")';
    $insertGalleryQuery = mysqli_query($link, $insertGallery) or die(mysqli_error($link)." Q=".$insertGallery);

    echo 'Gallery inserted with ID #'.$album_id.'<br/>';

    $rs = mysqli_query($link,'SELECT userid, title, description, filename FROM photoplog_fileuploads WHERE userid ='.$userid);

    while($row = mysqli_fetch_array($rs)){
        $userid = $row['userid'];
        $title = $row['title'];
        $description = $row['description'];
        $filename = $row['filename'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if(file_exists('forum_path/photoplog/images/'.$userid.'/'.$filename)){

            /*
             * INSERT FILE NODE
             */
            $insertNode = 'INSERT INTO node(routeid, contenttypeid, userid, authorname, parentid, created,publishdate,starter) VALUES("52", "23", "'.$userid.'", "'.$username.'", "'.$album_id.'", "'.$now.'", "'.$now.'","'.$album_id.'")';
            $insertNodeQuery = mysqli_query($link, $insertNode) or die(mysqli_error($link)." Q=".$insertNode);

            $lastNode = 'SELECT nodeid FROM node ORDER BY nodeid DESC LIMIT 1';
            $lastNodeQuery = mysqli_query($link, $lastNode) or die(mysqli_error($link)." Q=".$lastNode);
            $image_node_id = mysqli_fetch_array($lastNodeQuery);
            $image_node_id = $image_node_id[0];


            echo 'Image node inserted with ID '.$image_node_id.' and filename is: '.$filename.'<br/>';



            /*
             * INSERT FILE DATA
             */
            $fileSize = filesize('forum_path/photoplog/images/'.$userid.'/'.$filename);
            $filehash = md5($filename);

            $insertFileData = 'INSERT INTO filedata(userid, dateline, filedata, extension,filesize,filehash,refcount) VALUES("'.$userid.'", "'.$now.'", NULL, "'.$ext.'","'.$fileSize.'","'.$filehash.'","1")';
            $insertFileDataQuery = mysqli_query($link, $insertFileData) or die(mysqli_error($link)." Q=".$insertFileData);

            /*
             * GET FILE DATA LAST ID
             */
            $lastFiledata = 'SELECT filedataid FROM filedata ORDER BY filedataid DESC LIMIT 1';
            $lastFiledataQuery = mysqli_query($link, $lastFiledata) or die(mysqli_error($link)." Q=".$lastFiledata);
            $filedata_id = mysqli_fetch_array($lastFiledataQuery);
            $filedata_id = $filedata_id[0];

            echo 'Inserted filedata with ID '.$filedata_id.'<br/>';

            /*
             * INSERT INTO PHOTO TABLE
            ----------UPDATE--------    CHANGED TO ATTACH
            */
            $insertPhoto = 'INSERT INTO photo(nodeid, filedataid, caption) VALUES("'.$image_node_id.'", "'.$filedata_id.'", "'.mysqli_real_escape_string($link,$title).'")';
            $insertPhotoQuery = mysqli_query($link, $insertPhoto) or die(mysqli_error($link)." Q=".$insertPhoto);



            $insertAttach = 'INSERT INTO attach(nodeid,filedataid,visible,counter,filename,caption) VALUES("'.$image_node_id.'", "'.$filedata_id.'",1,0,"'.$filename.'","")';
            $insertAttachQuery = mysqli_query($link, $insertAttach) or die(mysqli_error($link)." Q=".$insertAttach);



            /*
             * CREATE USER DIRECTORY IF NOT EXIST
             */
            $dirArray = str_split($userid);
            $dirString = '';
            foreach($dirArray as $dir){
                if (!file_exists('forum_path-attachments/'.$dirString.'/'.$dir.'')) {
                    mkdir('forum_path-attachments'.$dirString.'/'.$dir.'', 0777, true);
                    echo 'making directory: forum_path-attachments'.$dirString.'/'.$dir.'';
                }
                $dirString = $dirString.'/'.$dir;
            }

            /*
             * MOVE OLD FILE TO NEW ATTACH FOLDER STRUCTURE
             */
            if( !file_exists('forum_path-attachments/'.$dirString.'/'.$filedata_id.'.attach') && file_exists('forum_path/photoplog/images/'.$userid.'/'.$filename) ){
                copy('forum_path/photoplog/images/'.$userid.'/'.$filename.'', 'community-attachments/'.$dirString.'/'.$filedata_id.'.attach');
            }else{
                echo 'Image already exists no need to create it again<br/>';
            }



            /*
            * INSERT IMAGE INTO CLOSURE
            */
            $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("'.$album_id.'","'.$image_node_id.'",1,0,"'.$now.'")';
            $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);

            echo 'Insert Closure '.$album_id.' - '.$image_node_id.'<br/>';

            $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("'.$image_node_id.'","'.$image_node_id.'",0,0,"'.$now.'")';
            $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);

            echo 'Insert Closure '.$image_node_id.' - '.$image_node_id.'<br/>';



            $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("9","'.$image_node_id.'",2,0,"'.$now.'")';
            $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);

            $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("6","'.$image_node_id.'",3,0,"'.$now.'")';
            $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);

            $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("1","'.$image_node_id.'",4,0,"'.$now.'")';
            $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);



            echo '<br/><br/>';

        }else{
            echo 'forum_path/photoplog/images/'.$userid.'/'.$filename.' Does not exist<br/>';
        }
    }
}

echo '<br/><br/> DONE';

echo '<br/><br/>';
