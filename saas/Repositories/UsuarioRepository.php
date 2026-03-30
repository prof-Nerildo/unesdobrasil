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
            $sql = "INSERT INTO usuario (idAcl, idStatus, idPerfil, primeiro_nome, sobrenome, cargo, email, username, senha) 
                    VALUES (:idAcl, :idStatus, :idPerfil, :primeiro_nome, :sobrenome, :cargo, :email, :username, :senha)";
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
            $this->db->commit();
            return $idUsuario;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function findByLogin($login) {
        // 1. Query com dois marcadores distintos
        $sql = "SELECT * FROM usuario WHERE email = :email OR username = :user LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        
        // 2. Passamos o valor de $login para ambos os marcadores
        $stmt->execute([
            ':email' => $login,
            ':user'  => $login
        ]);
        
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return null;

        // 3. Montamos o objeto Usuario com os dados do banco
        $usuario = new \Data\Models\Usuario();
        $usuario->setIdUsuario($row['idUsuario']);
        $usuario->setIdAcl($row['idAcl']);
        $usuario->setIdStatus($row['idStatus']);
        $usuario->setIdPerfil($row['idPerfil'] ?? 4);
        $usuario->setSenha($row['senha']);
        $usuario->setEmail($row['email']); // ESSENCIAL: preenche o e-mail para o PHPMailer
        $usuario->setPrimeiroNome($row['primeiro_nome']);
        
        return $usuario;
    }

    public function updateLastLogin($idUsuario) {
        $sql = "UPDATE usuario SET last_login = NOW() WHERE idUsuario = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $idUsuario]);
    }

    public function findMe($idUsuario) {
        $sql = "SELECT u.idUsuario as id, u.primeiro_nome as nome, u.sobrenome, u.cargo, u.email, 
                       a.tipo as nivel_acesso, i.nome_fantasia as instituicao
                FROM usuario u
                INNER JOIN acl a ON u.idAcl = a.idAcl
                LEFT JOIN instituicao i ON u.idInstituicao = i.idInstituicao
                WHERE u.idUsuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateSenha($email, $novaSenhaHash) {
        $sql = "UPDATE usuario SET senha = :senha WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':senha' => $novaSenhaHash, ':email' => $email]);
    }
}