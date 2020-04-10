<?php
     /*ページの説明・・・    
      このページではショッピングカートの中身を表示します。
      ショッピングカートの中身をHTMLのtable要素で表示する方法は参考書を参考にしてます（自分でこれを思いつくのは無理です）
      また、このページでは購入数の変更もできます。
      遷移先ページ → count_change.php → cart.phpに戻る。

      注文内容に問題がなければ、お客様情報の入力画面に遷移します。
      遷移先ページ → order.html
    */
     

    //セッション開始
    session_start();

    //セッションハイジャック対策
    session_regenerate_id(true);



    //適切なルートでこのページに来たかどうかを判定
    if (isset($_SESSION['top'])!=true){

        //URLで直接このページにアクセスされた場合に表示
        print'<p>申し訳ありませんが、トップページからのアクセスをお願いします</p>';
        print'<button onclick="location.href=\'shop_top.html\'">トップページへ</button>';
        print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
        print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';                
        exit();
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
        print'<a id="login_position" href="login.php">ログインする</a>'; 
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
            try  {

                //$_SESSION['cart']に中身があるかどうかを確認し、あれば$cartに格納
                if (isset($_SESSION['cart'])==true) {
                    $cart=$_SESSION['cart'];                    
                    
                    //注文された商品の種類数を数えて、その数を変数に格納
                    //$order_count=count($count);でも可
                    $countInCart=count($cart);

                    //$cartの要素数を確認
                    if ($countInCart==0) {

                    //$cartに要素数が０（カートに何も入っていない）なら、注文を促す
                    //カートに商品を入れた後、カート内の商品をすべて削除した時の処理
                    print'<br><br>';
                    print'<div class="kara"><div class="kara_content"><p>カートには何も入っていません</p>';
                    print'<br><br><br>';
                    print'<form>';
                    print'<input type="button" value="商品一覧に戻る" onClick="location.href=\'product_list.php\'">';   //php内なので、'にはエスケープ処理を付けておく
                    print'<br><br>';
                    print'</form>';
                    print'<br><br>';
                    print'<img src="../product/picture/shopping_cart.png" width="300px">';
                    print'</div></div>'; 
                    exit();
                    }
                }              
                else {

                    //isset($_SESSION['cart'])==true以外(false,error)なら、こちらを表示
                    //カートに商品を何も入れず、いきなりカートを見たらこの処理を行う
                    //この場合だと、$cartが作成されていない ( "$_SESSION['cart']"が存在しない）ので、elseで独自に処理しておく
                    print'<br><br>';
                    print'<div class="kara"><div class="kara_content"><p>カートには何も入っていません</p>';
                    print'<br><br><br>';
                    print'<form>';
                    print'<input type="button" value="商品一覧に戻る" onClick="location.href=\'product_list.php\'">';   //php内なので、'にはエスケープ処理を付けておく
                    print'<br><br>';
                    print'</form>';
                    print'<br><br>';
                    print'<img src="../product/picture/shopping_cart.png" width="300px">'; 
                    print'</div></div>';
                    exit();                  
                }
            
                //購入された商品の商品コードで配列を作る
                //商品コードは$cartのキーとして格納されている
                $code=array_keys($cart);
              
                //購入された商品の個数で配列を作る
                //個数は$cartの値として格納されている
                $count=array_values($cart);

                /*解説・・・$codeと$countで同じインデックスの値が、ひとつの商品に対する注文情報（商品コード&個数）となる */

                
                //データベースに接続
                $dsn='mysql:dbname=my_shop;host=127.0.0.1;charset=utf8';
                $user='root';
                $password='';
                $dbh=new PDO($dsn,$user,$password);
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);            
                
                //配列$codeの各インデックスの値（注文された商品コード）に対応した情報を、foreach構文を用いてデータベースから抽出する
                foreach($code as $value) {

                    //データベースのproduct表から、商品情報を貰う
                    $sql='SELECT code,name,price,picture FROM product WHERE code=?';
                    $stmt=$dbh->prepare($sql);

                    //$valに格納された値（$codeの各インデックスの値）で抽出する情報を絞る
                    //インデックスの初期値は０に設定
                    $data[0]=$value;
                    $stmt->execute($data);
                    $rec=$stmt->fetch(PDO::FETCH_ASSOC);

                    //$recの情報から、商品名、価格、商品画像名を抽出
                    //商品画像のある場所をパスで指定
                    $nameInCart[]=$rec['name'];
                    $priceInCart[]=$rec['price'];
                    $pictureInCart[]='<img src="../product/picture/'.$rec['picture'].'" width="200">';  
                }   
                
                //データベースから切断
                $dbh=null;
            }
            catch (Exception $e) {

                //障害発生中はこちらを表示する
                print'<br>';
                print'ただいま障害発生中により大変ご迷惑をお掛けしています<br><br>';
                print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
                print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';                
                exit();
            }         
        ?>
        <a id="return_to_list" href="product_list.php">お買い物を続ける</a>
         
        <div id="cart">
            <div>
                <p>カートの中身</p>
                <form method="post" action="count_change.php"> 
                    <table border="1" style="text-align:center;">            
                        <tr>
                            <td>商品名</td>
                            <td>商品画像</td>
                            <td>&emsp;価格&emsp; </td>
                            <td>&emsp;注文数変更&emsp; </td>
                            <td>&emsp;小計&emsp;</td>
                            <td>&ensp;削除&ensp;</td>
                        </tr>
                        <?php for($i=0; $i<$countInCart; $i++) {   /*注文の種類数分だけHTMLのtable要素を作り、その内容を表示していく*/   ?>
                        <tr>
                            <td><?php print$nameInCart[$i]; ?></td>
                            <td><?php print$pictureInCart[$i]; ?></td>
                            <td><?php print$priceInCart[$i]; ?>円</td>
                            <td style="padding: 0 10px;">
                                <input type="text" name="count_change<?php print$i;?>" value="<?php print$count[$i]; ?>"  maxlength="2" style="width:40px; text-align: right; ">個
                            </td>
                            <td><?php print$priceInCart[$i]*$count[$i];?>円</td>
                            <td><input type="checkbox" name="delete<?php print$i;?>"></td>
                        </tr>  
                        <?php 
                            //商品ごとの小計を配列に格納していく
                            $kingaku[]=$priceInCart[$i]*$count[$i];
                            
                            }   /*この波かっこは、for構文の閉じ括弧。 括弧をおなじ<?php?>内に書かなくても成立するのは、 個人的に驚き*/ 
                            
                            //配列$kingakuの値（商品ごとの小計）を合計すると、注文金額となる
                            $sum=array_sum($kingaku)
                        ?>
                    </table>
                        <input type="hidden" name="$countInCart" value="<?php print$countInCart;?>">
                        <div id="cart_button">                            
                            <input type="hidden" name="cart" value="on">
                            <input type="submit" value="数量変更">
                        </div>
                </form>
            </div>
            <div id="seikyuu">                                
                <?php
                    //注文金額に応じて、送料や代引き手数料を加えて合計金額とする
                    //商品をたくさん買ってもらえるよう、お得感があるような煽り文も表示する
                    
                    //ログインしているかどうかorお客様情報入力画面を経てきたかを判定                   
                    if (isset($_SESSION['login'])==true||isset($_SESSION['name'])==true) {


                        //ログインされているorお客様画面を経てきた場合に表示
                        /*order.htmlでお客様情報を入力する必要はありません。
                          なので、遷移先はkakunin.phpとなります。*/
                        if ($sum<2000) {

                            //注文金額が2000円未満なら、送料と代引き手数料を加える
                            //煽り文も表示する
                            $aori_1=2000-$sum;
                            $goukei_1=$sum+360+315;       
                            print'<br>';
                            print'<p>注文金額&emsp;&emsp;'.$sum.'円</p>';
                            print'<p>&emsp;&emsp; 送料&emsp; &emsp;560円</P>';
                            print'<p class="underbar">+&emsp;&emsp;&emsp;代引き手数料&emsp;&emsp;315円</p>';
                            print'<p>合計金額&emsp;&emsp;'.$goukei_1.'円</p>';
                            print'<br>';
                            print'<form method="post" action="kakunin.php">';
                            print'<input type="hidden" name="money" value="'.$goukei_1.'">';
                            print'<input type="hidden" name="cart" value="on">';
                            print'<input type="submit" value="購入する">';
                            print'</form>';
                            print'<p>あと、'.$aori_1.'円の注文で送料が無料になります！</p>';                       
                        }
                        else if (2000<$sum&&$sum<3000) {

                            //注文金額が2000円を超えて、3000円未満なら代引き手数料を加える
                            //煽り文も表示する
                            $aori_2=3000-$sum;
                            $goukei_2=$sum+315;
                            print'<br>';
                            print'<p>注文金額&emsp;&emsp;'.$sum.'円</p>';
                            print'<p class="underbar" >+&emsp;&emsp;&emsp;代引き手数料&emsp;&emsp; 315円</p>';
                            print'<p>合計金額&emsp;&emsp;'.$goukei_2.'円</p>';
                            print'<br>';
                            print'<form method="post" action="kakunin.php">';
                            print'<input type="hidden" name="money" value="'.$goukei_2.'">';
                            print'<input type="hidden" name="cart" value="on">';
                            print'<input type="submit" value="購入する">';
                            print'</form>';
                            print'<p>あと、'.$aori_2.'円の注文で代引き手数料が無料になります！</p>';
                        }
                        else {

                            //注文金額が3000円を超えたら、何も加えず合計金額とする
                            //煽り文は表示しない
                            print'<br>';
                            print'<p>注文金額&emsp;'.$sum.' 円</p>';
                            print'<form method="post" action="kakunin.php">';
                            print'<input type="hidden" name="money" value="'.$sum.'">';
                            print'<input type="hidden" name="cart" value="on">';
                            print'<input type="submit" value="購入する">';
                            print'</form>';
                        }                    
                    }
                    else {

                        //ログインされていないor注文完了画面を経ていない場合に表示 
                        //遷移先はorder.htmlとし、お客様情報を入力させる                                 
                        if ($sum<2000) {

                            //注文金額が2000円未満なら、送料と代引き手数料を加える
                            //煽り文も表示する
                            $aori_1=2000-$sum;
                            $goukei_1=$sum+360+315;       
                            print'<br>';
                            print'<p>注文金額&emsp;&emsp;'.$sum.'円</p>';
                            print'<p>&emsp;&emsp; 送料&emsp; &emsp;560円</P>';
                            print'<p class="underbar">+&emsp;&emsp;&emsp;代引き手数料&emsp;&emsp;315円</p>';
                            print'<p>合計金額&emsp;&emsp;'.$goukei_1.'円</p>';
                            print'<br>';
                            print'<form method="post" action="order.php">';
                            print'<input type="hidden" name="money" value="'.$goukei_1.'">';
                            print'<input type="hidden" name="cart" value="on">';
                            print'<input type="submit" value="購入する">';
                            print'</form>';
                            print'<p>あと、'.$aori_1.'円の注文で送料が無料になります！</p>';                       
                        }
                        else if (2000<$sum&&$sum<3000) {

                            //注文金額が2000円を超えて、3000円未満なら代引き手数料を加える
                            //煽り文も表示する
                            $aori_2=3000-$sum;
                            $goukei_2=$sum+315;
                            print'<br>';
                            print'<p>注文金額&emsp;&emsp;'.$sum.'円</p>';
                            print'<p class="underbar" >+&emsp;&emsp;&emsp;代引き手数料&emsp;&emsp; 315円</p>';
                            print'<p>合計金額&emsp;&emsp;'.$goukei_2.'円</p>';
                            print'<br>';
                            print'<form method="post" action="order.php">';
                            print'<input type="hidden" name="money" value="'.$goukei_2.'">';
                            print'<input type="hidden" name="cart" value="on">';
                            print'<input type="submit" value="購入する">';
                            print'</form>';
                            print'<p>あと、'.$aori_2.'円の注文で代引き手数料が無料になります！</p>';
                        }
                        else {

                            //注文金額が3000円を超えたら、何も加えず合計金額とする
                            //煽り文は表示しない
                            print'<br>';
                            print'<p>注文金額&emsp;'.$sum.' 円</p>';
                            print'<form method="post" action="order.php">';
                            print'<input type="hidden" name="money" value="'.$sum.'">';
                            print'<input type="hidden" name="cart" value="on">';
                            print'<input type="submit" value="購入する">';
                            print'</form>';
                        }                       
                    }
                ?> 
            </div>
        </div>        
    </body>
</html>
