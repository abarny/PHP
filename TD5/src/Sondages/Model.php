<?php

namespace Sondages;

class Model {

    protected $pdo;

    public function checkConnection($id) {
        $sql = 'SELECT * FROM users WHERE id = ?';
        $query = $this->pdo->prepare($sql);
        $query->execute(array($id));
        return $this->fetchOne($query);
    }

     function addPoll($question, $answer1, $answer2, $answer3) {
        $sql = 'INSERT INTO polls (question, answer1, answer2, answer3) VALUES (?, ?, ?, ?)';
        $query = $this->pdo->prepare($sql);
        $query->execute(array($question, $answer1, $answer2, $answer3));
        return true;
    }

    public function getPolls() {
        //$sql = 'SELECT question, answer1, answer2, 3, COUNT(*) as nbPolls FROM polls GROUP BY question, answer1, answer2, answer3';
        $sql = 'SELECT * FROM polls';
        return $this->pdo->query($sql);
    }

    public function getPollsFromId($id) {
        /* $sql = 'SELECT * FROM polls WHERE id=?';
          var_dump($id);
          $query = $this->pdo->prepare($sql);
          $query->execute(array($id));
          return $query->fetch(); */
        // moche mais je truc de dessus marche pas.. pas compris
        $sql = 'SELECT * FROM polls WHERE id = ' . $id;
        return $this->pdo->query($sql);
    }

    public function getIdFromUser($user) {
        $sql = 'SELECT * FROM users WHERE login = ?';
        $query = $this->pdo->prepare($sql);
        $query->execute(array($user));
        return $query->fetch()["id"];
    }

    public function checkRegister($id) {
        $sql = 'SELECT COUNT(*) as nb FROM users WHERE login = ?';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(htmlspecialchars($_POST['login'])));
        return $query->fetch();
    }

    public function answered($user, $pollId) {
        $id = $this->getIdFromUser($user);

        $sql = 'SELECT * FROM answers WHERE poll_id = ? AND user_id = ?';
        $query = $this->pdo->prepare($sql);
        $query->execute(array($pollId, $id));
        if ($query->rowCount()) {
            return true;
        }
        return false;
    }

    public function __construct($host, $database, $user, $password) {
        try {
            $this->pdo = new \PDO('mysql:dbname=' . $database . ';host=' . $host, $user, $password);
            $this->pdo->exec('SET CHARSET UTF8');
        } catch (\PDOException $exception) {
            die('Impossible de se connecter au serveur MySQL : ' . $exception->getMessage());
        }
    }

    

    public function register($login, $password) {
        $sql = 'SELECT * FROM users WHERE login = ? and password = ?';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(htmlspecialchars($login), md5(htmlspecialchars($password))));
        if ($query->rowCount()) {
            return false;
        } else {
            $sql = 'INSERT INTO users (login, password) VALUES (?, ?)';
            $query = $this->pdo->prepare($sql);
            $query->execute(array(htmlspecialchars($login), md5(htmlspecialchars($password))));
            return true;
        }
    }

    protected function fetchOne(\PDOStatement $query) {
        if ($query->rowCount() != 1) {
            return false;
        } else {
            return $query->fetch();
        }
    }


    public function login($login, $password, $app) {
        $sql = 'SELECT * FROM users WHERE login = ?';
        $query = $this->pdo->prepare($sql);
        $query->execute(array(htmlspecialchars($login)));
        if ($query->rowCount()) {
            $user = $query->fetch();
            if ($user['password'] == md5($password)) {
                $app['session']->set('user', $user['login']);
            }
        }
    }

    
    
   

    function getTotal($pollId) {
        $answers = array();
        foreach (array(1, 2, 3) as $answer) {
            $sql = 'SELECT COUNT(*) as nb FROM answers WHERE poll_id = ? AND answer = ?';
            $query = $this->pdo->prepare($sql);
            $query->execute(array($pollId, $answer));

            $result = $query->fetch();
            $answers[$answer] = $result['nb'];
        }
        $total = array_sum($answers);
        
        return array('total' => $total, 'answers' => $answers);
    }

    function addAnswer($user, $pollId, $answer) {
        $userId = $this->getIdFromUser($user);
        $sql = 'INSERT INTO answers (user_id, poll_id, answer) VALUES (?, ?, ?)';
        $query = $this->pdo->prepare($sql);
        $query->execute(array($userId, $pollId, $answer));
    }

}
