<?php

require_once __PATH__ . "/app/model/Model.php";

class Book extends Model {
    //constants
    const IMPORT_ERROR_COMMAND = 0;
    const IMPORT_ERROR_TO_GET_PDF_INFORMATION = 1;
    
    protected $bookId;
    protected $author;
    
    function getBookId() {
        return $this->bookId;
    }

    function getAuthor() {
        return $this->author;
    }

    function setBookId($bookId) {
        $this->bookId = $bookId;
    }

    function setAuthor($author) {
        $this->author = $author;
    }

    //methods
    /**
     * Import PDF to directories structure for present this book in a viewer
     * 
     * @param string $filename It is the PDF source path to import
     * @return boolean Return <b>TRUE</b> if the conversion was succesful 
     * or <b>INT</b> error code constant on failure
     */
    public function importPDF($filename){
        $destiny            = __PATH__ . "/public/resources/ebook/{$this->getBookId()}";
        $destinyToConvert   = "{$destiny}/pages";
        
        // Get PDF Information
        $pdfInfo = PDF_get_information($filename);
        
        if(!$pdfInfo){
            return self::IMPORT_ERROR_TO_GET_PDF_INFORMATION;
        }
        
        /*
         * Verify if destiny to convert exists, else create dir
         */
        !is_file($destinyToConvert) && !is_dir($destinyToConvert) && folder::create($destinyToConvert);
        
        /*
         * Set time limit to 10 minutes to convert pdf pages to images 
         */
        set_time_limit(600);
        
        $command                = "convert -density 200 -colorspace sRGB {$filename} {$destinyToConvert}/large.png";
        $commandToSmallImages   = "convert -density 90 -colorspace sRGB {$filename} {$destinyToConvert}/small.png";
        
        $return = $returnToSmallImages = null;
        $output = $outputToSmallImages = null;
        
        exec($command, $output, $return);
        exec($commandToSmallImages, $outputToSmallImages, $returnToSmallImages);
        
        if($return || $returnToSmallImages){
            folder::delete($destinyToConvert);
            
            return self::IMPORT_ERROR_COMMAND;
        }else{
            //Copy each small image page
            for($i = 0; $i < $pdfInfo->pages; $i++){
                $toIndex = $i + 1;

                $smallImagePath         = "{$destinyToConvert}/small-{$i}.png";
                $smallImagePathRenamed  = "{$destinyToConvert}/small-page-{$toIndex}.png";

                rename($smallImagePath, $smallImagePathRenamed);
            }
            
            //Copy each large image page
            for($i = 0; $i < $pdfInfo->pages; $i++){
                $toIndex = $i + 1;

                $largeImagePath         = "{$destinyToConvert}/large-{$i}.png";
                $largeImagePathRenamed  = "{$destinyToConvert}/large-page-{$toIndex}.png";

                rename($largeImagePath, $largeImagePathRenamed);
            }
            
            return true;
        }
    }
}
