<?php

namespace Studio\Bridge\Support;

use PDO;

class Database
{
    public PDO $db;

    public function __construct()
    {
        $this->db = new PDO('sqlite:../database/db.sqlite');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }

    public function init(): void
    {
        $query =
            "
            CREATE TABLE IF NOT EXISTS conversations
	        	(
	            	id              INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	                account_id      INTEGER,
	                conversation_id INTEGER UNIQUE,
	                conversation_token TEXT,
	        		ended TEXT
	            )
            ";

        $this->db->exec($query);
    }
}
