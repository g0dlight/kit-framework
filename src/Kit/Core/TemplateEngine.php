<?php

namespace Kit\Core;

class TemplateEngine{
	private $buildStatus = false;
	private $content;
	private $removedFiles = [];
	private $substance = [];

	public function __construct($file){
		$this->content = $this->getFileContent($file);
	}

	public function build(){
		$this->buildStatus = true;

		$reg = '/<span template-engine=".*"><\/span>/';

		$this->content = preg_replace_callback($reg, [$this, 'replace'], $this->content);

		$reg = '/<span template-engine=".*" template-engine-type="file"><\/span>/';

		$this->content = preg_replace_callback($reg, [$this, 'replaceFromFile'], $this->content);

		return $this->content;
	}

	public function addSubstance($id, $value){
		$this->substance[$id] = $value;
	}

	public function remove($file){
		$this->removedFiles[$file] = true;
	}

	private function checkRemovedFiles($file){
		return $this->removedFiles[$file] ?? false;
	}

	private function replace($matches){
		$matches = str_replace(['<span template-engine="', '"></span>'], '', $matches);

		return $this->substance[$matches[0]] ?? '';
	}

	private function replaceFromFile($matches){
		$matches = str_replace(['<span template-engine="', '" template-engine-type="file"></span>'], '', $matches);

		$content = '';

		if(!$this->checkRemovedFiles($matches[0])){
			$content = $this->getFileContent($matches[0]);
		}

		return $content;
	}

	private function getFileContent($file){
		ob_start();

		include BASE_PATH . 'App/Views/' . $file;

		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

	public function __destruct(){
		if(!$this->buildStatus){
			echo $this->build();
		}
	}
}
