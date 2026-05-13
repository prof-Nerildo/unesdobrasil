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
            if (!$this->db->inTransaction()) { $this->db->beginTransaction(); }

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

    public function updateLastLogin($idUsuario) {
        $agora = date('Y-m-d H:i:s');
        $sql = "UPDATE usuario SET last_login = :agora WHERE idUsuario = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':agora' => $agora, ':id' => $idUsuario]);
    }

    public function findByLogin($login) {
        $sql = "SELECT * FROM usuario WHERE email = :email OR username = :user LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $login);
        $stmt->bindValue(':user', $login);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

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
        $sql = "UPDATE usuario SET senha = :senha WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':senha' => $novaSenhaHash, ':email' => $email]);
    }

    public function update($id, $dados) {
        $campos = [];
        $params = [':id' => $id];

        // Mapeamento correto das colunas do seu banco
        if (!empty($dados['primeiro_nome'])) {
            $campos[] = "primeiro_nome = :p_nome";
            $params[':p_nome'] = $dados['primeiro_nome'];
        }
        if (!empty($dados['sobrenome'])) {
            $campos[] = "sobrenome = :sobrenome";
            $params[':sobrenome'] = $dados['sobrenome'];
        }
        if (!empty($dados['email'])) {
            $campos[] = "email = :email";
            $params[':email'] = $dados['email'];
        }
        if (!empty($dados['cargo'])) {
            $campos[] = "cargo = :cargo";
            $params[':cargo'] = $dados['cargo'];
        }
        if (!empty($dados['senha'])) { // No controller você trata a criptografia
            $campos[] = "senha = :senha";
            $params[':senha'] = $dados['senha'];
        }
        if (isset($dados['idAcl'])) {
            $campos[] = "idAcl = :idAcl";
            $params[':idAcl'] = $dados['idAcl'];
        }

        if (empty($campos)) return false;

        // Ajustado para 'usuario' e 'idUsuario' conforme seu findMe
        $sql = "UPDATE usuario SET " . implode(', ', $campos) . " WHERE idUsuario = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // Aproveita e adiciona este para listar todos na sua tela
    public function listarTodos() {
        $sql = "SELECT u.idUsuario as id, u.primeiro_nome, u.sobrenome, u.email, u.cargo, 
                       a.tipo as nivel, s.tipo as status, u.idStatus
                FROM usuario u 
                INNER JOIN acl a ON u.idAcl = a.idAcl
                INNER JOIN status s ON u.idStatus = s.idStatus
                WHERE u.idAcl = 2
                ORDER BY u.primeiro_nome ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $sql = "SELECT u.idUsuario as id, u.primeiro_nome, u.sobrenome, u.email, u.cargo,
                       u.idAcl, u.idStatus, u.idPerfil, u.username,
                       a.tipo as nivel, s.tipo as status
                FROM usuario u
                INNER JOIN acl a ON u.idAcl = a.idAcl
                INNER JOIN status s ON u.idStatus = s.idStatus
                WHERE u.idUsuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizarStatus($id, $idStatus) {
        $sql = "UPDATE usuario SET idStatus = :status WHERE idUsuario = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':status' => $idStatus, ':id' => $id]);
    }
}