<?php

    //セッション開始
    session_start();

    //セッションハイジャック対策
    session_regenerate_id(true);
  
  
  
    //適切なルートでこのページに来たかどうかを判定
    if (isset($_POST['cart'])!=true&&isset($_POST['kakunin'])!=true) {
  
        //URLで直接このページにアクセスされた場合に表示
        print'<p>申し訳ありませんが、トップページからのアクセスをお願いします</p>';
        print'<button onclick="location.href=\'shop_top.html\'">トップページへ</button>';
        print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
        print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';                
        exit();
    } 
   
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>カレーは飲み物屋さん</title>
        <link rel="stylesheet" href="stylesheet.css">
        <link rel="stylesheet" href="normalize.css">
    </head>

    <body id="order_main">
        <div id="order_form">
            <p id="order_form_top">お客様情報の入力をお願いいたします</p>
            <form id="form" method="post" action="order_check.php">
                <dl>
                    <dt><p>お名前&ensp;（※苗字と名前の間にスペースを入れてください）</p></dt>
                    <dd><input type="text" name="name" required></dd>

                    <dt><p>お名前のふりがな&ensp;（※苗字と名前の間にスペースを入れてください）</p></dt>
                    <dd><input type="text" name="name_hiragana" required></dd>
                    
                    <dt><p>郵便番号</p></dt>
                    <dd>
                        <input type="text" name="postal1" maxlength="3" style="width: 3em;" required>-
                        <input type="text" name="postal2" maxlength="4" style="width: 4em;" required>
                    </dd>
                    
                    <dt><p>住所</p></dt>
                    <dd><input  type = "text" name = "address" required></dd>
                      
                    <dt><p>電話番号&ensp;（※半角数字でお願いします）</p></dt>
                    <dd><input type="tel" name="tel" required></dd>  
                    
                    <dt><p>メールアドレス</p></dt>
                    <dd><input id="email" type="email" name="email" required></dd>
                    
                    <dt><p>会員登録しますか？</p></dt>
                    <dd>
                        <input id="radio1" type="radio" name="kaiin" value="off" onchange="radioButtonChange();"  checked>
                        <label for="radio1">今回だけの注文&emsp;</label>
                        <input id="radio2" type="radio" name="kaiin" value="on" onchange="radioButtonChange();">
                        <label for="radio2">会員登録して注文</label>
                    </dd>
                    
                    <div id="touroku">
                        <p id="kaiin">※会登録する場合は、以下の項目にも入力をお願いいたします</p>

                            
                        <dt><p>パスワードを設定してください</p></dt>
                        <dd><input id="p1" type="password" name="password1"></dd>
                    
                        <dt><p>確認のため、もう一度パスワードの入力をお願いいたします</p></dt>
                        <dd><input id="p2" type="password" name="password2"></dd>
                    
                        <dt><p>性別</p></dt>
                        <dd>
                            <input type="radio" name="sex" value="回答しない" checked>回答しない&emsp;
                            <input type="radio" name="sex" value="女性">女性&emsp;
                            <input type="radio" name="sex" value="男性">男性
                        </dd>
                    
                        <dt><p>誕生年</p></dt>
                        <dd>
                            西暦&ensp;<input id="birth" type="text" name="birth" maxlength="4" style="width: 3em;">年
                        </dd>                  
                    </div>                    
                </dl>
                <div id="submit">
                    <input type="hidden" name="order" value="on">
                    <input type="submit" value="注文確認画面へ">
                    <input type="button" value="カートに戻る" onclick="location.href='cart.php'" >
                </div>
            </form>
        </div> 
        <script type="text/javascript">

            //ラジオボタンのチェックされた項目に応じて、会員登録用メニューを表示非表示を切り替える
            function radioButtonChange () {                
            
                //ラジオボタンの各項目のチエック部分を取得   
                check1=document.forms.form.radio1.checked;
                check2=document.forms.form.radio2.checked;

                //登録時に追加される項目とその各要素を取得
                const touroku=document.getElementById('touroku'),
                      p1=document.getElementById('p1'),
                      p2=document.getElementById('p2'),
                      birth=document.getElementById('birth');
                                  

                //check１(今回だけの注文)にチェックされた場合の処理
                if (check1==true) {

                    //追加項目を非表示
                    //追加項目の各要素のrequired属性を削除（入力必須項目が減ります）
                    //removAttributeは、要素が無い状態で実行されてもエラーは起きない
                    touroku.classList.remove('view');
                    p1.removeAttribute('required');
                    p2.removeAttribute('required');
                    birth.removeAttribute('required');    
                }

                //check２(会員登録して注文)にチェックされた場合の処理                
                if (check2==true) {

                    //追加項目を表示
                    //追加項目の各要素に required="required" を追加（入力必須項目が追加されます）
                    touroku.classList.add('view');
                    p1.setAttribute('required','required');
                    p2.setAttribute('required','required');
                    birth.setAttribute('required','required');                 
                }              
            }           
        </script>       
    </body>
</html>
