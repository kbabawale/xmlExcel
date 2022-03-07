<?php
$FileToUpload = 'Upload.php';
if(file_exists($FileToUpload)){
  require($FileToUpload);
}else{
  exit("Error loading page. One or more dependencies unavailable");
}

$destination = 'TempUploads/';
$max = 300000;
$missing = false;

if(isset($_POST['export'])){
  try {
    //scan the files array        
      if($_FILES['image']['name'][0] == '' && $_FILES['image']['name'][1] == '' && $_FILES['image']['name'][2] == '' &&
      $_FILES['image']['name'][3] == '' && $_FILES['image']['name'][4] == ''){
        $missing = true;
      }else{
        $loader = new Upload($destination);        
        $loader->upload();        
        $result = $loader->getMessages();
      }
  } catch (Exception $e) {        
    echo $e->getMessage();    
  }
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title>XML CONVERTER</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <div class="container">
      <h1><center>XML CONVERTER</center></h1>
      <p>Upload file and click convert.</p>
      <form action="" role="form" enctype="multipart/form-data" id="form1" method="post">
        <input type="hidden" name="MAX_FILE_SIZE" value="<?= $max; ?>"> 
        <center><p class="form-group">
        <ul>
          <?php if(isset($result)){ ?>
                <li class="error"><?php foreach($result as $key => $message){ if($key==0) echo $message;}?></li>
          <?php	} ?>
        </ul>
          <input type="file" class="form-control" name="image[]" multiple />
        </p>
        <p class="form-group">
        <ul>
          <?php if(isset($result)){ ?>
                <li class="error"><?php foreach($result as $key => $message){ if($key==1) echo $message;}?></li>
          <?php	} ?>
        </ul>
          <input type="file" class="form-control" name="image[]" multiple />
        </p>
        <p class="form-group">
        <ul>
          <?php if(isset($result)){ ?>
                <li class="error"><?php foreach($result as $key => $message){ if($key==2) echo $message;}?></li>
          <?php	} ?>
        </ul>
          <input type="file" class="form-control" name="image[]" multiple />
        </p>
        <p class="form-group">
        <ul>
          <?php if(isset($result)){ ?>
                <li class="error"><?php foreach($result as $key => $message){ if($key==3) echo $message;}?></li>
          <?php	} ?>
        </ul>
          <input type="file" class="form-control" name="image[]" multiple />
        </p>
        <p class="form-group">
        <ul>
          <?php if(isset($result)){ ?>
                <li class="error"><?php foreach($result as $key => $message){ if($key==4) echo $message;}?></li>
          <?php	} ?>
        </ul>
          <input type="file" class="form-control" name="image[]" multiple />
        </p>
        
        <p class="form-group">
          <input type="submit" name="export" value="CONVERT TO EXCEL" class="form-control btn btn-primary" />
        <ul>
          <?php if($missing){ ?>
                <li class="error">
                  <?php echo "<script>window.alert('Upload a document before clicking button');</script>"; ?>
                </li>
          <?php	} ?>
        </ul>
        </p></center>
      </form>

    </div>

  </body>
</html>