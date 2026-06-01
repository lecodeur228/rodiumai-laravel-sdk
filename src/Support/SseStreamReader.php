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
