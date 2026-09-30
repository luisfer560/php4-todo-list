<?php 
class task{
    //conexion y nombre de la tabla
    private $conn;
    private $table_name = "tasks";

    // propiedades del objeto
    public $id; 
    public $title;
    public $completed;

    //constructor: recibe la conexion a la base de datos cuando se intancia el objeto
    public function __construct($db) {
        $this->conn = $db;
    }
    //1. Leer tareas (READ)
    public function readAll(){
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
    //2. crear tarea (create)
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (title) VALUES (:title)";
        $stmt = $this->conn->prepare($query);

        //limpiar entrada (evitar HTML/script dañinaos)
        $this->title = htmlspecialchars(strip_tags($this->title));

        //Vincular parametros para prevenir SQL
        $stmt->bindParam(":title", $this->title);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    // cambiar estado / completar (update)
    public function toggleComplete() {
        $query = "UPDATE " . $this->table_name . " SET completed = :completed WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":completed", $this->completed);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }
    //4. elimina tarea (delete)
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
}
?>