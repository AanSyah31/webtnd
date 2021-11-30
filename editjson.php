<?php
// load file
$namaJson       = 'data_modul.json';
$pindahLokasi   = 'Location:crud_jsondata_datamodul.php';

$data = file_get_contents($namaJson);

// decode json to associative array
$json_arr = json_decode($data, true);

$id = $_GET['id'];

$updateId           = '';
$updateMedia        = '';
$updateLink         = '';
$updateThumbnail    = '';
$updateTitle        = '';
$updateTag          = '';
$updateKategori     = '';
$saveDbTag          = '';
$indexjson          = 0;
$indexHapus         = 0;

//ALERT THINGS
if(isset($_GET['statusEdit']))
{
    ////VIDEO LINK KOSONG
    if($_GET['statusEdit'] == 0)
    {
        echo '<script>
            alert("Link Harus Diisi");
        </script>';
    }
    else if($_GET['statusEdit'] == 1)
    {
        echo '<script>
            alert("Title Harus Diisi");
        </script>';
    }
    else if($_GET['statusEdit'] == 2)
    {
        echo '<script>
            alert("Size Thumbnail Terlalu Besar");
        </script>';
    } 
    else if($_GET['statusEdit'] == 3)
    {
        echo '<script>
            alert("Judul Tidak Boleh Sama Dengan Yang Lain");
        </script>';
    } 
}
//

