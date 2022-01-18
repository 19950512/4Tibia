<?php

namespace Core;

use Core\De as de;
use Core\Router;


use Core\HTTP2\HTTP2\{
	Pusher,
	PusherInterface,
	PusherException
};

class View {

	/* Metas por Default */
	public $header = array(
		array('tag' => 'meta', 'name' => 'charset', 'content' => 'UTF-8'),
		array('tag' => 'meta', 'name' => 'description', 'content' => SITE_NOME),
		array('tag' => 'meta', 'name' => 'author', 'content' => AUTHOR ),
		array('tag' => 'meta', 'name' => 'robots', 'content' => 'noindex, nofollow',/* 'other' => 'sync="sync"'*/),
		array('tag' => 'meta', 'name' => 'viewport', 'content' => 'width=device-width'),

		array('tag' => 'link', 'other' => 'rel="canonical" href="'.SITE_PROTOCOLO.SITE_DOMINIO.'"'),
		array('tag' => 'meta', 'other' => 'http-equiv="X-UA-Compatible" content="IE=edge"'),
		
		array('tag' => 'meta', 'other' => 'property="og:site_name" content="'.SITE_NOME.'"'),
		array('tag' => 'meta', 'other' => 'property="og:image" content="'.SITE_IMAGEM.'"'),
		array('tag' => 'meta', 'other' => 'property="twitter:image" content="'.SITE_IMAGEM.'"'),
		array('tag' => 'meta', 'name' => 'twitter:card', 'content' => SITE_IMAGEM),
		//array('tag' => 'meta', 'name' => 'twitter:site', 'content' => TWITTER_USERNAME),
	);

	public $title;
	public $description;

	private $Router;

	private $authorization;

	public function __construct($authorization = null){

		$this->Router = new Router();

		$this->title = SITE_NOME; 
		$this->description = SITE_NOME; 

		$this->authorization = $authorization;

		$seo = [];
		$arquivo_seo = DIR.'/seo.json';
		if(is_file($arquivo_seo)){
			$seo = json_decode(file_get_contents($arquivo_seo), true);
		}

		$controlador = Core::strtolower($this->Router->controller);

		if(isset($seo[$controlador])){
			$this->title = $seo[$controlador]['index']['titl'] ?? $this->title;
			$this->description = $seo[$controlador]['index']['desc'] ?? $this->description;
			$this->header[] = ['tag' => 'meta', 'other' => 'property="og:title" content="'.$seo[$controlador][$this->Router->action]['titl'].'"'];
			$this->header[] = ['tag' => 'meta', 'other' => 'property="og:description" content="'.$seo[$controlador][$this->Router->action]['desc'].'"'];
			$this->header[] = ['tag' => 'meta', 'other' => 'property="og:url" content="'.$this->Router->url.'"'];
			$this->header[] = ['tag' => 'meta', 'other' => 'property="twitter:title" content="'.$seo[$controlador][$this->Router->action]['titl'].'"'];
			$this->header[] = ['tag' => 'meta', 'other' => 'property="twitter:description" content="'.$seo[$controlador][$this->Router->action]['desc'].'"'];
			$this->header[] = ['tag' => 'meta', 'other' => 'property="twitter:url" content="'.$this->Router->url.'"'];
			$this->header[] = ['tag' => 'meta', 'name' => 'twitter:image:alt', 'content' => $seo[$controlador][$this->Router->action]['desc']];
		}

		/* if(!DEV){

			$pusher = Pusher::getInstance();
			$pusher->link(DOMINIO_CDN.'/assets/css/site.min.css');
			
			// set css and image and src
			$pusher->link(DOMINIO_CDN.'/assets/css/site.min.css')
				->link(DOMINIO_CDN.'/assets/css/icones.min.css')
				->src(DOMINIO_CDN.'/assets/js/dev.min.js')
				->src(DOMINIO_CDN.'/assets/js/site.min.js')
				->set(Pusher::IMG, DOMINIO_CDN.'/assets/img/logo/logo.png')
				->set(Pusher::IMG, DOMINIO_CDN.'/assets/favicon.ico');
				
			// push header
			$pusher->push();
		} */
	}

