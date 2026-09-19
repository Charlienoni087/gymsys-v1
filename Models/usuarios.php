<?php

class Usuario {
    int $id;
    string $nombre;
    string $email;
    string $pass;
    string $rol;

    public public function __construct(Type $var = null) {
        $this->var = $var;
    }

    public function Login(string $email, string $pass, string $rol){
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = :email AND pass = :pass AND rol = :rol");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':pass', $pass);
        $stmt->bindParam(':rol', $rol);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

?>