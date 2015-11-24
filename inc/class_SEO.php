<?php

class SEO {
		private $txt;
		
		__construct( $txt ) {
				$this->txt = $txt;		
		}

		public function get_seo_word() {
			try {
				// remove " from string.
				$txt = str_replace('"',"",$this->txt);
				// turn in array.
				$array = explode( " " , $txt );
				// count occurances of words e.g. house=>3
				$SEO = array_count_values($array); 
				array_multisort( $SEO , SORT_DESC); 
				// array of words to remove from text...
				$delete_val  =  array("to", "was","as","the","and","of","a","in","for","that","by","but","is","she","where","have","for","at","her","like","known","sure", "our","first","up","which","doing","new","not","something","simple","same","has","on");
				// Search for the array key and unset   
				foreach( $delete_val as $key )  {
						$keyToDelete = array_key_exists( $key , $SEO  );
						if ( ( $keyToDelete == 1) || ( strlen($key) <=4 ) ) {
							unset( $SEO [$key] );
						}
				}
				// remove anything which is only in text once....
				foreach ( $SEO  as $key => $value){
					if ($value == "1") {
						unset( $SEO[$key]);
					}
				}
			} catch ( Exception $e ) { $SEO=array(); }

			return $SEO;
		}
}
?>