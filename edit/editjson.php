<?php
// load file
$namaJson       = 'data_youtube.json';
$pindahLokasi   = 'Location:crud_jsondata.php';

$data = file_get_contents($namaJson);

// decode json to associative array
$json_arr = json_decode($data, true);

$id = $_GET['id'];


if(isset($_GET['update']))
{
    
    $hapusId = $_GET['id'];
    for($i = 0; $i<count($json_arr['db_youtube']); $i++)
    {
        if($json_arr['db_youtube'][$i]['id'] == $id)
        {
            $updateIdVideo      = '';
            $updateJudulVideo   = '';
            $updateTag          = '';
            $updateKategori     = '';

            // videoId 
            if(!empty($_GET['videoId']))
            {
                $updateIdVideo = $_GET['videoId'];
            }
            else
            {
                $updateIdVideo = $json_arr['db_youtube'][$i]['video_id'];
            }

            //judulVideo
            if(!empty($_GET['judulVideo']))
            {
                $updateJudulVideo = $_GET['judulVideo'];
            }
            else
            {
                $updateJudulVideo = $json_arr['db_youtube'][$i]['judul'];
            }

            //kategori
            if(!empty($_GET['kategori']))
            {
                $updateKategori = $_GET['kategori'];
            }
            else
            {
                $updateKategori = $json_arr['db_youtube'][$i]['kategori'];
            }            

            //tag
            if(!empty($_GET['tag']))
            {
                $updateTag = $_GET['tag'];   
            }
            else
            {
                foreach ($json_arr['db_youtube'][$i]['db_tag'] as $arr) {
                    foreach ($arr as $index => $value) {
                        $updateTag .= $value.', ';
                    }
                }
                $updateTag = substr($updateTag, 0, -2);
            }
            
            //Hapus Data Json
            unset($json_arr['db_youtube'][$i]);

            //Set Array DB TAG
            $newArrDbTag    = explode(',',$updateTag);

            $saveDbTag = '[';
            foreach ($newArrDbTag as $value) {
                $saveDbTag .= '{"tag":"'.$value.'"},';
            }
            $saveDbTag  = substr($saveDbTag,0,-1);
            $saveDbTag .= ']'; 
            
            $saveDbTag = str_replace('\\', '', $saveDbTag);
            //


            $json_arr['db_youtube'][] = array('id'=>$hapusId, 'video_id'=>$updateIdVideo, 'judul'=>$updateJudulVideo, 'kategori'=>$updateKategori, 'db_tag'=>$saveDbTag);
            //unlink('data_youtube.json');
            $json_arr['db_youtube'] = array_values($json_arr['db_youtube']);
            
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





?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>MobilePro</title>
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
                    <form role="form" action="" method="GET">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Update Data Youtube</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Video ID</label>
                                    <?php if(isset($_GET['videoId'])):?>
                                        <?php if(empty($_GET['videoId'])) :?>
                                            <input type="text" class="form-control" name="videoId" placeholder="Id Video">
                                        <?php else : ?>
                                            <input type="text" class="form-control" name="videoId" value="<?=$_GET['videoId'];?>">
                                        <?php endif; ?>
                                    <?php else :?>
                                        <input type="text" class="form-control" name="videoId" placeholder="Id Video">
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Judul Video</label>
                                    <?php if(isset($_GET['judulVideo'])):?>
                                        <?php if(empty($_GET['judulVideo'])) :?>
                                            <input type="text" class="form-control" name="judulVideo" placeholder="Judul Video">
                                        <?php else : ?>
                                            <input type="text" class="form-control" name="judulVideo" value="<?=$_GET['judulVideo'];?>">
                                        <?php endif; ?>
                                    <?php else :?>
                                        <input type="text" class="form-control" name="judulVideo" placeholder="Judul Video">
                                    <?php endif; ?>
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
                                
                                <input type="hidden" name="id" value="<?=$_GET['id'];?>">

                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary" name="update">Update Data</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col">
                    <table class="table table-bordered table-hover text-center">
                        <thead>
                            <tr>
                                <th scope="col">Video Id</th>
                                <th scope="col">Judul Video</th>
                                <th scope="col">Tag</th>
                                <th scope="col">Kategori</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < count($json_arr['db_youtube']); $i++) : ?>
                                <?php if($json_arr['db_youtube'][$i]['id'] == $id):?>
                                <tr>
                                    <td><?= $json_arr['db_youtube'][$i]['video_id']; ?></td>
                                    <td><?= $json_arr['db_youtube'][$i]['judul']; ?></td>
                                    <td>
                                        <?php 
                                            $json_arr2 = json_encode($json_arr['db_youtube'][$i]['db_tag'], true);
                                            
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
                                    <td><?= $json_arr['db_youtube'][$i]['kategori']; ?></td>
                                </tr>
                                <?php endif;?>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>



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
        });
    </script>
</body>

</html>