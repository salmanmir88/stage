var config = {
	map: {
		'*': {
			'magestore/note': 'Magestore_Bannerslider/js/jquery/slider/jquery-ads-note',
			'magestore/impress': 'Magestore_Bannerslider/js/report/impress',
			'magestore/clickbanner': 'Magestore_Bannerslider/js/report/clickbanner',
			
			'owlcarousel': 'js/owl.carousel',
			'popper': 'js/popper.min',
			'popper.js': 'js/popper.min',
			'wow' : 'js/wow',
			'bootstrap' : 'js/bootstrap.min',
			'slick' : 'js/slick.min',
		},
	},
	paths: {
		'magestore/flexslider': 'Magestore_Bannerslider/js/jquery/slider/jquery-flexslider-min',
		'magestore/evolutionslider': 'Magestore_Bannerslider/js/jquery/slider/jquery-slider-min',
		'magestore/popup': 'Magestore_Bannerslider/js/jquery.bpopup.min',
		
		'owlcarousel': 'js/owl.carousel',
		'popper': 'js/popper.min',
		'popper.js': 'js/popper.min',
		'wow' : 'js/wow',
		'bootstrap' : 'js/bootstrap.min',
		'slick' : 'js/slick.min',
		
	},
	shim: {
		'magestore/flexslider': {
			deps: ['jquery']
		},
		'magestore/evolutionslider': {
			deps: ['jquery']
		},
		'magestore/zebra-tooltips': {
			deps: ['jquery']
		},
        "owlcarousel": {
			deps: ['jquery']
		},
        "popper":{
			deps: ['jquery']
		},
        "popper.js":{
			deps: ['jquery']
		},
        "wow": {
			deps: ['jquery']
		},
        "bootstrap": {
			deps: ['jquery']
		},
        "slick": {
			deps: ['jquery']
		}
	}
};
