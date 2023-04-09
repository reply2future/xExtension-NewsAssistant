<?php

require(THIRDPARTY_EXTENSIONS_PATH . '/xExtension-NewsAssistant/helper.php');

class FreshExtension_assistant_Controller extends Minz_ActionController {

	const DEFAULT_MODEL = 'text-davinci-003';
	const DEFAULT_TEMPERATURE = 0.2;
	const DEFAULT_MAX_TOKENS = 4096;
	const NEWS_CATEGORY_TYPE = 'c';

	private $config = array();
	private $entryDAO = null;

	public function __construct() {
		parent::__construct();

		$system_conf = Minz_Configuration::get('system');
		$this->config = (object) array(
			'limit' => $system_conf->limit,

			'model' => $system_conf->model,
			'temperature' => $system_conf->temperature,
			'max_tokens' => $system_conf->max_tokens,
			'openai_api_key' => $system_conf->openai_api_key,
			'to_translate' => $system_conf->to_translate,
		);
		$this->entryDAO = FreshRSS_Factory::createEntryDao();
	}

	public function summaryAction() {
		$cat_id = filter_var(Minz_Request::param('cat_id', 0), FILTER_VALIDATE_INT);
		$state = filter_var(Minz_Request::param('state', FreshRSS_Entry::STATE_NOT_READ), FILTER_VALIDATE_INT);

		$news = $this->getNews($cat_id, $state, $this->config->limit);

		$this->view->summary_content = self::summaryNews($this->config, $news);
		$this->view->summary_ids = array_map(function ($newsItem) { return $newsItem['id']; }, $news);
	}

	public static function buildNewsContent(array $news)
	{
		$pickTitleFn = function ($newsItem) {
			$title = $newsItem['title'];

			if (endsWithPunctuation($title)) return $title;
			return $title . '.';
		};

		return implode('', array_map($pickTitleFn, $news));
	}

	public static function endsWithPunctuation($str) {
		$pattern = '/\p{P}$/u'; // regex pattern for ending with punctuation marks
		return preg_match($pattern, $str);
	}

	public static function addSummaryPrompt(string $content) {
		return 'Summarize this as you are news editor, you should merge the similar topic.\n\n' . $content . '\n\n';
	}

	public function summaryNews(object $config, array $news): string
	{
		if (count($news) === 0) return '';

		$content = self::buildNewsContent($news);
		$content = self::addSummaryPrompt($content);

		$summaryNews = requestOpenAiApi($config, $content);

		if (is_null($summaryNews)) return '';

		if ($config->to_translate === true) {
			$summaryNews = self::translateNews($config, $summaryNews);
		}

		return $summaryNews;
	}

	public static function addTranslatePrompt(string $content) {
		return 'Translate the content to traditional Chinese.\n\n' . $content . '\n\n';
	}

	public static function translateNews(object $config, string $content) {
		$content = self::addTranslatePrompt($content); 
		return requestOpenAiApi($config, $content);
	}

	private function getNews(int $cat_id = 0, int $state = FreshRSS_Entry::STATE_NOT_READ, int $limit = 30)
	{
		$generator = $this->entryDAO->listWhere(self::NEWS_CATEGORY_TYPE, $cat_id, $state, 'DESC', $limit, '', null);
		$result = array();
		foreach ($generator as $entry) {
			$result[] = $entry->toArray();
		}

		return $result;
	}
}
