<?php
session_start();
//xBCD3LdM3qxsPhBR
require_once("twitterOauth/twitteroauth/twitteroauth.php"); //Path to twitteroauth library
require_once("db/Db.class.php");
$notweets =200;
$consumerkey = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
$consumersecret = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
$accesstoken = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
$accesstokensecret = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
$connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);

$db = new Db();
$jobs_categoryArray=$db->query("select * from jobs_category where active=1");
if (count($jobs_categoryArray>0)){	
	
	foreach($jobs_categoryArray as $keyCategory=>$valueCategory){
		//OR Java OR werkvoorbereider OR acceptant OR engineer
		$jobs_searchwordsArray = $db->query("SELECT * FROM jobs_searchwords 
				where active=1  and jobs_category_id='".$valueCategory['id']."'");
		echo "SELECT * FROM jobs_searchwords 
				where active=1  and jobs_category_id='".$valueCategory['id']."'<br>";
		$jobs_searchwordsString="";
		if (count($jobs_searchwordsArray)>0){
			$main_word=1;
			
			foreach($jobs_searchwordsArray as $key=>$value){								
				if($value['main_word']==1){
					$main_word="\"".$value['search_words']."\"";
				}else{
					$jobs_searchwordsString[$value['id']]="\"".$value['search_words']."\"";
				}
			}
			$jobs_searchwords = implode (" OR ", $jobs_searchwordsString);
			
			$search = str_replace(".","%2E",urlencode($main_word." ".$jobs_searchwords));			

			$select_since_id="select max(id_tweet_string) as max_id  from jobs a left join jobs_searchwords b on 
						a.jobs_searchwords_id=b.id left join jobs_category c on b.jobs_category_id = c.id 
						where c.id='".$valueCategory['id']."' limit 1";
			echo $select_since_id."<br>";
			$since_id=$db->single($select_since_id);

			$since_id_parameter="";

			if (count($since_id)>0){
				$since_id_parameter="&since_id=".$since_id;
			}
			
			echo "<br>https://api.twitter.com/1.1/search/tweets.json?q=".$search."&count=".$notweets."&lang=nl".$since_id_parameter."<br>";
			//echo "since_id=".$since_id_parameter."<br>";exit();
			//$search = str_replace("#", "%23", $search);
			$tweets = $connection->get("https://api.twitter.com/1.1/search/tweets.json?q=".$search."&count=".$notweets."&lang=nl".$since_id_parameter);
			
			$data = json_encode($tweets, true);
			//echo $data;
			//exit();

			$tweetsList=json_decode($data, true);

			if (is_array($tweetsList) && count($tweetsList)>0){
				foreach ($tweetsList['statuses'] as $key =>$value){	
					$linkurl="";
					$linkurlDisplay="";
					$create_date=gmdate('Y-m-d H:i:s', strtotime($value['created_at']));
					$jobs_searchwords_id=0;
					foreach($jobs_searchwordsString as $keySearchWordString =>$valueSearchWordString){
						//echo strtolower($value['text'])."---".strtolower(str_replace("\"", "",$valueSearchWordString))."<br>";
						if (strpos(strtolower(str_replace("#", "",$value['text'])),strtolower(str_replace("\"", "",$valueSearchWordString))) !== false) { //find function string 
							$jobs_searchwords_id=$keySearchWordString;							
						}
					}
					
					if ($jobs_searchwords_id==0){
						$select_jobs_searchwords_id="select id  from jobs_searchwords 
						where jobs_category_id='".$valueCategory['id']."' order by id limit 1";	
						echo $select_jobs_searchwords_id."<br>";
						$jobs_searchwords_idArray=$db->single($select_jobs_searchwords_id);
						echo "jobs_searchwords_idArray:".$jobs_searchwords_idArray."<br>";
						$jobs_searchwords_id=$jobs_searchwords_idArray;
					}
					echo "jobs_searchwords_id:".$jobs_searchwords_id."<br>";					
					echo "tweet create date: ".$create_date."<br>";
					//echo "<pre>source:".$value['source']."</pre><br>";
					$source_link=do_reg($value['source']);
					echo "tweet source link: ".$source_link."<br>";
					echo "tweet text: ".$value['text']."<br>";
					$link_job=do_reg($value['text']);
					echo "tweet job link:".$link_job."<br>";
					echo "tweet id: ".$value['id']."<br>";
					echo "tweet id string: ".$value['id_str']."<br>";
					echo "tweet user: ".$value['user']['name']."<br>";
					echo "tweet user description: ".$value['user']['description']."<br>";
					echo "tweet user link: ".$value['user']['url']."<br>";
					echo "tweet user location: ".$value['user']['location']."<br>";
					if (isset($value['user']['entities']['url']['urls']['0']['expanded_url'])){
						$linkurl=$value['user']['entities']['url']['urls']['0']['expanded_url'];
						echo "tweet user display link: <a target=\"_blank\" href=\"".$linkurl."\">".$linkurl."</a><br>";
					}
					if(isset($value['user']['entities']['url']['urls']['0']['display_url'])){
						$linkurlDisplay=$value['user']['entities']['url']['urls']['0']['display_url'];			
						echo "tweet user display link text: <a target=\"_blank\" href=\"".$linkurl."\">".$linkurlDisplay."</a><br>";
					}
					echo "tweet user image:".$value['user']['profile_image_url']."<br><img src=\"".$value['user']['profile_image_url']."\"><br>";
					echo "<br><br>";		
					
					//check double tweet id
					$db->bind("id_tweet_string",$value['id_str']);
					$jobselect   =  $db->query("SELECT * FROM jobs WHERE id_tweet_string = :id_tweet_string");
					
					if (count($jobselect) == 0){ // no id tweet found		
						// Insert	

						echo $value['id_str']."<br>"; 
						$db->bindMore(array(				
							"source_name"=>str_replace("\"","",$source_link),	
							"title"=>$value['text'],	
							"description"=>$value['user']['description'],
							"link_job"=>$link_job,
							"link_url"=>$value['user']['url'],
							"link_image"=>$value['user']['profile_image_url'],
							"user_name"=>$value['user']['name'],
							"user_description"=>$value['user']['description'],
							"user_location"=>$value['user']['location'],
							"create_date"=>$create_date,
							"id_tweet"=>$value['id'],
							"id_tweet_string"=>$value['id_str'],
							"user_link"=>$linkurl,
							"link_job"=>$link_job,
							"jobs_searchwords_id"=>$jobs_searchwords_id
							));
						$insert   =  $db->query("INSERT INTO jobs(source_name,title,description,link_url,link_image,user_name,
										user_description,user_location,create_date,id_tweet,id_tweet_string,user_link,link_job,
										jobs_searchwords_id
										) 
										VALUES(:source_name,:title,:description,:link_url,:link_image,:user_name,
										:user_description,:user_location,:create_date,:id_tweet,:id_tweet_string,:user_link,:link_job,
										:jobs_searchwords_id
										)");

						// Do something with the data 
						if($insert > 0 ) {
						  echo 'Succesfully created a new job !';
						}else{
							echo "foutmelding: ".$insert;
						}
					}else{
						echo "tweet bestaat al in de jobs tabel";
					}
				}
			}else{
				echo "geen resultaten aanwezig";
			}
		}else{
			echo "Er zijn geen zoekwoorden gevonden";
		}
	}
} 

function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
	$connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
	return $connection;
} 
function do_reg($text)
{
	// The Regular Expression filter
	$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";	

	// Check if there is a url in the text
	if(preg_match($reg_exUrl, $text, $url)) {

	   // make the urls hyper links
	   //return preg_replace($reg_exUrl, "<a href="{$url[0]}">{$url[0]}</a> ", $text);
	   // write only link
		return $url[0];

	} else {

		   // if no urls in the text just return the text
		   $text="";
		   return $text;

	}
}
//var_dump(json_decode($json, true));
?>
