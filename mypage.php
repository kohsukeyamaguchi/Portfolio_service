<?php

require('function.php');

//ユーザーI D取得
$u_id = $_SESSION['user_id'];
//DBからポートフォリオデータ取得
$portfolioData = getMyPortfolios($u_id);
//DBから連絡掲示板データを取得
$boardData = getMyMsgsAndBord($u_id);
//DBからお気に入りデータを取得
$likeData = getMyLike($u_id);
//DBからプロフィールデータを取得
$profData = getMyProf($u_id);
//DBから職務経歴データを取得
$workHistoryData = getMyWorkHistory($u_id);
?>


<?php
//ログイン認証
require('auth.php');
$siteTitle = 'マイページ';
require('head.php');
?>

<body class="page-mypage page-2colum page-logined">
  <style>
    #main {
      border: none !important;
    }
  </style>

  <!-- メニュー -->
  <?php
  require('header.php');
  ?>

  <p id="js-show-msg" style="display:none;" class="msg-slide">

  </p>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

    <h1 class="page-title">MYPAGE</h1>

    <!-- Main -->
    <section id="main">
      <section class="list panel-list">
        <h2 class="title" style="margin-bottom:15px;">
          登録ポートフォリオ一覧
        </h2>
        <?php
        if (!empty($portfolioData)) :
          foreach ($portfolioData as $key => $val) :
            ?>
            <a href="portfolio_registration.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . '&p_id=' . $val['portfolio_id'] : '?p_id=' . $val['portfolio_id']; ?>" class="panel">
              <div class="panel-head">
                <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['portfolio_name']); ?> ">
              </div>
              <div class="panel-body">
                <p class="panel-title"><?php echo sanitize($val['portfolio_name']); ?></p>
                <p class="p_language"><?php echo sanitize($val['p_language']); ?></p>
              </div>
            </a>
        <?php
          endforeach;
        endif;
        ?>
      </section>

      <style>
        .list {
          margin-bottom: 30px;
        }
      </style>

      <section class="list list-table">
        <h2 class="title">
          連絡掲示板一覧
        </h2>
        <table class="table">
          <thead>
            <tr>
              <th>最新送信日時</th>
              <th>連絡相手</th>
              <th>メッセージ</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (!empty($boardData)) {
              foreach ($boardData as $key => $val) {
                if (!empty($val['msg'])) {
                  $msg = array_shift($val['msg']);

                  ?>
                  <tr>
                    <td><?php echo sanitize(date('Y.m.d H:i:s', strtotime($msg['transmission_date']))); ?></td>
                    <?php

                          $speakerA = $msg['sender_id'];
                          $speakerB = $msg['recipient_id'];
                          if ($speakerA == $_SESSION['user_id']) {
                            $partnerId = $speakerB;
                          } else {
                            $partnerId = $speakerA;
                          }
                          $partnerInfo = getUser($partnerId);
                          ?>
                    <td><?php echo $partnerInfo['name']; ?></td>
                    <td><a href="message_board.php?m_id=<?php echo sanitize($val['board_id']); ?>"><?php echo mb_substr(sanitize($msg['message']), 0, 40); ?></a></td>
                  </tr>
                <?php
                    } else {
                      ?>
            <?php
                }
              }
            }
            ?>
          </tbody>
        </table>
      </section>

      <section class="list panel-list">
        <h2 class="title" style="margin-bottom:15px;">
          お気に入り一覧
        </h2>
        <?php
        if (!empty($likeData)) :
          foreach ($likeData as $key => $val) :
            ?>
            <a href="portfolio_detail.php?p_id=<?php echo $val['portfolio_id']; ?>" class="panel">
              <div class="panel-head">
                <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="お気に入りのポートフォリオ画像">
              </div>
              <div class="panel-body">
                <p class="panel-title"><?php echo sanitize($val['portfolio_name']); ?></p>
                <p class="p_language"><?php echo sanitize($val['p_language']); ?></p>
              </div>
            </a>
        <?php
          endforeach;
        endif;
        ?>
      </section>

      <section class="list prof_main">
        <h2 class='title'>
          マイプロフィール
        </h2>
        <?php
        $gend = showData(sanitize($profData[0]['gender'])) || null;
        $gender = '';
        switch ($gend) {
          case 'men':
            $gender = '男';
            break;
          case 'women':
            $gender = '女';
            break;
          case 0:
            break;
        }

        if (!empty($profData)) :
          foreach ($profData as $key => $val) :
            ?>
              <table>
                <!--
              <thead>
                  <tr><th>氏名</th><td></td></tr>
              </thead>
-->
                <tbody>
                  <tr>
                    <th>プロフィール画像</th>
                    <td><?php if(!empty($val['pic'])) : ?><img class="mypage-img" src="<?php echo sanitize($val['pic']); ?>" alt=""><?php endif; ?></td>
                  </tr>
                  <tr>
                    <th>氏名</th>
                    <td><?php echo showData(sanitize($val['name'])); ?></td>
                  </tr>
                  <tr>
                    <th>性別</th>
                    <td><?php echo $gender; ?></td>
                  </tr>
                  <tr>
                    <th>最終学歴</th>
                    <td><?php echo showData(sanitize($val['background'])); ?></td>
                  </tr>
                  <tr>
                    <th>PR</th>
                    <td><?php echo showData(sanitize($val['pr'])); ?></td>
                  </tr>
                  <tr>
                    <th>資格1</th>
                    <td><?php echo showData(sanitize($val['tastes1'])); ?></td>
                  </tr>
                  <tr>
                    <th>資格2</th>
                    <td><?php echo showData(sanitize($val['tastes2'])); ?></td>
                  </tr>
                  <tr>
                    <th>資格3</th>
                    <td><?php echo showData(sanitize($val['tastes3'])); ?></td>
                  </tr>
                  <tr>
                    <th>資格4</th>
                    <td><?php echo showData(sanitize($val['tastes4'])); ?></td>
                  </tr>
                  <tr>
                    <th>趣味</th>
                    <td><?php echo showData(sanitize($val['qualifications'])); ?></td>
                  </tr>
                </tbody>
              </table>
          <?php
            endforeach;
          endif;
          ?>
      </section>
      <section class="prof_sub">
        <h2 class='title'>

        </h2>
        <?php
        if (!empty($workHistoryData)) :
          foreach ($workHistoryData as $key => $val) :
            ?>
            <table>
              <tbody>
                <tr>
                  <th>業務名</th>
                  <td><?php echo showData(sanitize($val['business'])); ?></td>
                </tr>
                <tr>
                  <th>業務期間</th>
                  <td><?php echo showData(sanitize($val['work_period'])); ?></td>
                </tr>
                <tr>
                  <th>業務内容</th>
                  <td><?php echo showData(sanitize($val['contents'])); ?></td>
                </tr>
                <tr>
                  <th>使用言語</th>
                  <td><?php echo showData(sanitize($val['p_language'])); ?></td>
                </tr>
              </tbody>
            </table>
        <?php
          endforeach;
        endif;
        ?>
      </section>
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