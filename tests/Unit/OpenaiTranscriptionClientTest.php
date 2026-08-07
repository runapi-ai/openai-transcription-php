<?php

declare(strict_types=1);

namespace RunApi\OpenaiTranscription\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use RunApi\Core\ClientOptions;
use RunApi\Core\Tests\Fixtures\QueueHttpClient;
use RunApi\OpenaiTranscription\OpenaiTranscriptionClient;

final class OpenaiTranscriptionClientTest extends TestCase
{
    public function testPostsRepeatedMultipartFieldsAndPreservesRawText(): void
    {
        $audio = tempnam(sys_get_temp_dir(), 'runapi-audio-') . '.mp3';
        file_put_contents($audio, 'audio');
        $transport = new QueueHttpClient([new Response(200, ['Content-Type' => 'text/plain'], "hello from audio\n")]);
        $client = new OpenaiTranscriptionClient(new ClientOptions(apiKey: 'k', httpClient: $transport, maxRetries: 0));

        $result = $client->speechToText->run($audio, ['model' => 'gpt-transcribe', 'languages' => ['en', 'zh'], 'keywords' => ['RunAPI', 'release notes'], 'response_format' => 'text']);

        $body = (string) $transport->requests[0]->getBody();
        self::assertSame('/v1/audio/transcriptions', $transport->requests[0]->getUri()->getPath());
        self::assertSame(2, substr_count($body, 'name="languages[]"'));
        self::assertSame(2, substr_count($body, 'name="keywords[]"'));
        self::assertSame("hello from audio\n", $result);
    }

    public function testDefaultsModelAndPreservesJson(): void
    {
        $audio = tempnam(sys_get_temp_dir(), 'runapi-audio-') . '.wav';
        file_put_contents($audio, 'audio');
        $transport = new QueueHttpClient([new Response(200, ['Content-Type' => 'application/json'], '{"text":"hello"}')]);
        $client = new OpenaiTranscriptionClient(new ClientOptions(apiKey: 'k', httpClient: $transport, maxRetries: 0));

        self::assertSame(['text' => 'hello'], $client->speechToText->run($audio));
        self::assertStringContainsString("\r\nwhisper-1\r\n", (string) $transport->requests[0]->getBody());
    }
}
