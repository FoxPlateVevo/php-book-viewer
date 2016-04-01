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
    $errorCode = $request->param("e");
    
    /*
     * List all books
     */
    $books = $booksResource->listBooks();
    
    //header params
    $service->pageTitle = "Books";
    
    //content params
    $service->books         = $books;
    $service->errorCode     = $errorCode;
    
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
    
    $destiny = __PATH__ . "/public/resources/ebook/{$insertedBookId}";
    
    $uploadData = file::upload("file", $destiny);
    
    if($uploadData->success){
        $book = $booksResource->get($insertedBookId);
//        $book = new Book();
        
        $success = $book->importPDF($uploadData->file->path);
        
        if($success === true){
            //successful
            $response->redirect("/ebook")->send();
        }else{
            $booksResource->delete($insertedBookId);
            folder::delete($destiny);
            
            //error
            $response->redirect("/ebook/?e={$success}")->send();
        }
    }else{
        $booksResource->delete($insertedBookId);
        
        //error
        $response->redirect("/ebook/?e=upload_error")->send();
    }
});
