<?php
/*
 * 		ytSimpleApi.php
 * 
 *		Author: Giuseppe D'Inverno (aka giudinvx)
 * 		Email:  giudinvx[at]gmail[dot]com
 * 
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata_YouTube');

class ytSimpleApi extends Zend_Gdata_YouTube {
	
	protected static $videoEntry = null;
	protected static $ytVideo = null;
	protected static $videoId = null;
	
	public function __construct( $videoId = null )
	{
		if( is_null($videoId) ) {
			return "Set an Id or Url!"; 
		}
		self::checkId( $videoId );
		self::getApi();
	}
	
	protected static function getApi()
	{
 			$yt = new Zend_Gdata_YouTube();
			try {
				$yt->setMajorProtocolVersion(2);
				self::$ytVideo = $yt;
				self::$videoEntry = $yt->getVideoEntry( self::$videoId );
			} catch( Exception $e ) {
				echo $e->getMessage();
			}

		return self::$videoEntry;	
	}

	public static function checkId( $urlorid )
	{
		try {
			if( preg_match("~watch\?v=([a-zA-Z0-9\-_]+)~", $urlorid, $match) )
				return self::$videoId = $match[1];
			else if( preg_match("~([a-zA-Z0-9\-_]+)~", $urlorid, $match) )
				return self::$videoId = $match[0];
			else
				throw new Exception('Invalid Id or Url');
		} catch( Exception $e ) {
			 echo $e->getMessage();
		}
	}
	
	protected static function setId( $videoId )
	{
		try {
			if( empty($videoId) )
				throw new Exception('Empty video id');
			self::checkId( $videoId );
			self::getApi();
		} catch( Exception $e ) {
			echo $e->getMessage();
		}
	}
	
	/*
	* name: embed
	* @param yt id or url, width, height
	* @return a html embed code
	*/
	public static function embed( $arg1 = null, $arg2 = null, $arg3 = null )
	{
		if( is_null(self::$videoId) ) {
			if( is_null($arg1) ) {
				$videoId = self::setId( $arg1 );
			} else {
				self::checkId( $arg1 );
				self::getApi();
			}
		} else {
			if(	func_num_args() >= 2 ) {
				$numargs = func_get_args();
				$arg2   = ( ( is_null($numargs[0]) ) ? 640 : $numargs[0] );
				$arg3  = ( ( is_null($numargs[1]) ) ? 385 : $numargs[1] );
			}
		}
		return '<iframe class="youtube-player" type="text/html" width="'.( ( is_null($arg2) ) ? 640 : $arg2).'" height="'.( ( is_null($arg3) ) ? 385 : $arg3).'" src="http://www.youtube.com/embed/'.self::$videoEntry->getVideoId().'" frameborder="0"></iframe>';
	}

	/*
	 * name: info
	 * @param id or url (if call it with scope operator), "json" if want a json code
	 * @return An array or json code with info of video
	 */
	public static function info( $arg1 = null, $arg2 = null )
	{
		if( !is_null($arg1) && $arg1 !== "json" )
			self::setId($arg1);
			
		$array = array();
 
		$array['VideoId'] = self::$videoEntry->getVideoId();
		$array['VideoTitle'] = self::$videoEntry->getVideoTitle();
 		$array['Author'] = self::$videoEntry->author[0]->name->text;
		$array['LastUpdate'] = self::$videoEntry->getUpdated()->getText();
		$array['Published'] = self::$videoEntry->getPublished()->getText();
		$array['Category'] = self::$videoEntry->getVideoCategory();		
		$array['Description'] = self::$videoEntry->getVideoDescription();
		$array['Tags'] = implode( ", ", self::$videoEntry->getVideoTags() ); 
		$array['Duration'] = self::$videoEntry->getVideoDuration();
		$array['NumView'] = self::$videoEntry->getVideoViewCount();
		$array['NumLikes'] = self::$videoEntry->extensionElements[7]->extensionAttributes['numLikes']['value'];
	    $array['numDislikes'] = self::$videoEntry->extensionElements[7]->extensionAttributes['numDislikes']['value'];
	    $array['numComments'] = count(self::$ytVideo->getVideoCommentFeed(self::$videoId));
		$array['Rating'] = self::$videoEntry->getVideoRatingInfo();
		$array['Rating']['average'] = $array['Rating']['average'];
		$array['Rating']['numRaters'] = $array['Rating']['numRaters'];
		$array['Thumb'] = self::$videoEntry->getVideoThumbnails();
		
		if( ($arg1 === "json") || ($arg2 === "json") )
			return json_encode($array);
		else
			return $array;
	}
	
	/*
	 * name: comment
	 * @param id or url (if call it with scope operator), n comment, n comment
	 * @return all comment, only n comment or comment between n and n
	 */
	public static function comment( $arg1 = null, $arg2 = null, $arg3 = null )
	{
		if( is_null(self::$videoId) ) {
			if( func_num_args() == 2 ) {
				$arg3 = $arg2;
				$arg2 = 0;
			}
			if( is_null($arg1) ) {
				$videoId = self::setId( $arg1 );
			} else {
				self::checkId( $arg1 );
				self::getApi();
			}
		} else {
			if( func_num_args() == 1 ) {
				$arg3 = $arg1;
			} else if( func_num_args() >= 2 ) {
				$numargs = func_get_args();
				$arg2 = ( ( is_null($numargs[0]) ) ? 0 : $numargs[0] );
				$arg3 = ( ( is_null($numargs[1]) ) ? 0 : $numargs[1] );
			}
		}
		$commentFeed = self::$ytVideo->getVideoCommentFeed(self::$videoId);

 		$ncomment = count($commentFeed);

		$l = ( is_null($arg2) ) ? 0 : $arg2;
		$r = ( is_null($arg3) ) ? $ncomment : $arg3;

		for( ; $l < $r; $l++ ) {
			echo '<hr>'.$commentFeed[$l]->content->text.'</hr>';
		}
	}
}
