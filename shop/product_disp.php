<?php    
    /*ページの説明・・・    
      このページではproduct_list.phpで選択された商品を表示します。
      テキストボックスで購入数が入力できます。
      入力された購入数をcount_check.phpに送信します。
      ログインページ（login.html）にも移動できます。
    */


    //セッション開始
    session_start(); 

    //セッションハイジャック対策に、セッションIDを再生成
    session_regenerate_id(true);

    

    //適切なルートでこのページに来たかどうかを判定
    if (isset($_POST['product_list_flg'])!=true) {

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

    require_once('sanitize.php');
    $post=sanitize($_POST);
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

                //product_list.phpより送られてきたcodeを受け取り、変数$pro_codeに格納
                $pro_code=$post['code'];

                //データベースに接続
                $dsn='mysql:dbname=my_shop;host=127.0.0.1;charset=utf8';
                $user='root';
                $password='';
                $dbh=new PDO($dsn,$user,$password);
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                
                //データベースのproduct表から、商品情報を貰う
                //?を使うことで、?部分に後からデータを入れられるようにする（?がプレースホルダーになる）
                $sql='SELECT code,name,price,picture FROM product WHERE code=?';
                $stmt=$dbh->prepare($sql);

                //抽出する情報を商品コード（pro_code）で絞ってからSQL文を実行（変数名は$dataでなくても可）
                $data[]=$pro_code;
                $stmt->execute($data);

                //データベースから切断
                $dbh=null;

                //受け取った情報を$recに保存
                $rec=$stmt->fetch(PDO::FETCH_ASSOC);

                //$recより情報を取り出して、それぞれを変数に格納
                $pro_name=$rec['name'];
                $pro_price=$rec['price'];
                $pro_picture=$rec['picture'];

                //商品画像のある場所までのパスを、$disp_pictureに格納
                $disp_picture='<img src="../product/picture/'.$pro_picture.'" style="height:400px;">'; 
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
        
        <div id="product_disp">
            <h1>このカレーが食べたい？</h1>
            <div><?php echo $disp_picture ?></div>
            <div id="product_disp_picture"><?php echo $pro_name,' --- ',$pro_price, '円'?></div>
            <form method="post" action="count_check.php"> 
                <input type="text" name="count_pre" maxlength="2" style="text-align: right; width:50px;" required>個
                <input id="eat" type="submit" value="注文する"> 
                <input type="button" value="考え直す" onclick="location.href='product_list.php'" > 
                <input type="hidden" name="code" value="<?php echo $pro_code ?>">
                <input type="hidden" name="product_disp_flg" value="on">     
            </form>
        </div>
    </body>
</html>
