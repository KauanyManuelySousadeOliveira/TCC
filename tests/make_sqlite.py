"""Converte database/schema.sql (MySQL) para SQLite — usado só nos testes automatizados."""
import re, sys
s = open('database/schema.sql', encoding='utf8').read()
s = re.sub(r'CREATE DATABASE[^;]*;|USE [^;]*;', '', s)
s = s.replace('INT AUTO_INCREMENT PRIMARY KEY', 'INTEGER PRIMARY KEY AUTOINCREMENT')
s = re.sub(r'ENUM\([^)]*\)', 'TEXT', s)
s = s.replace('TINYINT(1)', 'INTEGER').replace(') ENGINE=InnoDB;', ');')
s = re.sub(r'UNIQUE KEY \w+ \(', 'UNIQUE (', s)
s = re.sub(r',\s*INDEX \w+ \([^)]*\)', '', s)
open(sys.argv[1], 'w', encoding='utf8').write(s)
