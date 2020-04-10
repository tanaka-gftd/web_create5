<?php 
    /*ページの説明・・・    
      このページではcart.phpで数量変更や削除があった場合に、注文内容の変更を行います。
      入力された数値が適切なものなら数量変更して、削除にチェックされていたら商品を削除します。
      処理終了後、自動的に元のページ（cart.php）に戻ります。
    */


    //セッション開始
    session_start();

    //セッションハイジャック対策に、セッションIDを再生成
    session_regenerate_id(true);
     
    //適切なルートでこのページに来たかどうかを判定
    if (isset($_POST['cart'])!=true) {

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

    //セッション変数より$cartを取り出し、$codeと$countも取り出す
    //$cart・・・注文された商品コードをキー、注文された商品個数を値とする連想配列
    //$code・・・注文された商品コードを値とする配列
    //$count・・・注文された商品個数を値とする配列
    $cart=$_SESSION['cart'];
    $code=array_keys($cart);
    $count=array_values($cart);

    //注文された商品個数の配列を作り直すために、$countを一旦空にする
    $count=array(); 
    
    //数値が変更された時の処理開始-----ここから
    //（実際には、変更されていない数値も処理していく）

    //注文された商品の種類数を受けとって、$maxに格納
    $max=$post['$countInCart'];

    //product_disp.phpで入力された数値（ここでは$count）が適切なものか、if文を用いてチェック
    //入力された数値が適切なものなら、配列の要素として格納していく
    //商品の種類数分（ここでは$max）だけ、ループを繰り返す
    //$count_change・・・"数量変更"ボタンが押された時の、各商品の個数 
    //".$i"・・・０から始まる数値     
    for ($i=0; $i<$max; $i++) {

        //数値が入力されているかを判定
        if ($post['count_change'.$i]=='') {

            //数値が入力されていない場合に表示
            print'<br><br>';
            print'個数の入力をお願いします';
            print'<br><br>';
            print'<a href="cart.php">カートに戻る<a>';
            print'<br><br>';
            print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
            print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';
            exit();
        }

        //入力された数値が半角数字かどうかを、正規表現を用いて判定
        if (preg_match("/\A[0-9]+\z/",$post['count_change'.$i])==0) {

            //半角数字以外が入力された場合に表示
            print'<br><br>';
            print'個数の入力は半角数字でお願いします';
            print'<br><br>';
            print'<a href="cart.php">カートに戻る<a>';
            print'<br><br>';
            print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
            print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';
            exit();
        }
        
        //入力された数値が適正範囲内かどうかを判定
        if ($post['count_change'.$i]<1||10<$post['count_change'.$i]) {

            //入力された数値が適正範囲外だった場合に表示
            print'<br><br>';
            print'１種類につき、購入数は１個から１０個まででお願いします';
            print'<br><br>';
            print'<a href="cart.php">カートに戻る<a>';           
            print'<br><br>';
            print'<img src="../product/picture/ojigi_tenin_man.png" width="300px">';
            print'<img src="../product/picture/ojigi_tenin_woman.png" width="300px">';
            exit();
        }  

        //初期化された$count（配列）に、入力された数値を要素として追加していく
        //変更された数値だけでなく、変更されていない数値も追加しなおす
        //count_change０の数値、count_change１の数値、count_change２・・という風に配列$countに格納される     
        $count[]=$post['count_change'.$i];
    } 
    //数値が変更された時の処理開始-----ここまで

    //商品が削除された時の処理-----ここから
    //配列の前側から削除していくとインデックスがズレてしまい、想定した形にならない
    //（疎な配列にならないよう、インデックスが自動的に変更されていく）
    //後ろ側から削除していけば、この問題は回避できる
    ///商品の種類数分（ここでは$max）だけ、削除するかどうかの判定を繰り返す
    for ($i=$max; 0<=$i; $i--) {

        //"削除する"にチェックが入っているかを確認
        //チェックされていれば、array_spliceで１個分削除
        if (isset($_POST['delete'.$i])==true) {
            array_splice($count,$i,1);
            array_splice($code,$i,1);
        }   
    }
    //商品が削除された時の処理-----ここまで
     

    //$cartを作り直すために、$cartを一旦空にする
    $cart=array();     

    //配列$codeと配列$countをarray_combineで組み合わせて、連想配列$cartを作成
    //（要素が複数ある配列同士を組み合わせるので、$cart[$code]=$countでは駄目）
    $cart= array_combine($code, $count);

    //新しく作成した$cartで、セッション変数 $_SESSION['cart']を上書き
    $_SESSION['cart']=$cart;

    //全ての処理が上手く行けば、cart.phpに自動的に遷移
    header('Location:cart.php');
    exit();
?>
