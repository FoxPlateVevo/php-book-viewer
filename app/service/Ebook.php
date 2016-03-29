<?php

require_once __PATH__ . "/app/model/Book.php";

class Service_Ebook {
    public $books;
    
    public function __construct() {
        $this->books = new Service_Ebook_Books_Resource();
    }
}

/*
 * Resource SCRUD Management
 */
class Service_Ebook_Books_Resource {
    public function listBooks(array $optionParams = null) {
        /*
         * bookId : optional String, Array
         */
        $alternateAttributes = [
            "bookId" => "book_id"
        ];
        
        $CONDITIONALS_PART_STRING = get_conditional_string($alternateAttributes, $optionParams);
        
        $query = "
        SELECT 
        book_id,
        author
        FROM book
        {$CONDITIONALS_PART_STRING}
        ";
        
        $booksData = db::fetchAll($query);
        
        $books = [];
        
        foreach ($booksData as $bookData){
            $books[$bookData->book_id] = new Book([
                "bookId"    => $bookData->book_id,
                "author"    => $bookData->author
            ]);
        }
        
        return $books;
    }
    
    public function insert(Book $book){
        $insertedBookId = db::insert("book", [
          "author"          => $book->getAuthor()
        ]);
        
        return $insertedBookId;
    }
    
    public function get($bookId){
        return array_pop($this->listBooks([
            "bookId" => $bookId
        ]));
    }
    
    public function update(Book $book){
        $affectedRows = db::update("book", [
            "author"    => $book->getAuthor()
        ], [
            "book_id"   => $book->getBookId()
        ]);
        
        return $affectedRows;
    }
    
    public function delete($bookId){
        $affectedRows = db::delete("book", [
            "book_id" => $bookId
        ]);
        
        return $affectedRows;
    }
}