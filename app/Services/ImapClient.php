<?php

namespace App\Services;

/**
 * Lightweight IMAP client using PHP sockets.
 * No php-imap extension or external composer package required.
 *
 * Supports SSL/TLS connections via stream_socket_client.
 */
class ImapClient
{
    private $stream = null;
    private string $host;
    private int $port;
    private string $encryption;
    private int $tagCounter = 0;
    private ?string $lastError = null;

    public function __construct(string $host, int $port, string $encryption = 'ssl')
    {
        $this->host = $host;
        $this->port = $port;
        $this->encryption = strtolower($encryption);
    }

    /**
     * Connect and authenticate to the IMAP server.
     */
    public function connect(string $username, string $password): bool
    {
        $this->lastError = null;

        // Build connection string
        if ($this->encryption === 'ssl') {
            $connectionString = "ssl://{$this->host}:{$this->port}";
        } elseif ($this->encryption === 'tls') {
            $connectionString = "tcp://{$this->host}:{$this->port}";
        } else {
            $connectionString = "tcp://{$this->host}:{$this->port}";
        }

        $errno = 0;
        $errstr = '';

        $this->stream = @stream_socket_client(
            $connectionString,
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT
        );

        if (!$this->stream) {
            $this->lastError = "Connection failed: {$errstr} ({$errno})";
            return false;
        }

        stream_set_timeout($this->stream, 30);

        // Read server greeting
        $greeting = $this->readLine();
        if ($greeting === false || !str_starts_with($greeting, '* OK')) {
            $this->lastError = "Server greeting failed: " . ($greeting ?: 'No response');
            $this->disconnect();
            return false;
        }

        // If TLS, upgrade the connection with STARTTLS
        if ($this->encryption === 'tls') {
            $response = $this->sendCommand('STARTTLS');
            if (!$this->isResponseOk($response)) {
                $this->lastError = "STARTTLS failed: {$response}";
                $this->disconnect();
                return false;
            }

            // Enable crypto
            if (!@stream_socket_enable_crypto($this->stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                $this->lastError = "TLS negotiation failed";
                $this->disconnect();
                return false;
            }
        }

        // Authenticate
        $response = $this->sendCommand('LOGIN ' . $this->escape($username) . ' ' . $this->escape($password));
        if (!$this->isResponseOk($response)) {
            $this->lastError = "Login failed: {$response}";
            $this->disconnect();
            return false;
        }

        return true;
    }

    /**
     * Select a mailbox folder.
     */
    public function selectFolder(string $folder = 'INBOX'): ?array
    {
        $response = $this->sendCommand('SELECT ' . $this->escape($folder));

        if (!$this->isResponseOk($response)) {
            $this->lastError = "Failed to select folder: {$response}";
            return null;
        }

        $info = [];
        if (preg_match('/(\d+) EXISTS/', $response, $m)) {
            $info['exists'] = (int) $m[1];
        }
        if (preg_match('/(\d+) RECENT/', $response, $m)) {
            $info['recent'] = (int) $m[1];
        }
        if (preg_match('/UNSEEN (\d+)/', $response, $m)) {
            $info['unseen'] = (int) $m[1];
        }

        return $info;
    }

    /**
     * Search for messages. Returns array of message sequence numbers or UIDs.
     */
    public function search(string $criteria = 'UNSEEN', bool $useUid = true): array
    {
        $command = ($useUid ? 'UID ' : '') . 'SEARCH ' . $criteria;
        $response = $this->sendCommand($command);

        if (!$this->isResponseOk($response)) {
            $this->lastError = "Search failed: {$response}";
            return [];
        }

        // Parse search results from * SEARCH ...
        $results = [];
        if (preg_match('/\* SEARCH\s+(.*)/i', $response, $matches)) {
            $results = array_map('intval', preg_split('/\s+/', trim($matches[1])));
        }

        return $results;
    }

    /**
     * Fetch message headers for a given message number/UID.
     */
    public function fetchHeaders(int $messageNum, bool $isUid = true): ?array
    {
        $prefix = $isUid ? 'UID ' : '';
        $command = $prefix . 'FETCH ' . $messageNum . ' (BODY[HEADER.FIELDS (SUBJECT FROM TO CC DATE MESSAGE-ID)])';
        $response = $this->sendCommand($command);

        if (!$this->isResponseOk($response)) {
            return null;
        }

        return $this->parseHeaders($response);
    }

    /**
     * Fetch full message structure.
     */
    public function fetchStructure(int $messageNum, bool $isUid = true): ?array
    {
        $prefix = $isUid ? 'UID ' : '';
        $command = $prefix . 'FETCH ' . $messageNum . ' BODYSTRUCTURE';
        $response = $this->sendCommand($command);

        if (!$this->isResponseOk($response)) {
            return null;
        }

        return $this->parseStructure($response);
    }

    /**
     * Fetch a specific body part.
     */
    public function fetchBody(int $messageNum, string $section = '1', bool $isUid = true): ?string
    {
        $prefix = $isUid ? 'UID ' : '';
        $command = $prefix . 'FETCH ' . $messageNum . ' BODY[' . $section . ']';
        $response = $this->sendCommand($command);

        if (!$this->isResponseOk($response)) {
            return null;
        }

        return $this->extractBodyFromResponse($response);
    }

    /**
     * Fetch the entire message body (tries TEXT then HTML parts).
     */
    public function fetchMessageBodies(int $messageNum, bool $isUid = true): array
    {
        $prefix = $isUid ? 'UID ' : '';

        // First get the structure to find parts
        $structure = $this->fetchStructure($messageNum, $isUid);

        $bodies = ['html' => '', 'text' => ''];

        if ($structure && !empty($structure['parts'])) {
            foreach ($structure['parts'] as $partNum => $part) {
                $section = (string) ($partNum + 1);
                $subtype = strtoupper($part['subtype'] ?? '');

                if ($part['type'] === 'TEXT' && $subtype === 'PLAIN') {
                    $body = $this->fetchBody($messageNum, $section, $isUid);
                    if ($body !== null) {
                        $bodies['text'] = $this->decodeBody($body, $part['encoding'] ?? '');
                    }
                } elseif ($part['type'] === 'TEXT' && $subtype === 'HTML') {
                    $body = $this->fetchBody($messageNum, $section, $isUid);
                    if ($body !== null) {
                        $bodies['html'] = $this->decodeBody($body, $part['encoding'] ?? '');
                    }
                }

                // Handle nested multipart
                if (($part['type'] ?? '') === 'MULTIPART' && !empty($part['parts'])) {
                    foreach ($part['parts'] as $subPartNum => $subPart) {
                        $subSection = $section . '.' . ($subPartNum + 1);
                        $subSubtype = strtoupper($subPart['subtype'] ?? '');

                        if ($subPart['type'] === 'TEXT' && $subSubtype === 'PLAIN' && empty($bodies['text'])) {
                            $body = $this->fetchBody($messageNum, $subSection, $isUid);
                            if ($body !== null) {
                                $bodies['text'] = $this->decodeBody($body, $subPart['encoding'] ?? '');
                            }
                        } elseif ($subPart['type'] === 'TEXT' && $subSubtype === 'HTML' && empty($bodies['html'])) {
                            $body = $this->fetchBody($messageNum, $subSection, $isUid);
                            if ($body !== null) {
                                $bodies['html'] = $this->decodeBody($body, $subPart['encoding'] ?? '');
                            }
                        }
                    }
                }
            }
        } else {
            // Simple message - just fetch body section 1 or empty
            $command = $prefix . 'FETCH ' . $messageNum . ' BODY[1]';
            $response = $this->sendCommand($command);
            if ($this->isResponseOk($response)) {
                $body = $this->extractBodyFromResponse($response);
                if ($body !== null) {
                    $bodies['text'] = $this->decodeBody($body, $structure['encoding'] ?? '7BIT');
                }
            }

            // If no text from section 1, try the whole body
            if (empty($bodies['text'])) {
                $command = $prefix . 'FETCH ' . $messageNum . ' BODY[]';
                $response = $this->sendCommand($command);
                if ($this->isResponseOk($response)) {
                    $fullBody = $this->extractBodyFromResponse($response);
                    if ($fullBody !== null) {
                        // Try to extract text after headers
                        $headerEnd = strpos($fullBody, "\r\n\r\n");
                        if ($headerEnd !== false) {
                            $fullBody = substr($fullBody, $headerEnd + 4);
                        }
                        $bodies['text'] = $fullBody;
                    }
                }
            }
        }

        // If no plain text but have HTML, strip tags
        if (empty($bodies['text']) && !empty($bodies['html'])) {
            $bodies['text'] = strip_tags($bodies['html']);
        }

        return $bodies;
    }

    /**
     * Fetch message flags.
     */
    public function fetchFlags(int $messageNum, bool $isUid = true): array
    {
        $prefix = $isUid ? 'UID ' : '';
        $command = $prefix . 'FETCH ' . $messageNum . ' FLAGS';
        $response = $this->sendCommand($command);

        $flags = [];
        if ($this->isResponseOk($response) && preg_match('/FLAGS \(([^)]+)\)/', $response, $m)) {
            $flags = array_map('trim', preg_split('/\s+/', $m[1]));
        }

        return $flags;
    }

    /**
     * Get all email details for syncing.
     */
    public function fetchMessageForSync(int $messageNum, bool $isUid = true): ?array
    {
        $headers = $this->fetchHeaders($messageNum, $isUid);
        if ($headers === null) {
            return null;
        }

        $bodies = $this->fetchMessageBodies($messageNum, $isUid);

        return [
            'message_id' => $headers['message_id'] ?? uniqid(),
            'subject' => $headers['subject'] ?? '(No Subject)',
            'from_name' => $headers['from_name'] ?? '',
            'from_email' => $headers['from_email'] ?? '',
            'to_email' => $headers['to_email'] ?? '',
            'cc' => $headers['cc'] ?? [],
            'date' => $headers['date'] ?? date('Y-m-d H:i:s'),
            'body_html' => $bodies['html'] ?? '',
            'body_text' => $bodies['text'] ?? '',
        ];
    }

    /**
     * Disconnect from the IMAP server.
     */
    public function disconnect(): void
    {
        if ($this->stream) {
            $this->sendCommand('LOGOUT');
            @fclose($this->stream);
            $this->stream = null;
        }
    }

    /**
     * Get the last error message.
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    // ─── Private helper methods ──────────────────────────────────

    private function sendCommand(string $command): string
    {
        if (!$this->stream) {
            return '';
        }

        $tag = 'TAG' . ++$this->tagCounter;
        $fullCommand = $tag . ' ' . $command . "\r\n";

        @fwrite($this->stream, $fullCommand);

        $response = '';
        while (($line = $this->readLine()) !== false) {
            $response .= $line . "\n";
            // Stop when we see the tagged response
            if (str_starts_with($line, $tag . ' ')) {
                break;
            }
        }

        return $response;
    }

    private function readLine(): string|false
    {
        if (!$this->stream) {
            return false;
        }

        $line = @fgets($this->stream, 8192);
        return $line !== false ? rtrim($line, "\r\n") : false;
    }

    private function isResponseOk(string $response): bool
    {
        return (bool) preg_match('/TAG\d+\s+OK/i', $response);
    }

    private function escape(string $str): string
    {
        // If string contains special chars, use literal syntax
        if (preg_match('/[\x00-\x1f\x7f*\\"(){}]/', $str)) {
            return '{' . strlen($str) . "}\r\n" . $str;
        }
        return '"' . addcslashes($str, '\\"') . '"';
    }

    private function parseHeaders(string $response): array
    {
        $headers = [];

        // Extract the header block from the response
        // Look for content between {size} and the tag
        if (preg_match('/\{(\d+)\}\r?\n(.*?)TAG\d/s', $response, $m)) {
            $headerBlock = $m[2];
        } else {
            // Try to extract from * FETCH response
            $headerBlock = $response;
        }

        // Decode MIME encoded words
        $headerBlock = $this->decodeMimeHeader($headerBlock);

        // Parse individual header fields
        if (preg_match('/^Subject:\s*(.+)$/mi', $headerBlock, $m)) {
            $headers['subject'] = trim($m[1]);
        }
        if (preg_match('/^From:\s*(.+)$/mi', $headerBlock, $m)) {
            $parsed = $this->parseEmailAddress($m[1]);
            $headers['from_name'] = $parsed['name'];
            $headers['from_email'] = $parsed['email'];
        }
        if (preg_match('/^To:\s*(.+)$/mi', $headerBlock, $m)) {
            $parsed = $this->parseEmailAddress($m[1]);
            $headers['to_email'] = $parsed['email'];
        }
        if (preg_match('/^Cc:\s*(.+)$/mi', $headerBlock, $m)) {
            $headers['cc'] = $this->parseEmailList($m[1]);
        }
        if (preg_match('/^Date:\s*(.+)$/mi', $headerBlock, $m)) {
            $headers['date'] = date('Y-m-d H:i:s', strtotime(trim($m[1])));
        }
        if (preg_match('/^Message-ID:\s*(.+)$/mi', $headerBlock, $m)) {
            $headers['message_id'] = trim($m[1]);
        }

        return $headers;
    }

    private function parseStructure(string $response): array
    {
        $structure = [
            'type' => 'TEXT',
            'subtype' => 'PLAIN',
            'parts' => [],
            'encoding' => '7BIT',
        ];

        // Try to find BODYSTRUCTURE in the response
        if (!preg_match('/BODYSTRUCTURE\s+\(/i', $response)) {
            return $structure;
        }

        // Simple parsing - detect if multipart
        if (preg_match('/BODYSTRUCTURE\s+\(\(/i', $response)) {
            // Multipart message
            $structure['type'] = 'MULTIPART';

            // Find the multipart subtype (usually at the end before the closing parens)
            if (preg_match('/"mixed"|"alternative"|"related"|"plain"|"html"/i', $response, $m)) {
                $structure['subtype'] = strtoupper(trim($m[0], '"'));
            }

            // Count top-level parts
            $partCount = preg_match_all('/\("(TEXT|APPLICATION|IMAGE|AUDIO|VIDEO|MULTIPART)"/i', $response, $partMatches);
            $partTypes = $partMatches[1] ?? [];

            // Try to extract part info
            if (preg_match_all('/"((?:PLAIN|HTML|PDF|JPEG|PNG|GIF|ZIP|OCTET-STREAM)[^"]*)"/i', $response, $subtypeMatches)) {
                $subtypes = $subtypeMatches[1];
                $encodings = [];
                if (preg_match_all('/"(7BIT|8BIT|QUOTED-PRINTABLE|BASE64|BINARY)"/i', $response, $encMatches)) {
                    $encodings = $encMatches[1];
                }

                foreach ($subtypes as $i => $subtype) {
                    $partInfo = [
                        'type' => strtoupper($subtype) === 'HTML' || strtoupper($subtype) === 'PLAIN' ? 'TEXT' : 'APPLICATION',
                        'subtype' => strtoupper($subtype),
                        'encoding' => $encodings[$i] ?? '7BIT',
                    ];
                    $structure['parts'][] = $partInfo;
                }
            }
        } else {
            // Simple single-part message
            if (preg_match('/"((?:PLAIN|HTML)[^"]*)"/i', $response, $m)) {
                $structure['subtype'] = strtoupper($m[1]);
            }
            if (preg_match('/"(7BIT|8BIT|QUOTED-PRINTABLE|BASE64|BINARY)"/i', $response, $m)) {
                $structure['encoding'] = strtoupper($m[1]);
            }
        }

        return $structure;
    }

    private function extractBodyFromResponse(string $response): ?string
    {
        // The body is between {size}\n and the tag response
        if (preg_match('/\{(\d+)\}\r?\n(.*)TAG\d/s', $response, $m)) {
            $expectedLen = (int) $m[1];
            $body = $m[2];
            // Remove trailing closing parenthesis and CRLF before tag
            $body = preg_replace('/\s*\)\s*$/s', '', $body);
            return substr($body, 0, $expectedLen);
        }

        // Fallback: try to extract between quotes or literal markers
        if (preg_match('/"((?:[^"\\\\]|\\\\.)*)"/s', $response, $m)) {
            return stripcslashes($m[1]);
        }

        return null;
    }

    private function decodeBody(string $body, string $encoding): string
    {
        $encoding = strtoupper($encoding);

        if ($encoding === 'BASE64') {
            $decoded = base64_decode($body, true);
            return $decoded !== false ? $decoded : $body;
        }

        if ($encoding === 'QUOTED-PRINTABLE') {
            return quoted_printable_decode($body);
        }

        return $body;
    }

    private function decodeMimeHeader(string $header): string
    {
        // Decode =?charset?B/Q?encoded_text?= patterns
        return preg_replace_callback(
            '/=\?([^?]+)\?([BQbq])\?([^?]+)\?=/',
            function ($matches) {
                $charset = $matches[1];
                $encoding = strtoupper($matches[2]);
                $text = $matches[3];

                if ($encoding === 'B') {
                    $decoded = base64_decode($text);
                } else {
                    $decoded = quoted_printable_decode(str_replace('_', ' ', $text));
                }

                if ($charset && $charset !== 'UTF-8') {
                    $decoded = @mb_convert_encoding($decoded, 'UTF-8', $charset);
                }

                return $decoded;
            },
            $header
        );
    }

    private function parseEmailAddress(string $addr): array
    {
        $addr = trim($addr);
        $name = '';
        $email = '';

        // Format: "Name" <email@domain.com>
        if (preg_match('/^(.+?)\s*<([^>]+)>/', $addr, $m)) {
            $name = trim($m[1], '"\' ');
            $email = trim($m[2]);
        }
        // Format: <email@domain.com>
        elseif (preg_match('/<([^>]+)>/', $addr, $m)) {
            $email = trim($m[1]);
        }
        // Format: email@domain.com
        elseif (preg_match('/[\w.+-]+@[\w.-]+/', $addr, $m)) {
            $email = $m[0];
        }

        return ['name' => $name, 'email' => $email];
    }

    private function parseEmailList(string $list): array
    {
        $emails = [];

        // Split by comma, parse each address
        $parts = preg_split('/,\s*(?=(?:[^"]*"[^"]*")*[^"]*$)/', $list);

        foreach ($parts as $part) {
            $parsed = $this->parseEmailAddress(trim($part));
            if (!empty($parsed['email'])) {
                $emails[] = $parsed['email'];
            }
        }

        return $emails;
    }

    /**
     * Destructor - ensure connection is closed.
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}
