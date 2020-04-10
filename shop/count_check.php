<?php
    /*ページの説明・・・    
      このページではproduct_disp.phpで入力された数値をチェックします。
      数値が適切のものでなければ、再入力を促します。
      数値が適切なら商品コードをキー、購入数を値とする連想配列に追加します。
      さらに、その連想配列をセッション変数に格納します。
      全ての処理が終了すると、自動でカート(cart.php)に遷移します。
    */

    
    //セッション開始
    session_start(); 

    //セッションハイジャック対策に、セッションIDを再生成
    session_regenerate_id(true);

    //product_disp.phpを経由してこのページに来たかどうかを判定
    if (isset($_POST['product_disp_flg'])!=true) {


        //URLで直接このページにアクセスされた場合に表示
        print'<p>申し訳ありませんが、トップページからのアクセスをお願いします</p>';
        print'<button onclick="location.href=\'shop_top.html\'">トップページへ</button>';
        print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
        print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';                
        exit();
    }  

    //POSTされてきた入力データに、サニタイズ関数を適用
    //サニタイズ関数のphpファイルは、このページと同じ階層にある  
    require_once('sanitize.php');
    $post=sanitize($_POST);

    //product_disp.phpから送られたきた商品コード（code）を変数に格納
    $code=$post['code'];

    //product_disp.phpから送られてきた購入数（count_pre）を変数に格納
    $count_pre=$post['count_pre'];

    //product_disp.phpで入力された数値（ここでは$count）が適切なものか、if文を用いてチェックを開始              
    if (preg_match("/\A[0-9]+\z/",$count_pre)==0) {

        //半角数字以外が入力された場合に表示
        print'<br><br>';
        print'個数の入力は半角数字でお願いします';
        print'<br><br>';
        print'<form method="post" action="product_disp.php">';
        print'<input type="hidden" name="code" value="'.$code.'">';
        print'<input type="submit" value="戻る">';
        print'</form>';
        print'<br><br>';
        print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
        print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';
        exit();
    }

    if ($count_pre<1||10<$count_pre) {

        //入力された数値が適正範囲外だった場合に表示
        print'<br><br>';
        print'１種類につき、購入数は１個から１０個まででお願いします';
        print'<br><br>';
        print'<form method="post" action="product_disp.php">';
        print'<input type="hidden" name="code" value="'.$code.'">';
        print'<input type="submit" value="戻る">';
        print'</form>';
        print'<br><br>';
        print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
        print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';
        exit();
    }

    //入力された数値が適正なものなら、その数値を$countに格納
    $count=$count_pre;   
    

    //このページに来たのが２回目以降の場合の処理-----ここから

    //このページに来たのが２回目以降の場合は、すでに$cart内に要素があるので、その要素を$cartにコピーしておく
    //この処理をしておかないと、このページに来るたびに$cartが上書きされてしまい、商品が追加されない
    if (isset($_SESSION['cart'])==true) {
        $cart=$_SESSION['cart'];

        //選ばれた商品が、すでにカート内にあるかをチェックする処理-----ここから
        //$cartからキー（$code）を配列として取得
        $cart_keys=array_keys($cart);

        //配列$cart_keysに$codeがすでに含まれているかを検索
        if (in_array($code,$cart_keys)==true) {

            //含まれていれば表示し、商品の再選択を促す
            print'<br><br>';
            print'その商品はすでにカートに入っています';
            print'<br><br>';
            print'<form>';
            print'<input type="button" value="商品一覧に戻る" onClick="location.href=\'product_list.php\'">';//php内なので、'にはエスケープ処理を付けておく
            print'<br><br>';           
            print'</form>';
            print'<br><br>';
            print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
            print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">'; 
            exit();
        }
        //選ばれた商品が、すでにカート内にあるかをチェックする処理-----ここまで
    }
    //このページに来たのが２回目以降の場合の処理-----ここまで
    
    
    //このページに来たのが初めてなら、商品コードをキー、購入数を値とする連想配列（$cart）を作成
    //このページに来たのが２回目以降なら、$cartに要素を追加する
    //要素を一つずつ追加していくので、この記述方法でOK
    $cart[$code]=$count; 

    //$cartの中身が画面遷移しても消えないよう、セッション変数に格納
    $_SESSION['cart']=$cart;   


    //以上の処理が終了したら、cart.phpに自動的に遷移            
    header('Location:cart.php');
    exit();            
?>
