# myspace_clone
una wannabe myspace
MySpace Clone

Questo progetto è un piccolo social network ispirato a MySpace, sviluppato in PHP e MySQL come esercizio personale per imparare a gestire autenticazione, sessioni e profili utente. Include registrazione, login, logout, pagina profilo e personalizzazione del tema.

Caratteristiche principali

Registrazione con username, email e password hashata

Login e gestione della sessione

Pagina profilo accessibile solo dopo l'autenticazione

Modifica della bio e del colore tema personale

Eliminazione del proprio account

Struttura chiara dei file e connessione al database tramite PDO

Struttura del progetto

myspace-clone/
│
├── index.html (presentazione statica del progetto visibile su GitHub)
├── index.php (reindirizzamento e accesso al profilo se loggati)
├── login.php (form di login)
├── register.php (form di registrazione)
├── profile.php (pagina profilo utente)
├── logout.php (logout)
├── delete_account.php (eliminazione account)
├── config.php (connessione al database e sessioni)
├── style.css (stili base)
│
└── eventuali cartelle aggiuntive (img/, assets/, ecc.)

Requisiti

PHP 7.x o superiore

MySQL o MariaDB

Un ambiente come XAMPP, WAMP, MAMP o un server con supporto PHP

Configurazione del database

Aprire phpMyAdmin.

Creare un database chiamato: myspace_clone

Creare la tabella "users" con questa struttura:

CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50) NOT NULL UNIQUE,
email VARCHAR(100) NOT NULL UNIQUE,
password_hash VARCHAR(255) NOT NULL,
bio TEXT,
profile_color VARCHAR(7) DEFAULT '#222222',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

Esecuzione in locale

Clonare o scaricare il repository.

Copiare la cartella del progetto nella directory del server locale (ad esempio: htdocs/myspace-clone).

Avviare Apache e MySQL.

Verificare in config.php i dati di accesso al database.

Aprire nel browser: http://localhost/myspace-clone/

Per accedere al profilo è necessario registrare un nuovo utente.

Note importanti

GitHub non esegue file PHP. Per vedere il progetto funzionante è necessario un ambiente locale o un hosting che supporti PHP.

Il file index.html funge da pagina di presentazione ed è mostrato automaticamente da GitHub.

Possibili estensioni

Avatar utente e immagini di sfondo personalizzabili

Pagina profilo pubblica tramite URL dedicato

Bacheca dei commenti

Sistema di amici

Temi avanzati in stile MySpace anni 2000

Messaggi privati

Autrice

Progetto realizzato da Federica Perez come esercizio di studio nello sviluppo web.
