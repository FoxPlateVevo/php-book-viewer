<?php
require_once __PATH__ . "/app/service/Ebook.php";

/*
 * Set service and resource data
 */
$ebook = new Service_Ebook();
$booksResource = $ebook->books;

/* 
 * Book 
 */
$this->respond("GET", "/?", function ($request, $response, $service) use ($booksResource) {
    /*
     * List all books
     */
    $books = $booksResource->listBooks();
    
    //header params
    $service->pageTitle = "Books";
    
    //content params
    $service->books = $books;
    
    //render
    $service->render(__PATH__ . "/app/view/ebook/list.phtml");
});

$this->respond("GET", "/create", function ($request, $response, $service) {
     //header params
    $service->pageTitle = "Create Book";
    
    //render
    $service->render(__PATH__ . "/app/view/ebook/create.phtml");
});

$this->respond("GET", "/[i:bookId]", function ($request, $response, $service) use ($booksResource) {
    $bookId = $request->param("bookId");
    
    /*
     * Get book to show
     */
    $book = $booksResource->get($bookId);
    
    //header params
    $service->pageTitle = "Ver Ebook";
    
    //content params
    $service->book = $book;
    
    //render
    $service->layout(__PATH__ . "/app/view/layouts/empty.phtml");
    $service->render(__PATH__ . "/app/view/ebook/viewer.phtml");
});

$this->respond("POST", "/create", function ($request, $response, $service) use ($booksResource) {   
    $author = $request->param("author");
    
    $insertedBookId = $booksResource->insert(new Book([
        "author" => $author
    ]));
    
    /*
     * Set second limit to this process
     */
    set_time_limit(600);
    
    $destiny = __PATH__ . "/public/resources/ebook/{$insertedBookId}";
    
    $uploadData = file::upload("file", $destiny);
    
    if($uploadData->success){
        $fileUploadedPath   = $uploadData->file->path;
        $destinyToConvert   = "{$destiny}/pages";
        
        /*
         * Verify if destiny to convert exists, else create dir
         */
        !is_file($destinyToConvert) && !is_dir($destinyToConvert) && folder::create($destinyToConvert);
        
        $command = "convert -density 200 -colorspace sRGB {$fileUploadedPath} {$destinyToConvert}/large.png";
        $commandToSmallImages = "convert -density 90 -colorspace sRGB {$fileUploadedPath} {$destinyToConvert}/small.png";
        
        $return = $returnTwo = null;
        
        exec($command, $output, $return);
        exec($commandToSmallImages, $outputTwo, $returnTwo);
        
        if($return || $returnTwo){
            vd("error");
            
            //Exists a error
            $booksResource->delete($insertedBookId);
            
            folder::delete($destiny);
        }else{
            //Copy each small component
            $smallComponents = glob("{$destinyToConvert}/small-*.png");
            
            foreach ($smallComponents as $component){
                $matches = null;
                
                preg_match('/small-(\d+)\.png/', $component, $matches);
                
                $index = (int) array_pop($matches);
                $index++;
                
                $componentRenamed = "{$destinyToConvert}/small-page-{$index}.png";
                
                copy($component, $componentRenamed);
            }

            foreach ($smallComponents as $component){
                unlink($component);
            }
            
            //Copy each small component
            $largeComponents = glob("{$destinyToConvert}/large-*.png");
            
            foreach ($largeComponents as $component){
                $matches = null;
                
                preg_match('/large-(\d+)\.png/', $component, $matches);
                
                $index = (int) array_pop($matches);
                $index++;
                
                $componentRenamed = "{$destinyToConvert}/large-page-{$index}.png";
                
                copy($component, $componentRenamed);
            }

            foreach ($largeComponents as $component){
                unlink($component);
            }
            
            /*
             * Redirect to principal view
             */
            $response->redirect("/ebook")->send();
        }
    }else{
        vd("error");
        
        $booksResource->delete($insertedBookId);
    }
});
