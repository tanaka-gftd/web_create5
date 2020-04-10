<?php
    /*ページの説明・・・
      このページでは注文内容とお客様情報の確認を行います。
      "注文を確定する" ボタンが押されれば注文が確定されます。      
    */


     //セッション開始
     session_start();

     //セッションハイジャック対策
     session_regenerate_id(true);

    //適切なルートでこのページに来たかどうかを判定
    if (isset($_SESSION['order_check'])!=true) {

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
        <div  id="kakunin">
            <?php
                try {
                
                    //セッション変数から、注文情報（注文コードがキー、注文個数が値となる連想配列）を取り出す  
                    $cart=$_SESSION['cart']; 

                    //注文された種類数を数える
                    $countInCart=count($cart);

                    //$cartから注文コードを配列として取り出す（$cartのキー）
                    $code=array_keys($cart);

                    //$cartから注文数を配列として取り出す（$cartの値）
                    $count=array_values($cart);

                    //データベースに接続&データベース接続のオプション指定
                    $dsn='mysql:dbname=my_shop;host=127.0.0.1;charset=utf8';
                    $user='root';
                    $password='';
                    $dbh=new PDO($dsn,$user,$password);
                    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
                    
                    //foreach構文で、$codeの値(ここでは$value)ごとに情報を取得していく
                    foreach ($code as $value) {

                        //SQL文を作成＆プリペアードステートメントでSQL文を実行
                        $sql='SELECT code,name,price,picture FROM product WHERE code=?';
                        $stmt=$dbh->prepare($sql);

                        //データベースの情報を$rec（多次元配列？）に格納していく
                        $data[0]=$value;
                        $stmt->execute($data);
                        $rec=$stmt->fetch(PDO::FETCH_ASSOC);

                        //商品の名前、価格、画像までのパス名を取得
                        $name_kakunin[]=$rec['name'];
                        $price_kakunin[]=$rec['price'];
                        $picture_kakunin[]='<img src="../product/picture/'.$rec['picture'].'" width="200">';
                    }
                    
                    //ログインしている場合は、データベースからお客様情報を取り出してセッション変数に格納
                    if(isset($_SESSION['login'])==true) {

                        //login＿check.phpで会員コードをセッション変数に格納しておいたのは、ここで使用するため
                        $member_code=$_SESSION['member_code'];

                        $sql='SELECT name,name_hiragana,postal,address,tel,email FROM member WHERE code=?';
                        $stmt=$dbh->prepare($sql);
                        $member_rec[]=$member_code;
                        $stmt->execute($member_rec);

                        $info=$stmt->fetch(PDO::FETCH_ASSOC);

                        //取り出した情報を、一旦セッション変数に格納しておく
                        $_SESSION['name']=$info['name'];
                        $_SESSION['name_hiragana']=$info['name_hiragana'];
                        $_SESSION['postal']=$info['postal'];
                        $_SESSION['address']=$info['address'];
                        $_SESSION['tel']=$info['tel'];
                        $_SESSION['email']=$info['email'];
                       
                    }
                    
                    //データベースから切断
                    $dbh=null;
                }
                catch (Exeption $e) {
                    
                    print'<br>';
                    print'ただいま障害発生中により大変ご迷惑をお掛けしています<br><br>';
                    print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
                    print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';             
                    exit();
                }
            ?>
            <div id="kakunin_left">
                <p>ご注文内容をご確認ください</p>
                <table border=1 style="text-align:center;">
                    <tr>
                        <td>商品名</td>
                        <td>商品画像</td>
                        <td>&emsp;価格&emsp;</td>
                        <td>&emsp;注文数&emsp;</td>
                        <td>&emsp;小計&emsp;</td>
                    </tr>
                    <?php for($i=0; $i<$countInCart; $i++) {  /*注文の種類数分だけHTMLのtable要素を作り、その内容を表示していく*/  ?>
                    <tr>
                        <td><?php print$name_kakunin[$i]?></td>
                        <td><?php print$picture_kakunin[$i]?></td>
                        <td><?php print$price_kakunin[$i]?>円</td>
                        <td><?php print$count[$i]?>個</td>
                        <td><?php print$price_kakunin[$i]*$count[$i]?>円</td>
                    </tr>
                    <?php $kingaku[]=$price_kakunin[$i]*$count[$i];
                          }
                          $sum=array_sum($kingaku);
                    ?>
                </table>
                <div id="seikyuu_kakunin">
                    
                    
                <?php
                    //注文金額に応じて、送料や代引き手数料を加えて合計金額とする 
                    if ($sum<2000) {
                        $goukei=$sum+360+315;
                        print'<p>注文金額&emsp;&emsp;'.$sum.'円</p>';
                        print'<p>&emsp;&emsp; 送料&emsp; &ensp;560円</P>';
                        print'<p class="underbar">+&emsp;&ensp;&emsp;代引き手数料&emsp;&emsp;315円</p>';
                        print'<p style="font-size:125%;">ご請求金額&emsp;&ensp;'.$goukei.'円</p>';                       
                    }
                    else if ($sum<3000) {
                        $goukei=$sum+315;
                        print'<p>注文金額&emsp;&emsp;'.$sum.'円</p>';
                        print'<p class="underbar" >+&emsp;&emsp;&emsp;代引き手数料&emsp;&emsp; 315円</p>';
                        print'<p style="font-size:125%;">ご請求金額&emsp;&ensp;'.$goukei.'円</p>';                        
                    }
                    else {
                        $goukei=$sum;
                        print'<p style="font-size:125%;">ご請求金額&emsp;'.$sum.' 円</p>';
                    }
                    ?>                 
                </div>
                <div id="kakutei">
                    <div Id="kakutei_button">
                        <P>この内容でよろしいですか？</p>
                        <div>
                            <form method="post" action="order_done.php">
                                <input type="hidden" value="<?php print $goukei;?>" name="goukei">
                                <input type="hidden" name="kakutei" value="on">
                                <input type="submit" value="注文を確定する"  style="width:180px;">
                            </form>
                        </div>
                    </div>
                    <div id="kakutei_return"> 
                        <?php 
                              //ログインしている場合は、お客様情報を入力させない
                              if (isset($_SESSION['login'])==true||isset($_SESSION['member_name'])!=true) {
                                  print'<form method="post" action="order.php">';
                                  print'<input type="hidden" name="kakunin" value="on">';
                                  print'<input type="submit" value="お客様情報の入力画面に戻る" style="width:300px;">';
                                  print'</form>';
                              }
                        ?>                    
                        <input type="button" value="カートに戻る" onClick="location.href='cart.php'" style="width:170px;">
                    </div>
                </div>
            </div>
            <div id="kakunin_right">               
                <?php 
                    
                    //セッション変数に格納されているお客様情報を次々に取得                    
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
                            $password=$_SESSION['password'];
                            $sex=$_SESSION['sex'];
                            $birth=$_SESSION['birth'];
                        }
                    }
                ?>
        
                <div id="customer_kakunin"> 
                    <dl>
                        <dt>お客様のお名前</dt>
                        <dd><?php print $name;?></dd>
                        <dt>お客様のお名前（ふりがな）</dt>
                        <dd><?php print $name_hiragana;?></dd>
                        <dt>お客様のご住所</dt>
                        <dd><?php print $postal;
                                  print'<br>';
                                  print$address;
                            ?>
                        </dd>
                        <dt>お客様の連絡先</dt>
                        <dd><?php print $tel;?></dd>
                        <dt>お客様のメールアドレス</dt>
                        <dd><?php print $email;?></dd>
                        <?php
                            
                            //ログインしている時とそうでない場合で表示を変える
                            if(isset($_SESSION['login'])!=true) {
                                
                                //ログインしていない場合に表示
                                if ($kaiin=="off") {
                                   print'<dt>会員登録せずに注文する</dt>';
                                   print'<br>'; 
                                   print'<p>店舗からゲスト様へのメッセージ・・・<br>会員登録すると次回からのご注文が簡単に行えます！</p>';                           
                                }
                                else {
                                    print'<dt>会員登録する</dt>';
                                    print'<dt>お客様の性別</dt>';
                                    print'<dd>';
                                    print $sex;
                                    print '</dd>';
                                    print'<dt>お客様の誕生年</dt>';
                                    print'<dd>';
                                    print '西暦&ensp;'.$birth.'年';
                                    print '</dd>';
                                }
                            }
                            else {
                                print'<p>';
                                print$name.'&ensp;様、いつもありがとうございます';
                                print'</p>';
                            }
                        ?>
                    </dl>
                </div>
            </div>
        </div>
    </body>
</html>
