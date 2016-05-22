<?php

namespace Kit\Core;

class TemplateEngine{
	private $buildStatus = false;
	private $content;
	private $removedFiles = [];

	public function __construct($file){
		$this->content = $this->getFileContent($file);
	}

	public function build(){
		$this->buildStatus = true;

		$reg = '/<span template-engine=".*"><\/span>/';

		return preg_replace_callback($reg, [$this, 'replace'], $this->content);
	}

	public function remove($file){
		$this->removedFiles[$file] = true;
	}

	private function checkRemovedFiles($file){
		return $this->removedFiles[$file] ?? false;
	}

	private function replace($matches){
		$matches = str_replace(['<span template-engine="', '"></span>'], '', $matches);

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
