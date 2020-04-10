<?php
     /*ページの説明・・・
      このページではログアウト処理（セッション破棄）をします。
      セッションを破棄するには決まった手順があります。（php公式マニュアルより）
      ログアウト処理終了後、トップページ（shop_top.html）に自動的に遷移します。
    */


    //セッション開始
    session_start();

    //適切なルートでこのページに来たかどうか判定（妥協版）
    /*このやり方では、ログインしている状態でのURLによるアクセスが防げない。
      しかもログアウトされてしまう。
    */
    if (isset($_SESSION['login'])!=true) {

        //URLで直接このページにアクセスされた場合に表示
        print'<p>申し訳ありませんが、トップページからのアクセスをお願いします</p>';
        print'<button onclick="location.href=\'shop_top.html\'">トップページへ</button>';
        print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
        print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';                
        exit();
    }  
    
    //セッション変数の中身を空の配列にし、セッション変数を全て初期化
    $_SESSION[]=array();

    //クライアントのブラウザにクッキーとして保存されているセッションIDを破棄
    //session_name()・・・セッションID名を返す関数
    //setcookie()・・・クッキー変数を設定する関数
    /*解説・・・
      一つ目の引数はクッキー変数名、二つ目の引数はクッキー変数に設定する値
      三つ目の引数はクッキーの期限、四つ目の引数はクッキーを設定している範囲（？）*/
    //time()・・・エポック（1970年1月1日0時0分0秒）からの経過時間を表す
    /*解説・・・
      time()-42000で、エポックの42000秒前となります。
      time()でも即座にクッキーは破棄されると思うのですが、php公式マニュアルではtime()-42000で指定されている*/
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(),'',time()-42000,'/');
    }

    //最後にセッションを破棄する
    /*上記の手順を踏まずに、いきなりsession_destroy()してはいけない */
    session_destroy();

    //処理が上手く行けば、shop_top.htmlに自動的に遷移させる
    /*本当は商品一覧ページ（product_list.php）に遷移させたいが、
      セッションが破棄されているため、product_lost.phpに飛ぶとURLで直接アクセスしてきた扱いになり、
      最初の処理で弾かれてしまう。
      遷移直前にsession_start()させて、$_SESSION['top']に値を入れるのも手だと思ったのですが、(下記コード参照)
      なぜか遷移先ページ（product_list.php）にセッション変数が引き継がれない。
      原因は不明。対処も出来ないので諦めました。（自動的に遷移する場合はNGなのかも）*/
      /*残骸 session_start();
             $_SESSION['top']='on';*/
    
    header('Location:shop_top.html');
    exit();
?>
