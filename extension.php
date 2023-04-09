<?php

// doc: https://freshrss.github.io/FreshRSS/en/developers/03_Backend/05_Extensions.html
class NewsAssistantExtension extends Minz_Extension
{
	public function init()
	{
		Minz_View::appendStyle($this->getFileUrl('style.css', 'css'));
		Minz_View::appendScript($this->getFileUrl('script.js', 'js'));

		$this->registerTranslates();

		$this->registerController('assistant');
		$this->registerViews();
		$this->registerHook('nav_menu', array($this, 'addSummaryBtn'));
	}

	public function addSummaryBtn()
	{
		$cat_id = $this->getCategoryId();
		$state = $this->getState();
		$url = Minz_Url::display(array('c' => 'assistant', 'a' => 'summary', 'params' => array('cat_id' => $cat_id, 'state' => $state)));
		$icon_url = $this->getFileUrl('filter.svg', 'svg');

		return '<a id="summary" class="btn" href="' . $url . '" title="Get the summary news">
					<img class="icon" src="' . $icon_url . '" loading="lazy" alt="️☀️">
				</a>';
	}

	public function handleConfigureAction()
	{
		$this->registerTranslates();

		if (Minz_Request::isPost()) {
			FreshRSS_Context::$system_conf->openai_api_key = Minz_Request::param('openai_api_key', '');
			FreshRSS_Context::$system_conf->max_tokens = filter_var(Minz_Request::param('max_tokens', 7), FILTER_VALIDATE_INT);
			FreshRSS_Context::$system_conf->temperature = filter_var(Minz_Request::param('temperature', 1), FILTER_VALIDATE_FLOAT);;
			FreshRSS_Context::$system_conf->limit = filter_var(Minz_Request::param('limit', 30), FILTER_VALIDATE_FLOAT);;
			FreshRSS_Context::$system_conf->model = Minz_Request::param('model', 'text-davinci-003');
			FreshRSS_Context::$system_conf->to_translate = filter_var(Minz_Request::param('to_translate', true), FILTER_VALIDATE_BOOLEAN);
			FreshRSS_Context::$system_conf->save();
		}
	}

	private function getCategoryId(): int {
		if (!FreshRSS_Context::isCategory()) return 0;

		return FreshRSS_Context::$current_get['category'];
	}

	private function getState(): int {
		if (FreshRSS_Context::$state == 0) return FreshRSS_Entry::STATE_NOT_READ;

		return FreshRSS_Context::$state;
	}
}