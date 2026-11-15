-- Vívere SGC — script de criação do banco (DDL) — MySQL 8 / MariaDB 10.6+
CREATE DATABASE IF NOT EXISTS vivere_sgc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vivere_sgc;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  perfil ENUM('paciente','medico','recepcionista') NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS especialidades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pacientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL UNIQUE,
  nome VARCHAR(150) NOT NULL,
  cpf CHAR(11) NOT NULL UNIQUE,
  telefone VARCHAR(20) NOT NULL,
  email VARCHAR(150) NOT NULL,
  nascimento DATE NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS medicos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL UNIQUE,
  nome VARCHAR(150) NOT NULL,
  especialidade_id INT NOT NULL,
  crm VARCHAR(30) NOT NULL UNIQUE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (especialidade_id) REFERENCES especialidades(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS recepcionistas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL UNIQUE,
  nome VARCHAR(150) NOT NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- Horários que o médico bloqueou (folgas, ausências, encaixes) — RF20/RF21
CREATE TABLE IF NOT EXISTS agendamentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  medico_id INT NOT NULL,
  data DATE NOT NULL,
  hora CHAR(5) NOT NULL,
  disponivel TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uq_bloqueio (medico_id, data, hora),
  FOREIGN KEY (medico_id) REFERENCES medicos(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS consultas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  paciente_id INT NOT NULL,
  medico_id INT NOT NULL,
  data DATE NOT NULL,
  hora CHAR(5) NOT NULL,
  status ENUM('agendada','confirmada','cancelada','realizada','faltou') NOT NULL DEFAULT 'agendada',
  criado_em DATETIME NOT NULL,
  INDEX idx_medico_data (medico_id, data),
  INDEX idx_paciente (paciente_id),
  FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
  FOREIGN KEY (medico_id) REFERENCES medicos(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS prontuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  paciente_id INT NOT NULL,
  medico_id INT NOT NULL,
  consulta_id INT NULL,
  queixa TEXT NOT NULL,
  evolucao TEXT NOT NULL,
  criado_em DATETIME NOT NULL,
  FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
  FOREIGN KEY (medico_id) REFERENCES medicos(id),
  FOREIGN KEY (consulta_id) REFERENCES consultas(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reset_senhas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expira_em DATETIME NOT NULL,
  usado TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  acao VARCHAR(60) NOT NULL,
  detalhe VARCHAR(255) NOT NULL DEFAULT '',
  ip VARCHAR(45) NOT NULL,
  criado_em DATETIME NOT NULL,
  INDEX idx_logs_usuario (usuario_id)
) ENGINE=InnoDB;
