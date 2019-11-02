<?php
// phpQueryの読み込み
// phpQuery公式：https://code.google.com/archive/p/phpquery/
require_once("./phpQuery-onefile.php");


// B番号取得
if(isset($_POST['getBnumber']) && $_POST['getBnumber'] === "取得"){
    
    $url = htmlspecialchars($_POST['url'], ENT_QUOTES);

    $path = explode("/", $url);
    $dp = array_search("dp", $path);
    $bNum = $path[$dp+1];

    // 中古価格もとる ↓の中古価格取得も実行するためPOSTに追加
    $_POST['bNumber'] = $bNum;
    $_POST['getPrices'] = "取得";
}


// 中古価格取得
if(isset($_POST['getPrices']) && $_POST['getPrices'] === "取得"){

    $bNumber = htmlspecialchars($_POST['bNumber'], ENT_QUOTES);

    // 取得したいwebサイトを読み込む
    $target = file_get_contents("https://www.amazon.co.jp/gp/offer-listing/$bNumber/ref=dp_olp_used?ie=UTF8&condition=used");
    $doc = phpQuery::newDocument($target);

    $title = $doc['h1']->text();
    

    // 価格の取得
    $count = count($doc[".olpOfferPrice"]);
    for($i = 0; $i < $count; $i++){
        $prices[] = $doc[".olpOfferPrice:eq($i)"]->text();

        // ￥と,の除去
        $prices[$i] = mb_ereg_replace("[￥,]", "", $prices[$i]);
    }


    // 結果の組み立て　最安値、３件平均、５件平均、全件平均
    $html = "<h2>$title</h2>";
    $html .= "<table border='1' width='200'><tr><td>中古件数</td><td>$count 件</td></tr>";

    if($count == 0){
        $html .= "<tr><td>中古商品はありません。</td></tr>";
    }else {
        $html .= "<tr><td>最安値</td><td>$prices[0]</td></tr>";
    }

    if($count >= 3){
        $ave3 = array_sum(array_slice($prices, 0, 3)) / 3;
        $html .= "<tr><td>3件平均</td><td>$ave3</td></tr>";
    }

    if($count >= 5){
        $ave5 = array_sum(array_slice($prices, 0, 5)) / 5;
        $html .= "<tr><td>5件平均</td><td>$ave5</td></tr>";
    }

    if($count > 1){
        $ave = array_sum($prices) / $count;
        $html .= "<tr><td>全件平均</td><td>$ave</td></tr>";
    }

    $html .= "</table>";
}


?>


<h2>B番号取得</h2>
<form method="post" action="./index.php">
商品URL : <input required type="text" name="url" value="<?= $url; ?>" onfocus="this.select();">
<input type="submit" name="getBnumber" value="取得">
</form>

<?= "取得したB番号：" . $bNum; ?>
<br /><br />

<hr>

<h2>中古価格取得</h2>
<form method="post" action="./index.php">
B番号 : <input required type="text" name="bNumber" value="<?= $bNumber; ?>">
<input type="submit" name="getPrices" value="取得">
</form>

<?= $html ?>