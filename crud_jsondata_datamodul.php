<?php
// load file
//$data = file_get_contents('backup_y2.json');
$namaJson       = 'data_modul.json';
$pindahLokasi   = 'Location:crud_jsondata_datamodul.php';

$data = file_get_contents($namaJson);

// decode json to associative array
$json_arr = json_decode($data, true);

//EDIT
if(isset($_GET['statusEdit']))
{
    $pindahEdit = 'Location:editjson.php?id='.$_GET['id'].'&statusEdit='.$_GET['statusEdit'];
    header($pindahEdit);
}
//--

if(isset($_GET['pdf']))
{
    echo '<script>
        alert("Pdf Harus Kurang Dari 10mb");
    </script>';
}

if(isset($_GET['pdfUpdateCheck']))
{
    $pindahLokasi = 'Location:editjson.php?id='.$_GET['id'].'&pdfUpdateCheck='.$_GET['pdfUpdateCheck'];
    header($pindahLokasi);
}

//Alert video link kosong
if(isset($_GET['videoCheck']))
{
    $videoCheck = $_GET['videoCheck'];
    if($videoCheck == 0)
    {        
        echo '<script>
            alert("Masukan Link Video");
        </script>';
    }
}

//ALert Thumbnail Bukan jpg
if(isset($_GET['thumbnailCheck']))
{
    $thumbnailCheck = $_GET['thumbnailCheck'];
    if($thumbnailCheck == 0)
    {
        echo '<script>
            alert("Data Thumbnail Harus .jpg");
        </script>';
    }
    else if($thumbnailCheck == 1)
    {
        echo '<script>
            alert("Size Foto Harus Kurang Dari 5mb");
        </script>';
    }
}

//Alert Pdf Blm Ada isinya
if(isset($_GET['pdfCheck']))
{
    $pdfCheck = $_GET['pdfCheck'];
    if($pdfCheck == 0)
    {
        echo '<script>
            alert("Masukan File Pdf");
        </script>';
    }
    else if($pdfCheck == 1)
    {
        echo '<script>
            alert("Modul Harus File .pdf");
        </script>';
    }
    else if($pdfCheck == 2){
        echo '<script>
            alert("Ukuran Modul Harus Kurang Dari 10 mb");
        </script>';
    }
}

//Alert belum pilih Media
if(isset($_GET['mediaCheck']))
{
    $mediaCheck = $_GET['mediaCheck'];
    if($mediaCheck == 0)
    {
        echo '<script>
            alert("Pilih Media");
        </script>';
    }
}

//ALert Berhasil Memasukan Data
if(isset($_GET['create']))
{
    $createCheck = $_GET['create'];
    if($createCheck == 1)
    {
        echo '<script>
            alert("Berhasil Memasukan Data Baru");
        </script>';
    }
}

//ALert Gagal Upload PDF
if(isset($_GET['uploadPdf']))
{
    $uploadPdf = $_GET['uploadPdf'];
    if($uploadPdf == 0)
    {
        echo '<script>
            alert("Gagal Upload Pdf");
        </script>';
    }
    else if($uploadPdf == 1)
    {
        echo '<script>
            alert("Berhasil Upload Pdf");
        </script>';
    }
}

//ALert Gagal Upload Thumbnail
if(isset($_GET['uploadThumbnail']))
{
    $uploadThumbnail = $_GET['uploadThumbnail'];
    if($uploadThumbnail == 0)
    {
        echo '<script>
            alert("Gagal Upload Thumbnail");
        </script>';
    }
    else if($uploadThumbnail == 1){
        echo '<script>
            alert("Berhasil Upload Thumbnail");
        </script>';
    }
}


