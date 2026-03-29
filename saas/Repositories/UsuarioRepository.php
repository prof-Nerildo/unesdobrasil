<?php

namespace Repositories;

use Database;
use Models\RegisterRequestModelUsuario;
use Data\Models\Usuario;
use PDO;
use Exception;

class UsuarioRepository {
    private $db;

    public function __construct() {
        $this->db = \Database::getConnection();
    }

    /**
     * Verifica se e-mail ou username já existem
     */
    public function emailOuUsernameExiste($email, $username) {
        $sql = "SELECT COUNT(idUsuario) as total FROM USUARIO WHERE email = :email OR username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':username' => $username
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['total'] ?? 0) > 0;
    }

    /**
     * CRUD: Criar usuário
     */
    public function create(RegisterRequestModelUsuario $request) {
        try {
            $sql = "INSERT INTO USUARIO 
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

            return $this->db->lastInsertId();

        } catch (Exception $e) {
            throw new Exception("Erro ao criar usuário: " . $e->getMessage());
        }
    }

    /**
     * Busca para Login (E-mail ou Username)
     */
    public function findByLogin($login) {
        $sql = "SELECT * FROM USUARIO WHERE email = :email OR username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        
        $stmt->execute([
            ':email' => $login,
            ':username' => $login
        ]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        $usuario = new Usuario();
        $usuario->setIdUsuario($row['idUsuario']);
        $usuario->setIdAcl($row['idAcl']);
        $usuario->setIdStatus($row['idStatus']);
        $usuario->setIdPerfil($row['idPerfil']);
        $usuario->setPrimeiroNome($row['primeiro_nome']);
        $usuario->setSobrenome($row['sobrenome']);
        $usuario->setCargo($row['cargo']);
        $usuario->setEmail($row['email']);
        $usuario->setUsername($row['username']);
        $usuario->setSenha($row['senha']); 
        $usuario->setLastLogin($row['last_login']);
        $usuario->setCreatedAt($row['created_at']);
        $usuario->setUpdatedAt($row['updated_at']);

        return $usuario;
    }

    /**
     * CRUD: Atualizar Senha
     */
    public function updateSenha($email, $novaSenhaHash) {
        $sql = "UPDATE USUARIO SET senha = :senha, updated_at = NOW() WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':senha' => $novaSenhaHash,
            ':email' => $email
        ]);
    }

    /**
     * SISTEMA: Atualiza timestamp de último login (O que faltava!)
     */
    public function updateLastLogin($idUsuario) {
        try {
            $sql = "UPDATE USUARIO SET last_login = NOW() WHERE idUsuario = :idUsuario";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':idUsuario' => $idUsuario]);
        } catch (Exception $e) {
            // Log de erro opcional aqui
        }
    }
}