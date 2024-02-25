<?php

class File {
    public $db;
    public $table = 'files';
    public $title;
    public $thumb;
    public $filename;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function read()
    {
        $query  = 'SELECT * FROM '.$this->table. ' ORDER BY id';
        $st     = $this->db->prepare($query);

        $st->execute();
        return $st;
    }

    public function create() {
        $query  = 'INSERT INTO ' . $this->table . '(title, thumb, filename) VALUES (:title, :thumb, :filename)';
        $st     = $this->db->prepare($query);

        $this->title    = htmlspecialchars(strip_tags($this->title));
        $this->thumb    = htmlspecialchars(strip_tags($this->thumb));
        $this->filename = htmlspecialchars(strip_tags($this->filename));

        $st->bindParam(':title', $this->title);
        $st->bindParam(':thumb', $this->thumb);
        $st->bindParam(':filename', $this->filename);
        $st->execute();
        return $st;
    }

    public function show(int $id) {
        $query  = 'SELECT * FROM ' . $this->table . ' WHERE id = ?';
        $st     = $this->db->prepare($query);

        $st->bindParam(1, $id);
        $st->execute();
        return $st;
    }

    public function update(int $id) {
        $query  = 'UPDATE ' . $this->table . ' SET title = ?, thumb = ?, filename = ? WHERE id = ?';
        $st     = $this->db->prepare($query);

        $this->title    = htmlspecialchars(strip_tags($this->title));
        $this->thumb    = htmlspecialchars(strip_tags($this->thumb));
        $this->filename = htmlspecialchars(strip_tags($this->filename));

        $st->bindParam(1, $this->title);
        $st->bindParam(2, $this->thumb);
        $st->bindParam(3, $this->filename);
        $st->bindParam(4, $id);
        $st->execute();
        return $st;
    }

    public function delete(int $id) {
        $query  = 'DELETE FROM ' . $this->table . ' WHERE id = ?';
        $st     = $this->db->prepare($query);

        $st->bindParam(1, $id);
        $st->execute();
        return $st;
    }
}