for ($i = 0; $i < count($json_arr['db_modul']); $i++) {
    if ($json_arr['db_modul'][$i]['id'] == $id) {
        $indexHapus         = $i;
        $updateId           = $json_arr['db_modul'][$i]['id'];
        $updateMedia        = $json_arr['db_modul'][$i]['db_media'];
        $updateLink         = $json_arr['db_modul'][$i]['link'];
        $updateThumbnail    = $json_arr['db_modul'][$i]['thumbnail'];
        $updateTitle        = $json_arr['db_modul'][$i]['judul'];
        foreach ($json_arr['db_modul'][$i]['db_tag'] as $arr) {
            foreach ($arr as $index => $value) {
                $updateTag .= $value . ', ';
            }
        }
        
        $newArrDbTag        = explode(',', $updateTag);

        $saveDbTag = '[';
        foreach ($newArrDbTag as $value) {
            $saveDbTag .= '{"tag":"' . $value . '"},';
        }
        $saveDbTag  = substr($saveDbTag, 0, -1);
        $saveDbTag .= ']';

        $saveDbTag = str_replace('\\', '', $saveDbTag);
        $updateKategori     = $json_arr['db_modul'][$i]['kategori'];
    }
    $indexjson = $json_arr['db_modul'][$i]['id'];
}
if(isset($_POST['update']))
{    
    $updateId       = $indexjson + 2;
    if($updateMedia == 'video')
    {
        //Video Link Kosong
        if(empty($_POST['link']))
        {
            $pindahLokasi .= '?id='.$id.'&statusEdit=0';
            header($pindahLokasi);
        }
        //--

        //VIDEO DAN THUMBNAIL
        if(!empty($_POST['link']))
        {
            //APABILA THUMBNAIL KOSONG
            if($_FILES['thumbnail']['size'] == 0)
            {
                //1. CEK TITLE TIDAK KOSONG DULU
                if(empty($_POST['title']))
                {
                    $pindahLokasi .= '?id='.$id.'&statusEdit=1';
                    header($pindahLokasi);
                }
                if(!empty($_POST['title']))
                {
                    //2. KALO TITLE TIDAK KOSONG
                    $oldNameThumbnail = 'C:\xampp\htdocs\crud_jsondata\thumbnail2' . DIRECTORY_SEPARATOR . $updateThumbnail.'.jpg';
                    //$oldNameThumbnail = 'thumbnail2' . DIRECTORY_SEPARATOR . $updateThumbnail.'.jpg';
                    
                    $newNameThumbnail = 'C:\xampp\htdocs\crud_jsondata\thumbnail2'. DIRECTORY_SEPARATOR . strtolower(str_replace(' ','_',$_POST['title'])).'.jpg';
                    //$newNameThumbnail = 'thumbnail2'. DIRECTORY_SEPARATOR . strtolower(str_replace(' ','_',$_POST['title'])).'.jpg';

                    // echo $oldNameThumbnail;                 
                    $statusTitle = 0;

                    for ($i = 0; $i < count($json_arr['db_modul']); $i++) {
                        if ($json_arr['db_modul'][$i]['judul'] == $_POST['title'] && $json_arr['db_modul'][$i]['id'] != $id)
                        {
                            $statusTitle = 1;
                        }
                    }
                    if($statusTitle == 1)
                    {
                        $pindahLokasi .= '?id='.$id.'&statusEdit=3';
                        header($pindahLokasi);
                    }
                    else if($statusTitle == 0)
                    {                        
                        $renameFile = rename($oldNameThumbnail, $newNameThumbnail);
                        
                        unset($json_arr['db_modul'][$indexHapus]);

                        $updateLink         = $_POST['link'];
                        $updateThumbnail    = strtolower(str_replace(' ','_',$_POST['title']));
                        $updateTitle        = $_POST['title'];
                        $updateKategori     = $_POST['kategori'];
                        $updateTag          = $_POST['tag'];
                        
                        $newArrDbTag        = explode(',', $updateTag);

                        $saveDbTag = '[';
                        foreach ($newArrDbTag as $value) {
                            $saveDbTag .= '{"tag":"' . $value . '"},';
                        }
                        $saveDbTag  = substr($saveDbTag, 0, -1);
                        $saveDbTag .= ']';

                        $saveDbTag = str_replace('\\', '', $saveDbTag);

                        $json_arr['db_modul'][] = array('id'=>"$updateId", 'db_media'=>$updateMedia, 'link'=>$updateLink, 'thumbnail'=>$updateThumbnail, 'judul'=>$updateTitle, 'kategori'=>$updateKategori, 'db_tag'=>$saveDbTag);
                        
                        $json_arr['db_modul'] = array_values($json_arr['db_modul']);
                        
                        $jsonString = json_encode($json_arr);
                        $jsonString = str_replace('\\', "", $jsonString);
                        $jsonString = str_replace('"[', "[", $jsonString);
                        $jsonString = str_replace(']"', "]", $jsonString);

                        unlink($namaJson);
                        $file = fopen($namaJson, 'w');    
                        fwrite($file, $jsonString);
                        header($pindahLokasi);
                    }                      
                }
            }
            //THUMBNAIL TIDAK KOSONG
            if($_FILES['thumbnail']['size'] != 0)
            {
                //CEK THUMBNAIL BESAR
                if($_FILES['thumbnail']['size'] > (0.2 * 1048576))
                {                    
                    $pindahLokasi .= '?id='.$id.'&statusEdit=2';
                    header($pindahLokasi);
                }
                //THUMBNAIL STANDARD
                else
                {
                    if(empty($_POST['title']))
                    {
                        $pindahLokasi .= '?id='.$id.'&statusEdit=1';
                        header($pindahLokasi);
                    }
                    if(!empty($_POST['title']))
                    {
                        //1. HAPUS FILE LAMA
                        $hapusThumbnailLama = unlink('thumbnail2/'.$updateThumbnail.'.jpg');
                        if($hapusThumbnailLama)
                        {
                            $updateLink         = $_POST['link'];
                            $updateThumbnail    = strtolower(str_replace(' ','_',$_POST['title']));
                            $updateTitle        = $_POST['title'];
                            $updateKategori     = $_POST['kategori'];
                            $updateTag          = $_POST['tag'];

                            $newArrDbTag        = explode(',', $updateTag);

                            $saveDbTag = '[';
                            foreach ($newArrDbTag as $value) {
                                $saveDbTag .= '{"tag":"' . $value . '"},';
                            }
                            $saveDbTag  = substr($saveDbTag, 0, -1);
                            $saveDbTag .= ']';

                            $saveDbTag = str_replace('\\', '', $saveDbTag);

                            move_uploaded_file($_FILES['thumbnail']['tmp_name'], 'thumbnail2/'.$updateThumbnail.'.jpg');
                                                            
                            unset($json_arr['db_modul'][$indexHapus]);

                            $json_arr['db_modul'][] = array('id'=>"$updateId", 'db_media'=>$updateMedia, 'link'=>$updateLink, 'thumbnail'=>$updateThumbnail, 'judul'=>$updateTitle, 'kategori'=>$updateKategori, 'db_tag'=>$saveDbTag);
                    
                            $json_arr['db_modul'] = array_values($json_arr['db_modul']);
                            
                            $jsonString = json_encode($json_arr);
                            $jsonString = str_replace('\\', "", $jsonString);
                            $jsonString = str_replace('"[', "[", $jsonString);
                            $jsonString = str_replace(']"', "]", $jsonString);

                            unlink($namaJson);
                            $file = fopen($namaJson, 'w');    
                            fwrite($file, $jsonString);
                            header($pindahLokasi);
                        }
                    }
                }                
            }
        }
    }
    else if($updateMedia == 'pdf')
    {
        //cek Title kosong
        if(empty($_POST['title']))
        {
            echo '<script>
                alert("Judul Tidak Boleh Kosong");
            </script>';
        }
        else
        {
            $statusTitle = 0;
            for ($i = 0; $i < count($json_arr['db_modul']); $i++) {
                if ($json_arr['db_modul'][$i]['judul'] == $_POST['title'] && $json_arr['db_modul'][$i]['id'] != $id)
                {
                    $statusTitle = 1;
                }
            }
            //CEK title Sudah ada
            if($statusTitle == 1)
            {
                echo '<script>
                    alert("Judul Tidak Boleh Sama");
                </script>';
            }
            //Title blm ada
            else
            {
                //CEK PDF TIDAK ADA
                if($_FILES['linkPdf']['size'] == 0)
                {
                    //CEK THUMBNAIL KOSONG
                    if($_FILES['thumbnail']['size'] == 0)
                    {
                        //RENAME MODUL LAMA
                        $oldNameModul = 'C:\xampp\htdocs\crud_jsondata\modul2' . DIRECTORY_SEPARATOR . $updateLink.'.pdf';
                        $newNameModul = 'C:\xampp\htdocs\crud_jsondata\modul2'. DIRECTORY_SEPARATOR . strtolower(str_replace(' ','_',$_POST['title'])).'.pdf';
                        rename($oldNameModul, $newNameModul);

                        //RENAME THUMBNAIL LAMA
                        $oldNameThumbnail = 'C:\xampp\htdocs\crud_jsondata\thumbnail2' . DIRECTORY_SEPARATOR . $updateThumbnail.'.jpg';                        
                        $newNameThumbnail = 'C:\xampp\htdocs\crud_jsondata\thumbnail2'. DIRECTORY_SEPARATOR . strtolower(str_replace(' ','_',$_POST['title'])).'.jpg';
                        rename($oldNameThumbnail, $newNameThumbnail);

                        unset($json_arr['db_modul'][$indexHapus]);

                        $updateLink         = strtolower(str_replace(' ','_',$_POST['title']));
                        $updateThumbnail    = strtolower(str_replace(' ','_',$_POST['title']));
                        $updateTitle        = $_POST['title'];
                        $updateKategori     = $_POST['kategori'];
                        $updateTag          = $_POST['tag'];
                        
                        $newArrDbTag        = explode(',', $updateTag);

                        $saveDbTag = '[';
                        foreach ($newArrDbTag as $value) {
                            $saveDbTag .= '{"tag":"' . $value . '"},';
                        }
                        $saveDbTag  = substr($saveDbTag, 0, -1);
                        $saveDbTag .= ']';

                        $saveDbTag = str_replace('\\', '', $saveDbTag);

                        $json_arr['db_modul'][] = array('id'=>"$updateId", 'db_media'=>$updateMedia, 'link'=>$updateLink, 'thumbnail'=>$updateThumbnail, 'judul'=>$updateTitle, 'kategori'=>$updateKategori, 'db_tag'=>$saveDbTag);
                        
                        $json_arr['db_modul'] = array_values($json_arr['db_modul']);
                        
                        $jsonString = json_encode($json_arr);
                        $jsonString = str_replace('\\', "", $jsonString);
                        $jsonString = str_replace('"[', "[", $jsonString);
                        $jsonString = str_replace(']"', "]", $jsonString);

                        unlink($namaJson);
                        $file = fopen($namaJson, 'w');    
                        fwrite($file, $jsonString);
                        header($pindahLokasi);

                    }
                }
                else
                {
                    //File == PDF
                    if($_FILES['linkPdf']['type'] != 'application/pdf')
                    {
                        echo '<script>
                            alert("Modul Harus PDF");
                        </script>';
                    }
                    else
                    {
                        if($_FILES['linkPdf']['size'] > (5 * 1048576))
                        {
                            echo '<script>
                                alert("Modul Harus Kurang Dari 5mb");
                            </script>';
                        }
                        else
                        {
                            if($_FILES['thumbnail']['size'] == 0)
                            {
                                //Hapus Pdf Lama
                                $oldNameModul = 'C:\xampp\htdocs\crud_jsondata\modul2' . DIRECTORY_SEPARATOR . $updateLink.'.pdf';
                                unlink($oldNameModul);

                                //Upload Pdf Baru
                                $uploadNewModul = strtolower(str_replace(' ','_',$_POST['title']));
                                move_uploaded_file($_FILES['linkPdf']['tmp_name'], 'modul2/'.$uploadNewModul.'.pdf');

                                //rename thumbnail
                                $oldNameThumbnail = 'C:\xampp\htdocs\crud_jsondata\thumbnail2' . DIRECTORY_SEPARATOR . $updateThumbnail.'.jpg';                        
                                $newNameThumbnail = 'C:\xampp\htdocs\crud_jsondata\thumbnail2'. DIRECTORY_SEPARATOR . strtolower(str_replace(' ','_',$_POST['title'])).'.jpg';
                                rename($oldNameThumbnail, $newNameThumbnail);

                                unset($json_arr['db_modul'][$indexHapus]);

                                $updateLink         = strtolower(str_replace(' ','_',$_POST['title']));
                                $updateThumbnail    = strtolower(str_replace(' ','_',$_POST['title']));
                                $updateTitle        = $_POST['title'];
                                $updateKategori     = $_POST['kategori'];
                                $updateTag          = $_POST['tag'];
                                
                                $newArrDbTag        = explode(',', $updateTag);

                                $saveDbTag = '[';
                                foreach ($newArrDbTag as $value) {
                                    $saveDbTag .= '{"tag":"' . $value . '"},';
                                }
                                $saveDbTag  = substr($saveDbTag, 0, -1);
                                $saveDbTag .= ']';

                                $saveDbTag = str_replace('\\', '', $saveDbTag);

                                $json_arr['db_modul'][] = array('id'=>"$updateId", 'db_media'=>$updateMedia, 'link'=>$updateLink, 'thumbnail'=>$updateThumbnail, 'judul'=>$updateTitle, 'kategori'=>$updateKategori, 'db_tag'=>$saveDbTag);
                                
                                $json_arr['db_modul'] = array_values($json_arr['db_modul']);
                                
                                $jsonString = json_encode($json_arr);
                                $jsonString = str_replace('\\', "", $jsonString);
                                $jsonString = str_replace('"[', "[", $jsonString);
                                $jsonString = str_replace(']"', "]", $jsonString);

                                unlink($namaJson);
                                $file = fopen($namaJson, 'w');    
                                fwrite($file, $jsonString);
                                header($pindahLokasi);
                            }
                            else
                            {
                                //CEK THUMBNAIL BUKAN JPG
                                if($_FILES['thumbnail']['type'] != 'image/jpeg')
                                {
                                    echo '<script>
                                        alert("Thumbnail Harus Jpg");
                                    </script>';
                                }
                                else
                                {
                                    if($_FILES['thumbnail']['size'] > (5 * 1048576))
                                    {
                                        echo '<script>
                                            alert("Thumbnail Harus Kurang Dari 200 KB");
                                        </script>';
                                    }
                                    else
                                    {
                                        //Hapus Pdf Lama
                                        $oldNameModul = 'C:\xampp\htdocs\crud_jsondata\modul2' . DIRECTORY_SEPARATOR . $updateLink.'.pdf';
                                        unlink($oldNameModul);

                                        //Hapus Thumbnail Lama
                                        $oldNameThumbnail = 'C:\xampp\htdocs\crud_jsondata\thumbnail2' . DIRECTORY_SEPARATOR . $updateThumbnail.'.jpg';                        
                                        unlink($oldNameThumbnail);

                                        //upload Pdf Baru
                                        $uploadNewModul = strtolower(str_replace(' ','_',$_POST['title']));
                                        $extModul       = pathinfo($_FILES['linkPdf']['name'], PATHINFO_EXTENSION);
                                        move_uploaded_file($_FILES['linkPdf']['tmp_name'], 'modul2/'.$uploadNewModul.'.'.strtolower($extModul));

                                        //Upload Thumbnail Baru
                                        $uploadNewThumbnail = strtolower(str_replace(' ','_',$_POST['title']));
                                        $extThumbnail       = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
                                        move_uploaded_file($_FILES['thumbnail']['tmp_name'], 'thumbnail2/'.$uploadNewThumbnail.'.'.strtolower($extThumbnail));
                                        
                                        unset($json_arr['db_modul'][$indexHapus]);

                                        $updateLink         = strtolower(str_replace(' ','_',$_POST['title']));
                                        $updateThumbnail    = strtolower(str_replace(' ','_',$_POST['title']));
                                        $updateTitle        = $_POST['title'];
                                        $updateKategori     = $_POST['kategori'];
                                        $updateTag          = $_POST['tag'];
                                        
                                        $newArrDbTag        = explode(',', $updateTag);

                                        $saveDbTag = '[';
                                        foreach ($newArrDbTag as $value) {
                                            $saveDbTag .= '{"tag":"' . $value . '"},';
                                        }
                                        $saveDbTag  = substr($saveDbTag, 0, -1);
                                        $saveDbTag .= ']';

                                        $saveDbTag = str_replace('\\', '', $saveDbTag);

                                        $json_arr['db_modul'][] = array('id'=>"$updateId", 'db_media'=>$updateMedia, 'link'=>$updateLink, 'thumbnail'=>$updateThumbnail, 'judul'=>$updateTitle, 'kategori'=>$updateKategori, 'db_tag'=>$saveDbTag);
                                        
                                        $json_arr['db_modul'] = array_values($json_arr['db_modul']);
                                        
                                        $jsonString = json_encode($json_arr);
                                        $jsonString = str_replace('\\', "", $jsonString);
                                        $jsonString = str_replace('"[', "[", $jsonString);
                                        $jsonString = str_replace(']"', "]", $jsonString);

                                        unlink($namaJson);
                                        $file = fopen($namaJson, 'w');    
                                        fwrite($file, $jsonString);
                                        header($pindahLokasi);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
$src    = 'thumbnail2/'.$updateThumbnail.'.jpg';
$srcPdf = 'modul2/'.$updateLink.'.pdf'; 


?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Data Youtube</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="dist/img/favicon.ico">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="dist/css/ionicons.min.css">
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="dist/css/jquery.fancybox.min.css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="dist/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" type="text/css" href="dist/css/buttons.dataTables.min.css" />


    <style>
        .gallery img {
            width: 20%;
            height: auto;
            border-radius: 5px;
            cursor: pointer;
            transition: .3s;
        }


        .crop {
            width: 400px;
            height: 200px;
            overflow: hidden;
        }

        .crop img {
            width: 400px;
            height: 200px;
            margin: 0;
        }
    </style>
</head>

<body>
    <section class="content mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Tambah Data Youtube</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <input type="hidden" value="<?= $_GET['id'] ?>" name="id">
                                    <?php if($updateMedia == "pdf"):?>
                                        <label for="exampleInputPassword1">Pdf File</label>
                                        <input type="file" class='form-control' name="linkPdf">
                                        <a href="<?=$srcPdf?>" class="btn btn-primary mt-2">Download Pdf</a>
                                    <?php elseif($updateMedia == "video"):?>
                                        <label for="exampleInputPassword1">Video Link</label>
                                        <input type="text" class="form-control" name="link" placeholder="Link" value="<?= $updateLink ?>">
                                        <iframe class="mt-3" width="420" height="315" src="https://www.youtube.com/embed/<?=$updateLink?>">
                                        </iframe>
                                    <?php endif;?>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputPassword1">File Thumbnail</label>
                                    <input type="file" class='form-control' name="thumbnail">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Thumbnail</label><br>
                                    <div class="crop">
                                        <img src="<?=$src?>" clasc='form-control'>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Tag</label>
                                    <input type="text" class="form-control" name="tag" placeholder="Tag" value="<?= substr($updateTag,0,-2) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Title</label>
                                    <input type="text" class="form-control" name="title" placeholder="Title" value="<?= $updateTitle ?>">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Kategori</label>
                                    <input type="text" class="form-control" name="kategori" placeholder="Kategori" value='<?= $updateKategori ?>'>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary" name="update">Update Data</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-4">
                    <table class="table table-bordered table-hover text-center">
                        <thead>
                            <tr>
                                <th scope="col">Jenis</th>
                                <th scope="col">Link</th>
                                <th scope="col">Thumbnail</th>
                                <th scope="col">Title</th>
                                <th scope="col">Tag</th>
                                <th scope="col">Kategori</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php for ($i = 0; $i < count($json_arr['db_modul']); $i++) : ?>
                                <?php if ($json_arr['db_modul'][$i]['id'] == $id) : ?>
                                    <tr>
                                        <td><?= $json_arr['db_modul'][$i]['db_media']; ?></td>
                                        <td><?= $json_arr['db_modul'][$i]['link']; ?></td>
                                        <td><?= $json_arr['db_modul'][$i]['thumbnail']; ?></td>
                                        <td><?= $json_arr['db_modul'][$i]['judul']; ?></td>
                                        <td>
                                            <?php
                                            $json_arr2 = json_encode($json_arr['db_modul'][$i]['db_tag'], true);

                                            $json_arr3 = json_decode($json_arr2, true);

                                            $saveString = '';
                                            foreach ($json_arr3 as $arr) {
                                                foreach ($arr as $index => $value) {
                                                    $saveString .= $value . ', ';
                                                }
                                            }

                                            $saveString = substr($saveString, 0, -2);
                                            echo $saveString;
                                            ?>
                                        </td>
                                        <td><?= $json_arr['db_modul'][$i]['kategori']; ?></td>
                                        <?php $no += 1; ?>
                                    </tr>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>



    <script src="dist/jquery/jquery.min.js"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script>                                         -->
    <script src="dist/js_tabel/jquery-3.5.1.js"></script>
    <script src="dist/js_tabel/jquery.dataTables.min.js"></script>
    <script src="dist/js_tabel/dataTables.buttons.min.js"></script>
    <script src="dist/js_tabel/buttons.flash.min.js"></script>
    <script src="dist/js_tabel/jszip.min.js"></script>
    <script src="dist/js_tabel/pdfmake.min.js"></script>
    <script src="dist/js_tabel/vfs_fonts.js"></script>
    <script src="dist/js_tabel/buttons.html5.min.js"></script>
    <script src="dist/js_tabel/buttons.print.min.js"></script>
    <script>
        $(function() {
            $('#taksasi').DataTable({

                "searching": false,
                buttons: [],
                dom: 'Bfrtip',
            });
        })
    </script>
</body>

</html>