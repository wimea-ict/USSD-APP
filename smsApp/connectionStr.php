<?php
   class connectionStr{
        private $host;
        private $username;
        private $password;
        private $database;

        public function connectionFc()
        {
            $this->host = "localhost";
            $this->username = "root";
            $this->password = "karanzi";
            $this->database = "diss";

            $conn= new mysqli($this->host,$this->username,$this->password,$this->database);
            return $conn;
        }
}
?>
