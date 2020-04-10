<?php
    /*ページの説明・・・    
      このページでは注文完了処理をします。
      注文確定の旨をブラウザに表示し、注文内容などをデータベースに反映します。
      また注文確定メールを顧客に送りつつ、店舗にも受注確認メールを送ります。（メールサーバーがないのでエラーになります）
      注文が確定したので、カートは空にします。
      また、会員登録した場合は自動的にログイン状態になります。
    */


    //セッション開始
     session_start();

    //セッションハイジャック対策
     session_regenerate_id(true);

    //適切なルートでこのページに来たかを判定
    if (isset($_POST['kakutei'])!=true) {

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
    <body>
        <?php
            try {
                $name=$_SESSION['name'];
                $name_hiragana=$_SESSION['name_hiragana'];
                $postal=$_SESSION['postal'];
                $address=$_SESSION['address'];
                $tel=$_SESSION['tel'];
                $email=$_SESSION['email'];

                //会員登録するか否かの判定は、ログインされていない時限定の処理                        
                if(isset($_SESSION['login'])!=true) {
                        
                    //$_SESSION['kaiin']の値を取り出す
                    $kaiin=$_SESSION['kaiin'];
 
                    //会員登録する場合は、これらの情報もセッション変数から取り出す
                    if ($kaiin=="on") { 
                        $login_pass=$_SESSION['password'];
                        $sex=$_SESSION['sex'];
                        $birth=$_SESSION['birth'];
                    }
                }

                $goukei=$_POST['goukei'];

                //セッション変数から、注文情報（注文コードがキー、注文個数が値となる連想配列）を取り出す  
                $cart=$_SESSION['cart']; 

                //注文された種類数を数える
                $countInCart=count($cart);

                //$cartから注文コードを配列として取り出す（$cartのキー）
                $code=array_keys($cart);

                //$cartから注文数を配列として取り出す（$cartの値）
                $count=array_values($cart);


                //注文完了画面-----ここから
                print'<p>'.$name.'&ensp;様</p><br>';
                print'<p>ご注文ありがとうございました。</p>';
                print'<p>商品は以下の住所に発送させて頂きます。</p><br>';
                print'<p>'.$postal.'</p>';
                print'<p>'.$address.'</p><br>'; 
                print'<p>通常２４時間以内に発送いたしますので、商品到着までしばらくお待ちください。</p><br>';
            
                //会員登録処理は、ログインされていないとき限定
                if(isset($_SESSION['login'])!=true) {

                    //会員登録した際に追加で表示される文章
                    if ($kaiin=="on") {
                        print'<p>'.$name.' 様の会員登録が完了しました。</p>';
                        print'<p>次回からはメールアドレスとパスワードでログインすれば、';
                        print'ご注文が簡単に行えます。</p>';
                    }
                }
                //注文完了画面-----ここまで
        

                //注文確認メール作成-----ここから
                $mail_honbun="";
                $mail_honbun.=$name." 様 \n\n";
                $mail_honbun.="ご注文ありがとうございました。\n\n";
                $mail_honbun.="今回のご注文商品\n\n";

                //メール本文に商品情報を反映させるためにデータベースへ接続&データベースに接続のオプションを設定
                $dsn='mysql:dbname=my_shop;host=127.0.0.1;charset=utf8';
                $user='root';
                $password='';
                $dbh=new PDO($dsn,$user,$password);
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);                  
               
                //注文種類分だけ処理をループ
                //商品コードに対応した情報をデータベースから取得していく
                for ($i=0; $i<$countInCart; $i++) {
                    $sql='SELECT name,price FROM product WHERE code=?';
                    $stmt=$dbh->prepare($sql);               
                    $data[0]=$code[$i];
                    $stmt->execute($data);
                    $rec=$stmt->fetch(PDO::FETCH_ASSOC);

                    //商品の名前、価格を取得
                    $name_mail=$rec['name'];
                    $price_mail=$rec['price'];
                    $count_mail=$count[$i];
                    $mail_honbun.=$name_mail.'　'.$count_mail.'個';
                    $mail_honbun.="\n";
                }

                 //会員登録処理は、ログインされていないとき限定
                if(isset($_SESSION['login'])!=true) {
                    
                    //会員登録した際にメールに追加される文章
                    if ($kaiin=="on") {
                        $mail_honbun.="\n\n".$name." 様の会員登録が完了しました。\n";
                        $mail_honbun.="次回からはメールアドレスとパスワードでログインすれば、";
                        $mail_honbun.="ご注文が簡単に行えます。\n\n";
                    }
                }

                $mail_honbun.="-------------------------------------------------\n\n";
                $mail_honbun.="カレーは飲み物屋さん\n\n";
                $mail_honbun.="東京都 華麗区 勝華麗 0-141-4 カレービル92F\n";
                $mail_honbun.="TEL 000-0000-××××\n\n";
                $mail_honbun.="-------------------------------------------------";
                //注文確認メール作成-----ここまで

                //メール本文表示用コード
                //print nl2br($mail_honbun);   
                
                //お客様が入力した情報をデータベースに登録していく処理-----ここから
               
                //テーブルに排他ロックをかける
                $sql='LOCK TABLES orders WRITE,member WRITE,orders_product WRITE';
                $stmt=$dbh->prepare($sql);
                $stmt->execute();               

                //会員登録した際の個人情報をデータベースに登録していく処理-----ここから

                //ログイン済みの顧客にはこの処理はしない
                if(isset($_SESSION['login'])!=true) {

                    //会員登録しない人は会員コードを０とするので、まずは$membercodeに０を格納
                    $memberscode=0;                
                
                    //会員登録する場合の処理
                    if($kaiin=='on') {

                        //データベースにINSERT INTOで情報を追加していく
                        $sql='INSERT INTO member(name,name_hiragana,password,postal,address,tel,email,birth,sex)
                        VALUES(?,?,?,?,?,?,?,?,?)';
                        $stmt=$dbh->prepare($sql);
                    
                    
                        //パスワードの保管を厳重にするために、パスワードはハッシュ化しておく
                        //このアルゴリズムだと、ハッシュ化された文字列は常に６０文字となる
                        //（参考書で使われているMD5は、総当たりで崩せるので時代遅れの方式）
                        $hash=password_hash($login_pass,PASSWORD_BCRYPT);
                    
                        //入力された情報を配列にし、データベースに追加する
                        $stmt->execute([$name,$name_hiragana,$hash,$postal,$address,$tel,$email,$birth,$sex]); 
                                        
                        //直前の処理でデータベースが自動的に割り振った番号（＝会員番号）を取得する
                        $sql='SELECT LAST_INSERT_ID() FROM member';
                        $stmt=$dbh->prepare($sql);
                        $stmt->execute();
                        $rec=$stmt->fetch(PDO::FETCH_ASSOC);
                        $memberscode=$rec['LAST_INSERT_ID()']; 

                        //会員登録後は自動的にログイン状態にするので、$_SESSION['member_name']に顧客の名前を入れておく
                        $_SESSION['member_name']=$name;
                    }
                    else {
                        //会員登録しない人でも、引き続き商品ページで名前が表示されるようにするためのフラグ
                        $_SESSION['done']='on';
                    }
                }
                else {
                    //ログインされている場合は、会員コードをそのまま$memberscodeに格納する
                    $memberscode=$_SESSION['member_code'];
                }
                //会員登録した際の個人情報をデータベースに登録していく処理-----ここまで
                
                //商品の送り先と連絡先をデータベースに登録
                $sql='INSERT INTO orders (memberscode,name,name_hiragana,postal,address,tel,email)
                VALUES(?,?,?,?,?,?,?)';
                $stmt=$dbh->prepare($sql);
                $stmt->execute([$memberscode,$name,$name_hiragana,$postal,$address,$tel,$email]);

                //直前の処理でデータベースが自動的に割り振った番号（＝注文番号）を取得する
                $sql='SELECT LAST_INSERT_ID() FROM member';
                $stmt=$dbh->prepare($sql);
                $stmt->execute();
                $rec=$stmt->fetch(PDO::FETCH_ASSOC);
                $orderscode=$rec['LAST_INSERT_ID()']; 

                //注文内容をデータベースに登録していく処理-----ここから
                for ($i=0; $i<$countInCart; $i++) {
                    $sql='INSERT INTO orders_product (orderscode,productcode,price,count)
                    VALUES(?,?,?,?)';
                    $stmt=$dbh->prepare($sql);
                    $stmt->execute([$orderscode,$code[$i],$price_mail[$i],$count[$i]]);
                }
                //注文内容をデータベースに登録していく処理-----ここまで

                //お客様が入力した情報をデータベースに登録していく処理-----ここまで

                
                //データベースの排他ロック解除
                $sql='UNLOCK TABLES';
                $stmt=$dbh->prepare($sql);
                $stmt->execute();
                
                //データベースから切断
                $dbh=null;

                //注文が確定したので、カートを空にしておく
                $_SESSION['cart']=array();

                //一時的なフラグも解除
                $_SESSION['order_check']=array();
               


                //お客様への注文確認用メールの設定-----ここから
                //（メールサーバが無いので、エラーになります）

                //メールタイトル
                $title='毎度！ カレーは飲み物屋さんです';

                //メールヘッダに送信元を設定
                $header='From:info@curyyisdrink.co.jp';

                //HTMLで書かれたエンティティ（参照文字）を実体文字（UTF-8）に変更する
                //ENT_QUOTESでシングルクォートとダブルクォートも変換する
                $mail_honbun=html_entity_decode($mail_honbun,ENT_QUOTES,'UTF-8');
                
                //内部文字エンコーディングを日本語&UTF-8に設定
                /*内部文字エンコーディングの意味が、いまいち分からない*/
                mb_language('Japanese');
                mb_internal_encoding('UTF-8');

                //メールを送信
                mb_send_mail($email,$title,$mail_honbun,$header);
                //お客様への注文確認用メールの設定-----ここまで


                //店舗側に注文を知らせるメールの設定-----ここから
                //（メールサーバが無いので、こちらもエラーになります）
                $title='お客様からご注文がありました';
                $header='From:'.$email;
                $mail_honbun=html_entity_decode($mail_honbun,ENT_QUOTES,'UTF-8');
                mb_language('Japanese');
                mb_internal_encoding('UTF-8');
                mb_send_mail('info@curyyisdrink.co.jp',$title,$mail_honbun,$header);
                //店舗側に注文を知らせるメールの設定-----ここから

            }
            catch(Exeption $e) {
                print'<br>';
                print'ただいま障害発生中により大変ご迷惑をお掛けしています<br><br>';
                print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
                print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';             
                exit();
            }           
        ?>

        <button onclick="location.href='product_list.php'">商品一覧へ戻る</button>

    </body>
</html>