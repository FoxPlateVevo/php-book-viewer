<?php

require_once __PATH__ . "/app/model/Model.php";

class Book extends Model {
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


}