if(isset($_POST['submit']))
{
    if(empty($_POST['videoId']) || empty($_POST['judulVideo']) || empty($_POST['tag']) || empty($_POST['kategori']))
    {
        echo '<script>
            alert("Ada Data Yang Belum diisi");
        </script>';
    }
    
    $count = count($json_arr['db_modul']);
    $count = $count - 1;
    $newId = $json_arr['db_modul'][$count]['id'] + 1;

    $newVideoId     = $_POST['videoId'];
    $newJudul       = $_POST['judulVideo']; 
    $newKategori    = $_POST['kategori'];
    $newJenis       = $_POST['media'];
    $newTag         = $_POST['tag'];
    $newLink        = '';
    $newThumbnail   = '';

    $header = 'Location:crud_jsondata_datamodul.php?videoId='.$newVideoId.'&judulVideo='.$newJudul.'&kategori='.$newKategori.'&tag='.$newTag;
    
    $limaMb = 5 * 1048576;    
    $a10mb = $limaMb *2;

    if(!isset($_POST['media']))
    {
        $header .= '&mediaCheck=0';
        header($header);
    }
    
    if(isset($_POST['videoLink']))
    {        
        if($_POST['videoLink'] == '')
        {
            $header .= '&videoCheck=0';
            header($header);
        }
        else
        {
            if(isset($_FILES['thumbnail']))
            {
                //Cek Thumbnail Jpg
                if($_FILES['thumbnail']['type'] !== 'image/jpeg')
                {
                    $header .= '&thumbnailCheck=0';
                    header($header);
                }
                else
                {
                    if($_FILES['thumbnail']['size'] > (1048576 * 0.2))
                    {
                        $header .= '&thumbnailCheck=1';
                        header($header);                    
                    }
                    else
                    {
                        $newLink        = $_POST['videoLink'];
                        $fileThumbnail  = $_FILES['thumbnail']['name'];
                        $ext            = strtolower(pathinfo($fileThumbnail, PATHINFO_EXTENSION));
                        if($ext == 'jpeg')
                        {
                            $ext = 'jpg';
                        }

                        $namaFile       = strtolower(str_replace(' ', '_', $newJudul));
                        $formatNama     = $namaFile.'.'.$ext;
                        $dirUpload      = "thumbnail/";
                        $newThumbnail   = str_replace(' .jpg', '', $namaFile);
                        
                        move_uploaded_file($_FILES['thumbnail']['tmp_name'], $dirUpload.$formatNama); 
                        
                        $newArrDbTag    = explode(',',$newTag);

                        $saveDbTag = '[';
                        foreach ($newArrDbTag as $value) {
                            $saveDbTag .= '{"tag":"'.$value.'"},';
                        }
                        $saveDbTag  = substr($saveDbTag,0,-1);
                        $saveDbTag .= ']'; 
                        
                        $saveDbTag = str_replace('\\', '', $saveDbTag);    
                        
                        $json_arr['db_modul'][] = array('id'=>$newId, 'db_media'=>"$newJenis", 'link'=>$newLink, 'thumbnail'=>$newThumbnail, 'judul'=>$newJudul, 'kategori'=>$newKategori, 'db_tag'=>$saveDbTag);
                        
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
    
    if(isset($_FILES['addFilePdf']))
    {
        if($_FILES['addFilePdf']['size'] == 0)
        {
            $header .= '&pdfCheck=0';
            header($header);
        }
        else
        {
            if($_FILES['addFilePdf']['type'] !== 'application/pdf')
            {
                $header .= '&pdfCheck=1';
                header($header);
            }
            else
            {
                if($_FILES['addFilePdf']['size'] > $limaMb)
                {
                    $header .= '&pdfCheck=2';
                    header($header);
                }
                else
                {
                    if(isset($_FILES['thumbnail']))
                    {
                        //Cek Thumbnail Jpg
                        if($_FILES['thumbnail']['type'] !== 'image/jpeg')
                        {
                            $header .= '&thumbnailCheck=0';
                            header($header);
                        }
                        else
                        {
                            if($_FILES['thumbnail']['size'] > (1048576 * 0.2))
                            {
                                $header .= '&thumbnailCheck=1';
                                header($header);                            
                            }
                            else
                            {        
                                $formatNama     = strtolower(str_replace(' ', '_', $newJudul));                    
                                $newLink        = $formatNama;
                                
                                $ext            = pathinfo($_FILES['addFilePdf']['name'], PATHINFO_EXTENSION);
                                $namaFilePdf    = $formatNama.'.'.$ext;
                                $dirUploadPdf   = "modul/";
                                $uploadPdf      = move_uploaded_file($_FILES['addFilePdf']['tmp_name'], $dirUploadPdf.$namaFilePdf);
                                if($uploadPdf)
                                {                
                                    $fileThumbnail  = $_FILES['thumbnail']['name'];
                                    $ext            = pathinfo($fileThumbnail, PATHINFO_EXTENSION);

                                    $namaFile3           = $formatNama.'.'.$ext;
                                    $dirUploadThumbnail  = "thumbnail/";
                                    $newThumbnail        = $formatNama;
                                    $uploadThumbnail = move_uploaded_file($_FILES['thumbnail']['tmp_name'], $dirUploadThumbnail.$namaFile3);
                                    if($uploadThumbnail)
                                    {
                                        $newArrDbTag    = explode(',',$newTag);
    
                                        $saveDbTag = '[';
                                        foreach ($newArrDbTag as $value) {
                                            $saveDbTag .= '{"tag":"'.$value.'"},';
                                        }
                                        $saveDbTag  = substr($saveDbTag,0,-1);
                                        $saveDbTag .= ']'; 
                                        
                                        $saveDbTag = str_replace('\\', '', $saveDbTag);    
                                        
                                        $json_arr['db_modul'][] = array('id'=>"$newId", 'db_media'=>$newJenis, 'link'=>$newLink, 'thumbnail'=>$newThumbnail, 'judul'=>$newJudul, 'kategori'=>$newKategori, 'db_tag'=>$saveDbTag);
                                        
                                        $json_arr['db_modul'] = array_values($json_arr['db_modul']);
                                        
                                        $jsonString = json_encode($json_arr);
                                        $jsonString = str_replace('\\', "", $jsonString);
                                        $jsonString = str_replace('"[', "[", $jsonString);
                                        $jsonString = str_replace(']"', "]", $jsonString);
    
                                        unlink($namaJson);
                                        $file = fopen($namaJson, 'w');    
                                        fwrite($file, $jsonString);
                                        
                                        $pindahLokasi .= '?create=1&uploadPdf=1&uploadThumbnail=1';
                                        header($pindahLokasi);
                                    }
                                    else{
                                        $header .='&uploadThumbnail=0';
                                        header($header);                                        
                                    }
                                } 
                                else
                                {
                                    $header .='&uploadPdf=0';
                                    header($header);
                                }                                
                            }    
                        }
                    }
                }
            }
        }
    }    
}


if(isset($_GET['hapus']))
{    
    $hapusId = $_GET['id'];
    for($i = 0; $i<count($json_arr['db_modul']); $i++)
    {
        if($json_arr['db_modul'][$i]['id'] == $hapusId)
        {
            if($json_arr['db_modul'][$i]['db_media'] == 'video')
            {                
                $fileThumbnail = 'thumbnail/'.$json_arr['db_modul'][$i]['thumbnail'].'.jpg';
                unlink($fileThumbnail);
                unset($json_arr['db_modul'][$i]);
                unlink($namaJson);                

            }
            else if($json_arr['db_modul'][$i]['db_media'] == 'pdf')
            {
                $fileThumbnail  = 'thumbnail/'.$json_arr['db_modul'][$i]['thumbnail'].'.jpg';
                $filePdf        = 'modul/'.$json_arr['db_modul'][$i]['link'].'.pdf';
                unlink($fileThumbnail);
                unlink($filePdf);
                unset($json_arr['db_modul'][$i]);
                unlink($namaJson);                
            }
            
            $json_arr['db_modul'] = array_values($json_arr['db_modul']);
            file_put_contents($namaJson, json_encode($json_arr));
        }
    }
}



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
                                    <label for="exampleInputPassword1">Media</label>
                                    <select class="form-control" style="width: 100%;" name="media" id="media">
                                        <option selected="true" disabled>Pilih Media</option>
                                        <option value="video">Video</option>
                                        <option value="pdf">Pdf</option>
                                    </select>
                                </div>
                                <div class="form-group" id="mediaAdd">                                    
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Thumbnail</label>                                    
                                    <input type="file" class='form-control' name="thumbnail" placeholder="Thumbnail">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Tag</label>
                                    <?php if(isset($_GET['tag'])):?>
                                        <?php if(empty($_GET['tag'])) :?>
                                            <input type="text" class="form-control" name="tag" placeholder="Tag">
                                        <?php else : ?>
                                            <input type="text" class="form-control" name="tag" value="<?=$_GET['tag'];?>">
                                        <?php endif; ?>
                                    <?php else :?>
                                        <input type="text" class="form-control" name="tag" placeholder="Tag">
                                    <?php endif; ?>
                                </div>                                
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Title</label>
                                    <?php if(isset($_GET['judulVideo'])):?>
                                        <?php if(empty($_GET['judulVideo'])) :?>
                                            <input type="text" class="form-control" name="judulVideo" placeholder="Judul Video">
                                        <?php else : ?>
                                            <input type="text" class="form-control" name="judulVideo" value="<?=$_GET['judulVideo'];?>">
                                        <?php endif; ?>
                                    <?php else :?>
                                        <input type="text" class="form-control" name="judulVideo" placeholder="Title">
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Kategori</label>
                                    <?php if(isset($_GET['kategori'])):?>
                                        <?php if(empty($_GET['kategori'])) :?>
                                            <input type="text" class="form-control" name="kategori" placeholder="Kategori">
                                        <?php else : ?>
                                            <input type="text" class="form-control" name="kategori" value="<?=$_GET['kategori'];?>">
                                        <?php endif; ?>
                                    <?php else :?>
                                        <input type="text" class="form-control" name="kategori" placeholder="Kategori">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary" name="submit">Tambah Data</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-4">
                    <table class="table table-bordered table-hover text-center">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Jenis</th>
                                <th scope="col">Link</th>
                                <th scope="col">Thumbnail</th>
                                <th scope="col">Title</th>                            
                                <th scope="col">Tag</th>                            
                                <th scope="col">Kategori</th>  
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php for ($i = 0; $i < count($json_arr['db_modul']); $i++) : ?>
                                <tr>
                                    <td><?= $no; ?></td>
                                    <td><?= $json_arr['db_modul'][$i]['db_media']; ?></td>
                                    <td><?= $json_arr['db_modul'][$i]['link']; ?></td>    
                                    <td><?= $json_arr['db_modul'][$i]['thumbnail']; ?></td>  
                                    <td><?= $json_arr['db_modul'][$i]['judul']; ?></td>
                                    <td>
                                        <?php 
                                            $json_arr2 = json_encode($json_arr['db_modul'][$i]['db_tag'], true);
                                            
                                            $json_arr3 = json_decode($json_arr2, true);
                                            
                                            $saveString = '';
                                            foreach($json_arr3 as $arr)
                                            {
                                                foreach ($arr as $index => $value)
                                                {
                                                    $saveString .= $value.', ';
                                                }
                                            }
                                            
                                            $saveString = substr($saveString, 0, -2);
                                            echo $saveString;
                                        ?>
                                    </td>                                    
                                    <td><?= $json_arr['db_modul'][$i]['kategori']; ?></td>
                                    <td>
                                        <a href="editjson.php?id=<?=$json_arr['db_modul'][$i]['id'];?>"><abbr title='Edit Data'><i class='nav-icon fas fa-edit'></i></a>
                                        <a href="crud_jsondata_datamodul.php?hapus=1&id=<?=$json_arr['db_modul'][$i]['id'];?>"><abbr title='Hapus Data'><i class='nav-icon fas fa-trash-alt'></i></a>
                                    </td>
                                    <?php $no += 1; ?>
                                </tr>
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

            
            $("#media").change(function(){
                var media = $("#media").val();
                if(media == "video")
                {                
                    $("#mediaAdd").append("<label for='exampleInputPassword1' id='labelAddVideo'>Video Link</label><input type='text' class='form-control' name='videoLink' id='videoLink' placeholder='Video Link'>");
                    $("#labelAddPdf").remove();
                    $("#addFilePdf").remove();
                    $("#chooseFilePdf").remove();    
                }
                else
                {
                    $("#mediaAdd").append("<label for='exampleInputPassword1' id='labelAddPdf'>Pdf File</label><input type='file' class='form-control' name='addFilePdf' id='addFilePdf'>");
                    $("#labelAddVideo").remove();
                    $("#videoLink").remove();
                }
            });
        })

    </script>
</body>

</html>