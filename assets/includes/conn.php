    <?php

    class DbhConnection
    {
        private $servername;
        private $username;
        private $password;
        private $dbname;
        private $charset;

        public function connect()
        {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' || preg_match('/^192\.168\.2/', $_SERVER['SERVER_ADDR'])) {
                // Local development environment
                $this->servername = 'localhost';
                $this->username   = 'root';
                $this->password   = '';
                $this->dbname     = 'stages';
                $this->charset    = 'utf8mb4';
            } else {
                // Production environment
                $this->servername = 'localhost';
                $this->username   = '';
                $this->password   = '';
                $this->dbname     = '';
                $this->charset    = 'utf8mb4';
            }

            $dsn = 'mysql:host=' . $this->servername . ';dbname=' . $this->dbname . ';charset=' . $this->charset;

            try {
                $pdo = new PDO($dsn, $this->username, $this->password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $pdo;
            } catch (PDOException $e) {
                echo 'Connection failed: ' . $e->getMessage();
                return null;
            }
        }
    }
    ?>
