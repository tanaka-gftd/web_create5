<?php
    /*ページの説明・・・
      このページではlogin.htmlに入力されたemailとpasswordで会員を認証します。
      データベースから取得する情報を、login.htmlで入力されたemailで絞り、
      その情報に含まれるパスワード情報を基にpasswordを検証します。
      認証成功なら商品一覧ページ（product_list.php）へ遷移。
      認証失敗なら再入力を促します。
      なお、データーベース上ではパスワードは暗号化されて保存されています。
    */

     
   //shop_top.htmlを経由してこのページに来たかどうかを、$_POST['top']の値で判定
   if (isset($_POST['top'])!=true&&isset($_POST['login_flg'])!=true) {

    //URLで直接このページにアクセスされた場合に表示
    print'<p>申し訳ありませんが、トップページからのアクセスをお願いします</p>';
    print'<button onclick="location.href=\'shop_top.html\'">トップページへ</button>';
    print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
    print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';                
    exit();
    }  
 
    //login.htmlからPOSTされてきた入力データに、サニタイズ関数を適用
    //サニタイズ関数のphpファイルは、このページと同じ階層にある
    require_once('sanitize.php');
    $post=sanitize($_POST);

    try { 
        
        //login.htmlに入力されたemailとパスワードを変数に格納
        $member_email=$post['email'];

        //データベースに接続
        $dsn='mysql:dbname=my_shop;host=127.0.0.1;carset=utf8';
        $user='root';
        $password='';

        //データベース接続のオプションを連想配列形式で設定
        //PDO::setAttributeメソッドは、接続オプションを"接続後"に設定する場合に用いる
        $drivers_options=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false];
        $dbh=new PDO($dsn,$user,$password,$drivers_options);

        //入力されたemailで検索条件を絞る＆プリペアードステートメントでSQL文を実行
        $sql='SELECT name,code,password FROM member WHERE email=?';
        $stmt=$dbh->prepare($sql);
        $stmt->execute(array($member_email));
        
        //データベースから切断
        $dbh=null;

        //データベースから取得した情報を$resultに格納
        $result=$stmt->fetch(PDO::FETCH_ASSOC);
        
        //password_verify関数で入力されたパスワードを検証
        //password_verify関数では第１引数に検証したい文字列、第２引数にハッシュ化された文字列をそれぞれ記述する
        //password_verify関数の返り値はtrue,false
        //!を付ける事で、truefalseになる条件を反転させる
        if (!password_verify($post['password'],$result['password'])) {

            //認証失敗時
            print'<p>メールアドレスかパスワードが間違っています</p>';
            print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
            print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">'; 
            print'<button onclick="location.href=\'login.php\'">戻る</button>';   //php内なので、'にはエスケープ処理を付けておく                     
        }
        else {          
            
            //認証成功時
            session_start();


            //ログインしている証拠として、$_SESSION['login']に値を入れておく
            //ログインしていない状態では、$_SESSION['login']は存在しない事になる
            $_SESSION['login']='on';
            
            //セッション変数に会員名と会員コードを格納しておく
            //会員名は各ページで表示し、会員コードはお客様情報入力を簡易に行うためにデータベースの検索条件として使用する
            $_SESSION['member_name']=$result['name'];
            $_SESSION['member_code']=$result['code'];

            //処理が上手く行けば、product_list.phpに自動的に遷移
            header('Location:product_list.php');
            exit();        
        }  
    }
    catch(Exeption $e) {
        print'<br>';
        print'ただいま障害発生中により大変ご迷惑をお掛けしています<br><br>';
        print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
        print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';             
        exit();
    }           
?>
