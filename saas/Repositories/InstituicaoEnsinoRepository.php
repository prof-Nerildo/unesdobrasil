<?php
namespace Repositories;

use PDO;
use Exception;

class InstituicaoEnsinoRepository {
    private $db;

    public function __construct() {
        $this->db = \Data\Database::getConnection();
    }

    public function createSimples(\Models\RegisterRequestModelInstituicaoEnsino $model) {
        try {
            $agora = date('Y-m-d H:i:s');

            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }

            // 1. Grava a Instituição
            $sqlInst = "INSERT INTO instituicao (razao_social, nome_fantasia, cnpj, idStatus, created_at) VALUES (?, ?, ?, 3, ?)";
            $stmt = $this->db->prepare($sqlInst);
            $stmt->execute([
                $model->razao_social, 
                $model->nome_fantasia, 
                $model->cnpj,
                $agora
            ]);
            
            $idInst = $this->db->lastInsertId();

            // Sincroniza idLegado com idInstituicao para novos cadastros
            $sqlUpdLegado = "UPDATE instituicao SET idLegado = ? WHERE idInstituicao = ?";
            $this->db->prepare($sqlUpdLegado)->execute([$idInst, $idInst]);

            // 2. Grava o Endereço
            $sqlEnd = "INSERT INTO endereco (idReferencia, tipo_entidade, cep, logradouro, numero, complemento, bairro, cidade, uf) 
                       VALUES (?, 'instituicao', ?, ?, ?, ?, ?, ?, ?)";
            $stmtEnd = $this->db->prepare($sqlEnd);
            $stmtEnd->execute([
                $idInst, 
                $model->cep, 
                $model->logradouro,
                $model->numero,
                $model->complemento ?? '',
                $model->bairro, 
                $model->cidade, 
                $model->uf
            ]);

            // 3. Grava os Contatos
            $sqlCont = "INSERT INTO contato (idReferencia, tipo_entidade, tipo_contato, valor) VALUES (?, 'instituicao', ?, ?)";
            $stmtCont = $this->db->prepare($sqlCont);

            if (!empty($model->email_contato)) {
                $stmtCont->execute([$idInst, 'email_secretaria', $model->email_contato]);
            }
            if (!empty($model->telefone)) {
                $stmtCont->execute([$idInst, 'fixo', $model->telefone]);
            }

            $this->db->commit();
            return $idInst;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new Exception("Erro SQL: " . $e->getMessage());
        }
    }

    public function findAll() {
        $sql = "SELECT 
                    i.idInstituicao, 
                    i.idLegado, 
                    i.idStatus, 
                    i.nome_fantasia, 
                    
                    COALESCE((SELECT cidade FROM endereco WHERE idReferencia = i.idInstituicao AND tipo_entidade = 'instituicao' LIMIT 1), '---') AS cidade,
                    
                    -- CATRACA
                    COALESCE((SELECT ic.usa_catraca FROM instituicao_catraca ic WHERE ic.idInstituicao = i.idInstituicao LIMIT 1), 'nao') AS usa_catraca,
                    
                    -- RESPONSÁVEL
                    COALESCE(
                        (SELECT CONCAT_WS(' ', u.primeiro_nome, u.sobrenome) 
                        FROM usuario u 
                        WHERE u.idInstituicao = i.idInstituicao 
                        ORDER BY u.idUsuario ASC LIMIT 1), 
                        'Sem Responsável'
                    ) AS responsavel, 
                    
                    -- TELEFONE (do responsável)
                    COALESCE(
                        (SELECT c.valor 
                        FROM contato c
                        INNER JOIN usuario u ON u.idUsuario = c.idReferencia
                        WHERE u.idInstituicao = i.idInstituicao 
                        AND c.tipo_entidade = 'usuario' 
                        AND c.tipo_contato = 'celular'
                        ORDER BY u.idUsuario ASC LIMIT 1),
                        '---'
                    ) AS telefone,
                    
                    -- E-MAIL (do responsável)
                    COALESCE(
                        (SELECT email FROM usuario 
                        WHERE idInstituicao = i.idInstituicao 
                        ORDER BY idUsuario ASC LIMIT 1),
                        '---'
                    ) AS email_usuario

                FROM instituicao i
                WHERE i.idStatus != 1   
                ORDER BY i.idInstituicao DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $idStatus) {
        try {
            $sql = "UPDATE instituicao SET idStatus = ? WHERE idInstituicao = ?";
            return $this->db->prepare($sql)->execute([$idStatus, $id]);
        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar status: " . $e->getMessage());
        }
    }

    public function updatePerfilPelaInstituicao($id, $dados) {
        try {
            $agora = date('Y-m-d H:i:s');
            if (!$this->db->inTransaction()) { $this->db->beginTransaction(); }

            // 1. Atualiza dados básicos da instituição
            $sql = "UPDATE instituicao SET razao_social = ?, nome_fantasia = ?, updated_at = ? WHERE idInstituicao = ?";
            $this->db->prepare($sql)->execute([
                $dados['razao_social'] ?? '',
                $dados['nome_fantasia'] ?? '',
                $agora,
                $id
            ]);

            // 2. Atualiza endereço
            $sqlEnd = "UPDATE endereco SET cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, uf = ?
                       WHERE idReferencia = ? AND tipo_entidade = 'instituicao'";
            $this->db->prepare($sqlEnd)->execute([
                $dados['cep']         ?? '',
                $dados['logradouro']  ?? '',
                $dados['numero']      ?? '',
                $dados['complemento'] ?? '',
                $dados['bairro']      ?? '',
                $dados['cidade']      ?? '',
                $dados['uf']          ?? '',
                $id
            ]);

            // 3. Salva e-mail de contato (UPSERT: tenta atualizar, senão insere)
            if (!empty($dados['email_contato'])) {
                $upd = $this->db->prepare(
                    "UPDATE contato SET valor = ? WHERE idReferencia = ? AND tipo_entidade = 'instituicao' AND tipo_contato = 'email_secretaria'"
                );
                $upd->execute([$dados['email_contato'], $id]);

                if ($upd->rowCount() === 0) {
                    $this->db->prepare(
                        "INSERT INTO contato (idReferencia, tipo_entidade, tipo_contato, valor) VALUES (?, 'instituicao', 'email_secretaria', ?)"
                    )->execute([$id, $dados['email_contato']]);
                }
            }

            // 4. Salva telefone principal (UPSERT: tenta atualizar, senão insere)
            if (!empty($dados['telefone'])) {
                $upd = $this->db->prepare(
                    "UPDATE contato SET valor = ? WHERE idReferencia = ? AND tipo_entidade = 'instituicao' AND tipo_contato = 'fixo'"
                );
                $upd->execute([$dados['telefone'], $id]);

                if ($upd->rowCount() === 0) {
                    $this->db->prepare(
                        "INSERT INTO contato (idReferencia, tipo_entidade, tipo_contato, valor) VALUES (?, 'instituicao', 'fixo', ?)"
                    )->execute([$id, $dados['telefone']]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function updateCompleto($id, $dados) {
        try {
            $agora = date('Y-m-d H:i:s');
            if (!$this->db->inTransaction()) { $this->db->beginTransaction(); }

            // 1. Atualiza os dados principais da instituição
            $sql = "UPDATE instituicao SET 
                        razao_social = ?, nome_fantasia = ?, cnpj = ?, 
                        pode_editar_instituicao = ?, label_edita_instituicao = ?, 
                        pode_editar_curso = ?, label_edita_curso = ?, 
                        valor_documento_nacional = ?, valor_frete = ?, 
                        idStatus = 2, updated_at = ? 
                    WHERE idInstituicao = ?";

            $this->db->prepare($sql)->execute([
                $dados['razao_social']             ?? '',
                $dados['nome_fantasia']             ?? '',
                $dados['cnpj']                     ?? '',
                $dados['pode_editar_instituicao']  ?? 'nao',
                $dados['label_edita_instituicao']  ?? '',
                $dados['pode_editar_curso']        ?? 'nao',
                $dados['label_edita_curso']        ?? '',
                $dados['valor_documento_nacional'] ?? 0,
                $dados['valor_frete']              ?? 0,
                $agora,
                $id
            ]);

            // 2. Atualiza catraca (UPSERT)
            $usaCatraca = $dados['usa_catraca'] ?? 'nao';
            $upd = $this->db->prepare(
                "UPDATE instituicao_catraca SET usa_catraca = ?, modelo = ?, quantidade = ? WHERE idInstituicao = ?"
            );
            $upd->execute([
                $usaCatraca,
                $dados['modelo_catraca']    ?? '',
                $dados['quantidade_catraca'] ?? 0,
                $id
            ]);
            if ($upd->rowCount() === 0) {
                $this->db->prepare(
                    "INSERT INTO instituicao_catraca (idInstituicao, usa_catraca, modelo, quantidade) VALUES (?, ?, ?, ?)"
                )->execute([$id, $usaCatraca, $dados['modelo_catraca'] ?? '', $dados['quantidade_catraca'] ?? 0]);
            }

            // 3. Atualiza endereço (UPSERT)
            $updEnd = $this->db->prepare(
                "UPDATE endereco SET cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, uf = ?
                 WHERE idReferencia = ? AND tipo_entidade = 'instituicao'"
            );
            $updEnd->execute([
                $dados['cep']         ?? '',
                $dados['logradouro']  ?? '',
                $dados['numero']      ?? '',
                $dados['complemento'] ?? '',
                $dados['bairro']      ?? '',
                $dados['cidade']      ?? '',
                $dados['uf']          ?? '',
                $id
            ]);
            if ($updEnd->rowCount() === 0) {
                $this->db->prepare(
                    "INSERT INTO endereco (idReferencia, tipo_entidade, cep, logradouro, numero, complemento, bairro, cidade, uf)
                     VALUES (?, 'instituicao', ?, ?, ?, ?, ?, ?, ?)"
                )->execute([$id, $dados['cep'] ?? '', $dados['logradouro'] ?? '', $dados['numero'] ?? '', $dados['complemento'] ?? '', $dados['bairro'] ?? '', $dados['cidade'] ?? '', $dados['uf'] ?? '']);
            }

            // 4. Atualiza e-mail de contato (UPSERT)
            if (!empty($dados['email_contato'])) {
                $upd = $this->db->prepare(
                    "UPDATE contato SET valor = ? WHERE idReferencia = ? AND tipo_entidade = 'instituicao' AND tipo_contato = 'email_secretaria'"
                );
                $upd->execute([$dados['email_contato'], $id]);
                if ($upd->rowCount() === 0) {
                    $this->db->prepare(
                        "INSERT INTO contato (idReferencia, tipo_entidade, tipo_contato, valor) VALUES (?, 'instituicao', 'email_secretaria', ?)"
                    )->execute([$id, $dados['email_contato']]);
                }
            }

            // 5. Atualiza telefone principal (UPSERT)
            if (!empty($dados['telefone'])) {
                $upd = $this->db->prepare(
                    "UPDATE contato SET valor = ? WHERE idReferencia = ? AND tipo_entidade = 'instituicao' AND tipo_contato = 'fixo'"
                );
                $upd->execute([$dados['telefone'], $id]);
                if ($upd->rowCount() === 0) {
                    $this->db->prepare(
                        "INSERT INTO contato (idReferencia, tipo_entidade, tipo_contato, valor) VALUES (?, 'instituicao', 'fixo', ?)"
                    )->execute([$id, $dados['telefone']]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function findById($id) {
        $sql = "SELECT i.*, e.cep, e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.uf,
                       ic.modelo, ic.quantidade, COALESCE(ic.usa_catraca, 'nao') as usa_catraca,
                       (SELECT valor FROM contato WHERE idReferencia = i.idInstituicao AND tipo_entidade = 'instituicao' AND tipo_contato = 'email_secretaria' LIMIT 1) as email_contato,
                       (SELECT valor FROM contato WHERE idReferencia = i.idInstituicao AND tipo_entidade = 'instituicao' AND (tipo_contato = 'fixo' OR tipo_contato = 'celular') LIMIT 1) as telefone
                FROM instituicao i
                LEFT JOIN endereco e ON e.idReferencia = i.idInstituicao AND e.tipo_entidade = 'instituicao'
                LEFT JOIN instituicao_catraca ic ON ic.idInstituicao = i.idInstituicao
                WHERE i.idInstituicao = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function resumoDashboardUnes() {
        try {
            // Agora cada contagem ignora quem tem status 1 (Inativo/Excluído)
            $sql = "SELECT 
                        (SELECT COUNT(*) FROM instituicao 
                        WHERE idStatus = 3) as validar,
                        
                        (SELECT COUNT(DISTINCT i.idInstituicao) FROM instituicao i 
                        LEFT JOIN instituicao_catraca ic ON i.idInstituicao = ic.idInstituicao 
                        WHERE i.idStatus = 2 
                        AND (ic.usa_catraca = 'nao' OR ic.usa_catraca IS NULL)) as sem_catraca,
                        
                        (SELECT COUNT(DISTINCT i.idInstituicao) FROM instituicao i 
                        INNER JOIN instituicao_catraca ic ON i.idInstituicao = ic.idInstituicao 
                        WHERE i.idStatus = 2 
                        AND ic.usa_catraca = 'sim') as com_catraca";
                        
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erro no Dashboard: " . $e->getMessage());
        }
    }

    /**
     * Cria a instituição e o usuário administrador numa ÚNICA transação atômica.
     * Se o email ou username do usuário já existirem, faz rollback de tudo.
     *
     * @param \Models\RegisterRequestModelInstituicaoEnsino $modelInst
     * @param \Models\RegisterRequestModelUsuario           $modelUser
     * @param string                                        $senhaHash  Hash bcrypt já pronto
     * @return array  ['idInstituicao' => int, 'idUsuario' => int]
     * @throws Exception em qualquer falha (com mensagem adequada)
     */
    public function createComAdministrador(
        \Models\RegisterRequestModelInstituicaoEnsino $modelInst,
        \Models\RegisterRequestModelUsuario $modelUser,
        string $senhaHash
    ): array {
        try {
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }

            // 1. Verifica duplicidade de email e username ANTES de gravar qualquer coisa
            $sqlChk = "SELECT COUNT(*) as total FROM usuario WHERE email = :email OR username = :username";
            $stmtChk = $this->db->prepare($sqlChk);
            $stmtChk->execute([':email' => $modelUser->email, ':username' => $modelUser->username]);
            $chk = $stmtChk->fetch(PDO::FETCH_ASSOC);

            if (($chk['total'] ?? 0) > 0) {
                // Verifica qual dos dois está duplicado para dar mensagem precisa
                $sqlEmail = "SELECT COUNT(*) as c FROM usuario WHERE email = :email";
                $stmtE = $this->db->prepare($sqlEmail);
                $stmtE->execute([':email' => $modelUser->email]);
                if (($stmtE->fetch(PDO::FETCH_ASSOC)['c'] ?? 0) > 0) {
                    throw new Exception("O e-mail informado já está em uso por outro usuário.", 409);
                }
                throw new Exception("O nome de usuário (login) já está em uso. Escolha outro.", 409);
            }

            $agora = date('Y-m-d H:i:s');

            // 2. Grava a Instituição
            $sqlInst = "INSERT INTO instituicao (razao_social, nome_fantasia, cnpj, idStatus, created_at) VALUES (?, ?, ?, 3, ?)";
            $this->db->prepare($sqlInst)->execute([
                $modelInst->razao_social,
                $modelInst->nome_fantasia,
                $modelInst->cnpj,
                $agora
            ]);
            $idInst = $this->db->lastInsertId();

            // Sincroniza idLegado
            $this->db->prepare("UPDATE instituicao SET idLegado = ? WHERE idInstituicao = ?")->execute([$idInst, $idInst]);

            // 3. Grava o Endereço
            $sqlEnd = "INSERT INTO endereco (idReferencia, tipo_entidade, cep, logradouro, numero, complemento, bairro, cidade, uf)
                       VALUES (?, 'instituicao', ?, ?, ?, ?, ?, ?, ?)";
            $this->db->prepare($sqlEnd)->execute([
                $idInst,
                $modelInst->cep,
                $modelInst->logradouro,
                $modelInst->numero,
                $modelInst->complemento ?? '',
                $modelInst->bairro,
                $modelInst->cidade,
                $modelInst->uf
            ]);

            // 4. Grava Contatos da Instituição
            $sqlCont = "INSERT INTO contato (idReferencia, tipo_entidade, tipo_contato, valor) VALUES (?, 'instituicao', ?, ?)";
            $stmtCont = $this->db->prepare($sqlCont);
            if (!empty($modelInst->email_contato)) {
                $stmtCont->execute([$idInst, 'email_secretaria', $modelInst->email_contato]);
            }
            if (!empty($modelInst->telefone)) {
                $stmtCont->execute([$idInst, 'fixo', $modelInst->telefone]);
            }

            // 5. Grava o Usuário Administrador vinculado à instituição
            $sqlUser = "INSERT INTO usuario (idAcl, idStatus, idPerfil, idInstituicao, primeiro_nome, sobrenome, cargo, email, username, senha)
                        VALUES (:idAcl, :idStatus, :idPerfil, :idInst, :p_nome, :sobrenome, :cargo, :email, :username, :senha)";
            $stmtUser = $this->db->prepare($sqlUser);
            $stmtUser->execute([
                ':idAcl'    => 3,       // ACL: Instituição de Ensino
                ':idStatus' => 2,       // Status: Ativo
                ':idPerfil' => 4,       // Perfil: AdministradorInstituicao
                ':idInst'   => $idInst,
                ':p_nome'   => $modelUser->primeiro_nome,
                ':sobrenome'=> $modelUser->sobrenome,
                ':cargo'    => $modelUser->cargo,
                ':email'    => $modelUser->email,
                ':username' => $modelUser->username,
                ':senha'    => $senhaHash
            ]);
            $idUsuario = $this->db->lastInsertId();

            // 6. Grava contato do usuário (celular) se informado
            if (!empty($modelUser->celular)) {
                $this->db->prepare("INSERT INTO contato (idReferencia, tipo_entidade, tipo_contato, valor) VALUES (?, 'usuario', 'celular', ?)")
                         ->execute([$idUsuario, $modelUser->celular]);
            }

            $this->db->commit();

            return ['idInstituicao' => $idInst, 'idUsuario' => $idUsuario];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            // Relança a exceção com o código HTTP correto preservado (ex: 409)
            throw new Exception($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}