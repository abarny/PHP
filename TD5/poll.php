<?php include('header.php'); ?>

<?php
$poll = false;
if (isset($_GET['id'])) {
    $req = 'SELECT * FROM polls WHERE id= ?';
    $query = $pdo->prepare($req);
    $query->execute(array($_GET['id']));
    if ($query->rowCount()) {
        $poll = $query->fetch();
    }
}

if (!$poll) {
?>
<h2>Sondage</h2>

<div class="alert alert-danger">
    Sondage non trouvé.
</div>
<?php
} else {

$userAnswered = false;
$answers = null;

if ($currentUser) {
    $req = 'SELECT * FROM answers WHERE poll_id= ? AND user_id= ?';
    $query = $pdo->prepare($req);
    $query->execute(array($poll['id'], $currentUser['id']));
    $req =$query->fetch();
    if ($query->rowCount()) {
        $userAnswered = true;
    } else {
        if ($_SERVER['REQUEST_METHOD']=='POST' && !empty($_POST['answer']) &&
            ($_POST['answer']=='1' || $_POST['answer']=='2' || $_POST['answer']=='3')) {
                $req = 'INSERT INTO answers (user_id, poll_id, answer)
                    VALUES (?,?,?)';
            $query = $pdo->prepare($req);
            $query->execute(array($currentUser['id'], $poll['id'], $_POST['answer']));
            $req =$query->fetch();
                $userAnswered = true;
            }
    }

    if ($userAnswered) {
        $answers = array();
        foreach (array(1,2,3) as $answer) {
            $req ='SELECT COUNT(*) as nb FROM answers WHERE 
                poll_id= ? AND answer= ?';
            $query = $pdo->prepare($req);
            $query->execute(array($poll['id'], $answer));
            $result =$query->fetch();
            $answers[$answer] = $result['nb'];
        }
        $total = array_sum($answers);
    }
}

?>

<h2><?php echo $poll['question']; ?></h2>

<form method="post">

<?php foreach (array(1,2,3) as $answer) { ?>

<?php if (!$poll['answer'.$answer]) continue; ?>

<h3>
    <label>
    <?php if ($currentUser && !$userAnswered) { ?>
    <input type="radio" name="answer" value="<?php echo $answer; ?>" />
    <?php } ?>
    <?php echo $poll['answer'.$answer]; ?>
    </label>
</h3>

<?php
if ($answers) {
$pct = round($answers[$answer]*100/$total);

?>

<div class="progress">
<div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $pct; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $pct; ?>%;">
  <?php echo $pct; ?>%
  </div>
</div>

<?php } ?>
<?php } ?>

<?php if ($currentUser) { 
    if (!$userAnswered) { 
?>
    <input class="btn btn-success pull-right" type="submit" value="Participer!" />
<?php
}
} else {
?>
<div class="alert alert-warning">
Vous devez être identifié pour participer!
</div>
<?php
} 
?>
</form>

<?php } ?>

<?php include('footer.php'); ?>
