<?php
try{
ob_start();
  
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
        
        <div class="group">
        <ul>
          <?php if(isset($result)){ ?>
                <li class="error"><?php foreach($result as $key => $message){ if($key==0) echo $message;} ?></li>
          <?php	} ?>
        </ul>
        <div class="elements">
          <input type="file" class="ex" name="image[]" multiple />
          <input type="radio" class="ex" name="choice1" value="Returns" checked /><span>Returns</span>
          <input type="radio" class="ex" name="choice1" value="Overcharge"  /><span>Overcharge</span>
          <input type="radio" class="ex" name="choice1" value="Shortage"  /><span>Shortage</span>
        </div>
        </div>

        <div class="group">
        <ul>
          <?php if(isset($result)){ ?>
                <li class="error"><?php foreach($result as $key => $message){ if($key==1) echo $message;}?></li>
          <?php	} ?>
        </ul>
        <div class="elements">
          <input type="file" class="ex" name="image[]" multiple />
          <input type="radio" class="ex" name="choice2" value="Returns" checked /><span>Returns</span>
          <input type="radio" class="ex" name="choice2" value="Overcharge"  /><span>Overcharge</span>
          <input type="radio" class="ex" name="choice2" value="Shortage"  /><span>Shortage</span>
        </div>
        </div>
        <div class="group">
        <ul>
          <?php if(isset($result)){ ?>
                <li class="error"><?php foreach($result as $key => $message){ if($key==2) echo $message;}?></li>
          <?php	} ?>
        </ul>
        <div class="elements">
          <input type="file" class="ex" name="image[]" multiple />
          <input type="radio" class="ex" name="choice3" value="Returns" checked /><span>Returns</span>
          <input type="radio" class="ex" name="choice3" value="Overcharge"  /><span>Overcharge</span>
          <input type="radio" class="ex" name="choice3" value="Shortage"  /><span>Shortage</span>
        </div>
        </div>
        <div class="group">
        <ul>
          <?php if(isset($result)){ ?>
                <li class="error"><?php foreach($result as $key => $message){ if($key==3) echo $message;}?></li>
          <?php	} ?>
        </ul>
        <div class="elements">
          <input type="file" class="ex" name="image[]" multiple />
          <input type="radio" class="ex" name="choice4" value="Returns" checked /><span>Returns</span>
          <input type="radio" class="ex" name="choice4" value="Overcharge"  /><span>Overcharge</span>
          <input type="radio" class="ex" name="choice4" value="Shortage"  /><span>Shortage</span>
        </div>
        </div>
        <div class="group">
        <ul>
          <?php if(isset($result)){ ?>
                <li class="error"><?php foreach($result as $key => $message){ if($key==4) echo $message;}?></li>
          <?php	} ?>
        </ul>
        <div class="elements">
          <input type="file" class="ex" name="image[]" multiple />
          <input type="radio" class="ex" name="choice5" value="Returns" checked /><span>Returns</span>
          <input type="radio" class="ex" name="choice5" value="Overcharge"  /><span>Overcharge</span>
          <input type="radio" class="ex" name="choice5" value="Shortage"  /><span>Shortage</span>
        </div>
        </div>
        
        <div class="groups">
          <input type="submit" name="export" value="CONVERT TO EXCEL" class="btn btn-primary" />
        <ul>
          <?php if($missing){ ?>
                <li class="error">
                  <?php echo "<script>window.alert('Upload a document before clicking button');</script>"; ?>
                </li>
          <?php	} ?>
        </ul>
        </p>
      </form>

    </div>

  </body>
</html>

<?php 
}catch(Exception $i){
  ob_end_clean(); 
  header("Location: error.php");
  exit;
}

ob_end_flush();
?>