<?php

ini_set('log_errors', 'on');

ini_set('error_log', 'php.log');

$debug_flg = true;

function debug($str)
{
  global $debug_flg;
  if (!empty($debug_flg)) {
    error_log('デバッグ' . $str);
  }
}

// セッション準備・セッション有効期限を延ばす
//================================
//セッションファイルの置き場を変更する（/var/tmp/以下に置くと30日は削除されない）
session_save_path("/var/tmp/");
//ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime ', 60 * 60 * 24 * 30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//================================
// 画面表示処理開始ログ吐き出し関数
//================================
function debugLogStart()
{
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID：' . session_id());
  debug('セッション変数の中身：' . print_r($_SESSION, true));
  debug('現在日時タイムスタンプ：' . time());
  if (!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
    debug('ログイン期限日時タイムスタンプ：' . ($_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}

//================================
// 定数
//================================
//エラーメッセージを定数に設定
define('MSG01', '入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03', 'パスワード（再入力）が合っていません');
define('MSG04', '半角英数字のみご利用いただけます');
define('MSG05', '6文字以上で入力してください');
define('MSG06', '256文字以内で入力してください');
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08', 'そのEmailは既に登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '電話番号の形式が違います');
define('MSG11', '郵便番号の形式が違います');
define('MSG12', '古いパスワードが違います');
define('MSG13', '古いパスワードと同じです');
define('MSG14', '文字で入力してください');
define('MSG15', '正しくありません');
define('MSG16', '有効期限が切れています');
define('MSG17', '半角数字のみご利用いただけます');
define('MSG18', 'すべての必須項目を選択または入力してください');
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '登録しました');
define('SUC05', '購入しました！相手と連絡を取りましょう！');

//================================
// グローバル変数
//================================
//エラーメッセージ格納用の配列
$err_msg = array();

//================================
// バリデーション関数
//================================
//バリデーション関数（Email形式チェック）
function validEmail($str, $key)
{
  if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}

//バリデーション関数（最大文字数チェック）
function validMaxLen($str, $key, $max = 256)
{
  if (mb_strlen($str) > $max) {
    global $err_msg;
    $err_msg[$key] = MSG06;
  }
}

//バリデーション関数（最小文字数チェック）
function validMinLen($str, $key, $min = 6)
{
  if (mb_strlen($str) < $min) {
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}

//バリデーション関数（半角チェック）
function validHalf($str, $key)
{
  if (!preg_match("/^[a-zA-Z0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}

function validSelect($category_value, $key)
{
  if ($category_value = "0") {
    global $err_msg;
    $err_msg[$key] = MSG18;
  }
}

function validRequired($str, $key)
{
  if (empty($str)) {
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}



function validMatch($str1, $str2, $key)
{
  if ($str1 !== $str2) {
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}

//パスワードチェック
function validPass($str, $key)
{
  //半角英数字チェック
  validHalf($str, $key);
  //最大文字数チェック
  validMaxLen($str, $key);
  //最小文字数チェック
  validMinLen($str, $key);
}

function validEmailDup($email)
{

  global $err_msg;
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT  count(*) FROM users WHERE email = :email';
    $data = array(':email' => $email);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    //クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty(array_shift($result))) {
      $err_msg['email'] = MSG08;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//================================
// データベース
//================================
//DB接続関数
function dbConnect()
{
  //DBへの接続準備
  $dsn = 'mysql:dbname=xs0553_portfolio;host=localhost;charset=utf8';
  $user = 'xs0553_root';
  $password = 'rootpass';
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成（DBへ接続）
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}

function queryPost($dbh, $sql, $data)
{
  //クエリー作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行
  if (!$stmt->execute($data)) {
    debug('クエリに失敗しました。');
    debug('失敗したSQL：' . print_r($stmt, true));
    $err_msg['common'] = MSG07;
    return 0;
  }
  debug('クエリ成功。');
  return $stmt;
}

function getMyPortfolios($u_id)
{
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT * FROM portfolios WHERE creater_id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    if ($stmt) {
      //クエリ結果のデータを全取得
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生' . $e->getMessage());
  }
}

function getMatchBoardInfo($sale_user, $buy_user, $portfolio_id)
{
  try {

    $dbh = dbConnect();

    // すでに連絡掲示板があるかどうか確認
    $sql = 'SELECT * FROM board WHERE sale_user = :sale AND buy_user = :buy AND portfolio_id = :portfolio_id';

    $data = array(':sale' => $sale_user, ':buy' => $buy_user, ':portfolio_id' => $portfolio_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      //クエリ結果のデータを全取得
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生' . $e->getMessage());
  }
}

function getMyMsgsAndBord($u_id)
{
  try {
    $dbh = dbConnect();

    //まず掲示板レコードを取得
    $sql = 'SELECT * FROM board AS b WHERE b.sale_user = :id OR b.buy_user = :id AND b.delete_flg = 0';
    $data = array(':id' => $u_id);
    $stmt = queryPost($dbh, $sql, $data);
    $rst = $stmt->fetchAll();

    if (!empty($rst)) {
      foreach ($rst as $key => $val) {
        $sql = 'SELECT * FROM message WHERE board_id = :id AND delete_flg = 0 ORDER BY transmission_date DESC';
        $data = array(':id' => $val['board_id']);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $rst[$key]['msg'] = $stmt->fetchAll();
      }
    }
    if ($stmt) {
      return $rst;
    } else {
      return false;
    }
  } catch (Exception $e) {

    error_log('エラー発生' . $e->getMessage());
  }
}

function getMyLike($u_id)
{

  try {
    //DBへ接続
    $dbh = dbConnect();

    //SQL文作成
    $sql = 'SELECT * FROM favorite AS f LEFT JOIN portfolios AS p  ON f.portfolio_id = p.portfolio_id WHERE f.user_id = :u_id';

    $data = array(':u_id' => $u_id);

    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生' . $e->getMessage());
  }
}

function getMyProf($u_id)
{
  try {
    //DBへ接続
    $dbh = dbConnect();

    //SQL文作成
    $sql = 'SELECT * FROM users WHERE user_id = :u_id AND delete_flg = 0';

    $data = array(':u_id' => $u_id);

    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生' . $e->getMessage());
  }
}

//エラーメッセージ表示
function getErrMsg($key)
{
  global $err_msg;
  if (!empty($err_msg[$key])) {
    return $err_msg[$key];
  }
}

function getMyWorkHistory($u_id)
{
  try {
    //DBへ接続
    $dbh = dbConnect();

    //SQL文作成
    $sql = 'SELECT * FROM work_history WHERE user_id = :u_id AND delete_flg = 0';

    $data = array(':u_id' => $u_id);

    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生' . $e->getMessage());
  }
}
//GETパラメータ付与
// $del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array())
{
  if (!empty($_GET)) {
    $str = '?';
    foreach ($_GET as $key => $val) {
      if (!in_array($key, $arr_del_key, true)) { //取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
        $str .= $key . '=' . $val . '&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}

//画像表示用関数
function showImg($path)
{
  if (empty($path)) {
    return 'images/sample-img.jpg';
  } else {
    return $path;
  }
}

function showData($data)
{
  if (empty($data)) {
    return '';
  } else {
    return $data;
  }
}

function sanitize($str)
{
  return htmlspecialchars($str, ENT_QUOTES);
}

function getMsgsAndBord($id)
{
  try {
    $dbh = dbConnect();

    $sql = 'SELECT m.id, m.board_id, m.transmission_date, m.recipient_id, m.sender_id, m.message, b.sale_user, b.buy_user, b.board_id, b.registration_date, b.portfolio_id FROM message AS m RIGHT JOIN board AS b ON b.board_id = m.board_id WHERE b.board_id = :id  ORDER BY m.transmission_date ASC';

    $data = array(':id' => $id);

    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function getPortfolio($u_id, $p_id)
{

  try {
    $dbh = dbConnect();

    $sql = 'SELECT * FROM portfolios WHERE creater_id = :u_id AND portfolio_id = :p_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id);

    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (Exception $e) {

    error_log('エラー発生:' . $e->getMessage());
  }
}

function getProductList($currentMinNum = 1, $category, $sort, $span = 20)
{
  debug('商品情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // 件数用のSQL文作成
    $sql = 'SELECT portfolio_id FROM portfolios';
    if (!empty($category)) $sql .= ' WHERE category_id = ' . $category;
    if (!empty($sort)) {
      switch ($sort) {
        case 1:
          $sql .= ' ORDER BY update_date ASC';
          break;
        case 2:
          $sql .= ' ORDER BY update_date DESC';
          break;
      }
    }
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount(); //総レコード数
    $rst['total_page'] = ceil($rst['total'] / $span); //総ページ数
    debug('総ページ' . $rst['total_page']);
    if (!$stmt) {
      return false;
    }

    // ページング用のSQL文作成
    $sql = 'SELECT * FROM portfolios';
    if (!empty($category)) $sql .= ' WHERE category_id = ' . $category;
    if (!empty($sort)) {
      switch ($sort) {
        case 1:
          $sql .= ' ORDER BY price ASC';
          break;
        case 2:
          $sql .= ' ORDER BY price DESC';
          break;
      }
    }
    $sql .= ' LIMIT ' . $span . ' OFFSET ' . $currentMinNum;
    $data = array();
    debug('SQL：' . $sql);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果のデータを全レコードを格納
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

// フォーム入力保持
function getFormData($str, $flg = false)
{
  if ($flg) {
    $method = $_GET;
  } else {
    $method = $_POST;
  }
  global $dbFormData;
  // ユーザーデータがある場合
  if (!empty($dbFormData)) {
    //フォームのエラーがある場合
    if (!empty($err_msg[$str])) {
      //POSTにデータがある場合
      if (isset($method[$str])) {
        return sanitize($method[$str]);
      } else {
        //ない場合（基本ありえない）はDBの情報を表示
        return sanitize($dbFormData[$str]);
      }
    } else {
      //POSTにデータがあり、DBの情報と違う場合
      if (isset($method[$str]) && $method[$str] !== $dbFormData[$str]) {
        return sanitize($method[$str]);
      } else {
        return sanitize($dbFormData[$str]);
      }
    }
  } else {
    if (isset($method[$str])) {
      return sanitize($method[$str]);
    }
  }
}

//================================
// ログイン認証
//================================
function isLogin()
{
  // ログインしている場合
  if (!empty($_SESSION['login_date'])) {
    debug('ログイン済みユーザーです。');

    // 現在日時が最終ログイン日時＋有効期限を超えていた場合
    if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
      debug('ログイン有効期限オーバーです。');

      // セッションを削除（ログアウトする）
      session_destroy();
      return false;
    } else {
      debug('ログイン有効期限以内です。');
      return true;
    }
  } else {
    debug('未ログインユーザーです。');
    return false;
  }
}

function isLike($u_id, $p_id)
{
  debug('お気に入り情報があるか確認します。');
  debug('ユーザーID：' . $u_id);
  debug('商品ID：' . $p_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM favorite WHERE portfolio_id = :p_id AND user_id = :u_id';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt->rowCount()) {
      debug('お気に入りです');
      return true;
    } else {
      debug('特に気に入ってません');
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

////業務内容フォーム入力保持
//function getWorkData($str, $flg = false){
//  if($flg){
//    $method = $_GET;
//  }else{
//    $method = $_POST;
//  }
//  global $dbFormHistoryData;
//  // ユーザーデータがある場合
//  if(!empty($dbFormHistoryData)){
//    //フォームのエラーがある場合
//    if(!empty($err_msg[$str])){
//      //POSTにデータがある場合
//      if(isset($method[$str])){
//        return sanitize($method[$str]);
//      }else{
//        //ない場合（基本ありえない）はDBの情報を表示
//        return sanitize($dbFormHistoryData[$str]);
//      }
//    }else{
//      //POSTにデータがあり、DBの情報と違う場合
//      if(isset($method[$str]) && $method[$str] !== $dbFormHistoryData[$str]){
//        return sanitize($method[$str]);
//      }else{
//        return sanitize($dbFormHistoryData[$str]);
//      }
//    }
//  }else{
//    if(isset($method[$str])){
//      return sanitize($method[$str]);
//    }
//  }
//}



function getPortfolioOne($p_id)
{
  try {

    $dbh = dbConnect();

    $sql = 'SELECT p.portfolio_id , p.portfolio_name , p.details , p.p_language , p.pic1 , p.pic2 , p.pic3 , p.creater_id , p.registration_date , p.update_date , c.category AS category
        FROM portfolios AS p LEFT JOIN categories  AS c ON p.category_id  = c.category_id WHERE p.portfolio_id = :p_id AND p.delete_flg = 0 AND c.delete_flg = 0';

    $data = array(':p_id' => $p_id);

    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function getCategory()
{
  debug('カテゴリー情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM categories';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function getUser($u_id)
{
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM users  WHERE user_id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを１レコード返却
    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function getWorkHistory($u_id)
{
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM work_history  WHERE user_id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを全レコード返却
    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function getTastes($u_id)
{
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM tastes  WHERE user_id = :u_id ';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを全レコード返却
    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//sessionを１回だけ取得できる
function getSessionFlash($key)
{
  if (!empty($_SESSION[$key])) {
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}

//メール送信
function sendMail($from, $to, $subject, $comment)
{
  if (!empty($to) && !empty($subject) && !empty($comment)) {

    mb_language("Japanese");
    mb_internal_encoding("UTF-8");

    $result = mb_send_mail($to, $subject, $comment, "From:" . $from);

    if ($result) {
      debug('メールを送信しました');
    } else {
      debug('メールの送信に失敗しました');
    }
  }
}

function validLength($str, $key, $len = 8)
{
  if (mb_strlen($str) !== $len) {
    global $err_msg;
    $err_msg[$key] = $len . MSG14;
  }
}

function makeRandKey($length = 8)
{
  static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
  $str = '';
  for ($i = 0; $i < $length; ++$i) {
    $str .= $chars[mt_rand(0, 61)];
  }
  return $str;
}

function uploadImg($file, $key)
{
  debug('画像アップロード処理開始');
  debug('FILE情報：' . print_r($file, true));

  if (isset($file['error']) && is_int($file['error'])) {
    try {
      // バリデーション
      // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
      switch ($file['error']) {
        case UPLOAD_ERR_OK: // OK
          break;
        case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過した場合
        case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
          throw new RuntimeException('ファイルサイズが大きすぎます');
        default: // その他の場合
          throw new RuntimeException('その他のエラーが発生しました');
      }

      // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
        throw new RuntimeException('画像形式が未対応です');
      }

      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'uploads/' . sha1_file($file['tmp_name']) . image_type_to_extension($type);
      if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：' . $path);
      return $path;
    } catch (RuntimeException $e) {

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}
