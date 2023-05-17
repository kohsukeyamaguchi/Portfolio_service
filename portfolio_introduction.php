<?php

require('function.php');

$currentPageNum = (!empty($_GET['p']))? $_GET['p'] : 1;
$category = (!empty($_GET['c_id']))? $_GET['c_id'] : '';
$sort = (!empty($_GET['sort']))? $_GET['sort'] : '';

if(!is_int((int)$currentPageNum)){
    error_log('エラー発生：不正な値が入りました');
    header("Location:top.php");
}

$listSpan = 20;

$currentMinNum = (($currentPageNum-1)*$listSpan);

$dbProductData = getProductList($currentMinNum, $category, $sort);

$dbCategoryData = getCategory();
?>


<?php
$siteTitle = 'HOME';
require('head.php'); 
?>

  <body class="page-home page-2colum">

    <!-- ヘッダー -->
    <?php
      require('header.php'); 
    ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <!-- サイドバー -->
      <section id="sidebar">
        <form name="" method="get">
          <h1 class="title">カテゴリー</h1>
          <div class="selectbox">
            <span class="icn_select"></span>
            <select name="c_id" id="">
              <option value="0" <?php if(getFormData('category_id',true) == 0 ){ echo 'selected'; } ?> >選択してください</option>
              <?php
                foreach($dbCategoryData as $key => $val){
              ?>
                <option value="<?php echo $val['category_id'] ?>" <?php if(getFormData('c_id',true) == $val['category_id'] ){ echo 'selected'; } ?> >
                  <?php echo $val['category']; ?>
                </option>
              <?php
                }
              ?>
            </select>
          </div>
          
          <input type="submit" value="検索">
        </form>

      </section>

      <!-- Main -->
      <section id="main" >
        <div class="search-title">
          <div class="search-left">
            <span class="total-num"><?php echo sanitize($dbProductData['total']); ?></span>件の商品が見つかりました
          </div>
          <div class="search-right">
            <span class="num"><?php echo (!empty($dbProductData['data'])) ? $currentMinNum+1 : 0; ?></span> - <span class="num"><?php echo $currentMinNum+count($dbProductData['data']); ?></span>件 / <span class="num"><?php echo sanitize($dbProductData['total']); ?></span>件中
          </div>
        </div>
        <div class="panel-list">
         <?php
            foreach($dbProductData['data'] as $key => $val):
          ?>
            <a href="portfolio_detail.php?p_id=<?php echo $val['portfolio_id']; ?>" class="panel">
              <div class="panel-head">
                <img src="<?php echo sanitize($val['pic1']); ?>" alt="<?php echo sanitize($val['name']); ?>">
              </div>
              <div class="panel-body">
                <p class="panel-title"><?php echo sanitize($val['portfolio_name']); ?> <br><span class="language"><?php echo sanitize($val['p_language']); ?></span></p>
              </div>
            </a>
          <?php
            endforeach;
          ?>
        </div>
        
        <div class="pagination">
          <ul class="pagination-list">
            <?php
              $pageColNum = 5;
              $totalPageNum = $dbProductData['total_page'];
              // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
              if( $currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
                $minPageNum = $currentPageNum - 4;
                $maxPageNum = $currentPageNum;
              // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
              }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum >= $pageColNum){
                $minPageNum = $currentPageNum - 3;
                $maxPageNum = $currentPageNum + 1;
              // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
              }elseif( $currentPageNum == 2 && $totalPageNum >= $pageColNum){
                $minPageNum = $currentPageNum - 1;
                $maxPageNum = $currentPageNum + 3;
              // 現ページが1の場合は左に何も出さない。右に５個出す。
              }elseif( $currentPageNum == 1 && $totalPageNum >= $pageColNum){
                $minPageNum = $currentPageNum;
                $maxPageNum = 5;
              // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
              }elseif($totalPageNum < $pageColNum){
                $minPageNum = 1;
                $maxPageNum = $totalPageNum;
              // それ以外は左に２個出す。
              }else{
                $minPageNum = $currentPageNum - 2;
                $maxPageNum = $currentPageNum + 2;
              }
            ?>
            <?php if($currentPageNum != 1): ?>
              <li class="list-item"><a href="?p=1">&lt;</a></li>
            <?php endif; ?>
            <?php
              for($i = $minPageNum; $i <= $maxPageNum; $i++):
            ?>
              <li class="list-item <?php if($currentPageNum == $i ) echo 'active'; ?>"><a href="?p=<?php echo $i; ?>"><?php echo $i; ?></a></li>
            <?php
              endfor;
            ?>
            <?php if($currentPageNum != $maxPageNum): ?>
              <li class="list-item"><a href="?p=<?php echo $maxPageNum; ?>">&gt;</a></li>
            <?php endif; ?>
          </ul>
        </div>
        
      </section>

    </div>

    <!-- footer -->
    <?php
      require('footer.php'); 
    ?>
