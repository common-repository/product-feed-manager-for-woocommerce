<?php

/**
 * This class is called upon activation of the plugin
 * Channels and countries are set
 */
class Convpfm_Activation
{
	
	public $convpfm_channels;

	public static function activate_checks()
	{
		$convpfm_channels = array(
			"All countries" => array(
				"Google Shopping" => array(
					"channel_hash" => md5("Google Shopping"),
					"name" => "Google Shopping",
					"fields" => "google_shopping",
					"taxonomy" => "google_shopping",
					"utm_source" => "Google Shopping",
					"type" => "Advertising"
				),
				"Google Merchant Promotions Feed" => array(
					"channel_hash" => md5("Google Merchant Promotions Feed"),
					"name" => "Google Merchant Promotions Feed",
					"fields" => "google_shopping_promotions",
					"taxonomy" => "none",
					"utm_source" => "Google Shopping",
					"type" => "Advertising"
				),
				"Google Remarketing - DRM" => array(
					"channel_hash" => md5("Google Remarketing"),
					"name" => "Google Remarketing - DRM",
					"fields" => "google_drm",
					"taxonomy" => "none",
					"utm_source" => "Google Remarketing",
					"type" => "Advertising"
				),
				"Google DSA Feed" => array(
					"channel_hash" => md5("Google DSA"),
					"name" => "Google - DSA",
					"fields" => "google_dsa",
					"taxonomy" => "none",
					"utm_source" => "Google DSA",
					"type" => "Advertising"
				),
				"Google Local Products Feed" => array(
					"channel_hash" => md5("Google Local Products"),
					"name" => "Google Local Products",
					"fields" => "google_local_products",
					"taxonomy" => "google_shopping",
					"utm_source" => "Google Local Products",
					"type" => "Advertising"
				),
				"Google Local Products Inventory Feed" => array(
					"channel_hash" => md5("Google Local Products Inventory"),
					"name" => "Google Local Products Inventory",
					"fields" => "google_local",
					"taxonomy" => "google_shopping",
					"utm_source" => "Google Local Product Inventory",
					"type" => "Advertising"
				),
				"Google Product Review Feed" => array(
					"channel_hash" => md5("Google Product Review"),
					"name" => "Google Product Review",
					"fields" => "google_product_review",
					"taxonomy" => "none",
					"utm_source" => "Google Product Review",
					"type" => "Advertising"
				),
				"Bing Shopping" => array(
					"channel_hash" => md5("Bing Shopping"),
					"name" => "Bing Shopping",
					"fields" => "google_shopping",
					"taxonomy" => "google_shopping",
					"utm_source" => "Bing Shopping",
					"type" => "Advertising"
				),
				"Bing Shopping Promotions" => array(
					"channel_hash" => md5("Bing Shopping Promotions"),
					"name" => "Bing Shopping Promotions",
					"fields" => "google_shopping_promotions",
					"taxonomy" => "google_shopping_promotions",
					"utm_source" => "Bing Shopping Promotions",
					"type" => "Advertising"
				),
				"Facebook Catalog Feed / Instagram" => array(
					"channel_hash" => md5("Facebook Remarketing"),
					"name" => "Meta / Facebook Catalog Feed / Instagram",
					"fields" => "facebook_drm",
					"taxonomy" => "google_shopping",
					"utm_source" => "Facebook Catalog Feed",
					"type" => "Advertising"
				),
				"Pinterest" => array(
					"channel_hash" => md5("Pinterest"),
					"name" => "Pinterest",
					"fields" => "pinterest",
					"taxonomy" => "google_shopping",
					"utm_source" => "Pinterest",
					"type" => "Advertising"
				),
				"Twitter" => array(
					"channel_hash" => md5("Twitter"),
					"name" => "Twitter",
					"fields" => "google_shopping",
					"taxonomy" => "google_shopping",
					"utm_source" => "Twitter",
					"type" => "Advertising"
				),
				"Pinterest RSS Board" => array(
					"channel_hash" => md5("Pinterest RSS Board"),
					"name" => "Pinterest RSS Board",
					"fields" => "pinterest_rss",
					"taxonomy" => "none",
					"utm_source" => "Pinterest RSS Board",
					"type" => "Advertising"
				),
				"Snapchat Product Catalog" => array(
					"channel_hash" => md5("Snapchat Product Catalog"),
					"name" => "Snapchat Product Catalog",
					"fields" => "snapchat",
					"taxonomy" => "google_shopping",
					"utm_source" => "snapchat",
					"type" => "Advertising"
				),
				"TikTok Product Catalog" => array(
					"channel_hash" => md5("TikTok Product Catalog"),
					"name" => "TikTok Product Catalog",
					"fields" => "tiktok",
					"taxonomy" => "google_shopping",
					"utm_source" => "tiktok",
					"type" => "Advertising"
				),
				"Vivino" => array(
					"channel_hash" => md5("Vivino"),
					"name" => "Vivino",
					"fields" => "vivino",
					"taxonomy" => "none",
					"utm_source" => "Vivino",
					"type" => "Advertising"
				),
			),
			"Custom Feed" => array(
				"Custom Feed" => array(
					"channel_hash" => md5("Custom Feed"),
					"name" => "Custom Feed",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Custom Feed",
					"type" => "Custom Feed"
				)
			),
			"Afghanistan" => array(),
			"Albania" => array(),
			"Algeria" => array(),
			"Andorra" => array(),
			"Angola" => array(),
			"Antigua & Deps" => array(),
			"Argentina" => array(
				"ShopMania" => array(
					"channel_hash" => md5("Shopmania"),
					"name" => "Shopmania.com.ar",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "Shopmania.com.ar",
					"type" => "Marketplace"
				),
				"Wish.com" => array(
					"channel_hash" => md5("Wish.com"),
					"name" => "Wish.com",
					"fields" => "wishcom",
					"taxonomy" => "none",
					"utm_source" => "Wish.com",
					"type" => "Marketplace"
				),
			),
			"Armenia" => array(),
			"Australia" => array(
				"Catch.com.au" => array(
					"channel_hash" => md5("Catch.com.au"),
					"name" => "Catch.com.au",
					"fields" => "catchcomau",
					"taxonomy" => "none",
					"utm_source" => "Catch.com.au",
					"type" => "Marketplace"
				),
				"Wish.com" => array(
					"channel_hash" => md5("Wish.com"),
					"name" => "Wish.com",
					"fields" => "wishcom",
					"taxonomy" => "none",
					"utm_source" => "Wish.com",
					"type" => "Marketplace"
				),
				"Kogan.com" => array(
					"channel_hash" => md5("Kogan.com"),
					"name" => "Kogan.com",
					"fields" => "kogan",
					"taxonomy" => "none",
					"utm_source" => "Kogan.com",
					"type" => "Marketplace"
				),
				"Fruugoaustralia" => array(
					"channel_hash" => md5("Fruugoaustralia.com"),
					"name" => "Fruugoaustralia.com",
					"fields" => "fruugoaus",
					"taxonomy" => "none",
					"utm_source" => "Fruugoaustralia.com",
					"type" => "Marketplace"
				),
				"Shopping.com" => array(
					"channel_hash" => md5("Shopping.com"),
					"name" => "Shopping.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Shopping.com",
					"type" => "Comparison shopping engine"
				),
				"Myshopping" => array(
					"channel_hash" => md5("Myshopping.com.au"),
					"name" => "Myshopping.com.au",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Myshopping.com.au",
					"type" => "Comparison shopping engine"
				),
				"ShopMania" => array(
					"channel_hash" => md5("Shopmania.com.au"),
					"name" => "Shopmania.com.au",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "Shopmania.com.au",
					"type" => "Comparison shopping engine"
				),
				"Polyvore.com" => array(
					"channel_hash" => md5("Polyvore.com"),
					"name" => "Polyvore.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Polyvore.com",
					"type" => "Comparison shopping engine"
				),
			),
			"Austria" => array(),
			"Azerbaijan" => array(),
			"Bahamas" => array(),
			"Bahrain" => array(),
			"Bangladesh" => array(),
			"Barbados" => array(),
			"Belarus" => array(),
			"Belgium" => array(
				"Vergelijk.be" => array(
					"channel_hash" => md5("Vergelijk.be"),
					"name" => "Vergelijk.be",
					"fields" => "vergelijkbe",
					"taxonomy" => "none",
					"utm_source" => "Vergelijk.be",
					"type" => "Comparison shopping engine"
				),
				"Comparer.be" => array(
					"channel_hash" => md5("Comparer.be"),
					"name" => "Comparer.be",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Comparer.be",
					"type" => "Comparison shopping engine"
				),
				"Kieskeurig.be" => array(
					"channel_hash" => md5("Kieskeurig.be"),
					"name" => "Kieskeurig.be",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Kieskeurig.be",
					"type" => "Comparison shopping engine"
				),
				"Beslist.be" => array(
					"channel_hash" => md5("Beslist.be"),
					"name" => "Beslist.be",
					"fields" => "beslist",
					"taxonomy" => "none",
					"utm_source" => "Beslist.be",
					"type" => "Comparison shopping engine"
				),
				"Bol.com" => array(
					"channel_hash" => md5("Bol.com"),
					"name" => "Bol.com",
					"fields" => "bol",
					"taxonomy" => "none",
					"utm_source" => "Bol.com",
					"type" => "Marketplace"
				),
			),
			"Belize" => array(),
			"Benin" => array(),
			"Bhutan" => array(),
			"Bolivia" => array(),
			"Bosnia Herzegovina" => array(),
			"Botswana" => array(),
			"Brazil" => array(
				"Stylight" => array(
					"channel_hash" => md5("Stylight.com.br"),
					"name" => "Stylight.com.br",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Stylight.com.br",
					"type" => "Comparison shopping engine"
				),
				"Shopmania" => array(
					"channel_hash" => md5("Shopmania.com.br"),
					"name" => "Shopmania.com.br",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Shopmanie.com.br",
					"type" => "Comparison shopping engine"
				),
			),
			"Brunei" => array(),
			"Bulgaria" => array(
				"Shopmania" => array(
					"channel_hash" => md5("Shopmania.bg"),
					"name" => "Shopmania.bg",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Shopmanie.bg",
					"type" => "Comparison shopping engine"
				),
			),
			"Burkina" => array(),
			"Burundi" => array(),
			"Cambodia" => array(),
			"Cameroon" => array(),
			"Canada" => array(
				"Incurvy" => array(
					"channel_hash" => md5("Incurvy.com"),
					"name" => "Incurvy.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Incurvy.com",
					"type" => "Marketplace"
				),
				"Kijiji" => array(
					"channel_hash" => md5("Kijiji.ca"),
					"name" => "Kijiji.ca",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Kijiji.ca",
					"type" => "Advertising"
				),
				"Polyvore.com" => array(
					"channel_hash" => md5("Polyvore.com"),
					"name" => "Polyvore.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Polyvore.com",
					"type" => "Comparison shopping engine"
				),
				"Stylight.ca" => array(
					"channel_hash" => md5("Stylight.ca"),
					"name" => "Stylight.ca",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Stylight.ca",
					"type" => "Comparison shopping engine"
				),
			),
			"Cape Verder" => array(),
			"Central African Rep" => array(),
			"Chad" => array(),
			"Chile" => array(
				"Shopmania.cl" => array(
					"channel_hash" => md5("Shopmania.cl"),
					"name" => "Shopmania.cl",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Shopmania.cl",
					"type" => "Comparison shopping engine"
				),
			),
			"China" => array(),
			"Colombia" => array(),
			"Comoros" => array(),
			"Congo" => array(),
			"Costa Rica" => array(),
			"Croatia" => array(),
			"Cuba" => array(),
			"Cyprus" => array(),
			"Czech Republic" => array(
				"Shop-mania.cz" => array(
					"channel_hash" => md5("Shop-mania.cz"),
					"name" => "Shop-mania.cz",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Shop-mania.cz",
					"type" => "Comparison shopping engine"
				),
				"Kelkoo.cz" => array(
					"channel_hash" => md5("Kelkoo.cz"),
					"name" => "Kelkoo.cz",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Kelkoo.cz",
					"type" => "Comparison shopping engine"
				),
				"Glami" => array(
					"channel_hash" => md5("Glami.cz"),
					"name" => "Glami.cz",
					"fields" => "glami",
					"taxonomy" => "none",
					"utm_source" => "Glami.cz",
					"type" => "Comparison shopping engine"
				),
				"Zbozi.cz" => array(
					"channel_hash" => md5("Zbozi.cz"),
					"name" => "Zbozi.cz",
					"fields" => "zbozi",
					"taxonomy" => "none",
					"utm_source" => "Zbozi.cz",
					"type" => "Comparison shopping engine"
				),
				"Shopalike.cz" => array(
					"channel_hash" => md5("Shopalike.cz"),
					"name" => "Shopalike.cz",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Shopalike.cz",
					"type" => "Comparison shopping engine"
				),
				"Heureka.cz" => array(
					"channel_hash" => md5("Heureka.cz"),
					"name" => "Heureka.cz",
					"fields" => "heureka",
					"taxonomy" => "none",
					"utm_source" => "Heureka.cz",
					"type" => "Marketplace"
				),
			),
			"Denmark" => array(
				"Adform" => array(
					"channel_hash" => md5("Adform.com"),
					"name" => "Adform",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Adform.com",
					"type" => "Advertising"
				),
				"Smartly.io" => array(
					"channel_hash" => md5("Smartly.io"),
					"name" => "Smartly.io",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Smartly.io",
					"type" => "Advertising"
				),
				"Pricerunner" => array(
					"channel_hash" => md5("Pricerunner.dk"),
					"name" => "Pricerunner.dk",
					"fields" => "pricerunner",
					"taxonomy" => "none",
					"utm_source" => "Pricerunner.dk",
					"type" => "Comparison shopping engine"
				),
				"Shopalike" => array(
					"channel_hash" => md5("Shopalike.dk"),
					"name" => "ShopAlike.dk",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "ShopAlike.dk",
					"type" => "Comparison shopping engine"
				),
				"Miinto" => array(
					"channel_hash" => md5("Miinto.dk"),
					"name" => "Miinto.dk",
					"fields" => "miinto_dk",
					"taxonomy" => "none",
					"utm_source" => "Miinto.dk",
					"type" => "Comparison shopping engine"
				),
				"Katoni" => array(
					"channel_hash" => md5("Katoni.dk"),
					"name" => "Katoni.dk",
					"fields" => "katoni",
					"taxonomy" => "none",
					"utm_source" => "Katoni.dk",
					"type" => "Comparison shopping engine"
				),
			),
			"Djibouti" => array(),
			"Dominica" => array(),
			"Dominican Republic" => array(),
			"East Timor" => array(),
			"Ecuador" => array(),
			"Egypt" => array(),
			"El Salvador" => array(),
			"Equatorial Guinea" => array(),
			"Eritrea" => array(),
			"Estonia" => array(),
			"Ethiopia" => array(),
			"Fiji" => array(),
			"Finland" => array(
				"Adform" => array(
					"channel_hash" => md5("Adform.com"),
					"name" => "Adform",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Adform.com",
					"type" => "Advertising"
				),
				"Smartly.io" => array(
					"channel_hash" => md5("Smartly.io"),
					"name" => "Smartly.io",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Smartly.io",
					"type" => "Advertising"
				),
				"Vertaa.fi" => array(
					"channel_hash" => md5("Vertaa.fi"),
					"name" => "Vertaa.fi",
					"fields" => "vertaafi",
					"taxonomy" => "none",
					"utm_source" => "Vertaa.fi",
					"type" => "Comparison shopping engine"
				),
				"Prisjakt" => array(
					"channel_hash" => md5("Prisjakt"),
					"name" => "Prisjakt",
					"fields" => "google_shopping",
					"taxonomy" => "google_shopping",
					"utm_source" => "Prisjakt",
					"type" => "Comparison shopping engine"
				),
				"Hintaseuranta" => array(
					"channel_hash" => md5("Hintaseuranta.fi"),
					"name" => "Hintaseuranta.fi",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Hintaseuranta.fi",
					"type" => "Comparison shopping engine"
				),
			),
			"France" => array(
				"Connexity" => array(
					"channel_hash" => md5("Connexity.com"),
					"name" => "Connexity",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Connexity.com",
					"type" => "Advertising"
				),
				"ManoMano" => array(
					"channel_hash" => md5("ManoMano.fr"),
					"name" => "ManoMano.fr",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "ManoMano.fr",
					"type" => "Marketplace"
				),
				"Incurvy" => array(
					"channel_hash" => md5("Incurvy"),
					"name" => "Incurvy",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Incurvy",
					"type" => "Marketplace"
				),
				"Cherchons.com" => array(
					"channel_hash" => md5("Cherchons.com"),
					"name" => "Cherchons.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Cherchons.com",
					"type" => "Comparison shopping engine"
				),
				"Choozen.fr" => array(
					"channel_hash" => md5("Choozen.fr"),
					"name" => "Choozen.fr",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Choozen.fr",
					"type" => "Comparison shopping engine"
				),
				"Ciao.fr" => array(
					"channel_hash" => md5("Ciao.fr"),
					"name" => "Ciao.fr",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Ciao.fr",
					"type" => "Comparison shopping engine"
				),
				"Comparer.fr" => array(
					"channel_hash" => md5("Comparer.fr"),
					"name" => "Comparer.fr",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Comparer.fr",
					"type" => "Comparison shopping engine"
				),
				"Idealo.fr" => array(
					"channel_hash" => md5("Idealo.fr"),
					"name" => "Idealo.fr",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Idealo.fr",
					"type" => "Comparison shopping engine"
				),
				"Kelkoo.fr" => array(
					"channel_hash" => md5("Kelkoo.fr"),
					"name" => "Kelkoo.fr",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Kelkoo.fr",
					"type" => "Comparison shopping engine"
				),
				"LeGuide.fr" => array(
					"channel_hash" => md5("LeGuide.fr"),
					"name" => "LeGuide.fr",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "LeGuide.fr",
					"type" => "Comparison shopping engine"
				),
				"Miinto" => array(
					"channel_hash" => md5("Miinto.fr"),
					"name" => "Miinto.fr",
					"fields" => "miinto_fr",
					"taxonomy" => "none",
					"utm_source" => "Miinto.fr",
					"type" => "Comparison shopping engine"
				),
				"Priceminister.fr" => array(
					"channel_hash" => md5("Priceminister.fr"),
					"name" => "Priceminister.fr",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Priceminister.fr",
					"type" => "Comparison shopping engine"
				),
				"Pricerunner.fr" => array(
					"channel_hash" => md5("Pricerunner.fr"),
					"name" => "Pricerunner.fr",
					"fields" => "pricerunner",
					"taxonomy" => "none",
					"utm_source" => "Pricerunner.fr",
					"type" => "Comparison shopping engine"
				),
				"ShopAlike.fr" => array(
					"channel_hash" => md5("ShopAlike.fr"),
					"name" => "ShopAlike.fr",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "ShopAlike.fr",
					"type" => "Comparison shopping engine"
				),
				"ShopMania.fr" => array(
					"channel_hash" => md5("ShopMania.fr"),
					"name" => "ShopMania.fr",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "ShopMania.fr",
					"type" => "Comparison shopping engine"
				),
				"Shopping.com" => array(
					"channel_hash" => md5("Shopping.com"),
					"name" => "Shopping.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Shopping.com",
					"type" => "Comparison shopping engine"
				),
				"Shopzilla.fr" => array(
					"channel_hash" => md5("Shopzilla.fr"),
					"name" => "Shopzilla.fr",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Shopzilla.fr",
					"type" => "Comparison shopping engine"
				),
				"Stylefruits.fr" => array(
					"channel_hash" => md5("Stylefruits.fr"),
					"name" => "Stylefruits.fr",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Stylefruits.fr",
					"type" => "Comparison shopping engine"
				),
				"Stylight.fr" => array(
					"channel_hash" => md5("Stylight.fr"),
					"name" => "Stylight.fr",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Stylight.fr",
					"type" => "Comparison shopping engine"
				),
				"Twenga.fr" => array(
					"channel_hash" => md5("Twenga.fr"),
					"name" => "Twenga.fr",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Twenga.fr",
					"type" => "Comparison shopping engine"
				),
				"Webmarchand.com" => array(
					"channel_hash" => md5("Webmarchand.com"),
					"name" => "Webmarchand.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Webmarchand.com",
					"type" => "Comparison shopping engine"
				),
			),
			"French Guiana" => array(),
			"Gabon" => array(),
			"Gambia" => array(),
			"Georgia" => array(),
			"Germany" => array(
				"Connexity" => array(
					"channel_hash" => md5("Connexity.com"),
					"name" => "Connexity",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Connexity.com",
					"type" => "Advertising"
				),
				"Adform" => array(
					"channel_hash" => md5("Adform"),
					"name" => "Adform",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Adform",
					"type" => "Advertising"
				),
				"AdRoll" => array(
					"channel_hash" => md5("AdRoll.de"),
					"name" => "AdRoll.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "AdRoll.de",
					"type" => "Advertising"
				),
				"ElCheapo.de" => array(
					"channel_hash" => md5("ElCheapo.de"),
					"name" => "ElCheapo.de",
					"fields" => "vergelijknl",
					"taxonomy" => "none",
					"utm_source" => "ElCheapo.de",
					"type" => "Comparison shopping engine"
				),
				"Smartly.io" => array(
					"channel_hash" => md5("Smartly.io"),
					"name" => "Smartly.io",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Smartly.io",
					"type" => "Advertising"
				),
				"TheNextAd" => array(
					"channel_hash" => md5("TheNextAd"),
					"name" => "TheNextAd",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "TheNextAd",
					"type" => "Advertising"
				),
				"Webgains" => array(
					"channel_hash" => md5("Webgains"),
					"name" => "Webgains.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Webgains.de",
					"type" => "Advertising"
				),
				"Crowdfox" => array(
					"channel_hash" => md5("Crowdfox.com"),
					"name" => "Crowdfox.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Crowdfox.com",
					"type" => "Marketplace"
				),
				"Real.de" => array(
					"channel_hash" => md5("Real.de"),
					"name" => "Real.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Real.de",
					"type" => "Marketplace"
				),
				"Incurvy.de" => array(
					"channel_hash" => md5("Incurvy.de"),
					"name" => "Incurvy.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Incurvy.de",
					"type" => "Marketplace"
				),
				"Allyouneed" => array(
					"channel_hash" => md5("Allyouneed.com"),
					"name" => "Allyouneed.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Allyouneed.de",
					"type" => "Comparison shopping engine"
				),
				"Apomio" => array(
					"channel_hash" => md5("Apomio.de"),
					"name" => "Apomio.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Apomio.de",
					"type" => "Comparison shopping engine"
				),
				"Billiger" => array(
					"channel_hash" => md5("Billiger.de"),
					"name" => "Billiger.de",
					"fields" => "billiger",
					"taxonomy" => "none",
					"utm_source" => "Billiger.de",
					"type" => "Comparison shopping engine"
				),
				"Choozen" => array(
					"channel_hash" => md5("Choozen.de"),
					"name" => "Choozen.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Choozen.de",
					"type" => "Comparison shopping engine"
				),
				"Ciao" => array(
					"channel_hash" => md5("Ciao.de"),
					"name" => "Ciao.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Ciao.de",
					"type" => "Comparison shopping engine"
				),
				"Domodi" => array(
					"channel_hash" => md5("Domodi.de"),
					"name" => "Domodi.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Domodi.de",
					"type" => "Comparison shopping engine"
				),
				"Fashiola" => array(
					"channel_hash" => md5("Fashiola.de"),
					"name" => "Fashiola.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Fashiola.de",
					"type" => "Comparison shopping engine"
				),
				"Geizhals" => array(
					"channel_hash" => md5("Geizhals.de"),
					"name" => "Geizhals.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Geishals.de",
					"type" => "Comparison shopping engine"
				),
				"Guenstiger" => array(
					"channel_hash" => md5("Guenstiger.de"),
					"name" => "Guenstiger.de",
					"fields" => "guenstiger",
					"taxonomy" => "none",
					"utm_source" => "Guenstiger.de",
					"type" => "Comparison shopping engine"
				),
				"Hood.de" => array(
					"channel_hash" => md5("Hood.de"),
					"name" => "Hood.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Hood.de",
					"type" => "Comparison shopping engine"
				),
				"Idealo.de" => array(
					"channel_hash" => md5("Idealo.de"),
					"name" => "Idealo.de",
					"fields" => "idealo",
					"taxonomy" => "none",
					"utm_source" => "Idealo.de",
					"type" => "Comparison shopping engine"
				),
				"Kelkoo.de" => array(
					"channel_hash" => md5("Kelkoo.de"),
					"name" => "Kelkoo.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Kelkoo.de",
					"type" => "Comparison shopping engine"
				),
				"Ladenzeile.de" => array(
					"channel_hash" => md5("Ladenzeile.de"),
					"name" => "Ladenzeile.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Ladenzeile.de",
					"type" => "Comparison shopping engine"
				),
				"Livingo.de" => array(
					"channel_hash" => md5("Livingo.de"),
					"name" => "Livingo.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Livingo.de",
					"type" => "Comparison shopping engine"
				),
				"Medizinfuchs.de" => array(
					"channel_hash" => md5("Medizinfuchs.de"),
					"name" => "Medizinfuchs.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Medizinfuchs.de",
					"type" => "Comparison shopping engine"
				),
				"Miinto.de" => array(
					"channel_hash" => md5("Miinto.de"),
					"name" => "Miinto.de",
					"fields" => "miinto_de",
					"taxonomy" => "none",
					"utm_source" => "Miinto.de",
					"type" => "Comparison shopping engine"
				),
				"Moebel.de" => array(
					"channel_hash" => md5("Moebel.de"),
					"name" => "Moebel.de",
					"fields" => "moebel",
					"taxonomy" => "none",
					"utm_source" => "Moebel.de",
					"type" => "Comparison shopping engine"
				),
				"My Best Brands" => array(
					"channel_hash" => md5("Mybestbrands.de"),
					"name" => "Mybestbrands.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Mybestbrands.de",
					"type" => "Comparison shopping engine"
				),
				"Preis.de" => array(
					"channel_hash" => md5("Preis.de"),
					"name" => "Preis.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Preis.de",
					"type" => "Comparison shopping engine"
				),
				"Pricerunner.de" => array(
					"channel_hash" => md5("Pricerunner.de"),
					"name" => "Pricerunner.de",
					"fields" => "pricerunner",
					"taxonomy" => "none",
					"utm_source" => "Pricerunner.de",
					"type" => "Comparison shopping engine"
				),
				"Rakuten.de" => array(
					"channel_hash" => md5("Rakuten.de"),
					"name" => "Rakuten.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Rakuten.de",
					"type" => "Comparison shopping engine"
				),
				"Restposten.de" => array(
					"channel_hash" => md5("Restposten.de"),
					"name" => "Restposten.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Restposten.de",
					"type" => "Comparison shopping engine"
				),
				"Shopmania.de" => array(
					"channel_hash" => md5("Shopmania.de"),
					"name" => "Shopmania.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Shopmania.de",
					"type" => "Comparison shopping engine"
				),
				"Shopping.com" => array(
					"channel_hash" => md5("Shopping.com"),
					"name" => "Shopping.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Shopping.com",
					"type" => "Comparison shopping engine"
				),
				"Shopzilla.de" => array(
					"channel_hash" => md5("Shopzilla.de"),
					"name" => "Shopzilla.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Shopzilla.de",
					"type" => "Comparison shopping engine"
				),
				"Sparmedo.de" => array(
					"channel_hash" => md5("Sparmedo.de"),
					"name" => "Sparmedo.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Sparmedo.de",
					"type" => "Comparison shopping engine"
				),
				"Stylefruits.de" => array(
					"channel_hash" => md5("Stylefruits.de"),
					"name" => "Stylefruits.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Stylefruits.de",
					"type" => "Comparison shopping engine"
				),
				"Stylelounge.de" => array(
					"channel_hash" => md5("Stylelounge.de"),
					"name" => "Stylelounge.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Stylelounge.de",
					"type" => "Comparison shopping engine"
				),
				"Stylight.de" => array(
					"channel_hash" => md5("Stylight.de"),
					"name" => "Stylight.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Stylight.de",
					"type" => "Comparison shopping engine"
				),
				"Twenga.de" => array(
					"channel_hash" => md5("Twenga.de"),
					"name" => "Twenga.de",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Twenga.de",
					"type" => "Comparison shopping engine"
				),
				"Wish.com" => array(
					"channel_hash" => md5("Wish.com"),
					"name" => "Wish.com",
					"fields" => "wishcom",
					"taxonomy" => "none",
					"utm_source" => "Wish.com",
					"type" => "Marketplace"
				),
			),
			"Ghana" => array(),
			"Greece" => array(
				"Incurvy" => array(
					"channel_hash" => md5("Incurvy.com"),
					"name" => "Incurvy",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Incurvy.com",
					"type" => "Marketplace"
				),
				"Shopflix" => array(
					"channel_hash" => md5("Shopflix.gr"),
					"name" => "Shopflix",
					"fields" => "shopflix",
					"taxonomy" => "none",
					"utm_source" => "Shopflix.gr",
					"type" => "Marketplace"
				),
				"Skroutz" => array(
					"channel_hash" => md5("Skroutz.gr"),
					"name" => "Skroutz",
					"fields" => "skroutz",
					"taxonomy" => "none",
					"utm_source" => "Skroutz.gr",
					"type" => "Comparison shopping engine"
				),
				"Bestprice" => array(
					"channel_hash" => md5("Bestprice.gr"),
					"name" => "Bestprice",
					"fields" => "bestprice",
					"taxonomy" => "none",
					"utm_source" => "Bestprice.gr",
					"type" => "Comparison shopping engine"
				),
				"Glami" => array(
					"channel_hash" => md5("Glami.gr"),
					"name" => "Glami.gr",
					"fields" => "glami",
					"taxonomy" => "none",
					"utm_source" => "Glami.gr",
					"type" => "Comparison shopping engine"
				),
			),
			"Grenada" => array(),
			"Guadeloupe" => array(),
			"Guatemala" => array(),
			"Guinea" => array(),
			"Guinea-Bissau" => array(),
			"Guyana" => array(),
			"Haiti" => array(),
			"Honduras" => array(),
			"Hong Kong" => array(),
			"Hungary" => array(
				"ShopMania" => array(
					"channel_hash" => md5("ShopMania.hu"),
					"name" => "ShopMania.hu",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "ShopMania.hu",
					"type" => "Comparison shopping engine"
				),
			),
			"Iceland" => array(),
			"India" => array(
				"ShopMania" => array(
					"channel_hash" => md5("ShopMania.in"),
					"name" => "ShopMania.in",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "ShopMania.in",
					"type" => "Comparison shopping engine"
				),
			),
			"Indonesia" => array(),
			"Iran" => array(),
			"Iraq" => array(),
			"Ireland" => array(
				"ShopMania" => array(
					"channel_hash" => md5("ShopMania.ie"),
					"name" => "ShopMania.ie",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "ShopMania.ie",
					"type" => "Comparison shopping engine"
				),
				"Stylight" => array(
					"channel_hash" => md5("Stylight.ie"),
					"name" => "Stylight.ie",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Stylight.ie",
					"type" => "Comparison shopping engine"
				),
			),
			"Israel" => array(
				"Zap.co.il" => array(
					"channel_hash" => md5("Zap.co.il"),
					"name" => "Zap.co.il",
					"fields" => "zap",
					"taxonomy" => "none",
					"utm_source" => "Zap.co.il",
					"type" => "Comparison shopping engine"
				),
			),
			"Italy" => array(
				"ShopAlike" => array(
					"channel_hash" => md5("ShopAlike.it"),
					"name" => "ShopAlike.it",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "ShopAlike.it",
					"type" => "Comparison shopping engine"
				),
				"Idealo.it" => array(
					"channel_hash" => md5("Idealo.it"),
					"name" => "Idealo.it",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Idealo.it",
					"type" => "Comparison shopping engine"
				),
				"ShopMania" => array(
					"channel_hash" => md5("ShopMania.it"),
					"name" => "ShopMania.it",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "ShopMania.it",
					"type" => "Comparison shopping engine"
				),
				"Trovaprezzi" => array(
					"channel_hash" => md5("Trovaprezzi.it"),
					"name" => "Trovaprezzi.it",
					"fields" => "trovaprezzi",
					"taxonomy" => "none",
					"utm_source" => "Trovaprezzi.it",
					"type" => "Comparison shopping engine"
				),
				"Stylight" => array(
					"channel_hash" => md5("Stylight.it"),
					"name" => "Stylight.it",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Stylight.it",
					"type" => "Comparison shopping engine"
				),
				"Kijiji" => array(
					"channel_hash" => md5("Kijiji.it"),
					"name" => "Kijiji.it",
					"fields" => "kijiji",
					"taxonomy" => "none",
					"utm_source" => "Kijiji.it",
					"type" => "Marketplace"
				),
				"Wish.com" => array(
					"channel_hash" => md5("Wish.com"),
					"name" => "Wish.com",
					"fields" => "wishcom",
					"taxonomy" => "none",
					"utm_source" => "Wish.com",
					"type" => "Marketplace"
				),
			),
			"Ivory Coast" => array(),
			"Jamaica" => array(),
			"Japan" => array(
				"ShopMania" => array(
					"channel_hash" => md5("ShopMania.jp"),
					"name" => "ShopMania.jp",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "ShopMania.jp",
					"type" => "Comparison shopping engine"
				),
			),
			"Jordan" => array(),
			"Kazakhstan" => array(),
			"Kenya" => array(),
			"Kiribati" => array(),
			"Korea North" => array(),
			"Korea South" => array(),
			"Kosovo" => array(),
			"Kuwait" => array(),
			"Kyrgyzstan" => array(),
			"Laos" => array(),
			"Latvia" => array(
				"Salidzini.lv" => array(
					"channel_hash" => md5("Salidzini.lv"),
					"name" => "Salidzini.lv",
					"fields" => "salidzini",
					"taxonomy" => "none",
					"utm_source" => "Salidzini.lv",
					"type" => "Comparison shopping engine"
				),
			),
			"Lebanon" => array(),
			"Lesotho" => array(),
			"Liberia" => array(),
			"Libya" => array(),
			"Lichtenstein" => array(),
			"Lithuania" => array(),
			"Luxembourg" => array(),
			"Macedonia" => array(),
			"Madagascar" => array(),
			"Malawi" => array(),
			"Malaysia" => array(),
			"Maldives" => array(),
			"Mali" => array(),
			"Malta" => array(),
			"Marshall Islands" => array(),
			"Martinique" => array(),
			"Mauritania" => array(),
			"Mauritius" => array(),
			"Mexico" => array(
				"ShopMania" => array(
					"channel_hash" => md5("ShopMania.com.mx"),
					"name" => "ShopMania.com.mx",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "ShopMania.com.mx",
					"type" => "Comparison shopping engine"
				),
				"Stylight" => array(
					"channel_hash" => md5("Stylight.com.mx"),
					"name" => "Stylight.com.mx",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Stylight.com.mx",
					"type" => "Comparison shopping engine"
				),

			),
			"Micronesia" => array(),
			"Moldova" => array(),
			"Monaco" => array(),
			"Mongolia" => array(),
			"Montenegro" => array(),
			"Morocco" => array(),
			"Mozambiqua" => array(),
			"Myanmar" => array(),
			"Namibia" => array(),
			"Nauru" => array(),
			"Nepal" => array(),
			"Netherlands" => array(
				"Vergelijk.nl" => array(
					"channel_hash" => md5("Vergelijk.nl"),
					"name" => "Vergelijk.nl",
					"fields" => "vergelijknl",
					"taxonomy" => "none",
					"utm_source" => "Vergelijk.nl",
					"type" => "Comparison shopping engine"
				),
				"Kieskeurig.nl" => array(
					"channel_hash" => md5("Kieskeurig.nl"),
					"name" => "Kieskeurig.nl",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Kieskeurig.nl",
					"type" => "Comparison shopping engine"
				),
				"Tweakers.nl" => array(
					"channel_hash" => md5("Tweakers.nl"),
					"name" => "Tweakers.nl",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Tweakers.nl",
					"type" => "Comparison shopping engine"
				),
				"Boetiek.nl" => array(
					"channel_hash" => md5("Boetiek.nl"),
					"name" => "Boetiek.nl",
					"fields" => "boetiek",
					"taxonomy" => "none",
					"utm_source" => "Boetiek.nl",
					"type" => "Comparison shopping engine"
				),
				"Fashionchick.nl" => array(
					"channel_hash" => md5("Fashionchick.nl"),
					"name" => "Fashionchick.nl",
					"fields" => "fashionchick",
					"taxonomy" => "none",
					"utm_source" => "Fashionchick.nl",
					"type" => "Comparison shopping engine"
				),
				"Kleding.nl" => array(
					"channel_hash" => md5("Kleding.nl"),
					"name" => "Kleding.nl",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Kleding.nl",
					"type" => "Comparison shopping engine"
				),
				"Hardware.info" => array(
					"channel_hash" => md5("Hardware.info"),
					"name" => "Hardware.info",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Hardware.info",
					"type" => "Comparison shopping engine"
				),
				"Kelkoo.nl" => array(
					"channel_hash" => md5("Kelkoo.nl"),
					"name" => "Kelkoo.nl",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Kelkoo.nl",
					"type" => "Comparison shopping engine"
				),
				"Ciao-shopping.nl" => array(
					"channel_hash" => md5("Ciao-shopping.nl"),
					"name" => "Cia-shopping.nl",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Ciao-shopping.nl",
					"type" => "Comparison shopping engine"
				),
				"Beslist.nl" => array(
					"channel_hash" => md5("Beslist.nl"),
					"name" => "Beslist.nl",
					"fields" => "beslist",
					"taxonomy" => "none",
					"utm_source" => "Beslist.nl",
					"type" => "Comparison shopping engine"
				),
				"Miinto" => array(
					"channel_hash" => md5("Miinto.nl"),
					"name" => "Miinto.nl",
					"fields" => "miinto_nl",
					"taxonomy" => "none",
					"utm_source" => "Miinto.nl",
					"type" => "Comparison shopping engine"
				),
				"Bol.com" => array(
					"channel_hash" => md5("Bol.com"),
					"name" => "Bol.com",
					"fields" => "bol",
					"taxonomy" => "none",
					"utm_source" => "Bol.com",
					"type" => "Marketplace"
				),
				"Fruugo.nl" => array(
					"channel_hash" => md5("Fruugo.nl"),
					"name" => "Fruugo.nl",
					"fields" => "fruugonl",
					"taxonomy" => "none",
					"utm_source" => "Fruugo.nl",
					"type" => "Marketplace"
				),
				"Ooshopping.nl" => array(
					"channel_hash" => md5("Ooshopping.nl"),
					"name" => "Ooshopping.nl",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Ooshopping.nl",
					"type" => "Marketplace"
				),
				"Adform" => array(
					"channel_hash" => md5("Adform.nl"),
					"name" => "Adform.nl",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Adform.nl",
					"type" => "Advertising"
				),
				"AdRoll" => array(
					"channel_hash" => md5("Adroll.nl"),
					"name" => "AdRoll.nl",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "AdRoll.nl",
					"type" => "Advertising"
				),
				"Smartly.io" => array(
					"channel_hash" => md5("Smartly.io"),
					"name" => "Smartly.io",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Smartly.io",
					"type" => "Advertising"
				),
				"TheNextAd" => array(
					"channel_hash" => md5("TheNextAd"),
					"name" => "TheNextAd",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "TheNextAd",
					"type" => "Advertising"
				),
				"Daisycon huis & tuin" => array(
					"channel_hash" => md5("Daisyconhuisentuin"),
					"name" => "Daisycon huis & tuin",
					"fields" => "daisyconhuisentuin",
					"taxonomy" => "google_shopping",
					"utm_source" => "Daisycon",
					"type" => "Advertising"
				),
			),
			"New Zealand" => array(),
			"Nicaragua" => array(),
			"Niger" => array(),
			"Nigeria" => array(),
			"Norway" => array(
				"Prisjakt" => array(
					"channel_hash" => md5("Prisjakt.no"),
					"name" => "Prisjakt.no",
					"fields" => "google_shopping",
					"taxonomy" => "google_shopping",
					"utm_source" => "Prisjakt.no",
					"type" => "Comparison shopping engine"
				),
			),
			"Oman" => array(),
			"Pakistan" => array(),
			"Palau" => array(),
			"Panama" => array(),
			"Papua New Guinea" => array(),
			"Paraguay" => array(),
			"Peru" => array(),
			"Philippines" => array(),
			"Poland" => array(
				"Adform" => array(
					"channel_hash" => md5("Adform.pl"),
					"name" => "Adform.pl",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Adform.pl",
					"type" => "Advertising"
				),
				"Cenowarka" => array(
					"channel_hash" => md5("Cenowarka.pl"),
					"name" => "Cenowarka.pl",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Cenowarka.pl",
					"type" => "Comparison shopping engine"
				),
				"Miinto" => array(
					"channel_hash" => md5("Miinto.pl"),
					"name" => "Miinto.pl",
					"fields" => "miinto_pl",
					"taxonomy" => "none",
					"utm_source" => "Miinto.pl",
					"type" => "Comparison shopping engine"
				),
				"ShopAlike" => array(
					"channel_hash" => md5("ShopAlike.pl"),
					"name" => "ShopAlike.pl",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "ShopAlike.pl",
					"type" => "Comparison shopping engine"
				),
				"ShopMania" => array(
					"channel_hash" => md5("ShopMania.pl"),
					"name" => "ShopMania.pl",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "ShoMania.pl",
					"type" => "Comparison shopping engine"
				),
				"Skapiec" => array(
					"channel_hash" => md5("Skapiec.pl"),
					"name" => "Skapiec.pl",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Skapiec.pl",
					"type" => "Comparison shopping engine"
				),
			),
			"Portugal" => array(
				"ShopMania" => array(
					"channel_hash" => md5("ShopMania.pt"),
					"name" => "ShopMania.pt",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "ShoMania.pt",
					"type" => "Comparison shopping engine"
				),
				"Kuantokusta" => array(
					"channel_hash" => md5("Kuantokusta.pt"),
					"name" => "Kuantokusta.pt",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Kuantokusta.pt",
					"type" => "Comparison shopping engine"
				),
			),
			"Puerto Rico" => array(),
			"Qatar" => array(),
			"Reunion" => array(),
			"Romania" => array(
				"Okazzi" => array(
					"channel_hash" => md5("Okazzi.ro"),
					"name" => "Okazzi.ro",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Okazzi.ro",
					"type" => "Marketplace"
				),
				"ShopMania" => array(
					"channel_hash" => md5("ShopMania.ro"),
					"name" => "ShopMania.ro",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "ShoMania.ro",
					"type" => "Comparison shopping engine"
				),
				"Compari" => array(
					"channel_hash" => md5("Compari.ro"),
					"name" => "Compari.ro",
					"fields" => "compari_ro",
					"taxonomy" => "none",
					"utm_source" => "Compari.ro",
					"type" => "Comparison shopping engine"
				),
			),
			"Russian Federation" => array(
				"Yandex" => array(
					"channel_hash" => md5("Yandex.com"),
					"name" => "Yandex",
					"fields" => "yandex",
					"taxonomy" => "none",
					"utm_source" => "Yandex.com",
					"type" => "Advertising"
				),
			),
			"Rwanda" => array(),
			"St Kitts & Nevis" => array(),
			"St Lucia" => array(),
			"St Vincent & the Grenadines" => array(),
			"Samoa" => array(),
			"San Marino" => array(),
			"Sao Tome & Principe" => array(),
			"Saudi Arabia" => array(),
			"Senegal" => array(),
			"Serbia" => array(
				"ShopMania" => array(
					"channel_hash" => md5("ShopMania.rs"),
					"name" => "ShopMania.rs",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "ShopMania.rs",
					"type" => "Comparison shopping engine"
				),
			),
			"Seychelles" => array(),
			"Sierra Leone" => array(),
			"Singapore" => array(),
			"Slovakia" => array(
				"ShopMania" => array(
					"channel_hash" => md5("ShopMania.sk"),
					"name" => "ShopMania.sk",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "ShopMania.sk",
					"type" => "Comparison shopping engine"
				),
				"Glami" => array(
					"channel_hash" => md5("Glami.sk"),
					"name" => "Glami.sk",
					"fields" => "glami",
					"taxonomy" => "none",
					"utm_source" => "Glami.sk",
					"type" => "Comparison shopping engine"
				),
				"Heureka.sk" => array(
					"channel_hash" => md5("Heureka.sk"),
					"name" => "Heureka.sk",
					"fields" => "heureka",
					"taxonomy" => "none",
					"utm_source" => "Heureka.sk",
					"type" => "Marketplace"
				),
				"Mall.sk availability" => array(
					"channel_hash" => md5("Mall.sk availability"),
					"name" => "Mall.sk availability",
					"fields" => "mall_availability",
					"taxonomy" => "none",
					"utm_source" => "Mall.sk availability",
					"type" => "Marketplace"
				),
				"Mall.sk main feed" => array(
					"channel_hash" => md5("Mall.sk"),
					"name" => "Mall.sk",
					"fields" => "mall",
					"taxonomy" => "none",
					"utm_source" => "Mall.sk",
					"type" => "Marketplace"
				),
			),
			"Slovenia" => array(),
			"Solomon Islands" => array(),
			"South Africa" => array(
				"ShopMania" => array(
					"channel_hash" => md5("ShopMania.co.za"),
					"name" => "ShopMania.co.za",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "ShopMania.co.za",
					"type" => "Comparison shopping engine"
				),
				"Pricecheck" => array(
					"channel_hash" => md5("Pricecheck.co.za"),
					"name" => "Pricecheck.co.za",
					"fields" => "pricecheck",
					"taxonomy" => "none",
					"utm_source" => "Pricecheck.co.za",
					"type" => "Comparison shopping engine"
				),
			),
			"South Sudan" => array(),
			"Spain" => array(
				"Adform" => array(
					"channel_hash" => md5("Adform"),
					"name" => "Adform",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Adform",
					"type" => "Advertising"
				),
				"Fruugoes" => array(
					"channel_hash" => md5("Fruugo.es"),
					"name" => "Fruugo.es",
					"fields" => "fruugoes",
					"taxonomy" => "none",
					"utm_source" => "Fruugo.es",
					"type" => "Marketplace"
				),
				"Kelkoo" => array(
					"channel_hash" => md5("Kelkoo.es"),
					"name" => "Kelkoo.es",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Kelkoo.es",
					"type" => "Comparison shopping engine"
				),
			),
			"Sri Lanka" => array(),
			"Sudan" => array(),
			"Suriname" => array(),
			"Swaziland" => array(),
			"Sweden" => array(
				"Adform" => array(
					"channel_hash" => md5("Adform"),
					"name" => "Adform",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Adform",
					"type" => "Advertising"
				),
				"Kelkoo" => array(
					"channel_hash" => md5("Kelkoo.se"),
					"name" => "Kelkoo.se",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Kelkoo.se",
					"type" => "Comparison shopping engine"
				),
				"Fyndiq" => array(
					"channel_hash" => md5("Fyndiq.se"),
					"name" => "Fyndiq.se",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Fyndiq.se",
					"type" => "Comparison shopping engine"
				),
				"Pricerunner" => array(
					"channel_hash" => md5("Pricerunner.se"),
					"name" => "Pricerunner.se",
					"fields" => "pricerunner",
					"taxonomy" => "none",
					"utm_source" => "Pricerunner.se",
					"type" => "Comparison shopping engine"
				),
				"Miinto" => array(
					"channel_hash" => md5("Miinto.se"),
					"name" => "Miinto.se",
					"fields" => "miinto_se",
					"taxonomy" => "none",
					"utm_source" => "Miinto.se",
					"type" => "Comparison shopping engine"
				),
				"Prisjakt" => array(
					"channel_hash" => md5("Prisjakt.se"),
					"name" => "Prisjakt.se",
					"fields" => "google_shopping",
					"taxonomy" => "google_shopping",
					"utm_source" => "Prisjakt.se",
					"type" => "Comparison shopping engine"
				),
			),
			"Switzerland" => array(
				"Kauftipp" => array(
					"channel_hash" => md5("Kauftipp.ch"),
					"name" => "Kauftipp.ch",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Kauftipp.ch",
					"type" => "Comparison shopping engine"
				),
				"Ricardo" => array(
					"channel_hash" => md5("Ricardo.ch"),
					"name" => "Ricardo.ch",
					"fields" => "ricardo",
					"taxonomy" => "none",
					"utm_source" => "Ricardo.ch",
					"type" => "Marketplace"
				),
			),
			"Syria" => array(),
			"Taiwan" => array(),
			"Tajikistan" => array(),
			"Tanzania" => array(),
			"Thailand" => array(),
			"Togo" => array(),
			"Tonga" => array(),
			"Trinidad & Tobago" => array(),
			"Tunesia" => array(),
			"Turkey" => array(),
			"Turkmenistan" => array(),
			"Tuvalu" => array(),
			"Uganda" => array(),
			"Ukraine" => array(
				"Yandex" => array(
					"channel_hash" => md5("Yandex.com"),
					"name" => "Yandex",
					"fields" => "yandex",
					"taxonomy" => "none",
					"utm_source" => "Yandex.com",
					"type" => "Advertising"
				),
			),
			"United Arab Emirates" => array(),
			"United Kingdom" => array(
				"Adform" => array(
					"channel_hash" => md5("Adform.com"),
					"name" => "Adform.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Adform.com",
					"type" => "Advertising"
				),
				"AdRoll" => array(
					"channel_hash" => md5("Adroll.com"),
					"name" => "AdRoll.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "AdRoll.com",
					"type" => "Advertising"
				),
				"Connexity" => array(
					"channel_hash" => md5("Connexity.com"),
					"name" => "Connexity.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Connexity.com",
					"type" => "Advertising"
				),
				"Smartly.io" => array(
					"channel_hash" => md5("Smartly.io"),
					"name" => "Smartly.io",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Smartly.io",
					"type" => "Advertising"
				),
				"TheNextAd" => array(
					"channel_hash" => md5("Thenextad.com"),
					"name" => "TheNextAd",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "TheNextAd.com",
					"type" => "Advertising"
				),
				"Fruugouk" => array(
					"channel_hash" => md5("Fruugo.co.uk"),
					"name" => "Fruugo.co.uk",
					"fields" => "fruugouk",
					"taxonomy" => "none",
					"utm_source" => "Fruugo.co.uk",
					"type" => "Marketplace"
				),
				"ManoMano" => array(
					"channel_hash" => md5("ManoMano.co.uk"),
					"name" => "ManoMano.co.uk",
					"fields" => "manomano",
					"taxonomy" => "none",
					"utm_source" => "ManoMano.co.uk",
					"type" => "Marketplace"
				),
				"Choozen" => array(
					"channel_hash" => md5("Choozen.co.uk"),
					"name" => "Choozen.co.uk",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Choozen.co.uk",
					"type" => "Comparison shopping engine"
				),
				"Ciao" => array(
					"channel_hash" => md5("Ciao.co.uk"),
					"name" => "Ciao.co.uk",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Ciao.co.uk",
					"type" => "Comparison shopping engine"
				),
				"Dooyoo" => array(
					"channel_hash" => md5("Dooyoo.co.uk"),
					"name" => "Dooyoo.co.uk",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Dooyoo.co.uk",
					"type" => "Comparison shopping engine"
				),
				"Idealo" => array(
					"channel_hash" => md5("Idealo.co.uk"),
					"name" => "Idealo.co.uk",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Idealo.co.uk",
					"type" => "Comparison shopping engine"
				),
				"Kelkoo" => array(
					"channel_hash" => md5("Kelkoo.co.uk"),
					"name" => "Kelkoo.co.uk",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Kelkoo.co.uk",
					"type" => "Comparison shopping engine"
				),
				"Pricegrabber" => array(
					"channel_hash" => md5("Pricegrabber.co.uk"),
					"name" => "Pricegrabber.co.uk",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Pricegrabber.co.uk",
					"type" => "Comparison shopping engine"
				),
				"Priceminister" => array(
					"channel_hash" => md5("Priceminister.com"),
					"name" => "Priceminister.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Priceminister.com",
					"type" => "Comparison shopping engine"
				),
				"Pricerunner" => array(
					"channel_hash" => md5("Pricerunner.co.uk"),
					"name" => "Pricerunner.co.uk",
					"fields" => "pricerunner",
					"taxonomy" => "none",
					"utm_source" => "Pricerunner.co.uk",
					"type" => "Comparison shopping engine"
				),
				"Pricespy" => array(
					"channel_hash" => md5("Pricespy.co.uk"),
					"name" => "Pricespy.co.uk",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Pricespy.co.uk",
					"type" => "Comparison shopping engine"
				),
				"Rakuten" => array(
					"channel_hash" => md5("Rakuten.com"),
					"name" => "Rakuten.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Rakuten.com",
					"type" => "Comparison shopping engine"
				),
				"ShopMania" => array(
					"channel_hash" => md5("ShopMania.co.uk"),
					"name" => "ShapMania.co.uk",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "ShopMania.co.uk",
					"type" => "Comparison shopping engine"
				),
				"Shopping.com" => array(
					"channel_hash" => md5("Shopping.com"),
					"name" => "Shopping.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Shopping.com",
					"type" => "Comparison shopping engine"
				),
				"Shopzilla" => array(
					"channel_hash" => md5("Shopzilla.co.uk"),
					"name" => "Shopzilla.co.uk",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Shopzilla.co.uk",
					"type" => "Comparison shopping engine"
				),
				"Skinflint" => array(
					"channel_hash" => md5("Skinflint.co.uk"),
					"name" => "Skinflint.co.uk",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Skinflint.co.uk",
					"type" => "Comparison shopping engine"
				),
				"Stylight" => array(
					"channel_hash" => md5("Stylight.co.uk"),
					"name" => "Stylight.co.uk",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Stylight.co.uk",
					"type" => "Comparison shopping engine"
				),
				"Twenga" => array(
					"channel_hash" => md5("Twenga.co.uk"),
					"name" => "Twenga.co.uk",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Twenga.co.uk",
					"type" => "Comparison shopping engine"
				),
				"Wish.com" => array(
					"channel_hash" => md5("Wish.com"),
					"name" => "Wish.com",
					"fields" => "wishcom",
					"taxonomy" => "none",
					"utm_source" => "Wish.com",
					"type" => "Marketplace"
				),
			),
			"United States" => array(
				"Adform" => array(
					"channel_hash" => md5("Adform.com"),
					"name" => "Adform.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Adform.com",
					"type" => "Advertising"
				),
				"AdRoll" => array(
					"channel_hash" => md5("Adroll.com"),
					"name" => "Adroll.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Adroll.com",
					"type" => "Advertising"
				),
				"Connexity" => array(
					"channel_hash" => md5("Connexity.com"),
					"name" => "Connexity.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Connexity.com",
					"type" => "Advertising"
				),
				"ShareASale" => array(
					"channel_hash" => md5("ShareASale"),
					"name" => "ShareASale",
					"fields" => "shareasale",
					"taxonomy" => "none",
					"utm_source" => "ShareASale",
					"type" => "Advertising"
				),
				"Smartly.io" => array(
					"channel_hash" => md5("Smartly.io"),
					"name" => "Smartly.io",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Smartly.io",
					"type" => "Advertising"
				),
				"TheNextAd" => array(
					"channel_hash" => md5("TheNextAd"),
					"name" => "TheNextAd",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "TheNextAd",
					"type" => "Advertising"
				),
				"Fruugo" => array(
					"channel_hash" => md5("Fruugo.us"),
					"name" => "Fruugo.us",
					"fields" => "fruugous",
					"taxonomy" => "none",
					"utm_source" => "Fruugo.us",
					"type" => "Marketplace"
				),
				"Polyvore" => array(
					"channel_hash" => md5("Polyvore"),
					"name" => "Polyvore.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Polyvore.com",
					"type" => "Comparison shopping engine"
				),
				"Pricegrabber" => array(
					"channel_hash" => md5("Pricegrabber"),
					"name" => "Pricegrabber.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Pricegrabber.com",
					"type" => "Comparison shopping engine"
				),
				"ShopMania" => array(
					"channel_hash" => md5("ShopMania"),
					"name" => "ShopMania.com",
					"fields" => "shopmania_ro",
					"taxonomy" => "none",
					"utm_source" => "ShopMania.com",
					"type" => "Comparison shopping engine"
				),
				"Shopping" => array(
					"channel_hash" => md5("Shopping.com"),
					"name" => "Shopping.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Shopping.com",
					"type" => "Comparison shopping engine"
				),
				"Shopzilla" => array(
					"channel_hash" => md5("Shopzilla.com"),
					"name" => "Shopzilla.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Shopzilla.com",
					"type" => "Comparison shopping engine"
				),
				"Stylight" => array(
					"channel_hash" => md5("Stylight.com"),
					"name" => "Stylight.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Stylight.com",
					"type" => "Comparison shopping engine"
				),
				"Twenga" => array(
					"channel_hash" => md5("Twenga.com"),
					"name" => "Twenga.com",
					"fields" => "customfeed",
					"taxonomy" => "none",
					"utm_source" => "Twenga.com",
					"type" => "Comparison shopping engine"
				),
				"Yandex" => array(
					"channel_hash" => md5("Yandex.com"),
					"name" => "Yandex",
					"fields" => "yandex",
					"taxonomy" => "none",
					"utm_source" => "Yandex.com",
					"type" => "Advertising"
				),
				"Wish.com" => array(
					"channel_hash" => md5("Wish.com"),
					"name" => "Wish.com",
					"fields" => "wishcom",
					"taxonomy" => "none",
					"utm_source" => "Wish.com",
					"type" => "Marketplace"
				),
			),
			"Uraguay" => array(),
			"Uzbekistan" => array(),
			"Vanuatu" => array(),
			"Vatican City" => array(),
			"Venezuela" => array(),
			"Vietnam" => array(),
			"Yemen" => array(),
			"Zambia" => array(),
			"Zimbabwe" => array(),
		);
		update_option('convpfm_channels', $convpfm_channels, 'no');

		/**
		 * Function for setting a cron job for regular creation of the feed
		 * Will create a new event when an old one exists, which will be deleted first
		 */
		if (!wp_next_scheduled('convpfm_cron_hook')) {
			wp_schedule_event(time(), 'hourly', 'convpfm_cron_hook');
		} 
		// else {
		// 	wp_schedule_event(time(), 'hourly', 'convpfm_cron_hook');
		// }

		/**
		 * We check only once if this is a paid version of the plugin
		 * De-register the jQuery code after 30 secvonds
		 */
		if (!wp_next_scheduled('convpfm_deregister_hook')) {
			wp_schedule_single_event(time() + 30, 'convpfm_deregister_hook');
		}

	
		$extra_attributes = array(
			"custom_attributes__convpfm_mpn" 	=> "convpfm mpn",
			"custom_attributes__convpfm_gtin" 	=> "convpfm gtin",
			"custom_attributes__convpfm_ean" 	=> "convpfm ean",
			"custom_attributes__convpfm_brand" 	=> "convpfm brand"
		);
		if (!get_option('convpfm_extra_attributes')) {
			update_option('convpfm_extra_attributes', $extra_attributes);
		}

		/**
		 * Disable structured data JSON=LD changes by default
		 * User needs to enable this setting in the plugin section
		 */
		update_option('convpfm_structured_data_fix', 'no');


		/**
		 * Delete the debug.log file from the uploads directory if it exists.
		 */
		$upload_dir = wp_upload_dir();
		$debug_file = $upload_dir['basedir'] . "/conversios-product-feed/logs/debug.log";
		if (file_exists($debug_file)) {
			unlink($debug_file);
		}
	}
}
