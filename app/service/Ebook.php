<?php

require_once __PATH__ . "/app/model/Book.php";
require_once __PATH__ . "/app/model/Note.php";

class Service_Ebook {
    public $books;
    public $notes;
    
    public function __construct() {
        $this->books = new Service_Ebook_Books_Resource();
        $this->notes = new Service_Ebook_Notes_Resource();
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
        name,
        author
        FROM book
        {$CONDITIONALS_PART_STRING}
        ";
        
        $booksData = db::fetchAll($query);
        
        $books = [];
        
        foreach ($booksData as $bookData){
            $books[$bookData->book_id] = new Book([
                "bookId"    => $bookData->book_id,
                "name"      => $bookData->name,
                "author"    => $bookData->author
            ]);
        }
        
        return $books;
    }
    
    public function insert(Book $book){
        $insertedBookId = db::insert("book", [
          "name"            => $book->getName(),
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
            "name"            => $book->getName(),
            "author"          => $book->getAuthor()
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

class Service_Ebook_Notes_Resource{
    public function listNotes(array $optionParams = null) {
        /*
         * noteId : optional String, Array
         */
        $alternateAttributes = [
            "noteId"    => "note_id",
            "color"     => "color",
            "page"      => "page",
            "bookId"    => "book_id",
        ];
        
        $CONDITIONALS_PART_STRING = get_conditional_string($alternateAttributes, $optionParams);
        
        $query = "
        SELECT 
        note_id,
        description,
        color,
        page,
        book_id
        FROM note
        {$CONDITIONALS_PART_STRING}
        ";
        
        $notesData = db::fetchAll($query);
        
        $notes = [];
        
        foreach ($notesData as $noteData){
            $notes[$noteData->note_id] = new Note([
                "noteId"        => $noteData->note_id,
                "description"   => $noteData->description,
                "color"         => $noteData->color,
                "page"          => $noteData->page,
                "bookId"        => $noteData->book_id
            ]);
        }
        
        return $notes;
    }
    
    public function insert(Note $note){
        $insertedNoteId = db::insert("note", [
            "description"   => $note->getDescription(),
            "color"         => $note->getColor(),
            "page"          => $note->getPage(),
            "book_id"       => $note->getBookId()
        ]);
        
        return $insertedNoteId;
    }
    
    public function get($noteId){
        return array_pop($this->listNotes([
            "noteId" => $noteId
        ]));
    }
    
    public function update(Note $note){
        $affectedRows = db::update("note", [
            "description"   => $note->getDescription(),
            "color"         => $note->getColor(),
            "page"          => $note->getPage(),
            "book_id"        => $note->getBookId()
        ], [
            "note_id"       => $note->getNoteId()
        ]);
        
        return $affectedRows;
    }
    
    public function delete($noteId){
        $affectedRows = db::delete("note", [
            "note_id" => $noteId
        ]);
        
        return $affectedRows;
    }
}