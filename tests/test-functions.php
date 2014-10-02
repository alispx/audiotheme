<?php

class Test_AudioTheme_Functions extends WP_UnitTestCase {

	public function test_shortcode_bool() {
		$this->assertFalse( audiotheme_shortcode_bool( 'false' ) );
		$this->assertFalse( audiotheme_shortcode_bool( '0' ) );
		$this->assertFalse( audiotheme_shortcode_bool( 'n' ) );
		$this->assertFalse( audiotheme_shortcode_bool( 'no' ) );
	}

}
