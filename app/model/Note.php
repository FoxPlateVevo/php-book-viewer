<?php

require_once __PATH__ . "/app/model/Model.php";

class Note extends Model {
    protected $noteId;
    protected $description;
    protected $color;
    protected $page;
    protected $bookId;
    
    function getNoteId() {
        return $this->noteId;
    }

    function getDescription() {
        return $this->description;
    }

    function getColor() {
        return $this->color;
    }

    function getPage() {
        return $this->page;
    }

    function getBookId() {
        return $this->bookId;
    }

    function setNoteId($noteId) {
        $this->noteId = $noteId;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setColor($color) {
        $this->color = $color;
    }

    function setPage($page) {
        $this->page = $page;
    }

    function setBookId($bookId) {
        $this->bookId = $bookId;
    }

    //methods
    public function toJson(){
        return [
            "id"            => $this->getNoteId(),
            "description"   => $this->getDescription(),
            "color"         => $this->getColor()
        ];
    }
}
