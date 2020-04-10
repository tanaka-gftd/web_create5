<?php
    /*ページの説明・・・
      このページではデータベースから貰った情報を基に、商品一覧を表示します。
      各商品画像の横にある"購入する"ボタンで、各商品を購入できます。
      選ばれた商品の商品コードをproduct_disp.phpに送信します。
      ログインページ（login.html）にも移動できます。
    */


    //セッション開始
    session_start(); 

    //セッションハイジャック対策に、セッションIDを再生成
    session_regenerate_id(true);
    
    

    //適切なルートでこのページに来たかどうかを判定
    if (isset($_POST['top'])!=true&&isset($_SESSION['login'])!=true&&isset($_POST['login_flg'])!=true&&isset($_SESSION['top'])!=true) {

        //URLで直接このページにアクセスされた場合に表示
        print'<p>申し訳ありませんが、トップページからのアクセスをお願いします</p>';
        print'<button onclick="location.href=\'shop_top.html\'">トップページへ</button>';
        print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
        print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';                
        exit();
    }  
    else {        
        $_SESSION['top']='on';    
    }

    //ログインしているかどうかを、$_SESSION['login']or$_SESSION['member_name']に値がセットされているかどうかで判定  
    if (isset($_SESSION['login'])==true||isset($_SESSION['member_name'])==true) {      
        
        //ログインしているor注文時に会員登録した場合に表示
        print'<div id="welcome">ようこそ&emsp;'; 
        print$_SESSION['member_name'];
        print '&ensp;様</div>';
        print'<a id="login_position" href="logout.php">ログアウトする</a>';      
    } 
    else {

        //ログインしていないand注文する際会員登録していないand注文確認画面を経た場合に表示
        if (isset($_SESSION['done'])==true) {
        print'<div id="welcome">ようこそ&emsp;'; 
        print$_SESSION['name'];
        print '&ensp;様</div>';
        print'<a id="login_position" href="login.html">ログインする</a>'; 
        }
        else {

            //ログインしていないandまだ注文していない場合に表示
            print'<div id="welcome">ようこそ&emsp;ゲスト&ensp;様</div>';
            print'<a id="login_position" href="login.php">ログインする</a>';
        }
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

    <body>
        <?php
             
            try {

                //データベースに接続する
              
                //$dsn・・・データベースに接続するために必要な情報を格納するための変数（変数名は$dsnでなくても可）
                //dbname・・・データベース名を指定
                //host・・・接続方法をホスト名かIPアドレスで指定（windows環境下ではIPアドレスで指定した方が接続が早いらしい）
                //charset・・・文字コードを指定（４バイト絵文字にも対応しているutf8mb4の方が良いらしいが、今回はutf8で）
                $dsn='mysql:dbname=my_shop;host=127.0.0.1;charset=utf8';

                //$user='root';・・・"管理者権限でデータベースに接続"を意味する（実務では最小権限の原則を重視しましょう）
                //$username・・・ユーザー名  
                //root・・・UNIX系のOSにおける管理者権限（windowsにおけるadmin権限） 
                $user='root';

                //$pasword・・・パスワードを記述（今回、データーベースにはパスワードを設定していませんが、実務では設定しましょう）
                $password='';

                //$dbh=new PDO($dsn,$user,$password);・・・データベース（MySQL）に接続（変数名は$dbhでなくても可）
                //PDO::__constructメソッドから、new演算子でインスタンスを生成
                //PDO::__constructメソッドはコンストラクタなので、実行する際はインスタンスを生成する必要がある
                $dbh=new PDO($dsn,$user,$password);

                //$dbh->setAttribute（PDO::....)・・・アロー演算子でドライバオプションをデータベース接続後に設定
                //PDO::ATTR_ERRMODE・・・SQL実行でエラーが起きた際の処理を指定
                //PDO::ERRMODE_EXCEPTION・・・PDO::ATTR_ERRMODEの値  SQL実行でエラーが起きると例外を投げる（一番無難な設定）
                //PDO::ERRMODE_EXCEPTIONのお陰で、try~catch構文でエラーがキャッチ出来るようになる
                //静的プレースホルダー（バインド機構）を使いたいので、PDO::ATTR_EMULATE_PREPARES（PDO側でのSQL文エミュレートの可否）にfalseを設定
                /*PHPのバージョンが5.2以上だと、デフォルトで動的プレースホルダーが実行されてしまい、
                  MySQLにSQL文がわたる際にはプリペアードステートメントではなくなってしまう。
                  この場合だと自動エスケープはしてくれるものの、文字コード変換を使ったSQLインジェクションに脆弱性が発生してしまう・・・らしい。*/
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


                //データベースのproduct表から"商品コード"、"商品名"、"商品の価格"、"商品画像のファイル名"の情報を抽出するためのSQL文
                //FROMでテーブル名（表）、SELECTで抽出したいカラム（項目）、WHEREで検索条件をそれぞれ指定
                //"WHERE 1"は検索条件無しなのでここでは省略可能だが、後々条件を絞る際にANDを使う時に構文をシンプルに出来る
                //SQL文を変数に格納する（変数名は$sqlでなくても可）                
                $sql='SELECT code,name,price,picture FROM product WHERE 1';
                              
                //プリペアードステートメントでSQL文を実行する
                //prepareでexecute()の実行準備をしてから、executeでプリペアードステートメントを実行（変数名は$stmtでなくても可）
                $stmt=$dbh->prepare($sql);
                $stmt->execute();
                
                //データベースから切断
                //$dbhにnullを代入して、その中身を消す
                $dbh=null;

                //正常時に表示される画面を作成-----ここから
                print'<h1 id="product_list_h1">好きなカレーを選んでください</h1><br><br>';
                print'<div class="product_list_container">';
                
                //while構文を使い、商品データのある分だけ商品データを画面に表示していく
                while (true) {

                    //データベースからの情報を$recに格納
                    //$stmtの中のPDOクラスのfetchメソッドを実行
                    //引数にPDO::FETCH_ASSOCを設定し、列名を記述しつつ情報を配列で取り出す
                    //fetch・・・取り出す
                    //Assoc・・・Association（連想する）の略
                    $rec=$stmt->fetch(PDO::FETCH_ASSOC);
                    
                    //商品データがなくなったら、ループを抜ける
                    if ($rec==false) {
                        break;
                    }

                    //$rec(連想配列)より、商品コードを取り出して変数に格納する
                    $pro_code=$rec['code'];

                    //商品の名前と価格を表示
                    print'<div class="product_list"><div class="product_list_disp"><div>';
                    print$rec['name'].' --- ';                   
                    print$rec['price'].'円';
                    print'</div>';

                    //"購入する"ボタンをクリックすると、クリックされた商品の商品コードがproduct.phpに送信される
                    print'<form method="post" action="product_disp.php">';
                    print'<input type="hidden" name="code" value="'.$pro_code.'">';
                    print'<input type="hidden" name="product_list_flg" value="on">';
                    print'<input type="submit" value="購入する">';
                    print'</form>';
                    print'</div>';
                    print'<img src="../product/picture/'.$rec['picture'].'" width="300">';
                    print'</div>';                    
                }
                print'</div>';               
                print'<a id="return_top" href="shop_top.html">トップページに戻る</a>';
                print'<a id="cart_position" href="cart.php">カートを見る</a>';
                
                //正常時に表示される画面-----ここまで                
            }
            catch (Exception $e) {

                //障害発生中はこちらを表示する
                //catch (Exception $e)・・・例外をキャッチする
                //Exeption・・・例外
                //PHPでは例外をキャッチする際に変数が必要（変数名は$eでなくても可）
                print'<br>';
                print'ただいま障害発生中により大変ご迷惑をお掛けしています<br><br>';
                print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
                print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';                
                exit();
            }
        ?>        
    </body>
</html>
