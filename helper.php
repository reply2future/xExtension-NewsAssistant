<?php

const OPENAI_API_COMPLETIONS_URL = 'https://api.openai.com/v1/completions';
function endsWithPunctuation($str)
{
	$pattern = '/\p{P}$/u'; // regex pattern for ending with punctuation marks
	return preg_match($pattern, $str);
}

function requestOpenAiApi(object $config, string $prompt)
{
	$data = array(
		"model" => $config->model,
		"prompt" => $prompt,
		"max_tokens" => $config->max_tokens,
		"temperature" => $config->temperature
	);

	$content = json_encode($data);
	Minz_Log::debug('Request params:' . $content);

	$options = array(
		'http' => array(
			'header' => "Content-type: application/json\r\n" .
				"Authorization: Bearer " . $config->openai_api_key . "\r\n",
			'method' => 'POST',
			'content' => $content,
		)
	);

	$context = stream_context_create($options);
	$result = file_get_contents(OPENAI_API_COMPLETIONS_URL, false, $context);

	if ($result === false) {
		$error = error_get_last();
		Minz_Log::error('Request failed:' . $error['message']);
		return;
	}

	Minz_Log::debug('Response result is:' . $result);
	$response = json_decode($result);

	return _dealResponse($response);
}

function _dealResponse($openai_response)
{
	return $openai_response->choices[0]->text;
}
