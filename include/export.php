<?php

	require_once "configuration.php";	
    require_once "class.DatabaseHelper.php";
    require_once "class.Storage.php";
    
    /*
        set true for base64 encoding binary files in json output file, else local paths will be stored
        WARNING: output file will have a INSANE size
    */
    define("EXPORT_BASE64BIN", FALSE);

	echo "homedocs export tool" , PHP_EOL;
    
    if (count($argv) != 2)
    {
        echo "Error: no export file specified";
    }
    else
    {
        $out_file = $argv[1];
        if (file_exists($out_file)) {
            unlink($out_file);
        }
        echo "Exporting users...";
        $sql = " SELECT HEX(ID) AS id, EMAIL AS email, PASSWORD AS password FROM USER ORDER BY email ";
        $rows = Database::exec_with_result_export($sql, array());
        $total_users = count($rows);
        if ($total_users > 0)
        {
            $str = "/* users */ " . PHP_EOL . PHP_EOL . json_encode($rows,JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;
            file_put_contents($out_file, $str, FILE_APPEND);
        }
        echo "(" , $total_users , ") ok!" , PHP_EOL;        
        echo "Exporting documents...";
        $sql = " SELECT HEX(ID) AS id, TITLE AS title, DESCRIPTION AS description, CREATED AS creationDate, HEX(USER_ID) AS userId, DTAGS.tags AS tags FROM DOCUMENT
        LEFT JOIN (SELECT document_id, GROUP_CONCAT(TAG) AS tags FROM DOCUMENT_TAG) DTAGS ON DTAGS.document_id = DOCUMENT.id ORDER BY CREATED ";
        $rows = Database::exec_with_result_export($sql, array());
        $total_documents = count($rows);
        if ($total_documents > 0)
        {
            for ($i = 0; $i < $total_documents; $i++)
            {
                $document_id = $rows[$i]["id"];
                $sql = " SELECT HEX(FILE.ID) AS id, FILE.SHA1_HASH AS sha1, FILE.NAME AS filename, FILE.UPLOADED AS uploadedDate, FILE.SIZE AS size FROM DOCUMENT_FILE INNER JOIN FILE ON FILE.ID = DOCUMENT_FILE.FILE_ID WHERE DOCUMENT_FILE.DOCUMENT_ID = UNHEX(:DOCUMENT_ID) ORDER BY FILE.NAME ";
                $rows2 = Database::exec_with_result_export($sql, 
                    array(
                        DatabaseParam::str(":DOCUMENT_ID", $document_id)
                    )
                );
                $total_files = count($rows2);
                if ($total_files > 0)
                {
                    
                    for ($j = 0; $j < $total_files; $j++)
                    {
                        $thumb_path = Storage::get_thumbnail_local_path($rows2[$j]["sha1"]);
                        $file_path = Storage::get_storage_path($rows2[$j]["sha1"]);
                        $rows2[$j]["thumbLocalPath"] = $thumb_path;
                        $rows2[$j]["localPath"] = $file_path;
                        if (EXPORT_BASE64BIN)
                        {
                            if (file_exists($thumb_path))
                            {
                                $rows2[$j]["base64Thumb"] = base64_encode(file_get_contents($thumb_path));
                            } else
                            {
                                $rows2[$j]["base64Thumb"] = null;
                            }
                            if (file_exists($file_path))
                            {
                                $rows2[$j]["base64Contents"] = base64_encode(file_get_contents($file_path));
                            } else
                            {
                                $rows2[$j]["base64Contents"] = null;
                            }                            
                        }
                        $rows[$i]["files"][] = $rows2;
                    }                    
                } else
                {
                    $rows[$i]["files"] = array();
                }                
            }
            $str = "/* documents */ " . PHP_EOL . PHP_EOL . json_encode($rows, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;
            file_put_contents($out_file, $str, FILE_APPEND);
        }
        echo "(" , $total_documents , ") ok!" , PHP_EOL;        
    }    
?>
