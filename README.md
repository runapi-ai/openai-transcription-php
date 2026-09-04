# OpenAI Transcription PHP SDK for RunAPI

[![Packagist](https://img.shields.io/packagist/v/runapi-ai/openai-transcription)](https://packagist.org/packages/runapi-ai/openai-transcription)
[![License](https://img.shields.io/github/license/runapi-ai/openai-transcription-php)](https://github.com/runapi-ai/openai-transcription-php/blob/main/LICENSE)

Use the OpenAI Transcription PHP SDK to transcribe audio through RunAPI with a
synchronous Composer client. JSON formats return associative arrays; text and
subtitle formats return their exact strings.

## Install

```bash
composer require runapi-ai/openai-transcription
```

## Quick start

```php
<?php

require __DIR__ . "/vendor/autoload.php";

use RunApi\OpenaiTranscription\OpenaiTranscriptionClient;

$client = new OpenaiTranscriptionClient(); // reads RUNAPI_API_KEY
$result = $client->speechToText->run('audio.mp3');

echo is_string($result) ? $result : $result['text'] . PHP_EOL;
```

Keep `RUNAPI_API_KEY` in the environment or your secret manager.

The result is an array for JSON response formats and a string for text or
subtitle formats, whether the request completes directly or through an accepted
Task. Use `subscribe()` to observe Task updates; terminal text, SRT, and VTT
results remain strings.

## Links

- Model page: https://runapi.ai/models/openai-transcription
- SDK docs: https://runapi.ai/docs/resources/sdks
- Product docs: https://runapi.ai/docs/api/openai-transcription/speech-to-text
- Pricing and formats: https://runapi.ai/models/openai-transcription/whisper-1
- Full catalog: https://runapi.ai/models
- GitHub repository: https://github.com/runapi-ai/openai-transcription-php
- Multi-language SDK repository: https://github.com/runapi-ai/openai-transcription-sdk

## License

Licensed under the Apache License, Version 2.0.
