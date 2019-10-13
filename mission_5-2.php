<?php
  /*//ファイル名を変数に読み込む
  $filename="mission_3-5.txt"; */

  //データベースへの接続　phpとMysqlの連携
  $dsn = 'mysql:dbname=データベース名;host=localhost;charset=utf8';//$dsnの式の中にスペースを入れないこと！ tb210378db : データベース名　localhost : MySQLホスト名
  $user = 'ユーザー名';
  $password = 'パスワード';
  $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));/*
  array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING)とは、データベース操作で発生したエラーを警告として表示してくれる設定をするための要素。
  デフォルトでは、PDOのデータベース操作で発生したエラーは何も表示されない。
  その場合、不具合の原因を見つけるのに時間がかかってしまうので、このオプションはつけておく。*/

  //createコマンドを使って、データベース内にテーブルを作成 ※データベースは作成済みのため、作成するのはテーブルだけでよい。
  $sql = "CREATE TABLE IF NOT EXISTS tbtest2"/* IF NOT EXISTSを入れないと２回目以降にこのプログラムを呼び出した際に、
  SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'tbtest' already exists
  という警告が発生する。これは、既に存在するテーブルを作成しようとした際に発生するエラー。*/
  ." ("
  . "id INT AUTO_INCREMENT PRIMARY KEY,"//,でつなげる
  . "name char(32),"
  . "comment TEXT,"
  . "datetime DATETIME,"
  . "password TEXT"
  .");";
  $stmt = $pdo->query($sql);

   //<編集フォーム入力時の処理>
   //編集フォームが入力されたかチェック
   //編集フォームが入力されている場合
  if(!empty($_POST["editNo"])){//if(empty($_POST["editNo"])==FALSE){
	//idと名前を選択
	$sql = 'SELECT name,comment FROM tbtest2 where id=:id';
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':id', $_POST["editNo"], PDO::PARAM_INT);
	$stmt->execute();

	//選択した内容全てを配列として読み込む
	$results = $stmt->fetchAll();
	  foreach ($results as $row){
		$editName = $row['name'];
		$editComment = $row['comment'];
	  }

   }
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset="utf-8">
  <title>ミッション5-1</title>
 </head>
<body>
  <form method="POST" action="mission_5-2.php">
    【 投稿フォーム 】<br>
    名前:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="name" placeholder="例：佐藤太郎" value="<?php if(!empty($_POST['editNo'])){echo $editName;}else{echo '';} ?>"><br>
    コメント:&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="何でも" placeholder="ご自由に！" value="<?php if(!empty($_POST['editNo'])){echo $editComment;}else{echo '';} ?>"><br>
    <input type="hidden" name="checkNo" value="<?php if(!empty($_POST['editNo'])){echo $_POST['editNo'];} ?>">
    パスワード:&nbsp;<input type="text" name="password" placeholder="パスワード認証"><br>
    <input type="submit" value="送信"><br>
    <br>
  </form>
  <form method="POST" action="mission_5-2.php">
    【 削除フォーム 】<br>
    投稿番号:&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="deleteNo" placeholder="例：1,2,..."><br>
    パスワード:&nbsp;<input type="text" name="password" placeholder="パスワード認証"><br>
    <input type="submit" name="delete" value="削除"><br>
    <br>
  </form>
  <form method="POST" action="mission_5-2.php">
    【 編集フォーム 】<br>
    投稿番号:&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="editNo" placeholder="例：1,2,..."><br>
    パスワード:&nbsp;<input type="text" name="password" placeholder="パスワード認証"><br>
    <input type="submit" name="edit" value="編集"><br>
  </form>
 <?php
   //<投稿フォームが入力された場合>
   //編集対象番号が入力されている場合
   if(!empty($_POST["name"]) && !empty($_POST["何でも"])){/* if(isset($_POST['name'], $_POST['何でも'])){ はフォームが空かどうかを確かめるには相性が良くないらしい　emptyは、0や””の空文字がセットされていても、空と評価します。他方、issetは、0や””がセットされていれば、空でないと評価します。*/
	$name = $_POST['name'];
	$comment = $_POST['何でも'];
	$datetime = date("Y/m/d H:i:s");
	$password = $_POST['password'];

	//新規投稿
	if($_POST['checkNo'] == ""){
	  $sql = "INSERT INTO tbtest2 (name, comment, datetime, password) VALUES (:name, :comment, :datetime, :password)";
	  $stmt = $pdo -> prepare($sql);
	  $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
	  $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
	  $stmt -> bindParam(':datetime', $datetime, PDO::PARAM_STR);
	  $stmt -> bindParam(':password', $password, PDO::PARAM_STR);
	  $stmt->execute();

	//編集対象番号が存在する場合⇒何もしない
	} else{/*編集対象番号が入力されていない場合⇒新規投稿処理へ*/
	  $editNo = $_POST['checkNo'];
	  $sql = 'UPDATE tbtest2 set name=:name,comment=:comment,datetime=:datetime WHERE id=:id AND password LIKE :password';//文字列は=でなくlike
	  $stmt = $pdo->prepare($sql);
	  $stmt -> bindParam(':id', $editNo, PDO::PARAM_INT);//INT注意
	  $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
	  $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
	  $stmt -> bindParam(':datetime', $datetime, PDO::PARAM_STR);
	  $stmt -> bindParam(':password', $password, PDO::PARAM_STR);
	  $stmt->execute();
	}

   //<削除フォーム入力時の処理
   } elseif(isset($_POST["deleteNo"])) {/* if(isset($_POST['deleteNo'])){ でも良い */
	$deleteNo = $_POST['deleteNo'];
	$password = $_POST['password'];
	$sql = 'DELETE FROM tbtest2 WHERE id=:id AND password LIKE :password';
	$stmt = $pdo->prepare($sql);
	$stmt -> bindParam(':id', $deleteNo, PDO::PARAM_INT);//INT注意
	$stmt -> bindParam(':password', $password, PDO::PARAM_STR);
	$stmt->execute();
   }

  //入力したデータをselectによって表示
  $sql = 'SELECT * FROM tbtest2';//*は全部の列を選択
  $stmt = $pdo->query($sql);
	  $results = $stmt->fetchAll();
		foreach ($results as $row){
		  //$rowの中にはテーブルのカラム名が入る
		  //$rowの添字（[ ]内）は4-2でどんな名前のカラムを設定したかで変える必要がある。
		  echo $row['id'].',';
		  echo $row['name'].',';
		  echo $row['comment'].',';
		  echo $row['datetime'].'<br>';
		  echo "<hr>";
		}
	  /* $result = $pdo->query($sql);を利用する方法もあるが、変数の値を直接SQL文に埋め込むのはとても危険！詳しくはSQLインジェクションで検索を！*/
 ?>
</body>
</html>
