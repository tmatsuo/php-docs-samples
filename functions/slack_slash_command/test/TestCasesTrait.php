<?php
/**
 * Copyright 2020 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types=1);

namespace Google\Cloud\Samples\Functions\SlackSlashCommand\Test;

function _valid_headers($body): array {

    // Calculate test case signature
    $key = getenv('SLACK_SECRET');
    $plaintext = 'v0:0:' . $body;
    $hash = 'v0=' . hash_hmac('sha256', $plaintext, $key);

    // Return new test case
    return [
        'X-Slack-Request-Timestamp' => '0',
        'X-Slack-Signature' => $hash,
    ];
}

trait TestCasesTrait
{
    public static function cases(): array
    {
        $SLACK_SIGNATURE = getenv('SLACK_TEST_SIGNATURE');

        return [
            // Only allows POST
            [
                'body' => '',
                'method' => 'GET',
                'expected' => null,
                'statusCode' => '405',
                'headers' => _valid_headers(''),
            ],

            // Requires valid auth headers
            [
                'body' => 'text=foo',
                'method' => 'POST',
                'expected' => null,
                'statusCode' => '403',
                'headers' => [],
            ],

            // Doesn't allow blank body
            [
                'body' => '',
                'method' => 'POST',
                'expected' => null,
                'statusCode' => '400',
                'headers' => _valid_headers(''),
            ],

            // Prohibits invalid signature
            [
                'body' => 'text=foo',
                'method' => 'POST',
                'expected' => null,
                'statusCode' => '403',
                'headers' => [
                    'X-Slack-Request-Timestamp' => '0',
                    'X-Slack-Signature' => 
                    'bad_signature'
                ],
            ],

            // Handles no-result query
            [
                'body' => 'text=asdfjkl13579',
                'method' => 'POST',
                'expected' => 'No results match your query',
                'statusCode' => '200',
                'headers' => _valid_headers('text=asdfjkl13579'),
            ],

            // Handles query with results
            [
                'body' => 'text=lion',
                'method' => 'POST',
                'expected' => 'https://en.wikipedia.org/wiki/Lion',
                'statusCode' => '200',
                'headers' => _valid_headers('text=lion'),
            ],

            // Ignores extra URL parameters
            [
                'body' => 'unused=foo&text=lion',
                'method' => 'POST',
                'expected' => 'https://en.wikipedia.org/wiki/Lion',
                'statusCode' => '200',
                'headers' => _valid_headers('unused=foo&text=lion'),
            ],
        ];
    }
}
