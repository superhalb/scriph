<?php
	// Copyright (c) 2013, Alberto San Blas Cañabate. License details can be found in license/license.txt

	require_once __DIR__ . '/../../vendor/autoload.php';
	require_once 'config.php';
	require_once 'image.php';
	require_once __DIR__ . '/../../base_url.php';

	class Post {

		static public function Create() {
			$user = Secure::User();
			if ( !user ) return false;

			$id = time() . '-' . rtrim( strtr ( base64_encode(  openssl_random_pseudo_bytes( 4 ) ) , '+/' , '-_' ) , '=' );
			$post = array( 'title' => '' , 'content' => '' , 'tags' => '' , 'author' => $user['id'] );
			Post::Update( $id , $post , $post );
			file_put_contents( __DIR__ . "/../../" . Config::$settings->Storage . "/state/draft/" . $id , "" ); // Always start as draft
			return $id;
		}

		static public function Rename( $old , $new ) {
			$clean = PosT::GetUrl( $new );
			if ( $clean === "cache" ) {
				$clean = "cache-post";
			}
			$id = $clean;
			$dir = ( __DIR__ . '/../../' . Config::$settings->Storage . '/data/' );
			$url = $dir . $clean . '.json';
			for ( $i = 2 ; file_exists( $url ) ; ++$i ) {
				$url = $dir . $clean . '-' . $i . '.json';
				$id = $clean . '-' . $i;
			}

			$post = Post::getPostById( $old );
			$post['id'] = $id;
			reset( $post['state'] );
			$state = key( $post['state'] );
			$datafile = '../../' . Config::$settings->Storage . '/data/' . $old . '.json' ;
			$statefile = '../../' . Config::$settings->Storage . '/state/' .$state .'/' . $old ;
			if ( file_exists( $datafile ) && file_exists( $statefile ) ) {
				// Rename post data
				$newdatafile = '../../' . Config::$settings->Storage . '/data/' . $id . '.json' ;
				rename( $datafile , $newdatafile );
				// Rename state
				$newstatefile = '../../' . Config::$settings->Storage . '/state/' .$state .'/' . $id ;
				rename( $statefile , $newstatefile );
				if( is_dir( '../../images/' . $old ) ) {
					rename( '../../images/' . $old ,'../../images/' . $id  ); // Rename source images
				}
				if( is_dir( '../../images/cache/' . $old ) ) {
					rename( '../../images/cache/' . $old ,'../../images/cache/' . $id  ); // Rename cached images
				}
				Post::Update( $id , $post , $post );
				Post::UpdateTags( $id , $post );
			}
			return $id;
		}

		static public function Remove( $post ) {
			reset( $post['state'] );
			$state = key( $post['state'] );
			$id = $post['id'];
			$datafile = '../../' . Config::$settings->Storage . '/data/' . $id . '.json' ;
			$statefile = '../../' . Config::$settings->Storage . '/state/' .$state .'/' . $id ;
			if ( file_exists( $datafile ) && file_exists( $statefile ) ) {
				unlink( $datafile );
				unlink( $statefile );
				Post::recursiveRemove( '../../images/' . $id  ); // Remove source images
				Post::recursiveRemove( '../../images/cache/' . $id  ); // Remove cached images
				if ( isset( $post['url'] ) && strlen( $post['url'] ) > 2 ) {
					Post::recursiveRemove( '../../content/' . $post['url']  ); // Remove permalink
				}
			}
		}

		static private function UpdateTags( $id , $post , $prev = array( 'tags' => '' ) ) {
			if ( $id === NULL ) return;
			$id =  Post::getIdFromFilename( $id );
			if ( $post ) {
				//krumo( $post['tags'] );
				$l0 = array_map( 'trim' , explode( ',' , $prev['tags'] ) );
				$l1 = array_map( 'trim' , explode( ',' , $post['tags'] ) );
				$del = array_diff( $l0, $l1 );
				$add = array_diff( $l1, $l0 );
				foreach( $del as $l ) {
					if( $l === '' ) continue;
					if ( strpbrk( $l , "\\/?%*:|\"<>") === FALSE ) {
						/* $filename is legal; doesn't contain illegal character. */
						$dir = __DIR__ . "/../../" . Config::$settings->Storage . "/tags/" . urlencode( $l );
						$file = $dir. "/" . urlencode( $id );
						if ( is_file( $file ) ) {
							unlink( $file );
							$iterator = new FilesystemIterator($dir);
							if ( !$iterator->valid() ) {
								rmdir( $dir );
								$tagFolder = __DIR__ . "/../../tags/" . urlencode( $l );
								unlink( $tagFolder . "/index.php" );
								rmdir( $tagFolder );
							}
						}
					}
				}
				foreach( $add as $l ) {
					if( $l === '' ) continue;
					if ( strpbrk( $l , "\\/?%*:|\"<>") === FALSE ) {
						/* $filename is legal; doesn't contain illegal character. */
						$dir = __DIR__ . "/../../" . Config::$settings->Storage . "/tags/" . urlencode( $l );
						if (!is_dir($dir)) {
							mkdir( $dir , 0770 , true );
							$tagFolder = __DIR__ . "/../../tags/" . urlencode( $l );
							mkdir( $tagFolder , 0770 , true );
							$indexphp = '<?php $_GET["f"] = "' . urlencode( $l ) . '"; include "../../index.php";';
							file_put_contents( $tagFolder . '/index.php' , $indexphp );

						}
						$file = $dir. "/" . urlencode( $id );
						file_put_contents( $file , "" );
					}
				}
			}
		}

		static private function GetUrl( $title ) {
			$clean = iconv('UTF-8', 'ASCII//TRANSLIT',  trim( $title ) );
			$clean = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $clean);
			$clean = strtolower(trim($clean, '-'));
			$clean = preg_replace("/[\/_| -]+/", '-', $clean);
			$dir = ( __DIR__ . '/../../content/' );
			$url = $dir . $clean;
			for ( $i = 2 ; is_dir( $url ) ; ++$i ) {
				$url = $dir . $clean . '-' . $i;
			}
			return basename( $url );
		}

		static public function UpdatePermalink( $id , &$post , $prev , $url = false ) {
			if( ! isset( $prev['url'] ) ) {
				if ( strlen( $post['title'] ) > 1 ) {
					$post['url'] = Post::GetUrl( $post['title'] );
					$postdir = ( __DIR__ . '/../../content/' ) . $post['url'];
					$indexphp = '<?php $_GET["id"] = "' . $id . '"; include "../../view.php";';
					mkdir( $postdir , 0770 , true );
					file_put_contents( $postdir . '/index.php' , $indexphp );
				}
			} else {
				if ( $url ) {
					$post['url'] =  Post::GetUrl( $url );
					if ( isset( $prev['url'] ) && strlen( $prev['url'] ) > 2 ) {
						Post::recursiveRemove( '../../content/' . $prev['url']  ); // Remove permalink
					}
				} else {
					$post['url'] =  $prev['url'];
				}
				$postdir = ( __DIR__ . '/../../content/' ) . $post['url'];
				if( !is_dir( $postdir ) ) {
					mkdir( $postdir , 0770 , true );
				}
				$indexphp = '<?php $_GET["id"] = "' . $id . '"; include "../../view.php";';
				file_put_contents( $postdir . '/index.php' , $indexphp );
			}
		}

		static public function Update( $id , $post , $prev ) {
			Post::UpdatePermalink( $id , $post , $prev );
			Post::UpdateTags( $id , $post , $prev );
			if ( isset( $prev['publish-time'] ) ) {
				$post['publish-time'] = $prev['publish-time'];
			}
			$json = json_encode( $post );
			file_put_contents( __DIR__ . "/../../" . Config::$settings->Storage . "/data/" . $id . ".json" , $json );
			$cache = realpath( __DIR__ . "/../../" . Config::$settings->Storage . "/cache/" ) ."/" . $id . ".html";
			if( file_exists( $cache ) ) {
				@unlink( $cache );
			}
			$caches = realpath( __DIR__ . "/../../" . Config::$settings->Storage . "/cache/" ) ."/" . $id . "s.html";
			if( file_exists( $caches ) ) {
				@unlink( $caches );
			}
		}

		static public function getIdFromFilename($filename) {
			$bname = basename( $filename , '.json' );
			$s = strrpos( $bname, '.' );
			$id = $s !== FALSE ? substr( $bname ,  $s + 1) : $bname;
			return $id;
		}

		static public function getPost( $filename ) {
			$json = Post::getContents( $filename );
			$data = json_decode( $json , true );
			$data['id'] = Post::getIdFromFilename($filename);
			if ( isset( $data[ 'intro' ]) ) {
				$data[ 'excerpt' ] = $data[ 'intro' ];
				$data[ 'content' ] = $data[ 'title' ] . "\n\n" . $data[ 'excerpt' ] . "\n\n" . $data[ 'content' ];
				unset( $data[ 'intro' ] );
				Post::Update( $data['id'] , $data , $data );
			}
			$data[ 'description' ] = $data[ 'excerpt' ] != "" ? $data[ 'title' ] ." - " . $data[ 'excerpt' ] : $data[ 'title' ];

			$pstate = Post::$statelookup[ $data['id'] ];
			$data['state'] = array( $pstate => true );
			if(isset($data['face'])){
				if(strpos($data['face'],'youtube:') === 0 ) {
					$data['faceurl'] = "//img.youtube.com/vi/" . substr( $data['face'] , 8 ) . "/hqdefault.jpg";
				} else {
					$data['faceurl'] = getBaseUrl() . '/' . Image::Generate( $data['id'] ,  $data['face'] , 'face' );
				}
				$data['unsecure-faceurl'] = str_replace( 'https://' , 'http://' , $data['faceurl'] );
			}
			if( isset( $data['url'] ) ) {
				$data['permalink'] = getBaseUrl() . '/content/' . $data['url'];
				$data['unsecure-permalink'] = str_replace( 'https://' , 'http://' , $data['permalink'] );
			}
			if( isset( $data['state']['published'] ) ){
				if( !isset( $data['publish-time'] ) ) {
					$parts = explode( '-' , $data['id'] );
					$data['publish-time'] = $parts[0];
					$oldname = realpath( dirname( $filename ) .'/../state/published' ) . '/' . $data['id'];
					$newname = realpath( dirname( $filename ) .'/../state/published' ) . '/' . $data['publish-time'] . '.' .  $data['id'];
					@rename( $oldname, $newname );
				}
			}
			if( isset( $data['labels'] )  ) {
				$data['tags'] = $data['labels'] ;
				unset( $data['labels'] );
			}
			return $data;
		}

		static public function getContents( $filename ) {
			if ( empty( $filename ) || !file_exists( $filename ) ) {
				return '';
			} else {
				return file_get_contents( $filename );
			}
		}

		static public function lookupStateById( $id ) {
			$path = realpath( __DIR__ .  '/../../' . Config::$settings->Storage . '/state' );
			$pattern =  $path . '/*/' . $id ;
			$file = glob( $pattern , GLOB_NOSORT );
			if ( count( $file ) === 0 ) {
				$pattern = $path . '/*/*.' . $id ;
				$file = glob( $pattern , GLOB_NOSORT );
			}
			if ( count( $file ) === 1 ) {
				$t = explode( '/' , str_replace( '\\' , '/' , $file[0] ) );
				if( $t[ count($t) - 3 ] === 'state' ) {
					Post::$statelookup[ $id ] = $t[ count($t) - 2 ];
				}
			}
		}

		static public function getPostById( $id ) {
			if( !isset( Post::$statelookup[ $id ] ) ) {
				Post::lookupStateById( $id  );
			}
			$path = __DIR__ .  '/../../' . Config::$settings->Storage . '/data/';
			$found = glob( $path . '*.' . $id . ".json" , GLOB_NOSORT );
			$post = count( $found ) === 1 ? Post::getPost( $found[0] ) : Post::getPost( $path . $id . ".json" );
			//$post = Post::getPost( __DIR__ .  '/../../' . Config::$settings->Storage . '/data/' . $id . ".json" );
			return $post;
		}

		static public function countPostList( $state = '' , $filter = '') {
			if ( $state === '' && $filter === '') {
				return ( Post::countFilesNamesRecursively( __DIR__ .  '/../../' . Config::$settings->Storage . '/state' ) );
			} else if( $filter === '' ) {
				return ( Post::countFilesNamesRecursively( __DIR__ .  '/../../' . Config::$settings->Storage . '/state/' . $state ) );
			} else if( $state === '' ) {
				$dir = __DIR__ .  '/../../' . Config::$settings->Storage . '/tags/' . urlencode( $filter );
				if ( is_dir($dir) ) {
					return ( Post::countFilesNamesRecursively( $dir ) );
				} else {
					return 0;
				}
			} else {
				$bystate = Post::getFilesNamesRecursively( __DIR__ .  '/../../' . Config::$settings->Storage . '/state/' . $state );
				$dir = __DIR__ .  '/../../' . Config::$settings->Storage . '/tags/' . urlencode( $filter );
				if ( is_dir($dir) ) {
					$byfilter = Post::getFilesNamesRecursively( $dir );
					foreach($bystate as $k=>$v) {
						$bystate[$k]= Post::getIdFromFilename( $v );
					}
					return count( array_intersect( $bystate , $byfilter ) );
				} else {
					return count( $bystate );
				}
			}
		}

		static public function getPostList( $from = -1 , $to = 0 , $state = '' , $filter = '') {
			if ( $state === '' && $filter === '') {
				$posts = Post::getFilesNamesRecursively( __DIR__ .  '/../../' . Config::$settings->Storage . '/state' );
			} else if( $filter === '' ) {
				$posts = Post::getFilesNamesRecursively( __DIR__ .  '/../../' . Config::$settings->Storage . '/state/' . $state );
			} else if( $state === '' ) {
				$dir = __DIR__ .  '/../../' . Config::$settings->Storage . '/tags/' . urlencode( $filter );
				if ( is_dir($dir) ) {
					$posts = Post::getFilesNamesRecursively( $dir );
				} else {
					$posts = array();
				}
			} else {
				$bystate = Post::getFilesNamesRecursively( __DIR__ .  '/../../' . Config::$settings->Storage . '/state/' . $state );
				$dir = __DIR__ .  '/../../' . Config::$settings->Storage . '/tags/' . urlencode( $filter );
				if ( is_dir($dir) ) {
					$byfilter = Post::getFilesNamesRecursively( $dir );
				} else {
					$byfilter = array();
				}
				foreach($bystate as $k=>$v) {
					$bystate[$k]= Post::getIdFromFilename( $v );
				}
				$posts = array_values( array_intersect( $bystate , $byfilter ) );
			}

			if ( $from < 0 ) {
				$from = count( $posts) + $from;
			}
			if ( $to < 0 ) {
				$to = count( $posts) + $to;
			}

			$delta = 1;
			if( $to < $from ) {
				$delta = -1;
			}

			$result = array();
			for( $i = $from;; $i += $delta ) {
				if(isset($posts[ $i ])) {
					$post = Post::getPostById( Post::getIdFromFilename( $posts[ $i ] )  );
					Post::UpdateTags( $posts[ $i ] , $post );
					//Post::Update( Post::getIdFromFilename( $posts[ $i ] ) , $post , $post );
					array_push( $result , $post );
				}
				if ( $i === $to ) break;
			}

			return $result;
		}

		static public function generateFacebookMetaTags( $post = NULL ) {
			$meta = "\n";
			if( $post !== NULL ) {
				$meta .= '<meta property="og:title" content="' . $post['title'] . '" />' . "\n";
				$meta .= '<meta property="og:type" content="article" />' . "\n";
				$meta .= '<meta property="og:description" content="' . $post['excerpt'] . '" />' . "\n";
				$meta .= '<meta property="og:image" content="' . $post['faceurl'] . '" />' . "\n";
				$meta .= '<meta property="og:url" content="'. getBaseUrl() .'/content/'. $post['url'] . '/" />' . "\n";
				$meta .= '<meta property="og:site_name" content="' . Config::$settings->Title . '" />' . "\n";
			} else {
				$meta .= '<meta property="og:title" content="' . Config::$settings->Title . '" />' . "\n";
				$meta .= '<meta property="og:type" content="website" />' . "\n";
				$meta .= '<meta property="og:description" content="' . Config::$settings->Description . '" />' . "\n";
				$meta .= '<meta property="og:url" content="'. getBaseUrl() . '/" />' . "\n";
				$meta .= '<meta property="og:site_name" content="' . Config::$settings->Title . '" />' . "\n";
			}
			return $meta;
		}

		static public function generateTwitterMetaTags( $post = NULL ) {
			$meta = "\n";
			if( $post !== NULL ) {
				$meta .= '<meta name="twitter:card" content="summary">' . "\n";
				$meta .= '<meta name="twitter:url" content="' . getBaseUrl() .'/content/'. $post['url'] . '/" />' . "\n";
				$meta .= '<meta name="twitter:title" content="' . $post['title'] . '" />' . "\n";
				$meta .= '<meta name="twitter:description" content="' . $post['excerpt'] . '" />' . "\n";
				$meta .= '<meta name="twitter:image" content="'. $post['faceurl'] . '" />' . "\n";
			} else {
				$meta .= '<meta name="twitter:card" content="summary">' . "\n";
				$meta .= '<meta name="twitter:url" content="' . getBaseUrl() . '/" />' . "\n";
				$meta .= '<meta name="twitter:title" content="' . Config::$settings->Title . '" />' . "\n";
				$meta .= '<meta name="twitter:description" content="' . Config::$settings->Description . '" />' . "\n";
			}
			return $meta;
		}

		static $statelookup = array();
		static private function getFilesNamesRecursively( $dir , $lookup = TRUE) {
			$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) , RecursiveIteratorIterator::CHILD_FIRST);
			$pfiles = array();
			while ( $it->valid() ) {
				if ( $it->getDepth() > 1 ) {
					$it->next();
					continue;
				}
				if ( ! $it->isDot() ) {
					$bn = basename( $it->key() );
					if($lookup) {
						$t = explode( '/' , str_replace( '\\' , '/' , $it->key() ) );
						if( $t[ count($t) - 3 ] === 'state' ) {
							Post::$statelookup[ $bn ] = $t[ count($t) - 2 ];
						}
					}
					$pfiles[] = $bn;
				}
				$it->next();
			}
			/*
			usort( $pfiles , function( $a , $b ) {
				return strcmp ( $a->id , $b->id );
			});
			*/
			sort( $pfiles );
			return $pfiles;
		}

		static private function countFilesNamesRecursively( $dir ) {
			$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) , RecursiveIteratorIterator::CHILD_FIRST);
			$pfiles = 0;
			while ( $it->valid() ) {
				if ( $it->getDepth() > 1 ) {
					$it->next();
					continue;
				}
				if ( ! $it->isDot() ) {
					$pfiles ++;
				}
				$it->next();
			}
			return $pfiles;
		}


		static private function recursiveRemove( $dirPath ) {
			if( is_dir($dirPath) ) {
				foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
					$path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
				}
				rmdir($dirPath);
			}
		}
	}
