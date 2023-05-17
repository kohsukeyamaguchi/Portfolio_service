<footer id="footer">
  <a href="https://www.google.com/?hl=ja">株式会社PORTFOLIO SERVICE</a>. All Rights Reserved.
</footer>

<script src="js/vendor/jquery-2.2.2.min.js"></script>
<script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
<script>
  var i = 1;
  $(function() {
    $('button#add').click(function() {


      var tr_form = '' +
        '<div id="form_area_' + i + '" class="second-form">' +
        '<label class="<?php if (!empty($err_msg['business'])) echo 'err'; ?>">' +
        '業務内容' +
        '<input type="text" name="business[]" value="">' +
        '</label>' +
        '<div class="area-msg">' +
        '<?php if (!empty($err_msg['business'])) echo $err_msg['business']; ?>' +
        '</div>' +
        '<label class="<?php if (!empty($err_msg['work_period'])) echo 'err'; ?>">' +
        '業務期間' +
        '<input type="text" name="work_period[]" value="">' +
        '</label>' +
        '<div class="area-msg">' +
        '<?php if (!empty($err_msg['work_period'])) echo $err_msg['work_period']; ?>' +
        '</div>' +
        '<label class="<?php if (!empty($err_msg['contents'])) echo 'err'; ?>">' +
        '業務内容' +
        '<textarea id="textarea2" maxlength="255" name="contents[]" style="height:350px;"></textarea>' +
        '</label>' +
        '<div class="area-msg">' +
        '<?php if (!empty($err_msg['contents'])) echo $err_msg['contents']; ?>' +
        '</div>' +
        '<label class="<?php if (!empty($err_msg['p_language'])) echo 'err'; ?>">' +
        '開発環境' +
        '<input type="text" name="p_language[]" value="">' +
        '</label>' +
        '<div class="area-msg">' +
        '<?php if (!empty($err_msg['p_language'])) echo $err_msg['addr']; ?>' +
        '</div>' +
        '<button id="' + i + '" type="button" onclick="deleteBtn(this)">削除</button>' +
        '</div>';

      $(tr_form).appendTo($('#tag'));
      i++;
    });


    //textareaの要素を取得
    let textarea = document.getElementById('textarea');
    //textareaのデフォルトの要素の高さを取得
    if (textarea) {
      let clientHeight = textarea.clientHeight;

      //textareaのinputイベント
      textarea.addEventListener('input', () => {
        //textareaの要素の高さを設定（rows属性で行を指定するなら「px」ではなく「auto」で良いかも！）
        textarea.style.height = clientHeight + 'px';
        //textareaの入力内容の高さを取得
        let scrollHeight = textarea.scrollHeight;
        //textareaの高さに入力内容の高さを設定
        textarea.style.height = scrollHeight + 'px';
      });

      window.addEventListener('load', function() {
        //textareaの要素の高さを設定（rows属性で行を指定するなら「px」ではなく「auto」で良いかも！）
        textarea.style.height = clientHeight + 'px';
        //textareaの入力内容の高さを取得
        let scrollHeight = textarea.scrollHeight;
        //textareaの高さに入力内容の高さを設定
        textarea.style.height = scrollHeight + 'px';

      })
    }




    // フッターを最下部に固定
    var $ftr = $('#footer');
    if (window.innerHeight > $ftr.offset().top + $ftr.outerHeight()) {
      $ftr.attr({
        'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;'
      });
    }
    // メッセージ表示
    var $jsShowMsg = $('#js-show-msg');
    var msg = $jsShowMsg.text();
    if (msg.replace(/^[\s　]+|[\s　]+$/g, "").length) {
      $jsShowMsg.slideToggle('slow');
      setTimeout(function() {
        $jsShowMsg.slideToggle('slow');
      }, 5000);
    }

    // 画像ライブプレビュー
    var $dropArea = $('.area-drop');
    var $fileInput = $('.input-file');
    $dropArea.on('dragover', function(e) {
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', '3px #ccc dashed');
    });
    $dropArea.on('dragleave', function(e) {
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', 'none');
    });
    $fileInput.on('change', function(e) {
      $dropArea.css('border', 'none');
      var file = this.files[0], // 2. files配列にファイルが入っています
        $img = $(this).siblings('.prev-img'), // 3. jQueryのsiblingsメソッドで兄弟のimgを取得
        fileReader = new FileReader(); // 4. ファイルを読み込むFileReaderオブジェクト

      // 5. 読み込みが完了した際のイベントハンドラ。imgのsrcにデータをセット
      fileReader.onload = function(event) {
        // 読み込んだデータをimgに設定
        $img.attr('src', event.target.result).show();
      };

      // 6. 画像読み込み
      fileReader.readAsDataURL(file);

    });

    // テキストエリアカウント
    var $countUp = $('#js-count'),
      $countView = $('#js-count-view');
    $countUp.on('keyup', function(e) {
      $countView.html($(this).val().length);
    });

    // 画像切替
    var $switchImgSubs = $('.js-switch-img-sub'),
      $switchImgMain = $('#js-switch-img-main');
    $switchImgSubs.on('click', function(e) {
      $switchImgMain.attr('src', $(this).attr('src'));
    });

    // お気に入り登録・削除
    var $like,
      likePortfolioId;
    $like = $('.js-click-like') || null;
    likePortfolioId = $like.data('portfolioid') || null;
    // 数値の0はfalseと判定されてしまう。portfolio_idが0の場合もありえるので、0もtrueとする場合にはundefinedとnullを判定する
    if (likePortfolioId !== undefined && likePortfolioId !== null) {
      $like.on('click', function() {
        var $this = $(this);
        $.ajax({
          type: "POST",
          url: "ajaxLike.php",
          data: {
            portfolioId: likePortfolioId
          }
        }).done(function(data) {
          console.log('Ajax Success');
          // クラス属性をtoggleでつけ外しする
          $this.toggleClass('active');
        }).fail(function(msg) {
          console.log('Ajax Error');
        });
      });
    }

  });

  function deleteBtn(target) {

    var target_id = target.id;
    var parent = document.getElementById('form_area_' + target_id);
    parent.remove();

  }



  window.addEventListener('DOMContentLoaded',
    function() {

      // テキストエリアのDOMを取得
      var nodePass = document.getElementById('pass');
      var nodePassRe = document.getElementById('pass-re');
      var nodeJsCount = document.getElementById('js-count');

      //初期値の文字数を表示する
      var counterNodePass = document.querySelector('.show-count-text');
      var counterNodePassRe = document.querySelector('.show-count-text-re');
      var counterNodeJsCount = document.querySelector('#js-count-view');

      if (counterNodePass) {
        counterNodePass.innerText = nodePass.value.length;

        nodePass.addEventListener('keyup', function() {

          // テキストの中身を取得し、その文字数（length）を数える
          var count = this.value.length;

          // HTML５から使えるquerySelectorを使ったDOMの取得パターン
          // カウンターを表示する箇所のDOM（HTML）を取得する
          var counterNode = document.querySelector('.show-count-text');

          // innerTextを使うと取得したDOMの中身のテキストを書き換えられる
          counterNode.innerText = count;

        }, false);
      }

      if (counterNodePassRe) {
        counterNodePassRe.innerText = nodePassRe.value.length;

        nodePassRe.addEventListener('keyup', function() {

          var count = this.value.length;

          var counterNode = document.querySelector('.show-count-text-re');

          counterNode.innerHTML = count;
        }, false);
      }

      if (counterNodeJsCount) {
        counterNodeJsCount.innerText = nodeJsCount.value.length;

        nodeJsCount.addEventListener('keyup', function() {

          var count = this.value.length;

          var counterNode = document.querySelector('#js-count-view');

          counterNode.innerHTML = count;
        }, false);
      }



    }, false
  );
</script>
<script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
<script src="main.js"></script>
</body>

</html>