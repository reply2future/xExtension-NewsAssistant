<?php

return array(
	'field' => array(
		'openai_base_url' => '請求基地址',
		'openai_api_key' => 'API Key',
		'provider' => 'AI提供者',
		'model' => '模型',
		'max_tokens' => 'Max Tokens',
		'temperature' => 'Temperature',
		'prompt' => 'Prompt',
		'limit' => '一次性讀取文章數量',
		'field' => '發送給AI的字段',
		'api_timeout' => 'API 請求超時(秒)',
		'configure_tips' => '你可以為 `AI提供者` 和 `模型` 輸入任何有效值，即使它們不在下拉列表中。但請確保它們受你的AI提供者或 [Portkey-AI/gateway](https://portkey.ai/docs/integrations/llms) 支持。',
	),
);
