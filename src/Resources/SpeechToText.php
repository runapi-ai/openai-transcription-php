<?php

declare(strict_types=1);

namespace RunApi\OpenaiTranscription\Resources;

use RunApi\Core\Contract\ContractValidator;
use RunApi\Core\Http\HttpClient;
use RunApi\Core\Http\MultipartBody;
use RunApi\Core\Http\MultipartFile;
use RunApi\Core\RequestOptions;

/** Synchronous audio transcription operations. */
final readonly class SpeechToText
{
    public function __construct(private HttpClient $http, private ContractValidator $validator = new ContractValidator())
    {
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>|string
     */
    public function run(string $file, array $params = [], ?RequestOptions $options = null): array|string
    {
        $params['file'] = $file;
        $params['model'] = isset($params['model']) && is_string($params['model']) && trim($params['model']) !== '' ? $params['model'] : 'whisper-1';
        $this->validator->validate('openai-transcription/speech-to-text', $params['model'], $params);
        $fields = array_diff_key($params, array_flip(['file', 'languages', 'keywords', 'timestamp_granularities']));
        foreach (['languages', 'keywords', 'timestamp_granularities'] as $name) {
            if (isset($params[$name])) {
                $fields[$name . '[]'] = $params[$name];
            }
        }
        $body = new MultipartBody(
            fields: $fields,
            files: ['file' => MultipartFile::fromPath($file, basename($file), $this->contentType($file))],
        );
        $request = ['body' => $body];
        if ($options !== null) {
            $request['options'] = $options;
        }

        return $this->http->requestRaw('post', '/v1/audio/transcriptions', $request)->value();
    }

    private function contentType(string $file): string
    {
        return match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            'mp3', 'mpeg', 'mpga' => 'audio/mpeg', 'm4a', 'mp4' => 'audio/mp4',
            'wav' => 'audio/wav', 'webm' => 'audio/webm', 'ogg' => 'audio/ogg', 'flac' => 'audio/flac',
            default => 'application/octet-stream',
        };
    }
}
