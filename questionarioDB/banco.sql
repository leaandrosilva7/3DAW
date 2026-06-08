CREATE DATABASE IF NOT EXISTS questionario;

USE questionario;

CREATE TABLE IF NOT EXISTS perguntas (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    tipo     VARCHAR(10),
    pergunta TEXT,
    resposta TEXT
);
