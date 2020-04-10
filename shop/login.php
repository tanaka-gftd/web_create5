<?php

    //セッション開始
    session_start();
    
    //セッションハイジャック対策に、セッションIDを再生成
    session_regenerate_id(true);

    //適切なルートでこのページに来たかを判定
    if (isset($_POST['top'])!=true&&isset($_SESSION['top'])!=true) {

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

    <body id="login_main">
        <div id="login_container">
        <p id="login_p">会員用ログイン</p>
        <form method="post" action="login_check.php">
            <p>メールアドレスを入力してください</p>
            <input type="text" name="email" required>
            <p>パスワードを入力してください</p>
            <input type="password" name="password" required>
            <input type="hidden" name="login_flg" value="on">
            <input type="submit" value="送信" style="width: 80px; text-align: center;">
        </form>
        <form method="post" action="product_list.php">
            <input type="hidden" name="login_flg" value="on">
            <input type="submit" value="ログインせずに商品一覧へ" style="text-align: center;">
        </form>
        </div>

    </body>
</html>