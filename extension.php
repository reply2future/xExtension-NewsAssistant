<?php

// doc: https://freshrss.github.io/FreshRSS/en/developers/03_Backend/05_Extensions.html
class NewsAssistantExtension extends Minz_Extension
{
	public function init()
	{
		parent::init();

		$this->registerController('assistant');
		$this->registerViews();
		$this->registerTranslates();
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
			FreshRSS_Context::$user_conf->openai_base_url = rtrim(Minz_Request::param('openai_base_url', 'https://api.openai.com'), '/');
			FreshRSS_Context::$user_conf->openai_api_key = Minz_Request::param('openai_api_key', '');
			FreshRSS_Context::$user_conf->provider = Minz_Request::param('provider', 'openai');
			FreshRSS_Context::$user_conf->max_tokens = filter_var(Minz_Request::param('max_tokens', 7), FILTER_VALIDATE_INT);
			FreshRSS_Context::$user_conf->temperature = filter_var(Minz_Request::param('temperature', 1), FILTER_VALIDATE_FLOAT);;
			FreshRSS_Context::$user_conf->limit = filter_var(Minz_Request::param('limit', 30), FILTER_VALIDATE_FLOAT);;
			FreshRSS_Context::$user_conf->model = Minz_Request::param('model', 'gpt-3.5-turbo-16k');
			FreshRSS_Context::$user_conf->prompt = Minz_Request::param('prompt', 'Summarize this as you are news editor, you should merge the similar topic.');
			FreshRSS_Context::$user_conf->fields = array_values(array_filter(array_map('trim', explode(',', Minz_Request::param('fields', 'title,content'))), 'strlen'));
			FreshRSS_Context::$user_conf->api_timeout = filter_var(Minz_Request::param('api_timeout', 60), FILTER_VALIDATE_INT);
			FreshRSS_Context::$user_conf->save();
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
