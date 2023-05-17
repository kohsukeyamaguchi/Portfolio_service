<?php

require('function.php');

require('auth.php');

if (!empty($_POST)) {
  try {

    $dbh = dbConnect();

    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE user_id = :us_id';
    $sql2 = 'UPDATE portfolio  SET delete_flg = 1 WHERE user_id = :us_id';
    $sql3 = 'UPDATE favorite SET delete_flg = 1 WHERE user_id = :us_id';

    $data = array(':us_id' => $_SESSION['user_id']);

    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    $stmt3 = queryPost($dbh, $sql3, $data);

    if ($stmt1) {
      session_destroy();

      header("Location:top.php");
    } else {
      $err_msg['common'] = MSG07;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
?>

<?php
$siteTitle = '退会';
require('head.php');
?>

<body class="page-withdraw page-1colum">

  <style>
    .form .btn {
      float: none;
    }

    .form {
      text-align: center;
    }
  </style>

  <!-- メニュー -->
  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <!-- Main -->
    <section id="main">
      <div class="form-container">
        <form action="" method="post" class="form">
          <h2 class="title">退会</h2>
          <div class="area-msg">
            <?php
            if (!empty($err_msg['common'])) echo $err_msg['common'];
            ?>
          </div>
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="退会する" name="submit">
          </div>
        </form>
      </div>
      <a href="mypage.php">&lt; マイページに戻る</a>
    </section>
  </div>

  <!-- footer -->
  <?php
  require('footer.php');
  ?>