<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　プロフィール編集ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');


//================================
// 画面処理
//================================
// DBからユーザーデータを取得
$dbFormData = getUser($_SESSION['user_id']);
$dbFormHistoryData = getWorkHistory($_SESSION['user_id']);

debug('取得したユーザー情報：' . print_r($dbFormData, true));
debug('業務データ：' . print_r($dbFormHistoryData, true));

// post送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');
  debug('POST情報：' . print_r($_POST, true));
  debug('FILE情報：' . print_r($_FILES, true));

  //変数にユーザー情報を代入
  $name = $_POST['name'];
  $gender = $_POST['gender'];
  $background = $_POST['background'];
  $pr = $_POST['pr'];
  $tastes1 = $_POST['tastes1'];
  $tastes2 = $_POST['tastes2'];
  $tastes3 = $_POST['tastes3'];
  $tastes4 = $_POST['tastes4'];
  $qualifications = $_POST['qualifications'];

  //  $zip = (!empty($_POST['zip'])) ? $_POST['zip'] : 0; //後続のバリデーションにひっかかるため、空で送信されてきたら0を入れる
  //  $addr = $_POST['addr'];
  //  $age = $_POST['age'];
  //  $email = $_POST['email'];
  //画像をアップロードし、パスを格納
  $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';
  // 画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
  $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;

  if (isset($_POST['business'])) {
    $business = $_POST['business'];
  }
  if (isset($_POST['work_period'])) {
    $workPeriod = $_POST['work_period'];
  }
  if (isset($_POST['contents'])) {
    $contents = $_POST['contents'];
  }
  if (isset($_POST['p_language'])) {
    $pLanguage = $_POST['p_language'];
  }
  //    debug('業務期間:'.$workPeriod);
  //    debug('業務内容：'.$contents);
  //    debug('プログラム言語：'.$pLanguage);

  //DBの情報と入力情報が異なる場合にバリデーションを行う
  if ($dbFormData['name'] !== $name) {
    //名前の最大文字数チェック
    validMaxLen($name, 'name');
    //名前の未入力チェック
    validRequired($name, 'name');
  }
  if ($dbFormData['background'] !== $background) {
    //最終学歴の最大文字数チェック
    validMaxLen($background, 'background');
    //最終学歴の未入力チェック
    validRequired($background, 'background');
  }
  if ($dbFormData['pr'] !== $pr) {
    //prの最大文字数チェック
    validMaxLen($pr, 'pr', 32767);
  }
  if ($dbFormData['tastes1'] !== $tastes1 || $dbFormData['tastes2'] !== $tastes2 || $dbFormData['tastes3'] !== $tastes3 || $dbFormData['tastes4'] !== $tastes4) {
    //資格の最大文字数チェック
    validMaxLen($tastes1, 'tastes1');
    validMaxLen($tastes2, 'tastes2');
    validMaxLen($tastes3, 'tastes3');
    validMaxLen($tastes4, 'tastes4');
  }
  if ($dbFormData['qualifications'] !== $qualifications) {
    //趣味の最大文字数チェック
  }


  if (empty($err_msg)) {
    debug('バリデーションOKです。');

    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'UPDATE users  SET name = :u_name, gender = :gender, background = :background, pr = :pr, tastes1 = :tastes1, tastes2 = :tastes2, tastes3 = :tastes3, tastes4 = :tastes4, qualifications = :qualifications, pic = :pic WHERE user_id = :u_id';
      $data = array(':u_name' => $name, ':gender' => $gender, ':background' => $background, ':pr' => $pr, ':tastes1' => $tastes1, ':tastes2' => $tastes2, ':tastes3' => $tastes3, ':tastes4' => $tastes4, ':qualifications' => $qualifications, ':pic' => $pic, ':u_id' => $dbFormData['user_id']);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if ($stmt) {
        $judge1 = true;
      } else {
        $judge1 = false;
      }
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }

    //例外処理２
    //例外処理
    try {
      //業務内容系入力フォームのDBレコード削除
      $dbh2 = dbConnect();
      $sql_delete_work = 'DELETE FROM work_history WHERE user_id = :u_id';
      $data_delete_work = array(':u_id' => $dbFormData['user_id']);
      $stmt_delete_work = queryPost($dbh2, $sql_delete_work, $data_delete_work);
      $judge2 = true;
      if ($stmt_delete_work) {
        $judge2 = true;
      } else {
        $judge2 = false;
      }
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }

    //例外処理3
    try {

      $stmt_insert_work = null;
      //業務内容入力フォームのDBレコード挿入
      if (isset($business)) {
        for ($i = 0; $i < count($business); $i++) {
          $sql_insert_work = 'INSERT INTO work_history (work_no, business, work_period, contents, p_language, delete_flg, registration_date, update_date, user_id)
             VALUES(NULL,:business,:work_period,:contents,:p_language,:delete_flg,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,:user_id)';

          $data_insert_work = array(
            ':business' => $business[$i], ':work_period' => $workPeriod[$i], ':contents' => $contents[$i], ':p_language' => $pLanguage[$i], ':delete_flg' => '0',
            ':user_id' => $dbFormData['user_id']
          );

          $stmt_insert_work = queryPost($dbh, $sql_insert_work, $data_insert_work);
        }
      }

      if ($stmt_insert_work) {
        $judge3 = true;
      } else {
        $judge3 = false;
      }
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }

    if(isset($business)){
        if ($judge1 == true && $judge2 == true && $judge3 == true) {
          $_SESSION['msg_success'] = SUC02;
          debug('マイページへ遷移します。');
          header("Location:mypage.php"); //マイページへ
        }
    }else{
        if ($judge1 == true && $judge2 == true) {
          $_SESSION['msg_success'] = SUC02;
          debug('マイページへ遷移します。');
          header("Location:mypage.php"); //マイページへ
        }
    }
      
    
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>


