<?php
/*
	SCSS Compiler
	Recently used projects config
*/
return [
	'project_name1'	=> [
		// Project directory scheme: files & directories are absolute
		'css_file'	=> 'c:/www/localhost/site1/public/assets/css/style.css',
		'scss_file'	=> 'c:/www/localhost/site1/resources/assets/scss/style.scss',
		'cache_dir'	=> 'c:/www/localhost/site1/resources/assets/scss_cache',

		// CSS file style: 0-'Compact',1-'Compressed',2-'Crunched',3-'Debug',4-'Expanded',5-'Nested'
		'css_style'	=> 4,

		// CSS file Signature (compile date&time) in a header of output CSS file
		'signature'	=> TRUE,
	],
	'project_name2'	=> [
		// Project directory scheme: files & directories are absolute
		'css_file'	=> 'c:/www/localhost/site2/public/assets/css/style.css',
		'scss_file'	=> 'c:/www/localhost/site2/resources/assets/scss/style.scss',
		'cache_dir'	=> 'c:/www/localhost/site2/resources/assets/scss_cache',

		// CSS file style: 0-'Compact',1-'Compressed',2-'Crunched',3-'Debug',4-'Expanded',5-'Nested'
		'css_style'	=> 4,

		// CSS file Signature (compile date&time) in a header of output CSS file
		'signature'	=> TRUE,
	],
];
