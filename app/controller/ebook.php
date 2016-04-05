<?php
require_once __PATH__ . "/app/service/Ebook.php";

/*
 * Set service and resource data
 */
$ebook = new Service_Ebook();
$booksResource = $ebook->books;
$notesResource = $ebook->notes;

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

$this->respond("POST", "/create", function ($request, $response, $service) use ($booksResource) {   
    $name   = $request->param("name");
    $author = $request->param("author");
    
    $insertedBookId = $booksResource->insert(new Book([
        "name"      => $name,
        "author"    => $author
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

$this->respond("GET", "/[i:bookId]/[delete|other_action:action]", function ($request, $response, $service) use ($booksResource) {
    $bookId = $request->param("bookId");
    $action = $request->param("action");
    
    /*
     * Valide action
     */
    if($action === "delete"){
        $pdfPath = __PATH__ . "/public/resources/ebook/{$bookId}";
        folder::delete($pdfPath);
        
        $booksResource->delete($bookId);
    }else if($action === "other_action"){
        
    }
    
    $response->redirect("/ebook");
});

/*
 * Note
 */
$this->respond("GET", "/[i:bookId]/note", function ($request, $response, $service) use ($notesResource) {
    $bookId = $request->param("bookId");
    $page   = $request->param("page");
    
    /*
     * Get notes
     */
    $notes = $notesResource->listNotes([
        "bookId"    => $bookId,
        "page"      => $page
    ]);
    
    $notesToJson = array_map(function(Note $note){
        return $note->toJson();
    }, $notes);
    
    $response->json(array_values($notesToJson));
});

$this->respond("POST", "/[i:bookId]/note/create", function ($request, $response, $service) use ($notesResource) {
    $bookId         = $request->param("bookId");
    $description    = $request->param("description");
    $color          = $request->param("color");
    $page           = $request->param("page");
    
    $noteIdInserted = $notesResource->insert(new Note([
        "description"   => $description,
        "color"         => $color,
        "page"          => $page,
        "bookId"        => $bookId
    ]));
    
    $note = $notesResource->get($noteIdInserted);
    
    $response->json($note->toJson());
});

$this->respond("POST", "/[i:bookId]/note/[i:noteId]", function ($request, $response, $service) use ($notesResource) {
    $bookId         = $request->param("bookId");
    $noteId         = $request->param("noteId");
    $description    = $request->param("description");
    
    $note = $notesResource->get($noteId);
    $note->setDescription($description);
    
    $success = $notesResource->update($note);
    
    $response->json($success);
});

$this->respond("GET", "/[i:bookId]/note/[i:noteId]/[delete:action]", function ($request, $response, $service) use ($notesResource) {
    $bookId = $request->param("bookId");
    $noteId = $request->param("noteId");
    $action = $request->param("action");
    
    $success = false;
    
    /*
     * Valide action
     */
    if($action === "delete"){
        $success = $notesResource->delete($noteId);
    }
    
    $response->json($success);
});