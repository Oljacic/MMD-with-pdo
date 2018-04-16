<?php

if (get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);

    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripcslashes($k)];
            }
            else {
                $process[$key][stripslashes($k)] = stripcslashes($v);
            }
        }
    }
    unset($process);
}

if (isset($_GET['addjoke'])) {
    include 'template/form.html.php';
    exit();
}

try {
    $pdo = new PDO('mysql:host=*host*t;dbname=*databasename*', '*username*', '*pass*');//here you need your host, your databasename, username and pass
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('SET NAMES "utf8"');
}
catch (PDOException $e) {
    $error = 'Unable to connect to the database server.';
    include 'template/error.html.php';
    exit();
}

if (isset($_POST['joketext'])) {
    try {
        $sql = 'INSERT INTO joke SET
            joketext = :joketext,
            jokedate = CURDATE()';
        $s = $pdo->prepare($sql);
        $s->bindValue(':joketext', $_POST['joketext']);
        $s->execute();
    }
    catch (PDOException $e) {
        $error = 'Error adding submited joke:' . $e->getMessage();
        include 'template/error.html.php';
        exit();
    }

    header('Location: .');
    exit();
}

if (isset($_GET['deletejoke']))
{
    try {
        $sql = 'DELETE FROM joke WHERE id = :id';
        $s = $pdo->prepare($sql);
        $s->bindValue(':id', $_POST['id']);
        $s->execute();
    }
    catch(PDOException $e) {
        $error = 'Error deleting joke: ' . $e->getMessage();
        include 'template/error.html.php';
        exit();
    }
}

try {
    $sql = 'SELECT id, joketext FROM joke';
    $result = $pdo->query($sql);
}
catch(PDOException $e)  {
    $error = 'Error fetching jokes: ' . $e->getMessage();
    include 'template/error.html.php';
    exit();
}
// while loope example in list jokes folder
foreach ($result as $row) {
    $jokes[] = array('id' => $row['id'], 'text' => $row['joketext']);
}

include 'template/jokes.html.php';