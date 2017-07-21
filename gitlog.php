#!/usr/bin/env php <?php
date_default_timezone_set('Europe/London');
exec('pwd',$pwd);
$repo = rtrim(array_shift($pwd),'/');
$repo = substr($repo,strrpos($repo,'/') + 1);
$db = new PDO('mysql:dbname=DB;host=127.0.0.1','USERNAME','PASSWORD');
exec('git log --all --pretty=format:"%H%n%ct%n%s%n%b%n<><><>"',$capture,$log);
if ($capture){
    // preprocess the log
    $commits = array();
    $current = array();
    foreach ($capture as $row){
        if (trim($row) === '<><><>') {
            $commits[] = $current;
            $current = array();
        } else {
            $current[] = $row;
        }
    }
    $v = array();
    $b = array();
    foreach ($commits as $commit){
        $sha = $commit[0];
        $m = $commit[2] . (trim($commit[3]) === '' ? '' : "\n\n" . implode("\n",array_slice($commit,3)));
        $d = date('Y-m-d H:i:s',$commit[1]);
        $v[] = '(?,?,?,?)';
        $b[] = $repo;
        $b[] = $sha;
        $b[] = $m;
        $b[] = $d;
    }
    $stmt = $db->prepare('insert ignore into log (repo,commit,message,`date`) values' . implode(',',$v));
    try {
        if ($stmt) {
            if (!$stmt->execute($b)) throw new PDOException;;
        } else Throw new PDOException;
    } catch (PDOException $e) {
        mail('EMAIL','Commit did not reach db',$e->getMessage());
    }
}
?>
