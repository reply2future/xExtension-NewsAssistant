<?php

return array(
	'field' => array(
		'openai_base_url' => '请求基地址',
		'openai_api_key' => 'API Key',
		'provider' => 'AI供应商',
		'model' => '模型',
		'max_tokens' => 'Max Tokens',
		'temperature' => 'Temperature',
		'prompt' => 'Prompt',
		'limit' => '一次性读取文章数量',
		'fields' => '发送给AI的字段',
		'api_timeout' => 'API 请求超时(秒)',
		'configure_tips' => '你可以为 `AI供应商` 和 `模型` 输入任何有效值，即使它们不在下拉列表中。但请确保它们受你的AI供应商或 [Portkey-AI/gateway](https://portkey.ai/docs/integrations/llms) 支持。',
	),
);
