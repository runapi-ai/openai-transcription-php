<?php

declare(strict_types=1);

namespace RunApi\OpenaiTranscription;

use RunApi\Core\BaseClient;
use RunApi\Core\ClientOptions;
use RunApi\OpenaiTranscription\Resources\SpeechToText;

/** OpenAI-compatible audio transcription client. */
final class OpenaiTranscriptionClient extends BaseClient
{
    public readonly SpeechToText $speechToText;

    public function __construct(ClientOptions $options = new ClientOptions())
    {
        parent::__construct($options);
        $this->speechToText = new SpeechToText($this->http);
    }
}
