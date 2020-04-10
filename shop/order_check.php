<?php    
    /*ページの説明・・・    
      このページではオーダーフォームに入力されたデータをチェックします。
      入力された値に問題が無ければ、お客様情報をセッション変数に格納しつつ注文確認画面（kakunin.php）へ遷移。
      入力された値にひとつでも問題があれば、再入力を促します
    */


    //セッション開始
    session_start();
    
    //セッションハイジャック対策に、セッションIDを再生成
    session_regenerate_id(true);

    //適切なルートでこのページに来たかどうかを判定
    if (isset($_POST['order'])!=true) {

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

    //いつもの通り、POSTされてきたデータを取得         
    $name=$post['name'];
    $name_hiragana=$post['name_hiragana'];
    $postal1=$post['postal1'];
    $postal2=$post['postal2'];
    $address=$post['address'];
    $tel=$post['tel'];
    $email=$post['email'];

    //会員登録するか否かは$post['kaiin']の値で判定
    $kaiin=$post['kaiin'];

            
    //会員登録する場合に追加で受け取るデータ
    //（こういったご時世なので、性別には回答しないという選択肢を設けてみました）
    if ($kaiin=="on") {
        $password1=$post['password1'];
        $password2=$post['password2'];
        $sex=$post['sex'];   //性別をsexとするのは駄目かも。genderの方が適切？
        $birth=$post['birth'];
    }
   

    //正規表現を用いて、入力されたデータをチェック
    //入力フォームにrequired属性を設定しているので、入力されているかどうかの判定は不要
    /*解説
      \A・・・文字列の先頭を表す（^ だと言語によっては複数行に対応できず、XSSへの脆弱性が残る）
      \z・・・文字列の末尾を表す（$ だと言語によっては複数行に対応できず、XSSへの脆弱性が残る)
      \p{Hiragana}・・・ひらがなを表す（Unicode文字プロパティが使える場合）
                        Unicode文字プロパティが使えない場合は [\u3041-\u3096] や [\x{3041}-\x{3096}] を用いる（Unicodeの文字コードで範囲を指定する）
      \p{Han}・・・日本語の漢字を表す（中国や韓国、ベトナムなどの日本国外で使われている漢字には非対応）
      \p{Katakana}・・・カタカナを表す（Unicode文字プロパティが使える場合）
                        Unicode文字プロパティが使えない場合は [\u30A1-\u30FA] や [\x{30A1}-\x{30FA}] を用いる（Unicodeの文字コードで範囲を指定する）
      u・・・Unicode文字を扱うための記号
      \w・・・アルファベット、数字、アンダーバーを表す
      []・・・[]内のいずれか一文字
      -・・・[]内でのみ使える文字で、範囲を指定できる
      |・・・orを表す     
      {n}・・・直前の文字のn回の繰り返しを表す
      ?・・・直前の文字が０or１回使われている事を表す（$telで使うのは、電話番号の入力の際は途中で - を入れる場合があるから）
      /・・・正規表現の開始と終了を表す
    */

    //入力チェックの最終的な結果を、$flgの値で管理
    //$flg="on"なら、入力された全ての値は問題なし
    //ひとつでも問題があれば、$flgにoffを格納する
    $flag="on";
    
    /*名前（$name_kanji）の入力チェックは漢字だけでなく、ひらがなやカタカナ交じりの人名もあるので複雑。
      一応、 "ひらがなorカタカナor漢字を１回以上繰り返す" で指定してみたが、これで正しいのかは不明。*/
    if (preg_match('/\A([\p{Hiragana}]|[\p{Katakana}]|[\p{Han}])+\s([\p{Hiragana}]|[\p{Katakana}]|[\p{Han}])+\z/u',$name)==0) {
        print'名前を正確に入力してください<br>';
        print$name;
        print'<br><br>';  
        $flag="of";     
    }
    
    if (preg_match('/\A[\p{Hiragana}]+\s{1}[\p{Hiragana}]+\z/u',$name_hiragana)==0) {
        print'名前の振り仮名を正確に入力してください<br>';
        print$name_hiragana;
        print'<br><br>';
        $flag="of"; 
    }

    if (preg_match('/\A[0-9]{3}\z/',$postal1)==0||preg_match('/\A[0-9]{4}\z/',$postal2)==0) {
        print'郵便番号を正確に入力してください<br>';
        print$postal1.'-'.$postal2;
        print'<br><br>';
        $flag="of"; 
    }

    /*住所のバリデーションチェックは複雑すぎて、自分には無理でした。
      例え、都道府県と市区町村と町番地号を別項目として設けて、それぞれに入力チェックを行ったとしても、
      架空の地名（横浜県とか）や今は存在しない地名（吉祥寺村とか）などがバリデーションをすり抜けてしまう。
      なので、今回は割愛。*/

    if (preg_match('/\A[0-9]{2,5}-?[0-9]{2,5}-?[0-9]{4,5}\z/',$tel)==0) {
        print'電話番号を正確に入力してください<br>';
        print$tel;
        print'<br><br>';
        $flag="of"; 
    }

    if (preg_match('/\A[\w\-\.]+\@[\w\-\.]+\.[a-z]+\z/',$email)==0) {
        print'メールアドレスを正確に入力してください<br>';
        print$email;
        print'<br><br>';
        $flag="of"; 
    }

    //会員登録する際に追加された項目の入力チェック
    if ($post['kaiin']=="on") {
        if ($password1!=$password2) {
            print'パスワードが一致しません<br>';
            print'<br><br>';
            $flag="of"; 
        }

        if (preg_match('/\A[0-9]{4}\z/',$birth)==0) {
            print'誕生年を正確に入力ください<br>';
            print'西暦'.$birth.'年'; 
            print'<br><br>';
            $flag="of"; 
        }
        
        //誕生年の入力チェックのために、今年の西暦を取得しておく
        $modernYear= date("Y");
        
        if (preg_match('/\A[0-9]{4}\z/',$birth)==1) {      

            //誕生年の範囲はこれくらいが妥当と思われる
            //（さすがに、150歳の人はいないだろう）
            if ($birth<1870 || $modernYear<$birth) {
            print'誕生年を正確に入力ください<br>';
            print'西暦'.$birth.'年';
            print'<br><br>';
            $flag="of"; 
            }
        }
    }

    //バリデーションチェックの結果を、$flgの値で確認
    if ($flag=="on") {

        //入力された値が全て適正なら、お客様情報をセッション変数に格納していく
        $_SESSION['name']=$name;
        $_SESSION['name_hiragana']=$name_hiragana;
        $_SESSION['postal']=$postal1.'-'.$postal2;
        $_SESSION['address']=$address;
        $_SESSION['tel']=$tel;
        $_SESSION['email']=$email;
        $_SESSION['kaiin']=$kaiin;
        $_SESSION['order_check']='on';
        
        //会員登録する場合は、これらの情報もセッション変数に格納する
        if ($kaiin=="on") {            
            $_SESSION['password']=$password1;
            $_SESSION['sex']=$sex;
            $_SESSION['birth']=$birth;
        }
        
        //注文確認画面へ自動的に遷移
        header('Location:kakunin.php');
        exit(); 
    }
    else {

        //ひとつでも入力された値に問題があったら、再入力を促す
        print'<input type="button" onClick="location.href=\'order.html\'" value="入力フォームに戻る">';
        exit();
    }
?>