<?php
$siteTitle = 'プロフィール編集';
require('head.php');
?>

<body class="page-profEdit page-2colum page-logined">

  <!-- メニュー -->
  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <h1 class="page-title">プロフィール編集</h1>
    <!-- Main -->
    <section id="main">
      <div class="form-container">
        <form action="" method="post" class="form" enctype="multipart/form-data">
          <div class="area-msg">
            <?php
            if (!empty($err_msg['common'])) echo $err_msg['common'];
            ?>
          </div>
          <label class="<?php if (!empty($err_msg['name'])) echo 'err'; ?>">
            氏名
            <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
          </label>
          <div class="area-msg">
            <?php
            if (!empty($err_msg['name'])) echo $err_msg['name'];
            ?>
          </div>
          <label class="<?php if (!empty($err_msg['gender'])) echo 'err'; ?>">
            性別
            <select name="gender">
              <option value="men" <?php if (getFormData('gender') == 'men') echo 'selected'; ?>>男</option> <option value="women" <?php if (getFormData('gender') == 'women') echo 'selected'; ?>>女</option></select> </label> <div class="area-msg">
                <?php
                if (!empty($err_msg['gender'])) echo $err_msg['gender'];
                ?>
      </div>
      <label class="background">
        最終学歴
        <input type="text" name="background" value="<?php echo getFormData('background'); ?>">
      </label>
      <div class="area-msg">
        <?php
        if (!empty($err_msg['background'])) echo $err_msg['background'];
        ?>
      </div>
      <label class="<?php if (!empty($err_msg['pr'])) echo 'err'; ?>">
        PR
        <textarea id="textarea" name="pr"><?php echo getFormData('pr'); ?></textarea>
      </label>
      <div class="area-msg">
        <?php
        if (!empty($err_msg['pr'])) echo $err_msg['pr'];
        ?>
      </div>
      <label class="<?php if (!empty($err_msg['tastes'])) echo 'err'; ?>">
        資格
        <input type="text" name="tastes1" value="<?php echo getFormData('tastes1'); ?>">
        <input type="text" name="tastes2" value="<?php echo getFormData('tastes2'); ?>">
        <input type="text" name="tastes3" value="<?php echo getFormData('tastes3'); ?>">
        <input type="text" name="tastes4" value="<?php echo getFormData('tastes4'); ?>">
      </label>
      <div class="area-msg">
        <?php
        if (!empty($err_msg['tastes'])) echo $err_msg['tastes'];
        ?>
      </div>
      <label class="<?php if (!empty($err_msg['qualifications'])) echo 'err'; ?>">
        趣味
        <input type="text" name="qualifications" value="<?php echo getFormData('qualifications'); ?>">
      </label>
      <div class="area-msg">
        <?php
        if (!empty($err_msg['qualifications'])) echo $err_msg['qualifications'];
        ?>
      </div>
      プロフィール画像
      <label class="area-drop <?php if (!empty($err_msg['pic'])) echo 'err'; ?>" style="height:370px;line-height:370px;">
        <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
        <input type="file" name="pic" class="input-file" style="height:370px;">
        <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if (empty(getFormData('pic'))) echo 'display:none;' ?>">
        ドラッグ＆ドロップ
      </label>
      <div class="area-msg">
        <?php
        if (!empty($err_msg['pic'])) echo $err_msg['pic'];
        ?>
      </div>
      <!--              追加項目-->
      <div id="tag">
        <?php foreach ($dbFormHistoryData as $key => $val) { ?>
          <div id="form_area_0" class="second-form">
            <label class="<?php if (!empty($err_msg['business'])) echo 'err'; ?>">
              業務名
              <input type="text" name="business[]" value="<?php echo $val['business']; ?>">
            </label>
            <div class="area-msg">
              <?php
                if (!empty($err_msg['business'])) echo $err_msg['business'];
                ?>
            </div>
            <label class="<?php if (!empty($err_msg['work_period'])) echo 'err'; ?>">
              業務期間
              <input type="text" name="work_period[]" value="<?php echo $val['work_period'];  ?>">
            </label>
            <div class="area-msg">
              <?php
                if (!empty($err_msg['work_period'])) echo $err_msg['work_period'];
                ?>
            </div>
            <label class="<?php if (!empty($err_msg['contents'])) echo 'err'; ?>">
              業務内容
              <textarea id="textarea2" maxlength="255" name="contents[]" style="height:350px;"><?php echo $val['contents']; ?></textarea>
            </label>
            <div class="area-msg">
              <?php
                if (!empty($err_msg['contents'])) echo $err_msg['contents'];
                ?>
            </div>
            <label class="<?php if (!empty($err_msg['p_language'])) echo 'p_language'; ?>">
              開発言語
              <input type="text" name="p_language[]" value="<?php echo $val['p_language']; ?>">
            </label>
            <div class="area-msg">
              <?php
                if (!empty($err_msg['p_language'])) echo $err_msg['p_language'];
                ?>
            </div>

            <button id="0" type="bu" onclick="deleteBtn(this)">削除</button>
          </div>
        <?php } ?>
      </div>
      <div class="btn-container">

        <input type="submit" class="btn btn-mid" value="変更する">
      </div>
      </form>
      <div class="option-container">
        <button id="add" type="bu">業務実績を追加する</button>
      </div>



  </div>

  </section>

  <!-- サイドバー -->
  <?php
  require('sidebar.php');
  ?>
  </div>

  <!-- footer -->
  <?php
  require('footer.php');
  ?>