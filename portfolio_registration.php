<?php

require('function.php');

require('auth.php');

$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';

$dbFormData = (!empty($p_id)) ? getPortfolio($_SESSION['user_id'], $p_id) : '';

$edit_flg = (empty($dbFormData)) ? false : true;

$dbCategoryData = getCategory();

$err_msg = null;

if (!empty($p_id) && empty($dbFormData)) {
  header('Location:mypage.php');
}

if (!empty($_POST)) {

  $name = $_POST['name'];
  $category_id = $_POST['category_id'];
  $details = $_POST['details'];
  $p_language = $_POST['p_language'];
  $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'], 'pic1') : '';
  $pic1 = (empty($pic1) && !empty($dbFormData['pic1'])) ? $dbFormData['pic1'] : $pic1;
  $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'], 'pic2') : '';
  $pic2 = (empty($pic2) && !empty($dbFormData['pic2'])) ? $dbFormData['pic2'] : $pic2;
  $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'], 'pic3') : '';
  $pic3 = (empty($pic3) && !empty($dbFormData['pic3'])) ? $dbFormData['pic3'] : $pic3;

  // 更新の場合はDBの情報と入力情報が異なる場合にバリデーションを行う
  if (empty($dbFormData)) {
    //未入力チェック
    validRequired($name, 'name');
    //最大文字数チェック
    validMaxLen($name, 'name');
    //セレクトボックスチェック
    validSelect($category_id, 'category_id');
    //最大文字数チェック
    validMaxLen($details, 'details', 500);
    //未入力チェック
    validRequired($p_language, 'p_language');
    //最大文字数チェック
    validMaxLen($p_language, 'p_language');
  } else {
    if ($dbFormData['portfolio_name'] !== $name) {
      //未入力チェック
      validRequired($name, 'name');
      //最大文字数チェック
      validMaxLen($name, 'name');
    }
    if ($dbFormData['category_id'] !== $category_id) {
      //セレクトボックスチェック
      validSelect($category_id, 'category_id');
    }
    if ($dbFormData['details'] !== $details) {
      //最大文字数チェック
      validMaxLen($details, 'details', 500);
    }
    if ($dbFormData['p_language'] !== $p_language) {
      //未入力チェック
      validRequired($p_language, 'p_language');
      //最大文字数チェック
      validMaxLen($p_language, 'p_language');
    }
  }


  if (empty($err_msg)) {
    debug('バリデーションOKです。');

    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      // 編集画面の場合はUPDATE文、新規登録画面の場合はINSERT文を生成
      if ($edit_flg) {
        debug('DB更新です。');
        $sql = 'UPDATE portfolios SET portfolio_name = :name, details = :details, p_language = :p_language, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3,  category_id = :category_id WHERE creater_id = :u_id AND portfolio_id = :p_id';
        $data = array(':name' => $name, ':details' => $details, ':p_language' => $p_language, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':category_id' => $category_id, ':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
      } else {
        debug('DB新規登録です。');
        $sql = 'insert into portfolios (portfolio_name, details, p_language, pic1, pic2, pic3, creater_id, registration_date, update_date, category_id ) values (:name, :details, :p_language, :pic1, :pic2, :pic3, :u_id, :date, :date, :category_id )';
        $data = array(':name' => $name, ':details' => $details, ':p_language' => $p_language, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'), ':category_id' => $category_id,);
      }
      debug('SQL:' . $sql);
      debug('流し込みデータ：' . print_r($data, true));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if ($stmt) {
        $_SESSION['msg_success'] = SUC04;
        debug('マイページへ遷移します。');
        header("Location:mypage.php"); //マイページへ
      }
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}

?>


<?php
$siteTitle = 'ポートフォリオ登録';
require('head.php');
?>

<body class="page-profEdit page-2colum page-logined">

  <!-- メニュー -->
  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <h1 class="page-title"><?php echo (!$edit_flg) ? 'ポートフォリオを登録する' : 'ポートフォリオを編集する'; ?></h1>
    <!-- Main -->
    <section id="main">
      <div class="form-container">
        <form action="" method="post" class="form" enctype="multipart/form-data" style="width:100%;box-sizing:border-box;">
          <div class="area-msg">
            <?php
            if (!empty($err_msg['common'])) echo $err_msg['common'];
            ?>
          </div>
          <label class="<?php if (!empty($err_msg['name'])) echo 'err'; ?>">
            ポートフォリオ名<span class="label-require">必須</span>
            <input type="text" name="name" value="<?php echo getFormData('portfolio_name'); ?>">
          </label>
          <div class="area-msg">
            <?php
            if (!empty($err_msg['name'])) echo $err_msg['name'];
            ?>
          </div>
          <label class="<?php if (!empty($err_msg['category_id'])) echo 'err'; ?>">
            カテゴリ<span class="label-require">必須</span>
            <select name="category_id" id="">
              <option value="0" <?php if (getFormData('category_id') == 0) {
                                  echo 'selected';
                                } ?>>選択してください</option>
              <?php
              foreach ($dbCategoryData as $key => $val) {
                ?>
                <option value="<?php echo $val['category_id'] ?>" <?php if (getFormData('category_id') == $val['category_id']) {
                                                                      echo 'selected';
                                                                    } ?>>
                  <?php echo $val['category']; ?>
                </option>
              <?php
              }
              ?>
            </select>
          </label>
          <div class="area-msg">
            <?php
            if (!empty($err_msg['category'])) echo $err_msg['category'];
            ?>
          </div>
          <label class="<?php if (!empty($err_msg['details'])) echo 'err'; ?>">
            詳細
            <textarea name="details" id="js-count" cols="30" rows="10" style="height:150px;"><?php echo getFormData('details'); ?></textarea>
          </label>
          <p class="counter-text"><span id="js-count-view">0</span>/500文字</p>
          <div class="area-msg">
            <?php
            if (!empty($err_msg['details'])) echo $err_msg['details'];
            ?>
          </div>
          <label class="<?php if (!empty($err_msg['p_language'])) echo 'err'; ?>">
            開発言語<span class="label-require">必須</span>
            <input type="text" name="p_language" value="<?php echo getFormData('p_language'); ?>">
          </label>
          <div class="area-msg">
            <?php
            if (!empty($err_msg['p_language'])) echo $err_msg['p_language'];
            ?>
          </div>
          <div style="overflow:hidden;">
            <div class="imgDrop-container">
              画像1
              <label class="area-drop <?php if (!empty($err_msg['pic1'])) echo 'err'; ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic1" class="input-file">
                <img src="<?php echo getFormData('pic1'); ?>" alt="" class="prev-img" style="<?php if (empty(getFormData('pic1'))) echo 'display:none;' ?>">
                ドラッグ＆ドロップ
              </label>
              <div class="area-msg">
                <?php
                if (!empty($err_msg['pic1'])) echo $err_msg['pic1'];
                ?>
              </div>
            </div>
            <div class="imgDrop-container">
              画像２
              <label class="area-drop <?php if (!empty($err_msg['pic2'])) echo 'err'; ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic2" class="input-file">
                <img src="<?php echo getFormData('pic2'); ?>" alt="" class="prev-img" style="<?php if (empty(getFormData('pic2'))) echo 'display:none;' ?>">
                ドラッグ＆ドロップ
              </label>
              <div class="area-msg">
                <?php
                if (!empty($err_msg['pic2'])) echo $err_msg['pic2'];
                ?>
              </div>
            </div>
            <div class="imgDrop-container">
              画像３
              <label class="area-drop <?php if (!empty($err_msg['pic3'])) echo 'err'; ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic3" class="input-file">
                <img src="<?php echo getFormData('pic3'); ?>" alt="" class="prev-img" style="<?php if (empty(getFormData('pic3'))) echo 'display:none;' ?>">
                ドラッグ＆ドロップ
              </label>
              <div class="area-msg">
                <?php
                if (!empty($err_msg['pic3'])) echo $err_msg['pic3'];
                ?>
              </div>
            </div>
          </div>

          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="<?php echo (!$edit_flg) ? '登録する' : '更新する'; ?>">
          </div>
        </form>
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