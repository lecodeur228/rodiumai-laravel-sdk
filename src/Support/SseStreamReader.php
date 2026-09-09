<?php

namespace RodiumAI\Support;

use Generator;
use Psr\Http\Message\StreamInterface;

/**
 * Parses Server-Sent Events from POST /v1/chat/completions when stream=true.
 *
 * @see https://www.rodiumai.io/docs/api/streaming
 */
final class SseStreamReader
{
    /**
     * @return Generator<string>
     */
    public function readTextDeltas(StreamInterface $body): Generator
    {
        while (! $body->eof()) {
            $line = $this->readLine($body);

            if ($line === '' || ! str_starts_with($line, 'data: ')) {
                continue;
            }

            $data = substr($line, 6);

            if (trim($data) === '[DONE]') {
                return;
            }

            $chunk = json_decode($data, true);
            $delta = $chunk['choices'][0]['delta']['content'] ?? null;

            if ($delta !== null) {
                yield $delta;
            }
        }
    }

    /**
     * Parses the Anthropic Messages streaming protocol (POST /v1/messages,
     * stream=true) and yields the incremental text.
     *
     * Anthropic emits named events (`message_start`, `content_block_delta`
     * carrying `delta.text`, `message_stop`, …) and, unlike the OpenAI chat
     * stream, has no `[DONE]` sentinel — iteration ends when the body is
     * exhausted.
     *
     * @return Generator<string>
     */
    public function readAnthropicTextDeltas(StreamInterface $body): Generator
    {
        while (! $body->eof()) {
            $line = $this->readLine($body);

            if ($line === '' || ! str_starts_with($line, 'data: ')) {
                continue;
            }

            $data = substr($line, 6);

            if (trim($data) === '[DONE]') {
                return;
            }

            $event = json_decode($data, true);

            if (! is_array($event) || ($event['type'] ?? null) !== 'content_block_delta') {
                continue;
            }

            $delta = $event['delta']['text'] ?? null;

            if (is_string($delta) && $delta !== '') {
                yield $delta;
            }
        }
    }

    /**
     * Parses the OpenAI Responses streaming protocol (POST /v1/responses,
     * stream=true) and yields the incremental text from
     * `response.output_text.delta` events.
     *
     * @return Generator<string>
     */
    public function readResponsesTextDeltas(StreamInterface $body): Generator
    {
        while (! $body->eof()) {
            $line = $this->readLine($body);

            if ($line === '' || ! str_starts_with($line, 'data: ')) {
                continue;
            }

            $data = substr($line, 6);

            if (trim($data) === '[DONE]') {
                return;
            }

            $event = json_decode($data, true);

            if (! is_array($event) || ($event['type'] ?? null) !== 'response.output_text.delta') {
                continue;
            }

            $delta = $event['delta'] ?? null;

            if (is_string($delta) && $delta !== '') {
                yield $delta;
            }
        }
    }

    private function readLine(StreamInterface $stream): string
    {
        $line = '';

        while (! $stream->eof()) {
            $char = $stream->read(1);
            if ($char === "\n") {
                break;
            }
            $line .= $char;
        }

        return rtrim($line, "\r");
    }
}
