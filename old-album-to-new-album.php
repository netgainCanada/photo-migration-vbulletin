<?php

/*
 * NOT FOR PHOTOPLOG MIRGRATION - THIS IS FOR USERS WITH MISSING ALBUMS AND ALBUM IMAGES
 */

//HOST, USER, PASS, NAME
$link = mysqli_connect('host','dbuser','dbpass','dbname') or die("Error " . mysqli_error($link));

//USE IF SERVER CAN RUN ALL QUERIES
$userquery = 'SELECT DISTINCT userid FROM picture ';

//USE IF SERVER CAN'T RUN ALL QUERIES AND YOU NEED TO BREAK IT UP BY OFFSET AND RUN SCRIPT MULTIPLE TIMES
//$userquery = 'SELECT DISTINCT userid FROM picture LIMIT 10 OFFSET 0';
$userresult = mysqli_query($link, $userquery) or die(mysqli_error($link)." Q=".$userquery);


/*
 * LOOP ALL THE USERS
 */
while($user = mysqli_fetch_array($userresult)){
    //GET USER ID
    $userid = $user['userid'];


    //SELECT ALL DATA FROM NODE
    $albumquery = 'SELECT * FROM node WHERE userid = '.$userid.' AND parentid = 9';
    $albumresults = mysqli_query($link, $albumquery) or die(mysqli_error($link)." Q=".$albumquery);

    //LOOP ALBUM TO GET NODE INFO
    while($albumdata = mysqli_fetch_array($albumresults)){
        //GET NODEID, PUBLISH DATE, URL && NEW URL
        $albumid = $albumdata['nodeid'];
        $username = $albumdata['authorname'];
        $publishdate = $albumdata['publishdate'];
        $url = $albumdata['urlident'];
        $newurl = 'new-albums-'.$userid;
        $routeid = $albumdata['routeid'];
        $lastcontentdate = $albumdata['lastcontent'];

        echo 'I am the album <strong>'.$albumid.'</strong><br/><br/>';


        //UPDATE urlident FROM NULL TO NEW URL
        if($url == NULL || $url == ''){
            $updateStarter = 'UPDATE node SET urlident = "'.$newurl.'" WHERE nodeid = '.$albumid;
            $updateStarterQuery = mysqli_query($link, $updateStarter) or die(mysqli_error($link)." Q=".$updateStarter);
        }

        //UPDATE routeid
        if($routeid == '51'){
            $updateRoute = 'UPDATE node SET routeid = "52" WHERE nodeid = '.$albumid;
            $updateRouteQuery = mysqli_query($link, $updateRoute) or die(mysqli_error($link)." Q=".$updateRoute);
        }

        //UPDATE LAST CONTENT DATE
        if($lastcontentdate == '' || $lastcontentdate == 0 || $lastcontentdate == NULL){
            $updateLastContent = 'UPDATE node SET lastcontent = "'.$publishdate.'" WHERE nodeid = '.$albumid;
            $updateLastContentQuery = mysqli_query($link, $updateLastContent) or die(mysqli_error($link)." Q=".$updateLastContent);
        }

        //INSERT ALBUM INTO CLOSURE
        $selectClosure = 'SELECT * FROM closure WHERE parent = '.$albumid.' AND child = '.$albumid.'';
        $selectresults = mysqli_query($link, $selectClosure) or die(mysqli_error($link)." Q=".$selectClosure);

        if(mysqli_num_rows($selectresults) == 0){
            $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("'.$albumid.'","'.$albumid.'",0,0,"'.$publishdate.'")';
            $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);
        }

        /*******/

        $selectClosure = 'SELECT * FROM closure WHERE parent = 9 AND child = '.$albumid.'';
        $selectresults = mysqli_query($link, $selectClosure) or die(mysqli_error($link)." Q=".$selectClosure);

        if(mysqli_num_rows($selectresults) == 0){
            $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("9","'.$albumid.'",1,0,"'.$publishdate.'")';
            $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);
        }

        /*******/

        $selectClosure = 'SELECT * FROM closure WHERE parent = 6 AND child = '.$albumid.'';
        $selectresults = mysqli_query($link, $selectClosure) or die(mysqli_error($link)." Q=".$selectClosure);

        if(mysqli_num_rows($selectresults) == 0){
            $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("6","'.$albumid.'",2,0,"'.$publishdate.'")';
            $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);
        }

        /*******/

        $selectClosure = 'SELECT * FROM closure WHERE parent = 1 AND child = '.$albumid.'';
        $selectresults = mysqli_query($link, $selectClosure) or die(mysqli_error($link)." Q=".$selectClosure);

        if(mysqli_num_rows($selectresults) == 0){
            $insertClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("1","'.$albumid.'",3,0,"'.$publishdate.'")';
            $insertQuery = mysqli_query($link, $insertClosure) or die(mysqli_error($link)." Q=".$insertClosure);
        }


        /**************************************************************/


        //QUERY & LOOP PICTURES FOR SPECIFIC USER ALBUM
        $pictable = 'SELECT * FROM node
            LEFT JOIN photo ON(
                photo.nodeid = node.nodeid
            )
            LEFT JOIN attachment ON(
                attachment.filedataid = photo.filedataid
            )
            LEFT JOIN picturelegacy ON(
                picturelegacy.attachmentid = attachment.attachmentid
            )
            LEFT JOIN picture ON(
                picture.pictureid = picturelegacy.pictureid
            )
            WHERE node.parentid = '.$albumid.' AND node.userid = '.$userid;
        $picresult = mysqli_query($link, $pictable) or die(mysqli_error($link)." Q=".$pictable);

        while($pic = mysqli_fetch_array($picresult)){
            $filenode = $pic['nodeid'];
            $picid = $pic['pictureid'];
            $filedataid = $pic['filedataid'];
            $filedata = $pic['filedata'];
            $ext = $pic['extension'];
            $filename = $pic['filename'];

            if($filedata != ''){
                //INSERT INTO ATTACH TABLE
                $selectAttach = 'SELECT * FROM attach WHERE nodeid = '.$filenode.' AND filedataid = '.$filedataid.'';
                $attachresults = mysqli_query($link, $selectAttach) or die(mysqli_error($link)." Q=".$selectAttach);

                if(mysqli_num_rows($attachresults) == 0){
                    $insertAttach = 'INSERT INTO attach(nodeid,filedataid,visible,counter,filename,caption) VALUES("'.$filenode.'", "'.$filedataid.'",1,0,"'.$filename.'","")';
                    $insertAttachQuery = mysqli_query($link, $insertAttach) or die(mysqli_error($link)." Q=".$insertAttach);
                }


                //UPDATE EXTENSION
                if($ext == 'png'){
                    $updateFiledata = 'UPDATE filedata SET extension = "jpg" WHERE filedataid = '.$filedataid;
                    $updateFiledataQuery = mysqli_query($link, $updateFiledata) or die(mysqli_error($link)." Q=".$updateFiledata);

                    echo 'Update PNG to JPG '.$filedataid.'<br/>';
                }

                /********************************************/


                //CREATE USER DIRECTORY IF NOT EXIST
                $dirArray = str_split($userid);
                $dirString = '';
                foreach($dirArray as $dir){
                    if (!file_exists('forum_path-attachments/'.$dirString.'/'.$dir.'')) {
                        mkdir('forum_path-attachments'.$dirString.'/'.$dir.'', 0777, true);
                    }
                    $dirString = $dirString.'/'.$dir;
                }

                // MOVE OLD FILE TO NEW ATTACH FOLDER STRUCTURE
                if(!file_exists('forum_path-attachments/'.$dirString.'/'.$filedataid.'.attach') && $filedata != ''){
                    file_put_contents('forum_path-attachments'.$dirString.'/'.$filedataid.'.attach', $filedata);
                }


                /********************************************/


                //INSERT INTO FILENODEID INTO CLOSURE WITH ALBUMID AS PARENT
                $selectAlbumClosure = 'SELECT * FROM closure WHERE parent = '.$albumid.' AND child = '.$filenode.'';
                $selectAlbumresults = mysqli_query($link, $selectAlbumClosure) or die(mysqli_error($link)." Q=".$selectAlbumClosure);

                if(mysqli_num_rows($selectAlbumresults) == 0){
                    $insertAlbumClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("'.$albumid.'","'.$filenode.'",1,0,"'.$publishdate.'")';
                    $insertAlClQuery = mysqli_query($link, $insertAlbumClosure) or die(mysqli_error($link)." Q=".$insertAlbumClosure);
                }

                /****/

                //INSERT INTO FILENODEID INTO CLOSURE WITH FILENODEID AS PARENT
                $selectFileClosure = 'SELECT * FROM closure WHERE parent = '.$filenode.' AND child = '.$filenode.'';
                $selectFileresults = mysqli_query($link, $selectFileClosure) or die(mysqli_error($link)." Q=".$selectFileClosure);

                if(mysqli_num_rows($selectFileresults) == 0){
                    $insertFileClosure = 'INSERT INTO closure(parent,child,depth,displayorder,publishdate) VALUES("'.$filenode.'","'.$filenode.'",0,0,"'.$publishdate.'")';
                    $insertFiClQuery = mysqli_query($link, $insertFileClosure) or die(mysqli_error($link)." Q=".$insertFileClosure);
                }
            }
        }
    }

    echo '<br/>DONE<br/>';
}