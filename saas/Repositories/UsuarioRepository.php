<?php
namespace Repositories;

use Data\Database;
use Models\RegisterRequestModelUsuario;
use Data\Models\Usuario;
use PDO;
use Exception;

class UsuarioRepository {
    private $db;

    public function __construct() {
        $this->db = \Data\Database::getConnection();
    }

    public function emailOuUsernameExiste($email, $username) {
        $sql = "SELECT COUNT(idUsuario) as total FROM usuario WHERE email = :email OR username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email, ':username' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['total'] ?? 0) > 0;
    }

    public function create(RegisterRequestModelUsuario $request) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO usuario 
                    (idAcl, idStatus, idPerfil, primeiro_nome, sobrenome, cargo, email, username, senha) 
                    VALUES 
                    (:idAcl, :idStatus, :idPerfil, :primeiro_nome, :sobrenome, :cargo, :email, :username, :senha)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':idAcl'         => $request->idAcl,
                ':idStatus'      => $request->idStatus,
                ':idPerfil'      => $request->idPerfil,
                ':primeiro_nome' => $request->primeiro_nome,
                ':sobrenome'     => $request->sobrenome,
                ':cargo'         => $request->cargo,
                ':email'         => $request->email,
                ':username'      => $request->username,
                ':senha'         => $request->senha 
            ]);

            $idUsuario = $this->db->lastInsertId();

            if (!empty($request->celular)) {
                $sqlC = "INSERT INTO contato (idReferencia, tipo_entidade, tipo_contato, valor) 
                         VALUES (?, 'usuario', 'celular', ?)";
                $this->db->prepare($sqlC)->execute([$idUsuario, $request->celular]);
            }

            $this->db->commit();
            return $idUsuario;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function vincularInstituicao($idUsuario, $idInstituicao) {
        // Verifique se o nome da coluna é exatamente idInstituicao e idUsuario
        $sql = "UPDATE usuario SET idInstituicao = :idInst WHERE idUsuario = :idUser";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':idInst' => $idInstituicao,
            ':idUser' => $idUsuario
        ]);
    }

    public function findByLogin($login) {
        $sql = "SELECT * FROM usuario WHERE email = :email OR username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $login, ':username' => $login]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        $usuario = new Usuario();
        $usuario->setIdUsuario($row['idUsuario']);
        $usuario->setIdAcl($row['idAcl']);
        $usuario->setIdStatus($row['idStatus']);
        $usuario->setSenha($row['senha']);
        $usuario->setPrimeiroNome($row['primeiro_nome']);
        return $usuario;
    }
}