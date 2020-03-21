<html>
	<head>
		<title>FUZZ BROADCAST 
			</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<style>
			html, body{margin: 0px; padding: 0px; }
			body{font-family: courier; text-align:center; }
			#page{ border-radius: 10px; margin: auto; margin-bottom: 10px; margin-top: 10px; width: 96%; max-width: 600px; border: solid 1px #000; padding: 1%; font-size: 20px;  }
			.input{padding:20px; width: 80%; margin: auto;}
			#fileupload{ width: 300px; height: 100px; padding-top: -200px; border: solid 1px #333; background: #666;  margin: auto;}
			#fileupload:hover{background: #999;}
			#fileupload::after{ content: " Drag MP3 File Here"; background-color: yellow; color: red; font-weight: bold;}
			textarea{width: 80%; height: 100px; margin: auto; }
			.item{width: 80%; margin: auto; margin-top: 10px; margin-bottom: 10px;  border :solid 1px #ddd; padding: 1%; }
		</style>
	</head>
	<body>
<?
	date_default_timezone_set('UTC');
	 
	include ('connect.php');
	
	$page=$_GET['page'];
	$go=$_POST['go'];
	echo'<div ID="page"><h2>FUZZ BROADCAST</h2> <br>';
	echo'<div ID="links"><a href="?page=add">ADD</a> \ <a href="?page=listen">LISTEN</a></div>';
		
	if($page==''){
		$page='listen';
		}

	
	if($page=='add'){
		echo'<p>Add your own Mp3s</p>';
		echo'<form method="POST" enctype="multipart/form-data"> ';
		echo'<input  ID="fileupload" type="file" name="embed" placeholder="upload MP3"><br>';
		echo'<p>ENTER SONG TITLE<br><input class="input" type="text" name="songtitle" placeholder="What is the name of this song?"></p>';
		echo'<p>ENTER BAND NAME<br><input class="input" type="text" name="bandname" placeholder="What is your band called?"></p>';

		echo'<p>SONG DESCRIPTION<br><textarea name="description" placeholder="Feel free to write anything here. including lyrics, your bandcamp facebook insta or venmo address, or any other message to listeners. "></textarea></p>';
		//echo'PLEASE BE COOL - Only upload your own songs. OR WE WILL DELETE YOUR SHIT<br><input type="checkbox" name="auth"> <br><center><B>OK</B></center> <br>';
		echo'<p><input  class="input" type="submit" name="go" value="Add Now"></p>';
		echo'</form>';
		
		
		}
		
		if($page=='listen'){
		echo'<p><a href="./podcast.xml">--->Subscribe<--- </a><br>Copy link into your favorite podcast app. </p>';
		echo'<h2>Episodes:</h2>(songs added so far)<br>';
		
		$query="Select * from podcast order by ID DESC";
		foreach($dbh->query( $query ) as $row){

		echo'<div class="item"><a href="./files/'.$row['embed'].'">'.$row['title'] .'</a><br>'.$row['description'].'</div>';
		
		}
	}
	
	
	
	
	if($go!=''){
		
	if(str_replace("'","''", $_FILES[embed][name])!=''){
		$embed=str_replace("'","''", $_FILES[embed][name]);
		echo ' -> Adding file ';
		$add="./files/".$embed;
		echo $add;
		$i=0;
		$query="Select * from podcast where embed = '".$embed."'";
		foreach($dbh->query( $query ) as $row){
			$i++;
			}
			if($i >0){
			echo " -> Upload Filename in use. change your filename - Error";	
			exit;
			}
			
		if(move_uploaded_file ($_FILES[embed][tmp_name],$add)){
			echo "<P>Successfully uploaded the song<P>";
			chmod("$add",0777);}
		else{
			echo " -> Upload Directory Error";	
			exit;
			}	
	
	$error='true';
	if ($_FILES[embed][type]=="audio/mpeg"){$error='false';}
	if ($_FILES[embed][type]=="video/x-aac"){$error='false';}
	if ($_FILES[embed][type]=="audio/x-m4a"){$error='false';}
	

	//if ($_FILES[file]['size'] > 200000000000000){$photoerror='true';}
		
	if($error=='true'){ echo " ->  Upload Type Error " .$_FILES[embed][type]." not allowed. not cool bro. use mp3 or m4a or aac  "; exit;}
	else{echo' ->   Upload Success';}
	}
	else{echo'No file attached. please try again. ';exit; }

	$t='"'.$_POST['songtitle'].'" by '.$_POST['bandname'];
			
	$title=str_replace("'","''", $t);
	$description=str_replace("'","''", $_POST['description']);
	
	echo'Adding New Episode to Podcast';
	
	$statement= $dbh->prepare("INSERT into podcast (title, description, embed) values (:title, :description, :embed )");
	$statement->bindParam(':title', $title);
	$statement->bindParam(':embed', $embed);
	$statement->bindParam(':description', $description);
	$statement->execute();
	
echo '<br>Action completed :  Episode Added <br><a href="index.php">Go Back</a><br>';
	
	
	$file = fopen("./podcast.xml","w");
	$xmlhead='<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">
<channel>
<title>FUZZ BROADCAST</title>
<link></link>
<language>en-us</language>
<itunes:subtitle>FUZZ BROADCAST</itunes:subtitle>
<itunes:author>FUZZ BROADCAST</itunes:author>
<itunes:summary>Upload your own songs. This is an experiment to see if we can all make a podcast together by uploading our songs to this page. </itunes:summary>
<description>Upload your own songs. This is an experiment to see if we can all make a podcast together by uploading our songs to this page.   </description>
<itunes:owner>
    <itunes:name>Nobody is in charge here</itunes:name>
    <itunes:email>john@red.yellow.blue</itunes:email>
</itunes:owner>
<itunes:explicit>no</itunes:explicit>
<itunes:image href="" />
<itunes:category text="Category Name">Music</itunes:category>';

echo fwrite($file, $xmlhead);
		
	

$item = '';
$query="Select * from podcast";
foreach($dbh->query( $query ) as $row){
	$pubdate = date("r", mktime($row['date']));
$item.='
<item>
    <title>'.$row['title'].'</title>
    <itunes:summary>'.$row['description'].'</itunes:summary>
    <description>'.$row['description'].'</description>
    <link>http://www.fuzzbroadcast.com/files/'.$row['embed'].'</link>
    <enclosure url="http://www.fuzzbroadcast.com/files/'.$row['embed'].'" type="audio/mpeg" length="1024"></enclosure>
    <pubDate>'.$pubdate.'</pubDate>
    <itunes:author>'.$row['title'].'</itunes:author>
    <itunes:duration></itunes:duration>
    <itunes:explicit>no</itunes:explicit>
    <guid>http://www.fuzzbroadcast.com/files/'.$row['embed'].'</guid>
</item> 
';


	}
	echo fwrite($file, $item);

	$xmlend= '</channel>
</rss>';
	echo fwrite($file, $xmlend);
	
	fclose($file);
	
	}
	
echo'</div>';
	
	?>
	</body>
	</html>