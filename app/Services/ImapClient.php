<?php

namespace App\Services;

/**
 * Lightweight IMAP client using PHP sockets.
 * No php-imap extension or external composer package required.
 *
 * Supports SSL/TLS connections via stream_socket_client.
 * Uses AUTHENTICATE PLAIN for reliable credential handling
 * (avoids quoting/escaping issues with the LOGIN command).
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
            $this->lastError = "Connection failed: {$errstr} ({$errno}). Check that the IMAP host and port are correct.";
            return false;
        }

        stream_set_timeout($this->stream, 30);

        // Read server greeting
        $greeting = $this->readLine();
        if ($greeting === false || !str_starts_with($greeting, '* OK')) {
            $this->lastError = "Server greeting failed: " . ($greeting ?: 'No response from server. Check host/port/encryption settings.');
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
                $this->lastError = "TLS negotiation failed. Try using SSL encryption instead of TLS.";
                $this->disconnect();
                return false;
            }
        }

        // Authenticate using AUTHENTICATE PLAIN (SASL)
        // This is more reliable than LOGIN because it sends credentials in base64,
        // avoiding all quoting/escaping issues with special characters.
        $authResult = $this->authenticatePlain($username, $password);

        if (!$authResult) {
            // Fallback: try LOGIN command with proper literal continuation protocol
            $authResult = $this->authenticateLogin($username, $password);
        }

        if (!$authResult) {
            // Error is already set by the auth methods
            $this->disconnect();
            return false;
        }

        return true;
    }

    /**
     * Authenticate using SASL PLAIN mechanism.
     * Sends base64-encoded \0username\0password after server continuation.
     */
    private function authenticatePlain(string $username, string $password): bool
    {
        if (!$this->stream) {
            return false;
        }

        $tag = 'TAG' . ++$this->tagCounter;

        // Send AUTHENTICATE PLAIN
        @fwrite($this->stream, $tag . " AUTHENTICATE PLAIN\r\n");

        // Read continuation response (+ ...)
        $line = $this->readLine();
        if ($line === false || !str_starts_with($line, '+')) {
            // Server doesn't support AUTHENTICATE PLAIN
            return false;
        }

        // Send base64-encoded credentials: \0username\0password
        $credentials = base64_encode("\x00" . $username . "\x00" . $password);
        @fwrite($this->stream, $credentials . "\r\n");

        // Read response
        $response = '';
        while (($line = $this->readLine()) !== false) {
            $response .= $line . "\n";
            if (str_starts_with($line, $tag . ' ')) {
                break;
            }
        }

        if ($this->isResponseOk($response)) {
            return true;
        }

        $this->lastError = $this->parseAuthError($response);
        return false;
    }

    /**
     * Authenticate using LOGIN command with proper IMAP literal continuation.
     * This handles the + continuation protocol correctly.
     */
    private function authenticateLogin(string $username, string $password): bool
    {
        if (!$this->stream) {
            return false;
        }

        $tag = 'TAG' . ++$this->tagCounter;

        // Send LOGIN with literal for username
        @fwrite($this->stream, $tag . " LOGIN {" . strlen($username) . "}\r\n");

        // Wait for continuation (+)
        $line = $this->readLine();
        if ($line === false || !str_starts_with($line, '+')) {
            $this->lastError = "Login failed: server does not support literal continuation.";
            return false;
        }

        // Send username, then literal for password
        @fwrite($this->stream, $username . " {" . strlen($password) . "}\r\n");

        // Wait for continuation (+)
        $line = $this->readLine();
        if ($line === false || !str_starts_with($line, '+')) {
            $this->lastError = "Login failed: server does not support literal continuation for password.";
            return false;
        }

        // Send password
        @fwrite($this->stream, $password . "\r\n");

        // Read response
        $response = '';
        while (($line = $this->readLine()) !== false) {
            $response .= $line . "\n";
            if (str_starts_with($line, $tag . ' ')) {
                break;
            }
        }

        if ($this->isResponseOk($response)) {
            return true;
        }

        $this->lastError = $this->parseAuthError($response);
        return false;
    }

    /**
     * Parse an authentication error response into a user-friendly message.
     */
    private function parseAuthError(string $response): string
    {
        // Extract the error code/message from the tagged response
        $errorMsg = $response;

        // Remove the tag prefix
        $errorMsg = preg_replace('/^TAG\d+\s+(NO|BAD)\s*/i', '', $errorMsg);
        $errorMsg = trim($errorMsg);

        // Check for common Gmail-specific errors
        if (str_contains($errorMsg, 'AUTHENTICATIONFAILED') || str_contains($errorMsg, 'Invalid credentials')) {
            return "Authentication failed: Invalid username or password. "
                . "For Gmail, you must use an App Password (not your regular password). "
                . "Steps: 1) Enable 2-Step Verification in your Google Account, "
                . "2) Go to Security > App Passwords, "
                . "3) Generate a new App Password for 'Mail', "
                . "4) Use that 16-character password here.";
        }

        if (str_contains($errorMsg, 'Too many login') || str_contains($errorMsg, 'temporarily locked')) {
            return "Authentication failed: Account temporarily locked due to too many failed login attempts. Please try again later.";
        }

        if (str_contains($errorMsg, 'Application-specific password required')) {
            return "Authentication failed: You need to use an App Password. "
                . "Go to your Google Account > Security > App Passwords to generate one.";
        }

        if (str_contains($errorMsg, 'Web login required')) {
            return "Authentication failed: Web login required. Please log in to your email account via browser first, then try again.";
        }

        return "Login failed: {$errorMsg}";
    }

    /**
     * Select a mailbox folder.
     */
    public function selectFolder(string $folder = 'INBOX'): ?array
    {
        $response = $this->sendCommand('SELECT "' . addcslashes($folder, '\\"') . '"');

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
     * Test the connection without syncing. Returns folder info on success.
     */
    public function testConnection(string $username, string $password, string $folder = 'INBOX'): ?array
    {
        if (!$this->connect($username, $password)) {
            return null;
        }

        $info = $this->selectFolder($folder);
        $this->disconnect();

        return $info;
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
