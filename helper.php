<?php

const OPENAI_API_COMPLETIONS_URL = 'https://api.openai.com/v1/completions';
function endsWithPunctuation($str)
{
	$pattern = '/\p{P}$/u'; // regex pattern for ending with punctuation marks
	return preg_match($pattern, $str);
}

function _dealResponse($openai_response)
{
	return $openai_response->choices[0]->text;
}

function streamOpenAiApi(object $config, string $prompt, callable $task_callback, callable $finish_callback)
{
	$post_fields = json_encode(array(
		"model" => $config->model,
		"prompt" => $prompt,
		"max_tokens" => $config->max_tokens,
		"temperature" => $config->temperature,
		"stream" => true,
	));

	$curl_info = [
		CURLOPT_URL            => OPENAI_API_COMPLETIONS_URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING       => 'utf-8',
		CURLOPT_MAXREDIRS      => 10,
		CURLOPT_TIMEOUT        => 30,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST  => 'POST',
		CURLOPT_POSTFIELDS     => $post_fields,
		CURLOPT_HTTPHEADER     => [
			"Content-Type: application/json",
			"Authorization: Bearer $config->openai_api_key",
		],
	];

	$curl_info[CURLOPT_WRITEFUNCTION] = function($curl_info, $data) use ($task_callback, $finish_callback) {
		$msg = trim(substr($data, 5));
		Minz_Log::debug('Receive msg:' . $msg);

		if ($msg == "[DONE]") {
			$finish_callback();
		} else {
			$task_callback(_dealResponse(json_decode($msg)));
		}

		return strlen($data);
	};

	$curl = curl_init();

	curl_setopt_array($curl, $curl_info);
	$response = curl_exec($curl);

	curl_close($curl);
	return $response;
}