	private function layout($layout = 'Layout'){

		$pathView = DIR . DS . SUBPASTA . DS . 'Controllers' . DS . 'Layouts' . DS . $layout . EXTENSAO_VIEW;

		$layoutView = file_exists($pathView) ? file_get_contents($pathView) : 'não existe';

		$time = !DEV ? '?cache='.time() : '';

		$mustache = array(
			'{{site_nome}}' => SITE_NOME,
			'{{color_primary}}' => '#0E1428',
			'{{metas}}' => $this->_getHead(),
			'{{titulo_page}}' => $this->title,
			'{{time}}' => $time,
			'{{ano}}' => date('Y'),
			'{DEV}' => DEV
		);


		$layout = str_replace(array_keys($mustache), array_values($mustache), $layoutView);
		return self::comprimeHTML($layout);
	}

	public function pushHistory($mustache = [], $view = ''){
		return str_replace(array_keys($mustache), array_values($mustache), $view);
	}

	public function mustache($mustache = [], $view = '', $layout = 'Layout'){
		$view = str_replace(array_keys($mustache), array_values($mustache), $view);

		return str_replace('{{view}}', $view, $this->layout($layout));;
	}

	public function getView($controlador = 'Index', $view = 'Index', $comprimir = true){ 
		return self::get($controlador, $view, $comprimir);
	}

	public static function get($controlador = 'Index', $view = 'Index', $comprimir = true){ 
		$pathView = DIR . DS . SUBPASTA . DS . 'Controllers' . DS . $controlador . DS . $view . EXTENSAO_VIEW;

		$contentHTMLView = file_exists($pathView) ? file_get_contents($pathView) : 'a';

		$contentHTMLView = Core::mustache([
			'{{voltar}}' => "(window.history.length > 2) ? window.history.back() : ''",
		], $contentHTMLView);

		if($comprimir == true){
			return self::comprimeHTML($contentHTMLView);
		}

		return $contentHTMLView;
	}

	public static function getLayouts($layout = 'Layout'){
		$pathView = DIR . DS . SUBPASTA . DS . 'VIEW' . DS . 'LAYOUT' . DS . $layout . EXTENSAO_VIEW;
		return file_exists($pathView) ? file_get_contents($pathView) : '';
	}

	public function getLayout($layout = 'Layout'){
		return self::getLayouts($layout);
	}

	private function _getHead(){
		$headers = '';
		if(is_array($this->header)){
	
			foreach($this->header as $data) {
		
				$other = '';
				if(isset($data['other']) and !empty($data['other'])){
					$other = $data['other'];
				}

				$name = '';
				if(isset($data['name']) and !empty($data['name'])){
					$name = 'name="'.$data['name'].'"';
				}

				$content = '';
				if(isset($data['content']) and !empty($data['content'])){
					$content = 'content="'.$data['content'].'"';
				}

				$headers .= '<'.$data['tag'].' '.$name.' '.$content.' '.$other.' />';
			}
		}

		return $headers;
	}
	public static function comprimeHTML($html = ''){

		if(DEV === true){
			return $html;
		}

		$html = preg_replace(array("/\/\*(.*?)\*\//", "/<!--(.*?)-->/", "/\t+/"), ' ', $html);

		$mustache = array(
			"\t"		=> '',
			""			=> ' ',
			PHP_EOL.''		=> '',
			'> <'		=> '><',
			'  '		=> '',
			'   '		=> '',
			'	'		=> '',
			'	 '		=> '',
			'> <'		=> '><',
			'NAOENTER'	=> PHP_EOL,
			'
'						=> ''
		);

		return str_replace(array_keys($mustache), array_values($mustache), $html);
	}
	/**
	 * @return array
	 */
	public function getHeader(): array
	{
		return $this->header;
	}

	/**
	 * @param array $header
	 */
	public function setHeader($array)
	{
		$temp = [];
		foreach ($array as $meta){
		
			$flag = false;
			foreach($this->header as $key => $arr){
			
				if(isset($arr['name']) and $arr['name'] === $meta['name']){
					$this->header[$key]['content'] = $meta['content'];
					$flag = true;
				}
			
			}
			if($flag == false){
				$temp[$key] = $meta;
				$temp[$key]['other'] = ($meta['other'] ?? '');
			}
		}
	
		$this->header = array_merge($this->header, $temp);
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle(string $title): void
	{
		$this->title = $title;
	}
}