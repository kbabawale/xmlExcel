<?php

//namespace PhpSolutions\File;
class Upload {

    protected $uploaded = [];
    protected $destination;
    protected $max = 300000;
    protected $messages = [];
    protected $permitted = [
        'text/xml'
    ];
    protected $typeCheckingOn = true;
    protected $notTrusted = ['bin', 'cgi', 'exe', 'js', 'pl', 'php', 'py', 'sh'];
    protected $suffix = '.upload';
    protected $newName;
    protected $renameDuplicates;
    protected $ConvertedFileNames = array();
    protected $RadioChoice = array();


    public function __construct($path) {
        if (!is_dir($path) || !is_writable($path)) {
            throw new \Exception("$path must be a valid, writable directory.");
        }
        $this->destination = $path;
    }

    public function upload($renameDuplicates = true) {
        $this->renameDuplicates = $renameDuplicates;
        $uploaded = current($_FILES);
        if (is_array($uploaded['name'])) {
            $len = count($uploaded['name']); $counter = 0; $radioCounter = 1;
            // deal with multiple uploads
            foreach ($uploaded['name'] as $key => $value) {
                $currentFile['name'] = $uploaded['name'][$key];
                $currentFile['type'] = $uploaded['type'][$key];
                $currentFile['tmp_name'] = $uploaded['tmp_name'][$key];
                $currentFile['error'] = $uploaded['error'][$key];
                $currentFile['size'] = $uploaded['size'][$key];
                //store radio button values alongside
                $this->RadioChoice[] = $_POST['choice'.$radioCounter];
                if ($this->checkFile($currentFile)) {
                    $this->moveFile($currentFile, $_POST['choice'.$radioCounter]);
                }
                $counter++; $radioCounter++;
                //if at the end of the loop, download zip file
                if($counter == $len){
                    $this->downloadd();
                }
            }
        } else {
            if ($this->checkFile($uploaded)) {
                $this->moveFile($uploaded);
                $this->downloadd();
                $this->RadioChoice[] = $_POST['choice1'];
            }
        }
    }

    public function getMessages() {
        return $this->messages;
    }

    public function getMaxSize() {
        return number_format($this->max/1024, 1) . ' KB';
    }

    public function setMaxSize($num) {
        if (is_numeric($num) && $num > 0) {
            $this->max = (int) $num;
        }
    }

    public function allowAllTypes($suffix = true) {
        $this->typeCheckingOn = false;
        if (!$suffix) {
            $this->suffix = '';  // empty string
        }
    }

    protected function checkFile($file) {
        $accept = true;
        if ($file['error'] != 0) {
            $this->getErrorMessage($file);
            // stop checking if no file submitted
            if ($file['error'] == 4) {
                return false;
            } else {
                $accept = false;
            }
        }
        if (!$this->checkSize($file)) {
            $accept = false;
        }
        if ($this->typeCheckingOn) {
            if (!$this->checkType($file)) {
                $accept = false;
            }
        }
        if ($accept) {
            $this->checkName($file);
        }
        return $accept;
    }

    protected function getErrorMessage($file) {
        switch($file['error']) {
            case 1:
            case 2:
                $this->messages[] = $file['name'] . ' is too big: (max: ' .
                    $this->getMaxSize() . ').';
                break;
            case 3:
                $this->messages[] = $file['name'] . ' was only partially uploaded.';
                break;
            case 4:
                $this->messages[] = 'No file submitted.';
                break;
            default:
                $this->messages[] = 'Sorry, there was a problem uploading ' . $file['name'];
                break;
        }
    }

    protected function checkSize($file) {
        if ($file['error'] == 1 || $file['error'] == 2) {
            return false;
        } elseif ($file['size'] == 0) {
            $this->messages[] = $file['name'] . ' is an empty file.';
            return false;
        } elseif ($file['size'] > $this->max) {
            $this->messages[] = $file['name'] . ' exceeds the maximum size
                for a file (' . $this->getMaxSize() . ').';
            return false;
        } else {
            return true;
        }
    }

    protected function checkType($file) {
        if (in_array($file['type'], $this->permitted)) {
            return true;
        } else {
            if (!empty($file['type'])) {
                $this->messages[] = $file['name'] . ' is not a permitted type of file.';
            }
            return false;
        }
    }

    protected function checkName($file) {
        $this->newName = null;
        $nospaces = str_replace(' ', '_', $file['name']);
        if ($nospaces != $file['name']) {
            $this->newName = $nospaces;
        }
        $extension = pathinfo($nospaces, PATHINFO_EXTENSION);
        if (!$this->typeCheckingOn && !empty($this->suffix)) {
            if (in_array($extension, $this->notTrusted) || empty($extension)) {
                $this->newName = $nospaces . $this->suffix;
            }
        }
        if ($this->renameDuplicates) {
            $name = isset($this->newName) ? $this->newName : $file['name'];
            $existing = scandir($this->destination);
            if (in_array($name, $existing)) {
                // rename file
                $basename = pathinfo($name, PATHINFO_FILENAME);
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $i = 1;
                do {
                    $this->newName = $basename . '_' . $i++;
                    if (!empty($extension)) {
                        $this->newName .= ".$extension";
                    }
                } while (in_array($this->newName, $existing));
            }
        }
    }

    protected function moveFile($file, $radioButtonValue) {
        $filename = isset($this->newName) ? $this->newName : $file['name'];
        $success = move_uploaded_file($file['tmp_name'], $this->destination . $filename);
        if ($success) {
            $result = $file['name'] . ' was uploaded and converted successfully';
            
            if($radioButtonValue == 'Returns'){
                require("convertfile1.php");
            }elseif($radioButtonValue == 'Overcharge'){
                require("convertfile2.php");
            }elseif($radioButtonValue == 'Shortage'){
                require("convertfile3.php");
            }
            
            //save filenames of converted documents
            if(file_exists('TempUploads/'.basename($filename, '.xml').'.xlsx')){
                $this->ConvertedFileNames[] = basename($filename, '.xml').'.xlsx';
            }else{
                throw new Exception("Couldnt get file");
                exit("Couldnt get file to save into array");
            }
            
            if (!is_null($this->newName)) {
                //$result .= ', and was renamed ' . $this->newName;
            }
            $this->messages[] = $result;
        } else {
            $this->messages[] = 'Could not upload ' . $file['name'];
        }
    }

    protected function downloadd(){
        $zipname = 'file.zip';
        $zip = new ZipArchive;
        if($zip->open($zipname, ZipArchive::CREATE)){
            foreach ($this->ConvertedFileNames as $file) {
                $zip->addFile('TempUploads/'.$file);
            }
            $zip->close();
        }else{
            exit('Couldnt create new zip file');
        }

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.$zipname);
        header('Content-Length: ' . filesize($zipname));
        readfile($zipname);

        unlink('file.zip');

        //delete xml and xlsx file after conversion
        foreach(new DirectoryIterator('TempUploads/') as $fileInfo){
            if(!$fileInfo->isDot()){
                unlink($fileInfo->getPathname());
            }
        }
    }

}