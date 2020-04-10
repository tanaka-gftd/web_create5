<?php   
    //sanitize関数
    //クロスサイトスクリプティング対策に用いる関数
    //引数に格納された値(ここでは$before)を全てサニタイジングして、その値(ここでは$after)を返す
    //htmlspecialchars関数・・・HTMLで特殊な意味をもつ文字を、別の文字列に変換する
    /*一つ目の引数で対象となる文字列、二つ目の引数でシングルクォートやダブルクォートの扱いなどを指定、
      三つ目の引数で変更後の文字コードを指定する。（二つ目、三つ目の引数は省略可）*/
    function sanitize($before) {
        foreach ($before as $key =>$value) {
            $after[$key]=htmlspecialchars ($value,ENT_QUOTES,'UTF-8');
        }
        return $after;   /*returnを忘れないように!*/
    }
?>
