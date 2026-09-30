<?php

declare(strict_types=1);

namespace RunApi\OpenaiTranscription\Resources;

use Generator;
use RunApi\Core\Errors\TaskFailedException;
use RunApi\Core\Http\HttpClient;
use RunApi\Core\Http\MultipartBody;
use RunApi\Core\Http\MultipartFile;
use RunApi\Core\RequestOptions;
use RunApi\Core\Tasks\HybridTask;
use RunApi\Core\Tasks\HybridTaskSubscriber;
use RunApi\Core\Tasks\HybridTaskUpdate;

/** Synchronous audio transcription operations. */
final readonly class SpeechToText
{
    public function __construct(private HttpClient $http)
    {
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>|string
     */
    public function run(string $file, array $params = [], ?RequestOptions $options = null): array|string
    {
        foreach ($this->subscribe($file, $params, $options) as $update) {
            if ($update->status === 'completed') {
                return $update->response->value();
            }
            if ($update->status === 'failed') {
                throw new TaskFailedException($update->error ?? 'Task failed', details: $update->response->value());
            }
        }

        throw new \RunApi\Core\Errors\ValidationException('Hybrid Task did not return a terminal response');
    }

    /**
     * @param array<string, mixed> $params
     * @return Generator<int, HybridTaskUpdate>
     */
    public function subscribe(string $file, array $params = [], ?RequestOptions $options = null): Generator
    {
        $response = $this->submit($file, $params, $options);
        if ($response->statusCode !== 202) {
            yield new HybridTaskUpdate(new HybridTask('', '', null), 'completed', $response);

            return;
        }

        yield from (new HybridTaskSubscriber($this->http))->subscribe(HybridTask::fromAcceptance($response), $options);
    }

    /** @param array<string, mixed> $params */
    private function submit(string $file, array $params, ?RequestOptions $options): \RunApi\Core\Http\RawResponse
    {
        $fields = array_diff_key($params, array_flip(['file', 'languages', 'keywords', 'timestamp_granularities']));
        $fields['model'] ??= 'whisper-1';
        foreach (['languages', 'keywords', 'timestamp_granularities'] as $name) {
            if (isset($params[$name])) {
                $fields[$name . '[]'] = $params[$name];
            }
        }
        $body = new MultipartBody(
            fields: $fields,
            files: ['file' => MultipartFile::fromPath($file, basename($file), $this->contentType($file))],
        );
        $request = ['body' => $body, 'accepted_statuses' => [202]];
        if (!($options?->hasHeader('Idempotency-Key') ?? false)) {
            $request['headers'] = ['Idempotency-Key' => bin2hex(random_bytes(16))];
        }
        if ($options !== null) {
            $request['options'] = $options;
        }

        return $this->http->requestRaw('post', '/v1/audio/transcriptions', $request);
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
