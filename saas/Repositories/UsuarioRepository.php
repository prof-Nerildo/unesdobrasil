<?php
namespace Repositories;

use PDO;
use Exception;

class UsuarioRepository {
    private $db;

    public function __construct() {
        $this->db = \Data\Database::getConnection();
    }

    public function create(\Models\RegisterRequestModelUsuario $request) {
        try {
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }

            $sql = "INSERT INTO usuario (idAcl, idStatus, idPerfil, idInstituicao, primeiro_nome, sobrenome, cargo, email, username, senha) 
                    VALUES (:idAcl, :idStatus, :idPerfil, :idInst, :primeiro_nome, :sobrenome, :cargo, :email, :username, :senha)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':idAcl'         => $request->idAcl,
                ':idStatus'      => $request->idStatus,
                ':idPerfil'      => $request->idPerfil,
                ':idInst'        => $request->idInstituicao,
                ':primeiro_nome' => $request->primeiro_nome,
                ':sobrenome'     => $request->sobrenome,
                ':cargo'         => $request->cargo,
                ':email'         => $request->email,
                ':username'      => $request->username,
                ':senha'         => $request->senha 
            ]);

            $idUsuario = $this->db->lastInsertId();

            if (!empty($request->celular)) {
                $sqlCont = "INSERT INTO contato (idReferencia, tipo_entidade, tipo_contato, valor) VALUES (?, 'usuario', 'celular', ?)";
                $this->db->prepare($sqlCont)->execute([$idUsuario, $request->celular]);
            }

            $this->db->commit();
            return $idUsuario;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new Exception("Erro ao criar usuário: " . $e->getMessage());
        }
    }

    public function findByLogin($login) {
        // 1. Usamos nomes diferentes (:email e :user) para não confundir o PDO
        $sql = "SELECT * FROM usuario WHERE email = :email OR username = :user LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        
        // 2. Passamos o mesmo valor ($login) para as duas "vagas"
        $stmt->bindValue(':email', $login);
        $stmt->bindValue(':user', $login);
        
        $stmt->execute();
        
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return null;

        // 3. Monta o objeto com os dados do banco
        $user = new \Data\Models\Usuario();
        $user->setIdUsuario($row['idUsuario']);
        $user->setIdAcl($row['idAcl']);
        $user->setIdInstituicao($row['idInstituicao'] ?? null); 
        $user->setPrimeiroNome($row['primeiro_nome']);
        $user->setEmail($row['email']);
        $user->setSenha($row['senha']);
        
        return $user;
    }

    public function emailOuUsernameExiste($email, $username) {
        $sql = "SELECT COUNT(idUsuario) as total FROM usuario WHERE email = :email OR username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email, ':username' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['total'] ?? 0) > 0;
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
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateSenha($email, $novaSenhaHash) {
        try {
            // Verifique se o nome da tabela é 'usuario' e a coluna é 'senha'
            $sql = "UPDATE usuario SET senha = :senha WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':senha' => $novaSenhaHash,
                ':email' => $email
            ]);
        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar senha no banco: " . $e->getMessage());
        }
    }
}